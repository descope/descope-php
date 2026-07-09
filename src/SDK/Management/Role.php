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
     * Creates a new role.
     *
     * @param  string      $name            The role name.
     * @param  string|null $description     Optional role description.
     * @param  array       $permissionNames Permission names to grant to the role.
     * @param  string|null $tenantId        Optional tenant ID for a tenant-scoped role.
     * @param  bool        $defaultRole     Whether this role is a default role for new users.
     * @return void
     * @throws AuthException If the request fails.
     */
    public function create(
        string $name,
        ?string $description = null,
        array $permissionNames = [],
        ?string $tenantId = null,
        bool $defaultRole = false
    ): void {
        $body = [
            'name' => $name,
            'permissionNames' => $permissionNames,
            'defaultRole' => $defaultRole,
        ];
        if ($description !== null) {
            $body['description'] = $description;
        }
        if ($tenantId !== null) {
            $body['tenantId'] = $tenantId;
        }

        $this->api->doPost(MgmtV1::$ROLE_CREATE_PATH, $body, true);
    }

    /**
     * Creates multiple roles in a single batch request.
     *
     * @param  array $roles List of role objects to create. Each entry mirrors the Go SDK
     *                     `descope.Role` shape (e.g. `['name' => ..., 'description' => ...,
     *                     'permissionNames' => [...], 'tenantId' => ...]`).
     * @return array The response containing the created roles.
     * @throws AuthException If the request fails.
     */
    public function createBatch(array $roles): array
    {
        $body = [
            'roles' => $roles,
        ];

        return $this->api->doPost(MgmtV1::$ROLE_CREATE_BATCH_PATH, $body, true);
    }

    /**
     * Updates an existing role. All parameters are required except where noted;
     * omitted optional values will be cleared on the role.
     *
     * @param  string      $name            The current role name.
     * @param  string      $newName         The new role name.
     * @param  string|null $description     Optional role description.
     * @param  array       $permissionNames Permission names to grant to the role.
     * @param  string|null $tenantId        Optional tenant ID for a tenant-scoped role.
     * @param  bool        $defaultRole     Whether this role is a default role for new users.
     * @return void
     * @throws AuthException If the request fails.
     */
    public function update(
        string $name,
        string $newName,
        ?string $description = null,
        array $permissionNames = [],
        ?string $tenantId = null,
        bool $defaultRole = false
    ): void {
        $body = [
            'name' => $name,
            'newName' => $newName,
            'permissionNames' => $permissionNames,
            'defaultRole' => $defaultRole,
        ];
        if ($description !== null) {
            $body['description'] = $description;
        }
        if ($tenantId !== null) {
            $body['tenantId'] = $tenantId;
        }

        $this->api->doPost(MgmtV1::$ROLE_UPDATE_PATH, $body, true);
    }

    /**
     * Updates an existing role identified by its ID.
     *
     * @param  string $id              The ID of the role to update.
     * @param  string $tenantId        Tenant ID for a tenant-scoped role (empty for project-level).
     * @param  string $newName         The new role name.
     * @param  string $description     Optional role description.
     * @param  array  $permissionNames Permission names to grant to the role.
     * @param  bool   $defaultRole     Whether this role is a default role for new users.
     * @param  bool   $private         Whether this role is private.
     * @return void
     * @throws AuthException If the request fails.
     */
    public function updateWithId(
        string $id,
        string $tenantId,
        string $newName,
        string $description = "",
        array $permissionNames = [],
        bool $defaultRole = false,
        bool $private = false
    ): void {
        $body = [
            'id' => $id,
            'newName' => $newName,
            'description' => $description,
            'permissionNames' => $permissionNames,
            'tenantId' => $tenantId,
            'default' => $defaultRole,
            'private' => $private,
        ];

        $this->api->doPost(MgmtV1::$ROLE_UPDATE_PATH, $body, true);
    }

    /**
     * Updates multiple roles in a single batch request.
     *
     * @param  array $roles List of role update objects. Each entry mirrors the Go SDK
     *                     `descope.RoleUpdateRequest` shape.
     * @return array The response containing the updated roles.
     * @throws AuthException If the request fails.
     */
    public function updateBatch(array $roles): array
    {
        $body = [
            'roles' => $roles,
        ];

        return $this->api->doPost(MgmtV1::$ROLE_UPDATE_BATCH_PATH, $body, true);
    }

    /**
     * Deletes a role.
     *
     * @param  string      $name     The role name.
     * @param  string|null $tenantId Optional tenant ID for a tenant-scoped role.
     * @return void
     * @throws AuthException If the request fails.
     */
    public function delete(string $name, ?string $tenantId = null): void
    {
        $body = ['name' => $name];
        if ($tenantId !== null) {
            $body['tenantId'] = $tenantId;
        }

        $this->api->doPost(MgmtV1::$ROLE_DELETE_PATH, $body, true);
    }

    /**
     * Deletes a role identified by its ID.
     *
     * @param  string $id       The ID of the role to delete.
     * @param  string $tenantId Optional tenant ID for a tenant-scoped role.
     * @return void
     * @throws AuthException If the request fails.
     */
    public function deleteWithId(string $id, string $tenantId = ""): void
    {
        $body = [
            'id' => $id,
            'tenantId' => $tenantId,
        ];

        $this->api->doPost(MgmtV1::$ROLE_DELETE_PATH, $body, true);
    }

    /**
     * Deletes multiple roles in a single batch request.
     *
     * @param  array  $roleNames List of role names to delete.
     * @param  string $tenantId  Optional tenant ID for tenant-scoped roles.
     * @param  array  $roleIds   List of role IDs to delete.
     * @return void
     * @throws AuthException If the request fails.
     */
    public function deleteBatch(array $roleNames = [], string $tenantId = "", array $roleIds = []): void
    {
        $body = [
            'roleNames' => $roleNames,
            'tenantId' => $tenantId,
            'roleIds' => $roleIds,
        ];

        $this->api->doPost(MgmtV1::$ROLE_DELETE_BATCH_PATH, $body, true);
    }

    /**
     * Loads all roles in the project.
     *
     * @return array The response containing the list of roles.
     * @throws AuthException If the request fails.
     */
    public function loadAll(): array
    {
        return $this->api->doGet(MgmtV1::$ROLE_LOAD_ALL_PATH, true);
    }

    /**
     * Searches roles matching the given filters.
     *
     * @param  array       $tenantIds       Filter by tenant IDs.
     * @param  array       $roleNames       Filter by role names.
     * @param  string|null $roleNameLike    Filter by a case-insensitive partial role name.
     * @param  array       $permissionNames Filter by permission names.
     * @return array The response containing the matching roles.
     * @throws AuthException If the request fails.
     */
    public function search(
        array $tenantIds = [],
        array $roleNames = [],
        ?string $roleNameLike = null,
        array $permissionNames = []
    ): array {
        $body = [
            'tenantIds' => $tenantIds,
            'roleNames' => $roleNames,
            'permissionNames' => $permissionNames,
        ];
        if ($roleNameLike !== null) {
            $body['roleNameLike'] = $roleNameLike;
        }

        return $this->api->doPost(MgmtV1::$ROLE_SEARCH_PATH, $body, true);
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
    public function validateTenantPermissions(array $jwtResponse, array $permissions, ?string $tenant = null): bool
    {
        $tenant = $tenant ?? '';

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
