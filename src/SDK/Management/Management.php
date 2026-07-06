<?php

namespace Descope\SDK\Management;

use Descope\SDK\API;

/**
 * Class Management
 *
 * Represents the management functionality for Descope, providing access to
 * the full set of management components (users, tenants, roles, permissions,
 * access keys, SSO, JWT, flows, audit and outbound apps).
 */
class Management
{
    public User $user;
    public Audit $audit;
    public OutboundApps $outboundApps;
    public Tenant $tenant;
    public Role $role;
    public Permission $permission;
    public AccessKey $accessKey;
    public SSOApplication $ssoApplication;
    public SSOSettings $sso;
    public JWT $jwt;
    public Flow $flow;

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
        $this->tenant = new Tenant($auth);
        $this->role = new Role($auth);
        $this->permission = new Permission($auth);
        $this->accessKey = new AccessKey($auth);
        $this->ssoApplication = new SSOApplication($auth);
        $this->sso = new SSOSettings($auth);
        $this->jwt = new JWT($auth);
        $this->flow = new Flow($auth);
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

    /**
     * Get the Tenant Management component.
     *
     * @return Tenant The Tenant management instance.
     */
    public function tenant(): Tenant
    {
        return $this->tenant;
    }

    /**
     * Get the Role Management component.
     *
     * @return Role The Role management instance.
     */
    public function role(): Role
    {
        return $this->role;
    }

    /**
     * Get the Permission Management component.
     *
     * @return Permission The Permission management instance.
     */
    public function permission(): Permission
    {
        return $this->permission;
    }

    /**
     * Get the Access Key Management component.
     *
     * @return AccessKey The Access Key management instance.
     */
    public function accessKey(): AccessKey
    {
        return $this->accessKey;
    }

    /**
     * Get the SSO Application Management component.
     *
     * @return SSOApplication The SSO Application management instance.
     */
    public function ssoApplication(): SSOApplication
    {
        return $this->ssoApplication;
    }

    /**
     * Get the SSO Settings Management component.
     *
     * @return SSOSettings The SSO Settings management instance.
     */
    public function sso(): SSOSettings
    {
        return $this->sso;
    }

    /**
     * Get the JWT Management component.
     *
     * @return JWT The JWT management instance.
     */
    public function jwt(): JWT
    {
        return $this->jwt;
    }

    /**
     * Get the Flow Management component.
     *
     * @return Flow The Flow management instance.
     */
    public function flow(): Flow
    {
        return $this->flow;
    }
}
