<?php

namespace Descope\SDK\Management;

use Descope\SDK\API;
use Descope\SDK\Exception\AuthException;
use Descope\SDK\Management\MgmtV1;

/**
 * Class SSOApplication
 *
 * Manages SSO applications (OIDC and SAML) for Descope acting as an Identity Provider (IdP).
 * Provides methods to create, update, delete, and load SSO applications.
 */
class SSOApplication
{
    private API $api;

    /**
     * SSOApplication constructor.
     *
     * @param API $api The API instance to be used for making requests.
     */
    public function __construct(API $api)
    {
        $this->api = $api;
    }

    /**
     * Create a new OIDC SSO application.
     *
     * @param string      $name         The name of the SSO application.
     * @param string      $loginPageUrl The URL of the login page for this application.
     * @param string|null $id           Optional custom ID for the SSO application.
     * @param bool        $enabled      Whether the application is enabled. Defaults to true.
     * @param string|null $description  Optional description of the application.
     * @param string|null $logo         Optional logo (base64 encoded image) for the application.
     *
     * @return array The response containing the created application's 'id'.
     *
     * @throws AuthException If the create operation fails.
     */
    public function createOidcApplication(
        string $name,
        string $loginPageUrl,
        ?string $id = null,
        bool $enabled = true,
        ?string $description = null,
        ?string $logo = null
    ): array {
        $body = [
            'name' => $name,
            'loginPageUrl' => $loginPageUrl,
            'enabled' => $enabled,
        ];

        if ($id !== null) {
            $body['id'] = $id;
        }

        if ($description !== null) {
            $body['description'] = $description;
        }

        if ($logo !== null) {
            $body['logo'] = $logo;
        }

        return $this->api->doPost(
            MgmtV1::$SSO_APPLICATION_OIDC_CREATE_PATH,
            $body,
            true
        );
    }

    /**
     * Create a new SAML SSO application.
     *
     * @param string      $name                The name of the SSO application.
     * @param string      $loginPageUrl        The URL of the login page for this application.
     * @param string|null $id                  Optional custom ID for the SSO application.
     * @param bool        $enabled             Whether the application is enabled. Defaults to true.
     * @param string|null $description         Optional description of the application.
     * @param string|null $logo                Optional logo (base64 encoded image) for the application.
     * @param bool        $useMetadataInfo     Whether to use metadata URL for configuration. Defaults to false.
     * @param string|null $metadataUrl         Optional SAML metadata URL (used when $useMetadataInfo is true).
     * @param string|null $entityId            Optional SAML entity ID (used when $useMetadataInfo is false).
     * @param string|null $acsUrl              Optional ACS URL (used when $useMetadataInfo is false).
     * @param string|null $certificate         Optional certificate (used when $useMetadataInfo is false).
     * @param array       $attributeMapping    Attribute mapping between Descope and the application.
     * @param array       $groupsMapping       Groups mapping between Descope and the application.
     * @param array       $acsAllowedCallbacks List of allowed ACS callback URLs.
     * @param string|null $subjectNameIdType   Optional subject name ID type.
     * @param string|null $subjectNameIdFormat Optional subject name ID format.
     * @param bool|null   $defaultRelayState   Optional default relay state.
     * @param bool|null   $forceAuthentication Optional flag to force authentication.
     *
     * @return array The response containing the created application's 'id'.
     *
     * @throws AuthException If the create operation fails.
     */
    public function createSamlApplication(
        string $name,
        string $loginPageUrl,
        ?string $id = null,
        bool $enabled = true,
        ?string $description = null,
        ?string $logo = null,
        bool $useMetadataInfo = false,
        ?string $metadataUrl = null,
        ?string $entityId = null,
        ?string $acsUrl = null,
        ?string $certificate = null,
        array $attributeMapping = [],
        array $groupsMapping = [],
        array $acsAllowedCallbacks = [],
        ?string $subjectNameIdType = null,
        ?string $subjectNameIdFormat = null,
        ?bool $defaultRelayState = null,
        ?bool $forceAuthentication = null
    ): array {
        $body = [
            'name' => $name,
            'loginPageUrl' => $loginPageUrl,
            'enabled' => $enabled,
            'useMetadataInfo' => $useMetadataInfo,
            'attributeMapping' => $attributeMapping,
            'groupsMapping' => $groupsMapping,
            'acsAllowedCallbacks' => $acsAllowedCallbacks,
        ];

        if ($id !== null) {
            $body['id'] = $id;
        }

        if ($description !== null) {
            $body['description'] = $description;
        }

        if ($logo !== null) {
            $body['logo'] = $logo;
        }

        if ($metadataUrl !== null) {
            $body['metadataUrl'] = $metadataUrl;
        }

        if ($entityId !== null) {
            $body['entityId'] = $entityId;
        }

        if ($acsUrl !== null) {
            $body['acsUrl'] = $acsUrl;
        }

        if ($certificate !== null) {
            $body['certificate'] = $certificate;
        }

        if ($subjectNameIdType !== null) {
            $body['subjectNameIdType'] = $subjectNameIdType;
        }

        if ($subjectNameIdFormat !== null) {
            $body['subjectNameIdFormat'] = $subjectNameIdFormat;
        }

        if ($defaultRelayState !== null) {
            $body['defaultRelayState'] = $defaultRelayState;
        }

        if ($forceAuthentication !== null) {
            $body['forceAuthentication'] = $forceAuthentication;
        }

        return $this->api->doPost(
            MgmtV1::$SSO_APPLICATION_SAML_CREATE_PATH,
            $body,
            true
        );
    }

