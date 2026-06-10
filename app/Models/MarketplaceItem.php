<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketplaceItem extends Model
{
    use HasFactory;

    // status values: pending | approved | rejected
    protected $fillable = [
        'user_id', 'name', 'slug', 'description', 'long_description',
        'category', 'type', 'version', 'price', 'is_free', 'thumbnail',
        'screenshots', 'tags', 'compatibility', 'download_url', 'demo_url',
        'documentation_url', 'downloads', 'rating', 'rating_count',
        'is_published', 'is_featured', 'published_at',
        'menu_items', 'icon', 'icon_color', 'package_file',
        'status', 'rejection_reason', 'demo_url_submission',
    ];

    protected $casts = [
        'screenshots'   => 'array',
        'tags'          => 'array',
        'compatibility' => 'array',
        'menu_items'    => 'array',
        'is_free'       => 'boolean',
        'is_published'  => 'boolean',
        'is_featured'   => 'boolean',
        'published_at'  => 'datetime',
        'price'         => 'decimal:2',
        'rating'        => 'decimal:2',
    ];

    public function developer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviews()
    {
        return $this->hasMany(MarketplaceReview::class);
    }

    public function installations()
    {
        return $this->hasMany(MarketplaceInstallation::class);
    }

    public function purchases()
    {
        return $this->hasMany(MarketplacePurchase::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)->where('status', 'approved');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function isApproved(): bool   { return $this->status === 'approved'; }
    public function isPending(): bool    { return $this->status === 'pending'; }
    public function isRejected(): bool   { return $this->status === 'rejected'; }

    public function getPriceFormattedAttribute(): string
    {
        return $this->is_free ? 'Free' : '$'.number_format($this->price, 2);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default    => 'Pending Review',
        };
    }
}
