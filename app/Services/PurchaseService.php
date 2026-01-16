<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sparepart;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PurchaseService
{
    public function __construct(private readonly StockService $stockService)
    {
    }

    public function create(array $payload, ?int $userId = null): Purchase
    {
        return DB::transaction(function () use ($payload, $userId): Purchase {
            $purchase = Purchase::create([
                'purchase_no' => $payload['purchase_no'],
                'supplier_id' => $payload['supplier_id'] ?? null,
                'user_id' => $userId,
                'status' => $payload['status'] ?? 'received',
                'notes' => $payload['notes'] ?? null,
                'purchased_at' => $payload['purchased_at'] ?? now(),
            ]);

            $total = 0;
            $status = $payload['status'] ?? 'received';

            foreach ($payload['items'] as $item) {
                $sparepart = Sparepart::lockForUpdate()->findOrFail($item['sparepart_id']);
                $qty = (int) $item['qty'];
                $price = (float) $item['price'];
                $subtotal = $qty * $price;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'sparepart_id' => $sparepart->id,
                    'qty' => $qty,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ]);

                $total += $subtotal;

                if ($status === 'received') {
                    $this->stockService->adjust(
                        $sparepart,
                        $qty,
                        'in',
                        'purchase',
                        $purchase->id,
                        $userId,
                        $payload['notes'] ?? null,
                        (string) $price
                    );
                    $sparepart->update(['price_buy' => $price]);
                }
            }

            $purchase->update([
                'total' => $total,
                'received_at' => $status === 'received' ? now() : null,
            ]);

            return $purchase->load('items.sparepart', 'supplier');
        });
    }

    public function receive(Purchase $purchase, ?int $userId = null): Purchase
    {
        if ($purchase->status === 'received') {
            throw new InvalidArgumentException('Purchase already received.');
        }

        return DB::transaction(function () use ($purchase, $userId): Purchase {
            $purchase->load('items');

            foreach ($purchase->items as $item) {
                $sparepart = Sparepart::lockForUpdate()->findOrFail($item->sparepart_id);

                $this->stockService->adjust(
                    $sparepart,
                    $item->qty,
                    'in',
                    'purchase',
                    $purchase->id,
                    $userId,
                    $purchase->notes,
                    (string) $item->price
                );

                $sparepart->update(['price_buy' => $item->price]);
            }

            $purchase->update([
                'status' => 'received',
                'received_at' => now(),
            ]);

            return $purchase->load('items.sparepart', 'supplier', 'user');
        });
    }
}
