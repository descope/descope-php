<?php

namespace Descope\SDK\Management;

use Descope\SDK\API;

/**
 * Class Management
 *
 * Represents the management functionality for Descope, providing access to
 * user management capabilities.
 */
class Management
{
    /**
     * @var User The User management component.
     */
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
     *
     * @return User The User management instance.
     */
    public function user(): User
    {
        return $this->user;
    }
}
