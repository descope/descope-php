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
    public Audit $audit;
    public OutboundApps $outboundApps;

    /**
     * Constructor for Management class.
     *
     * @param API $auth Auth object for making authenticated requests.
     */
    public function __construct(API $auth)
    {
        $this->user = new User($auth);
        $this->audit = new Audit($auth);
        $this->outboundApps = new OutboundApps($auth);
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

    /**
     * Get the Audit Management component.
     *
     * @return Audit The Audit management instance.
     */
    public function audit(): Audit
    {
        return $this->audit;
    }

    /**
     * Get the Outbound Apps Management component.
     *
     * @return OutboundApps The Outbound Apps management instance.
     */
    public function outboundApps(): OutboundApps
    {
        return $this->outboundApps;
    }
}
