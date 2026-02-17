<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Contest;

class ContestPolicy
{
    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, Contest $contest)
    {
        return true;
    }

    public function create(User $user)
    {
        return $user->isAdmin();
    }

    public function update(User $user, Contest $contest)
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Contest $contest)
    {
        return $user->isAdmin();
    }
}
