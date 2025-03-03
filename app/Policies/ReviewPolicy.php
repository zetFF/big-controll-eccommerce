<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Review $review): bool
    {
        return $user->id === $review->user_id || $user->isAdmin();
    }

    public function delete(User $user, Review $review): bool
    {
        return $user->id === $review->user_id || $user->isAdmin();
    }

    public function approve(User $user, Review $review): bool
    {
        return $user->isAdmin();
    }
} 