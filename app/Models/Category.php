<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'name',
        'code',
    ];

    public function spareparts(): HasMany
    {
        return $this->hasMany(Sparepart::class);
    }
}
