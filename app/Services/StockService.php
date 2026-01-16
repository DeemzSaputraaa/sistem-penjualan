<?php

namespace App\Services;

use App\Models\Sparepart;
use App\Models\StockLog;
use App\Models\User;
use App\Services\AuditLogService;
use InvalidArgumentException;

class StockService
{
    public function __construct(private readonly AuditLogService $auditLog)
    {
    }

    public function adjust(
        Sparepart $sparepart,
        int $deltaQty,
        string $type,
        ?string $refType = null,
        ?int $refId = null,
        ?User $user = null,
        ?string $notes = null,
        ?string $unitPrice = null
    ): void {
        $before = $sparepart->stock;
        $after = $before + $deltaQty;

        if ($after < 0) {
            throw new InvalidArgumentException('Insufficient stock for ' . $sparepart->sku);
        }

        $sparepart->update(['stock' => $after]);

        $log = StockLog::create([
            'sparepart_id' => $sparepart->id,
            'user_id' => $user?->id,
            'ref_type' => $refType,
            'ref_id' => $refId,
            'type' => $type,
            'qty' => abs($deltaQty),
            'before_stock' => $before,
            'after_stock' => $after,
            'unit_price' => $unitPrice,
            'notes' => $notes,
        ]);

        $this->auditLog->log(
            'stock.adjust',
            $log,
            $user,
            null,
            [
                'sparepart_id' => $sparepart->id,
                'before_stock' => $before,
                'after_stock' => $after,
                'qty' => $deltaQty,
                'type' => $type,
                'ref_type' => $refType,
                'ref_id' => $refId,
                'unit_price' => $unitPrice,
                'notes' => $notes,
            ],
            null
        );
    }
}
