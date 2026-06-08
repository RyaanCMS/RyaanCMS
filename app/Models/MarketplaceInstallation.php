<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketplaceInstallation extends Model
{
    protected $fillable = [
        'user_id', 'project_id', 'marketplace_item_id', 'version', 'status',
        'license_key', 'domain', 'activated_at', 'package_path',
    ];

    protected $casts = [
        'activated_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function item()
    {
        return $this->belongsTo(MarketplaceItem::class, 'marketplace_item_id');
    }
}
