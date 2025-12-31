<?php
// phpcs:ignoreFile

namespace Descope\SDK\Management;

use Descope\SDK\EndpointsV1;

const DEFAULT_URL_PREFIX = "https://api";
const DEFAULT_DOMAIN = "descope.com";

class MgmtV1
{
    /**
     * Base URL for the Descope API.
     *
     * @var string
     */
    public static string $baseUrl = DEFAULT_URL_PREFIX . '.' . DEFAULT_DOMAIN;

    // Paths for various management operations
    public static string $TEMPLATE_EXPORT_PATH;
    public static string $TEMPLATE_IMPORT_PATH;
    public static string $FLOW_EXPORT_PATH;
    public static string $FLOW_DELETE_PATH;
    public static string $FLOW_LIST_PATH;
    public static string $ROLE_SEARCH_PATH;
    public static string $ROLE_LOAD_ALL_PATH;
    public static string $ROLE_DELETE_PATH;
    public static string $ROLE_UPDATE_PATH;
    public static string $ROLE_CREATE_PATH;
    public static string $PERMISSION_LOAD_ALL_PATH;
    public static string $PERMISSION_DELETE_PATH;
    public static string $PERMISSION_UPDATE_PATH;
    public static string $PERMISSION_CREATE_PATH;
    public static string $IMPERSONATE_PATH;
    public static string $UPDATE_JWT_PATH;
    public static string $SSO_CONFIGURE_SAML_BY_METADATA_SETTINGS;
    public static string $SSO_CONFIGURE_SAML_SETTINGS;
    public static string $SSO_CONFIGURE_OIDC_SETTINGS;
    public static string $SSO_LOAD_SETTINGS_PATH;
    public static string $SSO_MAPPING_PATH;
    public static string $SSO_METADATA_PATH;
    public static string $SSO_SETTINGS_PATH;
    public static string $ACCESS_KEY_DELETE_PATH;
    public static string $ACCESS_KEY_ACTIVATE_PATH;
    public static string $ACCESS_KEY_DEACTIVATE_PATH;
    public static string $ACCESS_KEY_UPDATE_PATH;
    public static string $ACCESS_KEYS_SEARCH_PATH;
    public static string $ACCESS_KEY_LOAD_PATH;
    public static string $ACCESS_KEY_CREATE_PATH;
    public static string $USER_HISTORY_PATH;
    public static string $USER_GENERATE_EMBEDDED_LINK_PATH;
    public static string $USER_GENERATE_ENCHANTED_LINK_FOR_TEST_PATH;
    public static string $USER_GENERATE_MAGIC_LINK_FOR_TEST_PATH;
    public static string $USER_GENERATE_OTP_FOR_TEST_PATH;
    public static string $USER_REMOVE_TENANT_PATH;
    public static string $USER_ADD_TENANT_PATH;
    public static string $USER_REMOVE_ALL_PASSKEYS_PATH;
    public static string $USER_EXPIRE_PASSWORD_PATH;
    public static string $USER_SET_ACTIVE_PASSWORD_PATH;
    public static string $USER_SET_TEMPORARY_PASSWORD_PATH;
    public static string $USER_SET_PASSWORD_PATH;
    public static string $USER_REMOVE_SSO_APPS;
    public static string $USER_SET_SSO_APPS;
    public static string $USER_ADD_SSO_APPS;
    public static string $USER_REMOVE_ROLE_PATH;
    public static string $USER_ADD_ROLE_PATH;
    public static string $USER_SET_ROLE_PATH;
    public static string $USER_UPDATE_CUSTOM_ATTRIBUTE_PATH;
    public static string $USER_UPDATE_PICTURE_PATH;
    public static string $USER_UPDATE_NAME_PATH;
    public static string $USER_UPDATE_PHONE_PATH;
    public static string $USER_UPDATE_EMAIL_PATH;
    public static string $USER_UPDATE_LOGIN_ID_PATH;
    public static string $USER_UPDATE_STATUS_PATH;
    public static string $USER_GET_PROVIDER_TOKEN;
    public static string $USERS_SEARCH_PATH;
    public static string $USER_LOAD_PATH;
    public static string $USER_DELETE_ALL_TEST_USERS_PATH;
    public static string $USER_LOGOUT_PATH;
    public static string $USER_DELETE_PATH;
    public static string $USER_UPDATE_PATH;
    public static string $USER_CREATE_BATCH_PATH;
    public static string $USER_CREATE_PATH;
    public static string $SSO_APPLICATION_LOAD_ALL_PATH;
    public static string $SSO_APPLICATION_LOAD_PATH;
    public static string $SSO_APPLICATION_DELETE_PATH;
    public static string $SSO_APPLICATION_SAML_UPDATE_PATH;
    public static string $SSO_APPLICATION_OIDC_UPDATE_PATH;
    public static string $SSO_APPLICATION_SAML_CREATE_PATH;
    public static string $SSO_APPLICATION_OIDC_CREATE_PATH;
    public static string $TENANT_SEARCH_ALL_PATH;
    public static string $TENANT_LOAD_ALL_PATH;
    public static string $TENANT_LOAD_PATH;
    public static string $TENANT_DELETE_PATH;
    public static string $TENANT_UPDATE_PATH;
    public static string $TENANT_CREATE_PATH;
    public static string $AUDIT_SEARCH;
    public static string $AUDIT_CREATE_EVENT;

