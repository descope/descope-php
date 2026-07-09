<?php

namespace Descope\SDK\Management;

use Descope\SDK\API;
use Descope\SDK\Exception\AuthException;
use Descope\SDK\Management\MgmtV1;

/**
 * Provides functions for managing access keys in a project.
 *
 * Access keys are used for machine-to-machine authentication. This class
 * exposes methods to create, load, search, update, activate, deactivate,
 * and delete access keys via the Descope management API.
 */
class AccessKey
{
    private API $api;

    /**
     * AccessKey constructor.
     *
     * @param API $api The API instance to be used for making requests.
     */
    public function __construct(API $api)
    {
        $this->api = $api;
    }

    /**
     * Create a new access key.
     *
     * @param  string      $name         The name of the access key.
     * @param  int         $expireTime   Access key expiration time (in seconds since epoch). 0 means never expires.
     * @param  array       $roleNames    An optional list of the access key's roles without tenant association. These roles are supported for keys of a project-level access key.
     * @param  array       $keyTenants   An optional list of tenant associations, each an associative array like ['tenantId' => 't1', 'roleNames' => ['r1']].
     * @param  string|null $userId       An optional user ID to associate the access key with. The key will inherit the user's permissions and attributes.
     * @param  array       $customClaims An optional dictionary of custom claims to be added to the access key's JWT.
     * @param  string|null $description   An optional description for the access key.
     * @param  array       $permittedIps  An optional list of IP addresses or CIDR ranges that are allowed to use this access key.
     * @return array The created access key response, containing 'key' and 'cleartext'.
     * @throws AuthException If the request fails.
     */
    public function create(
        string $name,
        int $expireTime = 0,
        array $roleNames = [],
        array $keyTenants = [],
        ?string $userId = null,
        array $customClaims = [],
        ?string $description = null,
        array $permittedIps = []
    ): array {
        $body = [
            'name' => $name,
            'expireTime' => $expireTime,
            'roleNames' => $roleNames,
            'keyTenants' => $keyTenants,
            'customClaims' => $customClaims,
            'permittedIps' => $permittedIps,
        ];

        if ($userId !== null) {
            $body['userId'] = $userId;
        }
        if ($description !== null) {
            $body['description'] = $description;
        }

        return $this->api->doPost(MgmtV1::$ACCESS_KEY_CREATE_PATH, $body, true);
    }

    /**
     * Load an existing access key by its ID.
     *
     * @param  string $id The ID of the access key to load.
     * @return array The access key response, containing 'key'.
     * @throws AuthException If the request fails.
     */
    public function load(string $id): array
    {
        return $this->api->doGet(
            MgmtV1::$ACCESS_KEY_LOAD_PATH . '?' . http_build_query(['id' => $id]),
            true
        );
    }

    /**
     * Search all access keys, optionally filtering by tenant IDs.
     *
     * @param  array $tenantIds An optional list of tenant IDs to filter the access keys by.
     * @return array The search response, containing 'keys'.
     * @throws AuthException If the request fails.
     */
    public function searchAll(array $tenantIds = []): array
    {
        $body = [
            'tenantIds' => $tenantIds,
        ];

        return $this->api->doPost(MgmtV1::$ACCESS_KEYS_SEARCH_PATH, $body, true);
    }

    /**
     * Update an existing access key.
     *
     * @param  string      $id           The ID of the access key to update.
     * @param  string      $name         The updated name of the access key.
     * @param  array|null  $customClaims An optional dictionary of custom claims to update on the access key.
     * @param  string|null $description  An optional updated description for the access key.
     * @param  array|null  $roleNames    An optional updated list of the access key's roles without tenant association.
     * @param  array|null  $keyTenants   An optional updated list of tenant associations, each an associative array like ['tenantId' => 't1', 'roleNames' => ['r1']].
     * @return array The update response.
     * @throws AuthException If the request fails.
     */
    public function update(
        string $id,
        string $name,
        ?array $customClaims = null,
        ?string $description = null,
        ?array $roleNames = null,
        ?array $keyTenants = null
    ): array {
        $body = [
            'id' => $id,
            'name' => $name,
        ];

        if ($customClaims !== null) {
            $body['customClaims'] = $customClaims;
        }
        if ($description !== null) {
            $body['description'] = $description;
        }
        if ($roleNames !== null) {
            $body['roleNames'] = $roleNames;
        }
        if ($keyTenants !== null) {
            $body['keyTenants'] = $keyTenants;
        }

        return $this->api->doPost(MgmtV1::$ACCESS_KEY_UPDATE_PATH, $body, true);
    }

