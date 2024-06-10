<?php

namespace Descope\SDK\Management;

use Descope\SDK\API;

class Management
{
    public User $user;

    /**
     * Constructor for Management class.
     *
     * @param API $auth Auth object for making authenticated requests.
     */
    public function __construct(API $auth)
    {
        $this->user = new User($auth);
    }

    /**
     * Get the User Management component.
     */
    public function user(): User
    {
        return $this->user;
    }
}
