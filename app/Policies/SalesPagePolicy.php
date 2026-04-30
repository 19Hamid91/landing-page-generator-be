<?php

namespace App\Policies;

use App\Models\SalesPage;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SalesPagePolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SalesPage $salesPage): bool
    {
        return $user->id === $salesPage->user_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SalesPage $salesPage): bool
    {
        return $user->id === $salesPage->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SalesPage $salesPage): bool
    {
        return $user->id === $salesPage->user_id;
    }
}
