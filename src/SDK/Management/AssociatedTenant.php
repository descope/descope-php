<?php

namespace Descope\SDK\Management;

class AssociatedTenant
{
    /**
     * Represents a tenant association for a User or Access Key.
     * The tenant will be used to determine permissions and roles for the entity.
     *
     * @var string The Tenant ID.
     */
    public $tenantId;

    /**
     * Represents the role names for a user in the Tenant.
     *
     * @var array<string> The Role Names.
     */
    public $roleNames = [];

    /**
     * Constructor for the AssociatedTenant class.
     *
     * @param string        $tenantId  The Tenant ID.
     * @param array<string> $roleNames The role names for the user in the tenant.
     * @param array<string> $roleIds   The role IDs for the user in the tenant.
     */
    public function __construct(string $tenantId, array $roleNames = [])
    {
        $this->tenantId = $tenantId;
        $this->roleNames = $roleNames;
    }

    /**
     * Converts the AssociatedTenant object to an associative array.
     *
     * @return array The associative array representation of the tenant and roles.
     */
    public function toArray(): array
    {
        return [
            'tenantId' => $this->tenantId,
            'roleNames' => $this->roleNames
        ];
    }
}
