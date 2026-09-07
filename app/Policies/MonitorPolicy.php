<?php

namespace App\Policies;

use App\Models\Monitor;
use App\Models\Project;
use App\Models\User;

class MonitorPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Monitor $monitor): bool
    {
        return $this->ownsMonitor($user, $monitor);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, ?Project $project = null): bool
    {
        if ($project === null) {
            return true;
        }

        return $project->user_id === $user->id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Monitor $monitor): bool
    {
        return $this->ownsMonitor($user, $monitor);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Monitor $monitor): bool
    {
        return $this->ownsMonitor($user, $monitor);
    }

    /**
     * Determine whether the user can run a manual check.
     */
    public function check(User $user, Monitor $monitor): bool
    {
        return $this->ownsMonitor($user, $monitor);
    }

    private function ownsMonitor(User $user, Monitor $monitor): bool
    {
        return $monitor->project->user_id === $user->id;
    }
}
