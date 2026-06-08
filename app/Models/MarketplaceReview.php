<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketplaceReview extends Model
{
    protected $fillable = ['user_id', 'marketplace_item_id', 'rating', 'review'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function item()
    {
        return $this->belongsTo(MarketplaceItem::class, 'marketplace_item_id');
    }
}
