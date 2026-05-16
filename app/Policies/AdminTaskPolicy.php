<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\AdminTask;
use Illuminate\Auth\Access\HandlesAuthorization;

class AdminTaskPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AdminTask');
    }

    public function view(AuthUser $authUser, AdminTask $adminTask): bool
    {
        return $authUser->can('View:AdminTask');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AdminTask');
    }

    public function update(AuthUser $authUser, AdminTask $adminTask): bool
    {
        return $authUser->can('Update:AdminTask');
    }

    public function delete(AuthUser $authUser, AdminTask $adminTask): bool
    {
        return $authUser->can('Delete:AdminTask');
    }

    public function restore(AuthUser $authUser, AdminTask $adminTask): bool
    {
        return $authUser->can('Restore:AdminTask');
    }

    public function forceDelete(AuthUser $authUser, AdminTask $adminTask): bool
    {
        return $authUser->can('ForceDelete:AdminTask');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AdminTask');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AdminTask');
    }

    public function replicate(AuthUser $authUser, AdminTask $adminTask): bool
    {
        return $authUser->can('Replicate:AdminTask');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AdminTask');
    }

}