    /**
     * Update an existing OIDC SSO application.
     *
     * @param string      $id           The ID of the SSO application to update.
     * @param string      $name         The name of the SSO application.
     * @param string      $loginPageUrl The URL of the login page for this application.
     * @param bool        $enabled      Whether the application is enabled. Defaults to true.
     * @param string|null $description  Optional description of the application.
     * @param string|null $logo         Optional logo (base64 encoded image) for the application.
     *
     * @return void
     *
     * @throws AuthException If the update operation fails.
     */
    public function updateOidcApplication(
        string $id,
        string $name,
        string $loginPageUrl,
        bool $enabled = true,
        ?string $description = null,
        ?string $logo = null
    ): void {
        $body = [
            'id' => $id,
            'name' => $name,
            'loginPageUrl' => $loginPageUrl,
            'enabled' => $enabled,
        ];

        if ($description !== null) {
            $body['description'] = $description;
        }

        if ($logo !== null) {
            $body['logo'] = $logo;
        }

        $this->api->doPost(
            MgmtV1::$SSO_APPLICATION_OIDC_UPDATE_PATH,
            $body,
            true
        );
    }

    /**
     * Update an existing SAML SSO application.
     *
     * @param string      $id                  The ID of the SSO application to update.
     * @param string      $name                The name of the SSO application.
     * @param string      $loginPageUrl        The URL of the login page for this application.
     * @param bool        $enabled             Whether the application is enabled. Defaults to true.
     * @param string|null $description         Optional description of the application.
     * @param string|null $logo                Optional logo (base64 encoded image) for the application.
     * @param bool        $useMetadataInfo     Whether to use metadata URL for configuration. Defaults to false.
     * @param string|null $metadataUrl         Optional SAML metadata URL (used when $useMetadataInfo is true).
     * @param string|null $entityId            Optional SAML entity ID (used when $useMetadataInfo is false).
     * @param string|null $acsUrl              Optional ACS URL (used when $useMetadataInfo is false).
     * @param string|null $certificate         Optional certificate (used when $useMetadataInfo is false).
     * @param array       $attributeMapping    Attribute mapping between Descope and the application.
     * @param array       $groupsMapping       Groups mapping between Descope and the application.
     * @param array       $acsAllowedCallbacks List of allowed ACS callback URLs.
     * @param string|null $subjectNameIdType   Optional subject name ID type.
     * @param string|null $subjectNameIdFormat Optional subject name ID format.
     * @param bool|null   $defaultRelayState   Optional default relay state.
     * @param bool|null   $forceAuthentication Optional flag to force authentication.
     *
     * @return void
     *
     * @throws AuthException If the update operation fails.
     */
    public function updateSamlApplication(
        string $id,
        string $name,
        string $loginPageUrl,
        bool $enabled = true,
        ?string $description = null,
        ?string $logo = null,
        bool $useMetadataInfo = false,
        ?string $metadataUrl = null,
        ?string $entityId = null,
        ?string $acsUrl = null,
        ?string $certificate = null,
        array $attributeMapping = [],
        array $groupsMapping = [],
        array $acsAllowedCallbacks = [],
        ?string $subjectNameIdType = null,
        ?string $subjectNameIdFormat = null,
        ?bool $defaultRelayState = null,
        ?bool $forceAuthentication = null
    ): void {
        $body = [
            'id' => $id,
            'name' => $name,
            'loginPageUrl' => $loginPageUrl,
            'enabled' => $enabled,
            'useMetadataInfo' => $useMetadataInfo,
            'attributeMapping' => $attributeMapping,
            'groupsMapping' => $groupsMapping,
            'acsAllowedCallbacks' => $acsAllowedCallbacks,
        ];

        if ($description !== null) {
            $body['description'] = $description;
        }

        if ($logo !== null) {
            $body['logo'] = $logo;
        }

        if ($metadataUrl !== null) {
            $body['metadataUrl'] = $metadataUrl;
        }

        if ($entityId !== null) {
            $body['entityId'] = $entityId;
        }

        if ($acsUrl !== null) {
            $body['acsUrl'] = $acsUrl;
        }

        if ($certificate !== null) {
            $body['certificate'] = $certificate;
        }

        if ($subjectNameIdType !== null) {
            $body['subjectNameIdType'] = $subjectNameIdType;
        }

        if ($subjectNameIdFormat !== null) {
            $body['subjectNameIdFormat'] = $subjectNameIdFormat;
        }

        if ($defaultRelayState !== null) {
            $body['defaultRelayState'] = $defaultRelayState;
        }

        if ($forceAuthentication !== null) {
            $body['forceAuthentication'] = $forceAuthentication;
        }

        $this->api->doPost(
            MgmtV1::$SSO_APPLICATION_SAML_UPDATE_PATH,
            $body,
            true
        );
    }

    /**
     * Delete an SSO application.
     *
     * @param string $id The ID of the SSO application to delete.
     *
     * @return void
     *
     * @throws AuthException If the delete operation fails.
     */
    public function delete(string $id): void
    {
        $body = [
            'id' => $id,
        ];

        $this->api->doPost(
            MgmtV1::$SSO_APPLICATION_DELETE_PATH,
            $body,
            true
        );
    }

    /**
     * Load an SSO application by its ID.
     *
     * @param string $id The ID of the SSO application to load.
     *
     * @return array The SSO application details.
     *
     * @throws AuthException If the load operation fails.
     */
    public function load(string $id): array
    {
        return $this->api->doGet(
            MgmtV1::$SSO_APPLICATION_LOAD_PATH . '?' . http_build_query(['id' => $id]),
            true
        );
    }

    /**
     * Load all SSO applications.
     *
     * @return array The response containing all SSO applications under the 'apps' key.
     *
     * @throws AuthException If the load operation fails.
     */
    public function loadAll(): array
    {
        return $this->api->doGet(
            MgmtV1::$SSO_APPLICATION_LOAD_ALL_PATH,
            true
        );
    }
}
