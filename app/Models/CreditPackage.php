<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditPackage extends Model
{
    protected $fillable = [
        'name', 'slug', 'credits', 'price_usd', 'currency',
        'description', 'features', 'is_active', 'is_featured', 'sort_order',
    ];

    protected $casts = [
        'features'    => 'array',
        'is_active'   => 'boolean',
        'is_featured' => 'boolean',
        'price_usd'   => 'decimal:2',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function getPricePerCreditAttribute(): float
    {
        return $this->credits > 0 ? round($this->price_usd / $this->credits, 6) : 0;
    }
}
