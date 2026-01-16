<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sparepart extends Model
{
    protected $fillable = [
        'sku',
        'name',
        'category_id',
        'unit',
        'price_buy',
        'price_sell',
        'stock',
        'min_stock',
        'description',
        'is_active',
    ];

    protected $casts = [
        'price_buy' => 'decimal:2',
        'price_sell' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function stockLogs(): HasMany
    {
        return $this->hasMany(StockLog::class);
    }
}
