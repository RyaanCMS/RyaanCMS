<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketplacePurchase extends Model
{
    protected $fillable = [
        'user_id',
        'marketplace_item_id',
        'license_key',
        'domain',
        'purchased_at',
    ];

    protected $casts = [
        'purchased_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function item()
    {
        return $this->belongsTo(MarketplaceItem::class, 'marketplace_item_id');
    }

    public function installations()
    {
        return $this->hasMany(MarketplaceInstallation::class, 'marketplace_purchase_id');
    }
}
