<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\CriteriaItem;
use Illuminate\Auth\Access\HandlesAuthorization;

class CriteriaItemPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CriteriaItem');
    }

    public function view(AuthUser $authUser, CriteriaItem $criteriaItem): bool
    {
        return $authUser->can('View:CriteriaItem');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CriteriaItem');
    }

    public function update(AuthUser $authUser, CriteriaItem $criteriaItem): bool
    {
        return $authUser->can('Update:CriteriaItem');
    }

    public function delete(AuthUser $authUser, CriteriaItem $criteriaItem): bool
    {
        return $authUser->can('Delete:CriteriaItem');
    }

    public function restore(AuthUser $authUser, CriteriaItem $criteriaItem): bool
    {
        return $authUser->can('Restore:CriteriaItem');
    }

    public function forceDelete(AuthUser $authUser, CriteriaItem $criteriaItem): bool
    {
        return $authUser->can('ForceDelete:CriteriaItem');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CriteriaItem');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CriteriaItem');
    }

    public function replicate(AuthUser $authUser, CriteriaItem $criteriaItem): bool
    {
        return $authUser->can('Replicate:CriteriaItem');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CriteriaItem');
    }

}