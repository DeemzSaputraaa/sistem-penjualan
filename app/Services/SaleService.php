<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Sparepart;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SaleService
{
    public function __construct(private readonly StockService $stockService)
    {
    }

    public function create(array $payload, ?int $userId = null): Sale
    {
        return DB::transaction(function () use ($payload, $userId): Sale {
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
                $price = (float) $item['price'];
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
                    $userId,
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

            return $sale->load('items.sparepart', 'customer');
        });
    }
}
