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
    public function view(User $user, Project $project): bool
{
    return true; // semua yang login boleh melihat
}

public function report(User $user, Project $project): bool
{
    if ($user->hasRole('admin')) {
        return true;
    }

    if ($user->hasRole('manajer_proyek')) {
        return $project->manager_id === $user->id;
    }

    return $user->hasRole('petugas_lapangan')
        && $project->officers()->where('users.id', $user->id)->exists();
}
}