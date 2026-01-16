<?php

namespace App\Policies;

use App\Models\Purchase;
use App\Models\User;

class PurchasePolicy
{
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['purchasing', 'super-admin']);
    }

    public function order(User $user, Purchase $purchase): bool
    {
        return $user->hasAnyRole(['purchasing', 'super-admin']);
    }

    public function receive(User $user, Purchase $purchase): bool
    {
        return $user->hasAnyRole(['purchasing', 'super-admin']);
    }
}
