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

    /**
     * Create a new tenant with a caller-provided ID.
     *
     * This method creates a new tenant using the supplied ID rather than a
     * generated one. The ID must be unique within the project.
     *
     * @param string      $id                       The custom ID for the tenant.
     * @param string      $name                     The name of the tenant.
     * @param array       $selfProvisioningDomains  Optional list of domains that can self-provision into the tenant.
     * @param array|null  $customAttributes         Optional map of custom attributes for the tenant.
     *
     * @return array The create response containing the tenant 'id'.
     *
     * @throws AuthException If the create operation fails.
     */
    public function createWithId(
        string $id,
        string $name,
        array $selfProvisioningDomains = [],
        ?array $customAttributes = null
    ): array {
        $body = [
            'id' => $id,
            'name' => $name,
            'selfProvisioningDomains' => $selfProvisioningDomains,
        ];

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
     * Get the settings of a tenant.
     *
     * This method retrieves the settings configured for a specific tenant,
     * such as session and refresh token expiration, inactivity, and JIT settings.
     *
     * @param string $tenantId The ID of the tenant whose settings to load.
     *
     * @return array The tenant settings.
     *
     * @throws AuthException If the load operation fails.
     */
    public function getSettings(string $tenantId): array
    {
        return $this->api->doGet(
            MgmtV1::$TENANT_SETTINGS_PATH . '?' . http_build_query(['id' => $tenantId]),
            true
        );
    }

    /**
     * Configure the settings of a tenant.
     *
     * This method overwrites the tenant's settings with the provided values.
     * The $settings array is merged into the request body, so all fields should
     * be provided in full to avoid unintentionally clearing them.
     *
     * @param string $tenantId The ID of the tenant to configure.
     * @param array  $settings The settings to apply (e.g. 'selfProvisioningDomains',
     *                         'authType', 'enabled', 'refreshTokenExpiration',
     *                         'refreshTokenExpirationUnit', 'sessionTokenExpiration',
     *                         'sessionTokenExpirationUnit', 'stepupTokenExpiration',
     *                         'stepupTokenExpirationUnit', 'enableInactivity',
     *                         'inactivityTime', 'inactivityTimeUnit', 'domains',
     *                         'JITDisabled').
     *
     * @return void
     *
     * @throws AuthException If the configure operation fails.
     */
    public function configureSettings(string $tenantId, array $settings): void
    {
        // Explicit tenantId last so a stray 'tenantId' key in $settings cannot redirect the call.
        $body = array_merge($settings, ['tenantId' => $tenantId]);

        $this->api->doPost(
            MgmtV1::$TENANT_SETTINGS_PATH,
            $body,
            true
        );
    }

    /**
     * Generate an admin SSO configuration link for a tenant.
     *
     * This method generates a link that a tenant admin can use to self-configure
     * their SSO settings, optionally scoped to a specific SSO configuration.
     *
     * @param string $tenantId       The ID of the tenant.
     * @param int    $expireDuration The link expiration time.
     * @param string $ssoId          Optional SSO configuration ID.
     * @param string $email          Optional email to send the link to.
     * @param string $templateId     Optional template ID for the email.
     * @param string $actorId        Optional actor ID initiating the request.
     *
     * @return array The response containing the admin SSO configuration link.
     *
     * @throws AuthException If the generate operation fails.
     */
    public function generateSSOConfigurationLink(
        string $tenantId,
        int $expireDuration,
        string $ssoId = "",
        string $email = "",
        string $templateId = "",
        string $actorId = ""
    ): array {
        $body = [
            'tenantId' => $tenantId,
            'expireTime' => $expireDuration,
            'ssoId' => $ssoId,
            'email' => $email,
            'templateId' => $templateId,
            'actorId' => $actorId,
        ];

        return $this->api->doPost(
            MgmtV1::$TENANT_GENERATE_SSO_CONFIGURATION_LINK_PATH,
            $body,
            true
        );
    }

    /**
     * Revoke an admin SSO configuration link for a tenant.
     *
     * This method revokes previously generated admin SSO configuration links,
     * optionally scoped to a specific SSO configuration.
     *
     * @param string $tenantId The ID of the tenant.
     * @param string $ssoId    Optional SSO configuration ID.
     *
     * @return void
     *
     * @throws AuthException If the revoke operation fails.
     */
    public function revokeSSOConfigurationLink(string $tenantId, string $ssoId = ""): void
    {
        $body = [
            'tenantId' => $tenantId,
            'ssoId' => $ssoId,
        ];

        $this->api->doPost(
            MgmtV1::$TENANT_REVOKE_SSO_CONFIGURATION_LINK_PATH,
            $body,
            true
        );
    }

    /**
     * Update the default roles of a tenant.
     *
     * This method sets the default roles assigned to users within the tenant.
     *
     * @param string $tenantId     The ID of the tenant.
     * @param array  $defaultRoles The list of default role names to assign.
     *
     * @return void
     *
     * @throws AuthException If the update operation fails.
     */
    public function updateDefaultRoles(string $tenantId, array $defaultRoles): void
    {
        $body = [
            'id' => $tenantId,
            'defaultRoles' => $defaultRoles,
        ];

        $this->api->doPost(
            MgmtV1::$TENANT_UPDATE_DEFAULT_ROLES_PATH,
            $body,
            true
        );
    }
}
