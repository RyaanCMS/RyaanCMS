<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function view(User $user, Project $project): bool
    {
        return $project->user_id === $user->id
            || $project->collaborators()->where('user_id', $user->id)->exists();
    }

    public function update(User $user, Project $project): bool
    {
        if ($project->user_id === $user->id) return true;

        $collab = $project->collaborators()->where('user_id', $user->id)->first();
        return $collab && in_array($collab->pivot->role, ['editor', 'admin']);
    }

    public function delete(User $user, Project $project): bool
    {
        return $project->user_id === $user->id;
    }
}
