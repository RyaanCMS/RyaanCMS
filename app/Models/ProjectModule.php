<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectModule extends Model
{
    protected $fillable = ['project_id', 'module_key', 'status', 'options'];

    protected $casts = ['options' => 'array'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
