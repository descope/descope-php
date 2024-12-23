<?php

namespace Descope\SDK\Management;

use Descope\SDK\API;
use Descope\SDK\Exception\AuthException;
use Descope\SDK\Management\MgmtV1;

class Role
{
    private API $api;

    /**
     * Role constructor.
     *
     * @param API $api The API instance to be used for making requests.
     */
    public function __construct(API $api)
    {
        $this->api = $api;
    }

    /**
     * Validates tenant permissions for a JWT response.
     *
     * @param  array  $jwtResponse JWT response data.
     * @param  string $tenant      Tenant ID.
     * @param  array  $permissions Permissions to validate.
     * @return bool True if tenant permissions are valid, false otherwise.
     * @throws AuthException If JWT response is invalid.
     */
    public function validateTenantPermissions(array $jwtResponse, string $tenant = '', array $permissions): bool
    {
        if (!is_array($permissions)) {
            $permissions = [$permissions];
        }

        if (!is_array($jwtResponse)) {
            throw new AuthException(400, 'Invalid JWT response hash');
        }

        $grantedPermissions = $jwtResponse['permissions'] ?? [];
        if (!empty($tenant)) {
            if (empty($jwtResponse['tenants'][$tenant])) {
                return false;
            }
            $grantedPermissions = $jwtResponse['tenants'][$tenant]['permissions'] ?? [];
        }

        return empty(array_diff($permissions, $grantedPermissions));
    }

    /**
     * Validates roles for a JWT response.
     *
     * @param  array $jwtResponse JWT response data.
     * @param  array $roles       Roles to validate.
     * @return bool True if roles are valid, false otherwise.
     */
    public function validateRoles(array $jwtResponse, array $roles): bool
    {
        return $this->validateTenantRoles($jwtResponse, '', $roles);
    }

    /**
     * Validates tenant roles for a JWT response.
     *
     * @param  array  $jwtResponse JWT response data.
     * @param  string $tenant      Tenant ID.
     * @param  array  $roles       Roles to validate.
     * @return bool True if tenant roles are valid, false otherwise.
     * @throws AuthException If JWT response is invalid.
     */
    public function validateTenantRoles(array $jwtResponse, string $tenant, array $roles): bool
    {
        if (!is_array($roles)) {
            $roles = [$roles];
        }

        if (!is_array($jwtResponse)) {
            throw new AuthException(400, 'Invalid JWT response hash');
        }

        $grantedRoles = $jwtResponse['roles'] ?? [];
        if (!empty($tenant)) {
            if (empty($jwtResponse['tenants'][$tenant])) {
                return false;
            }
            $grantedRoles = $jwtResponse['tenants'][$tenant]['roles'] ?? [];
        }

        return empty(array_diff($roles, $grantedRoles));
    }
}
