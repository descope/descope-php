<?php

namespace Descope\SDK\Management;

use Descope\SDK\API;
use Descope\SDK\Exception\AuthException;
use Descope\SDK\Management\MgmtV1;

/**
 * Class SSOSettings
 *
 * Manages the SSO (Single Sign-On) configuration of a Descope tenant.
 * This allows configuring a tenant's SSO provider using either OIDC or SAML
 * (directly or via metadata), loading the current settings, and deleting them.
 *
 * Note: this manages a tenant's SSO configuration and is distinct from the
 * auth-flow SSO class in the Auth namespace.
 */
class SSOSettings
{
    private API $api;

    /**
     * SSOSettings constructor.
     *
     * @param API $api The API instance to be used for making requests.
     */
    public function __construct(API $api)
    {
        $this->api = $api;
    }

    /**
     * Load the SSO settings for a tenant.
     *
     * This method retrieves the current SSO configuration for the given tenant.
     *
     * @param string $tenantId The ID of the tenant whose SSO settings are loaded.
     *
     * @return array The tenant's SSO settings.
     *
     * @throws AuthException If the load operation fails.
     */
    public function loadSettings(string $tenantId): array
    {
        return $this->api->doGet(
            MgmtV1::$SSO_LOAD_SETTINGS_PATH . '?' . http_build_query(['tenantId' => $tenantId]),
            true
        );
    }

    /**
     * Configure the OIDC SSO settings for a tenant.
     *
     * This method overwrites the tenant's SSO configuration with the provided
     * OIDC settings.
     *
     * @param string $tenantId The ID of the tenant to configure.
     * @param array  $settings The OIDC settings (assoc array with keys such as
     *                         name, clientId, clientSecret, redirectUrl, authUrl,
     *                         tokenUrl, userDataUrl, scope, etc.). Passed through as-is.
     * @param array  $domains  Optional list of domains associated with the tenant.
     *
     * @return void
     *
     * @throws AuthException If the configure operation fails.
     */
    public function configureOIDCSettings(string $tenantId, array $settings, array $domains = []): void
    {
        $body = [
            'tenantId' => $tenantId,
            'settings' => $settings,
            'domains' => $domains,
        ];

        $this->api->doPost(
            MgmtV1::$SSO_CONFIGURE_OIDC_SETTINGS,
            $body,
            true
        );
    }

    /**
     * Configure the SAML SSO settings for a tenant.
     *
     * This method overwrites the tenant's SSO configuration with the provided
     * SAML settings.
     *
     * @param string      $tenantId    The ID of the tenant to configure.
     * @param array       $settings    The SAML settings. Passed through as-is.
     * @param string|null $redirectUrl Optional redirect URL for the SSO flow.
     * @param array       $domains     Optional list of domains associated with the tenant.
     *
     * @return void
     *
     * @throws AuthException If the configure operation fails.
     */
    public function configureSAMLSettings(
        string $tenantId,
        array $settings,
        ?string $redirectUrl = null,
        array $domains = []
    ): void {
        $body = [
            'tenantId' => $tenantId,
            'settings' => $settings,
            'domains' => $domains,
        ];

        if ($redirectUrl !== null) {
            $body['redirectUrl'] = $redirectUrl;
        }

        $this->api->doPost(
            MgmtV1::$SSO_CONFIGURE_SAML_SETTINGS,
            $body,
            true
        );
    }

    /**
     * Configure the SAML SSO settings for a tenant using metadata.
     *
     * This method overwrites the tenant's SSO configuration with the provided
     * SAML settings supplied via metadata.
     *
     * @param string      $tenantId    The ID of the tenant to configure.
     * @param array       $settings    The SAML metadata settings. Passed through as-is.
     * @param string|null $redirectUrl Optional redirect URL for the SSO flow.
     * @param array       $domains     Optional list of domains associated with the tenant.
     *
     * @return void
     *
     * @throws AuthException If the configure operation fails.
     */
    public function configureSAMLSettingsByMetadata(
        string $tenantId,
        array $settings,
        ?string $redirectUrl = null,
        array $domains = []
    ): void {
        $body = [
            'tenantId' => $tenantId,
            'settings' => $settings,
            'domains' => $domains,
        ];

        if ($redirectUrl !== null) {
            $body['redirectUrl'] = $redirectUrl;
        }

        $this->api->doPost(
            MgmtV1::$SSO_CONFIGURE_SAML_BY_METADATA_SETTINGS,
            $body,
            true
        );
    }

    /**
     * Delete the SSO settings for a tenant.
     *
     * This method removes the SSO configuration for the given tenant.
     *
     * @param string $tenantId The ID of the tenant whose SSO settings are deleted.
     *
     * @return void
     *
     * @throws AuthException If the delete operation fails.
     */
    public function deleteSettings(string $tenantId): void
    {
        $this->api->doDelete(
            MgmtV1::$SSO_SETTINGS_PATH . '?' . http_build_query(['tenantId' => $tenantId])
        );
    }
}