    /**
     * Deactivate an access key.
     *
     * Deactivated access keys cannot be used to authenticate but can be
     * reactivated later using the activate method.
     *
     * @param  string $id The ID of the access key to deactivate.
     * @return void
     * @throws AuthException If the request fails.
     */
    public function deactivate(string $id): void
    {
        $body = [
            'id' => $id,
        ];

        $this->api->doPost(MgmtV1::$ACCESS_KEY_DEACTIVATE_PATH, $body, true);
    }

    /**
     * Deactivate multiple access keys in a single batch request.
     *
     * Deactivated access keys cannot be used to authenticate but can be
     * reactivated later using the activate or activateBatch methods.
     *
     * @param  array $ids A list of access key IDs to deactivate.
     * @return void
     * @throws AuthException If the request fails.
     */
    public function deactivateBatch(array $ids): void
    {
        $body = [
            'ids' => $ids,
        ];

        $this->api->doPost(MgmtV1::$ACCESS_KEY_DEACTIVATE_BATCH_PATH, $body, true);
    }

    /**
     * Activate an access key.
     *
     * @param  string $id The ID of the access key to activate.
     * @return void
     * @throws AuthException If the request fails.
     */
    public function activate(string $id): void
    {
        $body = [
            'id' => $id,
        ];

        $this->api->doPost(MgmtV1::$ACCESS_KEY_ACTIVATE_PATH, $body, true);
    }

    /**
     * Activate multiple access keys in a single batch request.
     *
     * @param  array $ids A list of access key IDs to activate.
     * @return void
     * @throws AuthException If the request fails.
     */
    public function activateBatch(array $ids): void
    {
        $body = [
            'ids' => $ids,
        ];

        $this->api->doPost(MgmtV1::$ACCESS_KEY_ACTIVATE_BATCH_PATH, $body, true);
    }

    /**
     * Delete an access key.
     *
     * IMPORTANT: This action is irreversible. Once an access key is deleted
     * it cannot be recovered.
     *
     * @param  string $id The ID of the access key to delete.
     * @return void
     * @throws AuthException If the request fails.
     */
    public function delete(string $id): void
    {
        $body = [
            'id' => $id,
        ];

        $this->api->doPost(MgmtV1::$ACCESS_KEY_DELETE_PATH, $body, true);
    }

    /**
     * Delete multiple access keys in a single batch request.
     *
     * IMPORTANT: This action is irreversible. Once an access key is deleted
     * it cannot be recovered.
     *
     * @param  array $ids A list of access key IDs to delete.
     * @return void
     * @throws AuthException If the request fails.
     */
    public function deleteBatch(array $ids): void
    {
        $body = [
            'ids' => $ids,
        ];

        $this->api->doPost(MgmtV1::$ACCESS_KEY_DELETE_BATCH_PATH, $body, true);
    }

    /**
     * Rotate an access key, generating a new cleartext value for it.
     *
     * The old cleartext value is invalidated and the returned response
     * contains the new cleartext value along with the updated key info.
     *
     * @param  string $id The ID of the access key to rotate.
     * @return array The rotate response, containing 'key' and 'cleartext'.
     * @throws AuthException If the request fails.
     */
    public function rotate(string $id): array
    {
        $body = [
            'id' => $id,
        ];

        return $this->api->doPost(MgmtV1::$ACCESS_KEY_ROTATE_PATH, $body, true);
    }
}
