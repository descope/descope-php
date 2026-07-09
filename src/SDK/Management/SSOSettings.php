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

    /**
     * Load all SSO settings for a tenant.
     *
     * This method retrieves every SSO configuration associated with the given
     * tenant (a tenant may have multiple SSO configurations).
     *
     * @param string $tenantId The ID of the tenant whose SSO settings are loaded.
     *
     * @return array The tenant's SSO settings.
     *
     * @throws AuthException If the load operation fails.
     */
    public function loadAllSettings(string $tenantId): array
    {
        return $this->api->doGet(
            MgmtV1::$SSO_LOAD_ALL_SETTINGS_PATH . '?' . http_build_query(['tenantId' => $tenantId]),
            true
        );
    }

    /**
     * Configure the SSO redirect URL(s) for a tenant.
     *
     * At least one of $samlRedirectUrl or $oauthRedirectUrl should be provided.
     *
     * @param string      $tenantId         The ID of the tenant to configure.
     * @param string|null $samlRedirectUrl  Optional SAML redirect URL.
     * @param string|null $oauthRedirectUrl Optional OAuth (OIDC) redirect URL.
     * @param string      $ssoId            Optional SSO configuration ID.
     *
     * @return void
     *
     * @throws AuthException If the configure operation fails.
     */
    public function configureSSORedirectURL(
        string $tenantId,
        ?string $samlRedirectUrl = null,
        ?string $oauthRedirectUrl = null,
        string $ssoId = ""
    ): void {
        $body = [
            'tenantId' => $tenantId,
        ];

        if ($samlRedirectUrl !== null) {
            $body['samlRedirectUrl'] = $samlRedirectUrl;
        }
        if ($oauthRedirectUrl !== null) {
            $body['oauthRedirectUrl'] = $oauthRedirectUrl;
        }
        if ($ssoId !== "") {
            $body['ssoId'] = $ssoId;
        }

        $this->api->doPost(
            MgmtV1::$SSO_REDIRECT_URL_PATH,
            $body,
            true
        );
    }

    /**
     * Create a new SSO configuration for a tenant.
     *
     * @param string $tenantId    The ID of the tenant to configure.
     * @param string $ssoId       The ID to assign to the new SSO configuration.
     * @param string $displayName The display name of the new SSO configuration.
     *
     * @return array The created SSO configuration.
     *
     * @throws AuthException If the create operation fails.
     */
    public function newSettings(string $tenantId, string $ssoId, string $displayName): array
    {
        $body = [
            'tenantId' => $tenantId,
            'ssoId' => $ssoId,
            'displayName' => $displayName,
        ];

        return $this->api->doPost(
            MgmtV1::$SSO_SETTINGS_NEW_PATH,
            $body,
            true
        );
    }

    /**
     * Get the SSO settings for a tenant (legacy v1).
     *
     * @deprecated Use loadSettings() instead.
     *
     * @param string $tenantId The ID of the tenant whose SSO settings are loaded.
     *
     * @return array The tenant's SSO settings.
     *
     * @throws AuthException If the load operation fails.
     */
    public function getSettings(string $tenantId): array
    {
        return $this->api->doGet(
            MgmtV1::$SSO_SETTINGS_PATH . '?' . http_build_query(['tenantId' => $tenantId]),
            true
        );
    }

    /**
     * Configure the SSO settings for a tenant (legacy v1).
     *
     * @deprecated Use configureSAMLSettings() instead.
     *
     * @param string $tenantId    The ID of the tenant to configure.
     * @param string $idpURL      The IdP URL.
     * @param string $idpCert     The IdP certificate.
     * @param string $entityID    The IdP entity ID.
     * @param string $redirectURL The redirect URL for the SSO flow.
     * @param array  $domains     Optional list of domains associated with the tenant.
     *
     * @return void
     *
     * @throws AuthException If the configure operation fails.
     */
    public function configureSettings(
        string $tenantId,
        string $idpURL,
        string $idpCert,
        string $entityID,
        string $redirectURL,
        array $domains = []
    ): void {
        $body = [
            'tenantId' => $tenantId,
            'idpURL' => $idpURL,
            'idpCert' => $idpCert,
            'entityId' => $entityID,
            'redirectURL' => $redirectURL,
            'domains' => $domains,
        ];

        $this->api->doPost(
            MgmtV1::$SSO_SETTINGS_PATH,
            $body,
            true
        );
    }

    /**
     * Configure the SSO settings for a tenant using metadata (legacy v1).
     *
     * @deprecated Use configureSAMLSettingsByMetadata() instead.
     *
     * @param string $tenantId       The ID of the tenant to configure.
     * @param string $idpMetadataURL The IdP metadata URL.
     * @param string $redirectURL    The redirect URL for the SSO flow.
     * @param array  $domains        Optional list of domains associated with the tenant.
     *
     * @return void
     *
     * @throws AuthException If the configure operation fails.
     */
    public function configureMetadata(
        string $tenantId,
        string $idpMetadataURL,
        string $redirectURL,
        array $domains = []
    ): void {
        $body = [
            'tenantId' => $tenantId,
            'idpMetadataURL' => $idpMetadataURL,
            'redirectURL' => $redirectURL,
            'domains' => $domains,
        ];

        $this->api->doPost(
            MgmtV1::$SSO_METADATA_PATH,
            $body,
            true
        );
    }

    /**
     * Configure the SSO role/attribute mapping for a tenant (legacy v1).
     *
     * @deprecated Use configureSAMLSettings() or configureSAMLSettingsByMetadata() instead.
     *
     * @param string     $tenantId         The ID of the tenant to configure.
     * @param array      $roleMappings     Optional list of role mappings. Each entry
     *                                      is an assoc array with keys "groups" and "roleName".
     * @param array|null $attributeMapping Optional attribute mapping.
     *
     * @return void
     *
     * @throws AuthException If the configure operation fails.
     */
    public function configureMapping(
        string $tenantId,
        array $roleMappings = [],
        ?array $attributeMapping = null
    ): void {
        $body = [
            'tenantId' => $tenantId,
            'roleMappings' => $roleMappings,
            'attributeMapping' => $attributeMapping,
        ];

        $this->api->doPost(
            MgmtV1::$SSO_MAPPING_PATH,
            $body,
            true
        );
    }

    /**
     * Recalculate the SSO mappings for a tenant.
     *
     * @param string $tenantId The ID of the tenant whose mappings are recalculated.
     * @param string $ssoId    Optional SSO configuration ID.
     *
     * @return void
     *
     * @throws AuthException If the recalculate operation fails.
     */
    public function recalculateSSOMappings(string $tenantId, string $ssoId = ""): void
    {
        $body = [
            'tenantId' => $tenantId,
        ];

        if ($ssoId !== "") {
            $body['ssoId'] = $ssoId;
        }

        $this->api->doPost(
            MgmtV1::$SSO_RECALCULATE_MAPPINGS_PATH,
            $body,
            true
        );
    }
}
