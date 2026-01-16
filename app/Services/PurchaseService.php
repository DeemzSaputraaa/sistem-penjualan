<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sparepart;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PurchaseService
{
    public function __construct(
        private readonly StockService $stockService,
        private readonly AuditLogService $auditLog
    )
    {
    }

    public function create(array $payload, ?User $user = null): Purchase
    {
        $userId = $user?->id;

        return DB::transaction(function () use ($payload, $userId, $user): Purchase {
            $status = $payload['status'] ?? 'draft';

            if (! in_array($status, ['draft', 'ordered'], true)) {
                throw new InvalidArgumentException('Invalid purchase status.');
            }

            if ($this->isDuplicatePurchase($payload)) {
                throw new InvalidArgumentException('Pembelian serupa sudah tercatat. Periksa duplikasi PO.');
            }

            $purchase = Purchase::create([
                'purchase_no' => $payload['purchase_no'],
                'supplier_id' => $payload['supplier_id'] ?? null,
                'user_id' => $userId,
                'status' => $status,
                'notes' => $payload['notes'] ?? null,
                'purchased_at' => $payload['purchased_at'] ?? now(),
            ]);

            $total = 0;

            foreach ($payload['items'] as $item) {
                $sparepart = Sparepart::lockForUpdate()->findOrFail($item['sparepart_id']);
                $qty = (int) $item['qty'];
                $price = (float) $item['price'];
                $subtotal = $qty * $price;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'sparepart_id' => $sparepart->id,
                    'qty' => $qty,
                    'received_qty' => 0,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ]);

                $total += $subtotal;
            }

            $purchase->update([
                'total' => $total,
                'received_at' => null,
            ]);

            $this->auditLog->log(
                'purchase.create',
                $purchase,
                $user,
                null,
                null,
                [
                    'status' => $status,
                    'total' => $total,
                    'items' => $payload['items'],
                ]
            );

            return $purchase->load('items.sparepart', 'supplier');
        });
    }

    public function order(Purchase $purchase, ?User $user = null): Purchase
    {
        if ($purchase->status !== 'draft') {
            throw new InvalidArgumentException('Purchase must be in draft status.');
        }

        $purchase->update(['status' => 'ordered']);

        $this->auditLog->log(
            'purchase.order',
            $purchase,
            $user,
            null,
            ['status' => 'draft'],
            ['status' => 'ordered']
        );

        return $purchase->fresh(['items.sparepart', 'supplier', 'user']);
    }

    public function receive(Purchase $purchase, array $payload, ?User $user = null): Purchase
    {
        if ($purchase->status === 'received') {
            throw new InvalidArgumentException('Purchase already received.');
        }

        if ($purchase->status !== 'ordered') {
            throw new InvalidArgumentException('Purchase must be ordered before receiving.');
        }

        return DB::transaction(function () use ($purchase, $payload, $user): Purchase {
            $purchase->load('items');
            $receivedMap = collect($payload['items'] ?? [])
                ->keyBy('id')
                ->map(fn ($item) => (int) $item['received_qty']);

            foreach ($purchase->items as $item) {
                $sparepart = Sparepart::lockForUpdate()->findOrFail($item->sparepart_id);
                $receivedQty = (int) ($receivedMap[$item->id] ?? 0);
                $remaining = $item->qty - $item->received_qty;

                if ($receivedQty < 0 || $receivedQty > $remaining) {
                    throw new InvalidArgumentException('Received qty invalid for item ' . $item->id);
                }

                if ($receivedQty === 0) {
                    continue;
                }

                $this->stockService->adjust(
                    $sparepart,
                    $receivedQty,
                    'in',
                    'purchase',
                    $purchase->id,
                    $user,
                    $purchase->notes,
                    (string) $item->price
                );

                $sparepart->update(['price_buy' => $item->price]);
                $item->update([
                    'received_qty' => $item->received_qty + $receivedQty,
                ]);
            }

            $isFullyReceived = $purchase->items->every(fn ($item) => $item->received_qty >= $item->qty);

            $purchase->update([
                'status' => $isFullyReceived ? 'received' : 'ordered',
                'received_at' => $isFullyReceived ? now() : null,
            ]);

            $this->auditLog->log(
                'purchase.receive',
                $purchase,
                $user,
                null,
                ['status' => 'ordered'],
                [
                    'status' => $isFullyReceived ? 'received' : 'ordered',
                    'items' => $payload['items'],
                ]
            );

            return $purchase->load('items.sparepart', 'supplier', 'user');
        });
    }

    private function isDuplicatePurchase(array $payload): bool
    {
        if (empty($payload['supplier_id']) || empty($payload['items'])) {
            return false;
        }

        $date = isset($payload['purchased_at'])
            ? date('Y-m-d', strtotime((string) $payload['purchased_at']))
            : now()->format('Y-m-d');

        $signature = collect($payload['items'])
            ->map(fn ($item) => [
                'sparepart_id' => (int) $item['sparepart_id'],
                'qty' => (int) $item['qty'],
                'price' => (float) $item['price'],
            ])
            ->sortBy('sparepart_id')
            ->values()
            ->toJson();

        $candidates = Purchase::query()
            ->where('supplier_id', $payload['supplier_id'])
            ->whereDate('purchased_at', $date)
            ->whereIn('status', ['draft', 'ordered'])
            ->with('items')
            ->get();

        foreach ($candidates as $candidate) {
            $candidateSignature = $candidate->items
                ->map(fn ($item) => [
                    'sparepart_id' => (int) $item->sparepart_id,
                    'qty' => (int) $item->qty,
                    'price' => (float) $item->price,
                ])
                ->sortBy('sparepart_id')
                ->values()
                ->toJson();

            if ($candidateSignature === $signature) {
                return true;
            }
        }

        return false;
    }
}