    // Outbound Apps
    public static string $OUTBOUND_APP_USER_TOKEN_PATH;
    public static string $OUTBOUND_APP_DELETE_USER_TOKENS_PATH;
    public static string $OUTBOUND_APP_DELETE_TOKEN_BY_ID_PATH;

    /**
     * Sets the base URL based on the project ID, taking into account the region.
     *
     * @param string $projectId The project ID for determining the region.
     * @return void
     */
    public static function setBaseUrl(string $projectId): void
    {
        $region = EndpointsV1::extractRegionFromProjectId($projectId);
        $urlPrefix = DEFAULT_URL_PREFIX;

        if ($region) {
            $urlPrefix .= ".$region";
        }

        self::$baseUrl = "$urlPrefix." . DEFAULT_DOMAIN;
        self::updatePaths();
    }

    /**
     * Set the base URL directly from a string.
     * This allows for manual override of the base URL for different clusters.
     *
     * @param string $baseUrl The base URL to use for API endpoints.
     * @return void
     */
    public static function setBaseUrlFromString(string $baseUrl): void
    {
        self::$baseUrl = $baseUrl;
        self::updatePaths();
    }


    /**
     * Updates all API endpoint paths based on the current base URL.
     *
     * @return void
     */
    private static function updatePaths(): void
    {
        // Tenant
        self::$TENANT_CREATE_PATH = self::$baseUrl . "/v1/mgmt/tenant/create";
        self::$TENANT_UPDATE_PATH = self::$baseUrl . "/v1/mgmt/tenant/update";
        self::$TENANT_DELETE_PATH = self::$baseUrl . "/v1/mgmt/tenant/delete";
        self::$TENANT_LOAD_PATH = self::$baseUrl . "/v1/mgmt/tenant";
        self::$TENANT_LOAD_ALL_PATH = self::$baseUrl . "/v1/mgmt/tenant/all";
        self::$TENANT_SEARCH_ALL_PATH = self::$baseUrl . "/v1/mgmt/tenant/search";

        // SSO Applications
        self::$SSO_APPLICATION_OIDC_CREATE_PATH = self::$baseUrl . "/v1/mgmt/sso/idp/app/oidc/create";
        self::$SSO_APPLICATION_SAML_CREATE_PATH = self::$baseUrl . "/v1/mgmt/sso/idp/app/saml/create";
        self::$SSO_APPLICATION_OIDC_UPDATE_PATH = self::$baseUrl . "/v1/mgmt/sso/idp/app/oidc/update";
        self::$SSO_APPLICATION_SAML_UPDATE_PATH = self::$baseUrl . "/v1/mgmt/sso/idp/app/saml/update";
        self::$SSO_APPLICATION_DELETE_PATH = self::$baseUrl . "/v1/mgmt/sso/idp/app/delete";
        self::$SSO_APPLICATION_LOAD_PATH = self::$baseUrl . "/v1/mgmt/sso/idp/app/load";
        self::$SSO_APPLICATION_LOAD_ALL_PATH = self::$baseUrl . "/v1/mgmt/sso/idp/apps/load";

        // User
        self::$USER_CREATE_PATH = self::$baseUrl . "/v1/mgmt/user/create";
        self::$USER_CREATE_BATCH_PATH = self::$baseUrl . "/v1/mgmt/user/create/batch";
        self::$USER_UPDATE_PATH = self::$baseUrl . "/v1/mgmt/user/update";
        self::$USER_DELETE_PATH = self::$baseUrl . "/v1/mgmt/user/delete";
        self::$USER_LOGOUT_PATH = self::$baseUrl . "/v1/mgmt/user/logout";
        self::$USER_DELETE_ALL_TEST_USERS_PATH = self::$baseUrl . "/v1/mgmt/user/test/delete/all";
        self::$USER_LOAD_PATH = self::$baseUrl . "/v1/mgmt/user";
        self::$USERS_SEARCH_PATH = self::$baseUrl . "/v2/mgmt/user/search";
        self::$USER_GET_PROVIDER_TOKEN = self::$baseUrl . "/v1/mgmt/user/provider/token";
        self::$USER_UPDATE_STATUS_PATH = self::$baseUrl . "/v1/mgmt/user/update/status";
        self::$USER_UPDATE_LOGIN_ID_PATH = self::$baseUrl . "/v1/mgmt/user/update/loginid";
        self::$USER_UPDATE_EMAIL_PATH = self::$baseUrl . "/v1/mgmt/user/update/email";
        self::$USER_UPDATE_PHONE_PATH = self::$baseUrl . "/v1/mgmt/user/update/phone";
        self::$USER_UPDATE_NAME_PATH = self::$baseUrl . "/v1/mgmt/user/update/name";
        self::$USER_UPDATE_PICTURE_PATH = self::$baseUrl . "/v1/mgmt/user/update/picture";
        self::$USER_UPDATE_CUSTOM_ATTRIBUTE_PATH = self::$baseUrl . "/v1/mgmt/user/update/customAttribute";
        self::$USER_SET_ROLE_PATH = self::$baseUrl . "/v1/mgmt/user/update/role/set";
        self::$USER_ADD_ROLE_PATH = self::$baseUrl . "/v1/mgmt/user/update/role/add";
        self::$USER_REMOVE_ROLE_PATH = self::$baseUrl . "/v1/mgmt/user/update/role/remove";
        self::$USER_ADD_SSO_APPS = self::$baseUrl . "/v1/mgmt/user/update/ssoapp/add";
        self::$USER_SET_SSO_APPS = self::$baseUrl . "/v1/mgmt/user/update/ssoapp/set";
        self::$USER_REMOVE_SSO_APPS = self::$baseUrl . "/v1/mgmt/user/update/ssoapp/remove";
        self::$USER_SET_PASSWORD_PATH = self::$baseUrl . "/v1/mgmt/user/password/set";  // Deprecated
        self::$USER_SET_TEMPORARY_PASSWORD_PATH = self::$baseUrl . "/v1/mgmt/user/password/set/temporary";
        self::$USER_SET_ACTIVE_PASSWORD_PATH = self::$baseUrl . "/v1/mgmt/user/password/set/active";
        self::$USER_EXPIRE_PASSWORD_PATH = self::$baseUrl . "/v1/mgmt/user/password/expire";
        self::$USER_REMOVE_ALL_PASSKEYS_PATH = self::$baseUrl . "/v1/mgmt/user/passkeys/delete";
        self::$USER_ADD_TENANT_PATH = self::$baseUrl . "/v1/mgmt/user/update/tenant/add";
        self::$USER_REMOVE_TENANT_PATH = self::$baseUrl . "/v1/mgmt/user/update/tenant/remove";
        self::$USER_GENERATE_OTP_FOR_TEST_PATH = self::$baseUrl . "/v1/mgmt/tests/generate/otp";
        self::$USER_GENERATE_MAGIC_LINK_FOR_TEST_PATH = self::$baseUrl . "/v1/mgmt/tests/generate/magiclink";
        self::$USER_GENERATE_ENCHANTED_LINK_FOR_TEST_PATH = self::$baseUrl . "/v1/mgmt/tests/generate/enchantedlink";
        self::$USER_GENERATE_EMBEDDED_LINK_PATH = self::$baseUrl . "/v1/mgmt/user/signin/embeddedlink";
        self::$USER_HISTORY_PATH = self::$baseUrl . "/v1/mgmt/user/history";

        // Access Keys
        self::$ACCESS_KEY_CREATE_PATH = self::$baseUrl . "/v1/mgmt/accesskey/create";
        self::$ACCESS_KEY_LOAD_PATH = self::$baseUrl . "/v1/mgmt/accesskey";
        self::$ACCESS_KEYS_SEARCH_PATH = self::$baseUrl . "/v1/mgmt/accesskey/search";
        self::$ACCESS_KEY_UPDATE_PATH = self::$baseUrl . "/v1/mgmt/accesskey/update";
        self::$ACCESS_KEY_DEACTIVATE_PATH = self::$baseUrl . "/v1/mgmt/accesskey/deactivate";
        self::$ACCESS_KEY_ACTIVATE_PATH = self::$baseUrl . "/v1/mgmt/accesskey/activate";
        self::$ACCESS_KEY_DELETE_PATH = self::$baseUrl . "/v1/mgmt/accesskey/delete";

        // SSO
        self::$SSO_SETTINGS_PATH = self::$baseUrl . "/v1/mgmt/sso/settings";
        self::$SSO_METADATA_PATH = self::$baseUrl . "/v1/mgmt/sso/metadata";
        self::$SSO_MAPPING_PATH = self::$baseUrl . "/v1/mgmt/sso/mapping";
        self::$SSO_LOAD_SETTINGS_PATH = self::$baseUrl . "/v2/mgmt/sso/settings";  // v2 only
        self::$SSO_CONFIGURE_OIDC_SETTINGS = self::$baseUrl . "/v1/mgmt/sso/oidc";
        self::$SSO_CONFIGURE_SAML_SETTINGS = self::$baseUrl . "/v1/mgmt/sso/saml";
        self::$SSO_CONFIGURE_SAML_BY_METADATA_SETTINGS = self::$baseUrl . "/v1/mgmt/sso/saml/metadata";

        // JWT
        self::$UPDATE_JWT_PATH = self::$baseUrl . "/v1/mgmt/jwt/update";
        self::$IMPERSONATE_PATH = self::$baseUrl . "/v1/mgmt/impersonate";

        // Permissions
        self::$PERMISSION_CREATE_PATH = self::$baseUrl . "/v1/mgmt/permission/create";
        self::$PERMISSION_UPDATE_PATH = self::$baseUrl . "/v1/mgmt/permission/update";
        self::$PERMISSION_DELETE_PATH = self::$baseUrl . "/v1/mgmt/permission/delete";
        self::$PERMISSION_LOAD_ALL_PATH = self::$baseUrl . "/v1/mgmt/permission/all";

        // Role
        self::$ROLE_CREATE_PATH = self::$baseUrl . "/v1/mgmt/role/create";
        self::$ROLE_UPDATE_PATH = self::$baseUrl . "/v1/mgmt/role/update";
        self::$ROLE_DELETE_PATH = self::$baseUrl . "/v1/mgmt/role/delete";
        self::$ROLE_LOAD_ALL_PATH = self::$baseUrl . "/v1/mgmt/role/all";
        self::$ROLE_SEARCH_PATH = self::$baseUrl . "/v1/mgmt/role/search";

        // Flow
        self::$FLOW_LIST_PATH = self::$baseUrl . "/v1/mgmt/flow/list";
        self::$FLOW_DELETE_PATH = self::$baseUrl . "/v1/mgmt/flow/delete";
        self::$FLOW_EXPORT_PATH = self::$baseUrl . "/v1/mgmt/flow/export";
        self::$TEMPLATE_IMPORT_PATH = self::$baseUrl . "/v1/mgmt/template/import";
        self::$TEMPLATE_EXPORT_PATH = self::$baseUrl . "/v1/mgmt/template/export";

        // Audit 
        self::$AUDIT_SEARCH = self::$baseUrl . "/v1/mgmt/audit/search";
        self::$AUDIT_CREATE_EVENT = self::$baseUrl . "/v1/mgmt/audit/event";

        // Outbound Apps
        self::$OUTBOUND_APP_USER_TOKEN_PATH = self::$baseUrl . "/v1/mgmt/outbound/app/user/token";
        self::$OUTBOUND_APP_DELETE_USER_TOKENS_PATH = self::$baseUrl . "/v1/mgmt/outbound/user/tokens";
        self::$OUTBOUND_APP_DELETE_TOKEN_BY_ID_PATH = self::$baseUrl . "/v1/mgmt/outbound/token";
    }
}

