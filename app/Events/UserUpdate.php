<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class UserUpdate
{

    use Dispatchable;
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(private readonly User $user)
    {
    }

    /**
     * @return User
     */
    public function getUser() : User
    {
        return $this->user;
    }
}
