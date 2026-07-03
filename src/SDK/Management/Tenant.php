<?php

namespace Descope\SDK\Management;

use Descope\SDK\API;
use Descope\SDK\Exception\AuthException;
use Descope\SDK\Management\MgmtV1;

/**
 * Class Tenant
 *
 * Manages Descope tenants.
 * Tenants allow partitioning of users and configuration within a Descope project,
 * enabling multi-tenant applications with isolated settings and self-provisioning domains.
 */
class Tenant
{
    private API $api;

    /**
     * Tenant constructor.
     *
     * @param API $api The API instance to be used for making requests.
     */
    public function __construct(API $api)
    {
        $this->api = $api;
    }

    /**
     * Create a new tenant.
     *
     * This method creates a new tenant with the specified name and optional settings.
     * If no ID is provided, one will be generated automatically.
     *
     * @param string      $name                     The name of the tenant.
     * @param string|null $id                       Optional custom ID for the tenant.
     * @param array       $selfProvisioningDomains  Optional list of domains that can self-provision into the tenant.
     * @param array|null  $customAttributes         Optional map of custom attributes for the tenant.
     *
     * @return array The create response containing the tenant 'id'.
     *
     * @throws AuthException If the create operation fails.
     */
    public function create(
        string $name,
        ?string $id = null,
        array $selfProvisioningDomains = [],
        ?array $customAttributes = null
    ): array {
        $body = [
            'name' => $name,
            'selfProvisioningDomains' => $selfProvisioningDomains,
        ];

        if ($id !== null) {
            $body['id'] = $id;
        }

        if ($customAttributes !== null) {
            $body['customAttributes'] = $customAttributes;
        }

        return $this->api->doPost(
            MgmtV1::$TENANT_CREATE_PATH,
            $body,
            true
        );
    }

    /**
     * Update an existing tenant.
     *
     * This method overwrites the tenant's settings with the provided values.
     * All fields will be updated, so unspecified optional values should be
     * provided in full to avoid unintentionally clearing them.
     *
     * @param string      $id                       The ID of the tenant to update.
     * @param string      $name                     The new name of the tenant.
     * @param array       $selfProvisioningDomains  Optional list of domains that can self-provision into the tenant.
     * @param array|null  $customAttributes         Optional map of custom attributes for the tenant.
     *
     * @return void
     *
     * @throws AuthException If the update operation fails.
     */
    public function update(
        string $id,
        string $name,
        array $selfProvisioningDomains = [],
        ?array $customAttributes = null
    ): void {
        $body = [
            'id' => $id,
            'name' => $name,
            'selfProvisioningDomains' => $selfProvisioningDomains,
        ];

        if ($customAttributes !== null) {
            $body['customAttributes'] = $customAttributes;
        }

        $this->api->doPost(
            MgmtV1::$TENANT_UPDATE_PATH,
            $body,
            true
        );
    }

    /**
     * Delete a tenant.
     *
     * This method removes a tenant identified by its ID. When cascade is enabled,
     * users and other entities associated only with this tenant are also deleted.
     *
     * @param string $id      The ID of the tenant to delete.
     * @param bool   $cascade Whether to cascade the deletion to associated entities.
     *
     * @return void
     *
     * @throws AuthException If the delete operation fails.
     */
    public function delete(string $id, bool $cascade = false): void
    {
        $body = [
            'id' => $id,
            'cascade' => $cascade,
        ];

        $this->api->doPost(
            MgmtV1::$TENANT_DELETE_PATH,
            $body,
            true
        );
    }

    /**
     * Load a single tenant by its ID.
     *
     * This method retrieves the details of a specific tenant.
     *
     * @param string $id The ID of the tenant to load.
     *
     * @return array The tenant details.
     *
     * @throws AuthException If the load operation fails.
     */
    public function load(string $id): array
    {
        return $this->api->doGet(
            MgmtV1::$TENANT_LOAD_PATH . '?' . http_build_query(['id' => $id]),
            true
        );
    }

    /**
     * Load all tenants in the project.
     *
     * This method retrieves the details of every tenant configured in the project.
     *
     * @return array The response containing the list of 'tenants'.
     *
     * @throws AuthException If the load operation fails.
     */
    public function loadAll(): array
    {
        return $this->api->doGet(
            MgmtV1::$TENANT_LOAD_ALL_PATH,
            true
        );
    }

    /**
     * Search for tenants matching the provided filters.
     *
     * This method searches all tenants, optionally filtering by IDs, names,
     * self-provisioning domains, and custom attributes.
     *
     * @param array      $ids                      Optional list of tenant IDs to filter by.
     * @param array      $names                    Optional list of tenant names to filter by.
     * @param array      $selfProvisioningDomains  Optional list of self-provisioning domains to filter by.
     * @param array|null $customAttributes         Optional map of custom attributes to filter by.
     *
     * @return array The search response containing the matching tenants.
     *
     * @throws AuthException If the search operation fails.
     */
    public function searchAll(
        array $ids = [],
        array $names = [],
        array $selfProvisioningDomains = [],
        ?array $customAttributes = null
    ): array {
        $body = [
            'tenantIds' => $ids,
            'tenantNames' => $names,
            'tenantSelfProvisioningDomains' => $selfProvisioningDomains,
        ];

        if ($customAttributes !== null) {
            $body['customAttributes'] = $customAttributes;
        }

        return $this->api->doPost(
            MgmtV1::$TENANT_SEARCH_ALL_PATH,
            $body,
            true
        );
    }
}
