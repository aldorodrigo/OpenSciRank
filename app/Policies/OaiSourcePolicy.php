<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\OaiSource;
use Illuminate\Auth\Access\HandlesAuthorization;

class OaiSourcePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:OaiSource');
    }

    public function view(AuthUser $authUser, OaiSource $oaiSource): bool
    {
        return $authUser->can('View:OaiSource');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:OaiSource');
    }

    public function update(AuthUser $authUser, OaiSource $oaiSource): bool
    {
        return $authUser->can('Update:OaiSource');
    }

    public function delete(AuthUser $authUser, OaiSource $oaiSource): bool
    {
        return $authUser->can('Delete:OaiSource');
    }

    public function restore(AuthUser $authUser, OaiSource $oaiSource): bool
    {
        return $authUser->can('Restore:OaiSource');
    }

    public function forceDelete(AuthUser $authUser, OaiSource $oaiSource): bool
    {
        return $authUser->can('ForceDelete:OaiSource');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:OaiSource');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:OaiSource');
    }

    public function replicate(AuthUser $authUser, OaiSource $oaiSource): bool
    {
        return $authUser->can('Replicate:OaiSource');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:OaiSource');
    }

}