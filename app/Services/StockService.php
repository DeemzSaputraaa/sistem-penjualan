<?php

namespace App\Services;

use App\Models\Sparepart;
use App\Models\StockLog;
use InvalidArgumentException;

class StockService
{
    public function adjust(
        Sparepart $sparepart,
        int $deltaQty,
        string $type,
        ?string $refType = null,
        ?int $refId = null,
        ?int $userId = null,
        ?string $notes = null,
        ?string $unitPrice = null
    ): void {
        $before = $sparepart->stock;
        $after = $before + $deltaQty;

        if ($after < 0) {
            throw new InvalidArgumentException('Insufficient stock for ' . $sparepart->sku);
        }

        $sparepart->update(['stock' => $after]);

        StockLog::create([
            'sparepart_id' => $sparepart->id,
            'user_id' => $userId,
            'ref_type' => $refType,
            'ref_id' => $refId,
            'type' => $type,
            'qty' => abs($deltaQty),
            'before_stock' => $before,
            'after_stock' => $after,
            'unit_price' => $unitPrice,
            'notes' => $notes,
        ]);
    }
}
