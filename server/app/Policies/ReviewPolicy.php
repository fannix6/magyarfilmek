<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReviewPolicy
{
    public function update(User $user, Review $review): Response
    {
        if ($user->role === 1 || $user->id === $review->userid) {
            return Response::allow();
        }

        return Response::deny('Csak a sajat velemenyedet modositthatod.');
    }

    public function delete(User $user, Review $review): Response
    {
        if ($user->role === 1 || $user->id === $review->userid) {
            return Response::allow();
        }

        return Response::deny('Csak a sajat velemenyedet torolheted.');
    }
}

