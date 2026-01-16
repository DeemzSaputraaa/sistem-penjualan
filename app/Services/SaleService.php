<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Sparepart;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SaleService
{
    public function __construct(
        private readonly StockService $stockService,
        private readonly AuditLogService $auditLog
    )
    {
    }

    public function create(array $payload, ?User $user = null): Sale
    {
        $userId = $user?->id;
        $canEditPrice = $user?->hasPermission('manage-pricing') ?? false;

        return DB::transaction(function () use ($payload, $userId, $canEditPrice, $user): Sale {
            $sale = Sale::create([
                'invoice_no' => $payload['invoice_no'],
                'customer_id' => $payload['customer_id'] ?? null,
                'user_id' => $userId,
                'sold_at' => $payload['sold_at'] ?? now(),
            ]);

            $total = 0;

            foreach ($payload['items'] as $item) {
                $sparepart = Sparepart::lockForUpdate()->findOrFail($item['sparepart_id']);
                $qty = (int) $item['qty'];
                $price = $canEditPrice ? (float) $item['price'] : (float) $sparepart->price_sell;
                $cost = (float) $sparepart->price_buy;

                if ($sparepart->stock < $qty) {
                    throw new InvalidArgumentException('Stock not enough for ' . $sparepart->sku);
                }

                $subtotal = $qty * $price;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'sparepart_id' => $sparepart->id,
                    'qty' => $qty,
                    'price' => $price,
                    'cost' => $cost,
                    'subtotal' => $subtotal,
                ]);

                $total += $subtotal;

                $this->stockService->adjust(
                    $sparepart,
                    $qty * -1,
                    'out',
                    'sale',
                    $sale->id,
                    $user,
                    $payload['notes'] ?? null,
                    (string) $price
                );
            }

                $paid = (float) ($payload['paid'] ?? 0);
                $change = max(0, $paid - $total);

            $sale->update([
                'total' => $total,
                'paid' => $paid,
                'change' => $change,
            ]);

            $this->auditLog->log(
                'sale.create',
                $sale,
                $user,
                null,
                null,
                [
                    'total' => $total,
                    'paid' => $paid,
                    'change' => $change,
                    'items' => $payload['items'],
                ]
            );

            return $sale->load('items.sparepart', 'customer');
        });
    }
}
