<?php

namespace Descope\SDK\Management;

use Descope\SDK\API;
use Descope\SDK\Exception\AuthException;
use Descope\SDK\Management\MgmtV1;

/**
 * Provides functions for managing permissions in a project.
 */
class Permission
{
    private API $api;

    /**
     * Permission constructor.
     *
     * @param API $api The API instance to be used for making requests.
     */
    public function __construct(API $api)
    {
        $this->api = $api;
    }

    /**
     * Create a new permission.
     *
     * @param  string      $name        The name of the permission to create.
     * @param  string|null $description  Optional description to explain the purpose of the permission.
     * @return void
     * @throws AuthException If the creation request fails.
     */
    public function create(string $name, ?string $description = null): void
    {
        $body = [
            'name' => $name,
        ];

        if ($description !== null) {
            $body['description'] = $description;
        }

        $this->api->doPost(MgmtV1::$PERMISSION_CREATE_PATH, $body, true);
    }

    /**
     * Update an existing permission.
     *
     * @param  string      $name        The name of the permission to update.
     * @param  string      $newName      The updated name of the permission.
     * @param  string|null $description  Optional description to explain the purpose of the permission.
     * @return void
     * @throws AuthException If the update request fails.
     */
    public function update(string $name, string $newName, ?string $description = null): void
    {
        $body = [
            'name' => $name,
            'newName' => $newName,
        ];

        if ($description !== null) {
            $body['description'] = $description;
        }

        $this->api->doPost(MgmtV1::$PERMISSION_UPDATE_PATH, $body, true);
    }

    /**
     * Delete an existing permission.
     *
     * @param  string $name The name of the permission to delete.
     * @return void
     * @throws AuthException If the deletion request fails.
     */
    public function delete(string $name): void
    {
        $body = [
            'name' => $name,
        ];

        $this->api->doPost(MgmtV1::$PERMISSION_DELETE_PATH, $body, true);
    }

    /**
     * Load all permissions.
     *
     * @return array The response containing the list of permissions under the 'permissions' key.
     * @throws AuthException If the load request fails.
     */
    public function loadAll(): array
    {
        return $this->api->doGet(MgmtV1::$PERMISSION_LOAD_ALL_PATH, true);
    }
}