/**
 * Class representing login options for various authentication methods.
 */
class LoginOptions
{
    /**
     * @var bool Indicates if step-up authentication is required.
     */
    public bool $stepup;

    /**
     * @var bool Indicates if MFA is required.
     */
    public bool $mfa;

    /**
     * @var array|null Custom claims to include in the JWT.
     */
    public ?array $customClaims;

    /**
     * @var array|null Options for templates.
     */
    public ?array $templateOptions;

    /**
     * Constructor for the LoginOptions class.
     *
     * @param bool $stepup Whether step-up is required.
     * @param bool $mfa Whether MFA is required.
     * @param array|null $customClaims Additional custom claims for the JWT.
     * @param array|null $templateOptions Options for templates.
     */
    public function __construct(
        bool $stepup = false,
        bool $mfa = false,
        ?array $customClaims = null,
        ?array $templateOptions = null
    ) {
        $this->stepup = $stepup;
        $this->mfa = $mfa;
        $this->customClaims = $customClaims;
        $this->templateOptions = $templateOptions;
    }

    /**
     * Converts the LoginOptions object to an array.
     *
     * @return array The array representation of the login options.
     */
    public function toArray(): array
    {
        return [
            'stepup' => $this->stepup,
            'mfa' => $this->mfa,
            'customClaims' => $this->customClaims,
            'templateOptions' => $this->templateOptions,
        ];
    }
}

/**
 * Class representing different delivery methods for authentication.
 */
class DeliveryMethod
{
    public const WHATSAPP = 1;
    public const SMS = 2;
    public const EMAIL = 3;
    public const EMBEDDED = 4;
    public const VOICE = 5;

    /**
     * @var int The delivery method value.
     */
    private int $value;

    /**
     * Constructor for the DeliveryMethod class.
     *
     * @param int $value The delivery method value.
     */
    private function __construct(int $value)
    {
        $this->value = $value;
    }

    public static function WHATSAPP(): self { return new self(self::WHATSAPP); }
    public static function SMS(): self { return new self(self::SMS); }
    public static function EMAIL(): self { return new self(self::EMAIL); }
    public static function EMBEDDED(): self { return new self(self::EMBEDDED); }
    public static function VOICE(): self { return new self(self::VOICE); }

    /**
     * Gets the value of the delivery method.
     *
     * @return int The delivery method value.
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * Converts the delivery method to a string.
     *
     * @return string The string representation of the delivery method.
     */
    public function __toString(): string
    {
        return (string) $this->value;
    }
}
