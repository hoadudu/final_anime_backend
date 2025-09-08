<?php

namespace App\Observers;

use App\Models\User;
use App\Models\UserAnimeList;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Create default anime list for new user
        UserAnimeList::create([
            'user_id' => $user->id,
            'name' => 'My List',
            'type' => 'default',
            'is_default' => true,
            'visibility' => 'private',
        ]);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
