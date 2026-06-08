<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'slug', 'description', 'type', 'framework',
        'status', 'thumbnail', 'tech_stack', 'settings', 'blueprint', 'env_variables',
        'github_repo', 'github_branch', 'deployment_url', 'deployment_target',
        'last_deployed_at', 'storage_used', 'ai_tokens_used',
    ];

    protected $casts = [
        'tech_stack'       => 'array',
        'settings'         => 'array',
        'blueprint'        => 'array',
        'env_variables'    => 'array',
        'last_deployed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($project) {
            if (empty($project->slug)) {
                $project->slug = static::generateSlug($project->name);
            }
        });
    }

    public static function generateSlug(string $name): string
    {
        $slug = Str::slug($name);
        $count = static::where('slug', 'like', $slug.'%')->count();
        return $count ? $slug.'-'.$count : $slug;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function files()
    {
        return $this->hasMany(ProjectFile::class)->orderBy('path');
    }

    public function rootFiles()
    {
        return $this->hasMany(ProjectFile::class)
            ->where('path', 'not like', '%/%')
            ->orderBy('type', 'desc')
            ->orderBy('name');
    }

    public function conversations()
    {
        return $this->hasMany(AIConversation::class)->latest();
    }

    public function deployments()
    {
        return $this->hasMany(Deployment::class)->latest();
    }

    public function collaborators()
    {
        return $this->belongsToMany(User::class, 'project_collaborators')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function marketplaceInstallations()
    {
        return $this->hasMany(MarketplaceInstallation::class);
    }

    public function getTypeConfigAttribute(): array
    {
        return config('ryaan.project_types.'.$this->type, [
            'label' => ucfirst($this->type),
            'color' => 'gray',
        ]);
    }

    public function getStorageUsedFormattedAttribute(): string
    {
        $bytes = $this->storage_used;
        if ($bytes < 1024) return $bytes.' B';
        if ($bytes < 1048576) return round($bytes / 1024, 2).' KB';
        if ($bytes < 1073741824) return round($bytes / 1048576, 2).' MB';
        return round($bytes / 1073741824, 2).' GB';
    }

    public function isOwner(User $user): bool
    {
        return $this->user_id === $user->id;
    }
}
