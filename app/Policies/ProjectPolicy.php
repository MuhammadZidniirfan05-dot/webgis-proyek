<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'manajer_proyek']);
    }

    public function update(User $user, Project $project): bool
    {
        return $user->hasRole('admin')
            || ($user->hasRole('manajer_proyek') && $project->manager_id === $user->id);
    }

    public function delete(User $user, Project $project): bool
    {
        return $this->update($user, $project);
    }
}