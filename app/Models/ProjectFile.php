<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', 'name', 'path', 'type', 'mime_type',
        'extension', 'content', 'size', 'is_binary', 'disk_path',
    ];

    protected $casts = [
        'is_binary' => 'boolean',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function getLanguageAttribute(): string
    {
        $map = [
            'php'  => 'php',  'js'   => 'javascript', 'ts'   => 'typescript',
            'jsx'  => 'jsx',  'tsx'  => 'tsx',          'vue'  => 'vue',
            'html' => 'html', 'css'  => 'css',          'scss' => 'scss',
            'json' => 'json', 'yaml' => 'yaml',          'yml'  => 'yaml',
            'md'   => 'markdown', 'sql' => 'sql',        'sh'   => 'bash',
            'env'  => 'dotenv', 'xml' => 'xml',          'blade'=> 'html',
        ];

        $ext = strtolower($this->extension ?? pathinfo($this->name, PATHINFO_EXTENSION));

        // Handle .blade.php
        if (str_ends_with($this->name, '.blade.php')) {
            return 'html';
        }

        return $map[$ext] ?? 'plaintext';
    }

    public function getIconAttribute(): string
    {
        if ($this->type === 'directory') return 'folder';

        $icons = [
            'php'   => 'php',    'js'    => 'javascript', 'ts'  => 'typescript',
            'vue'   => 'vue',    'jsx'   => 'react',       'tsx' => 'react',
            'html'  => 'html',   'css'   => 'css',         'scss'=> 'scss',
            'json'  => 'json',   'md'    => 'markdown',    'sql' => 'database',
            'env'   => 'config', 'yml'   => 'yaml',        'xml' => 'xml',
            'blade' => 'blade',  'sh'    => 'terminal',
        ];

        $ext = strtolower($this->extension ?? pathinfo($this->name, PATHINFO_EXTENSION));
        return $icons[$ext] ?? 'file';
    }
}
