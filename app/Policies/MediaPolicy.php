<?php

namespace App\Policies;

use App\Models\Media;
use App\Models\User;

class MediaPolicy
{
    public function view(User $user, Media $media): bool
    {
        return $user->isAdmin() || $media->uploaded_by_id === $user->id;
    }

    public function delete(User $user, Media $media): bool
    {
        return $user->isAdmin() || $media->uploaded_by_id === $user->id;
    }

    public function attach(User $user, Media $media): bool
    {
        return $user->isAdmin() || $media->uploaded_by_id === $user->id;
    }
}
