<?php

namespace Descope\SDK\Management;

class AssociatedTenant
{
    /**
     * Represents a tenant association for a User or Access Key. The tenant will be used to determine permissions and roles for the entity.
     *
     * @var string The Tenant ID
     */
    public $tenantId;

    /**
     * Represents the role names for a user in the Tenant
     *
     * @var array<string> The Role Names
     */
    public $roleNames = [];

    /**
     * Represents the role IDs for a user in the Tenant
     *
     * @var array<string> The Role IDs
     */
    public $roleIds = [];

    public function __construct($tenantId, $roleNames = [], $roleIds = [])
    {
        $this->tenantId = $tenantId;
        $this->roleNames = $roleNames;
        $this->roleIds = $roleIds;
    }

    public function toArray(): array
    {
        return [
            'tenantId' => $this->tenantId,
            'roleNames' => $this->roleNames,
            'roleIds' => $this->roleIds,
        ];
    }
}