<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockLog extends Model
{
    protected $fillable = [
        'sparepart_id',
        'user_id',
        'ref_type',
        'ref_id',
        'type',
        'qty',
        'before_stock',
        'after_stock',
        'unit_price',
        'notes',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
    ];

    public function sparepart(): BelongsTo
    {
        return $this->belongsTo(Sparepart::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
