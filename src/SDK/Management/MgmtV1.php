<?php
// phpcs:ignoreFile

namespace Descope\SDK\Management;

use Descope\SDK\EndpointsV1;

const DEFAULT_URL_PREFIX = "https://api";
const DEFAULT_DOMAIN = "descope.com";

class MgmtV1
{
    private static $baseUrl = DEFAULT_URL_PREFIX . '.' . DEFAULT_DOMAIN;

    public static function setBaseUrl(string $projectId): void
    {
        $region = self::extractRegionFromProjectId($projectId);
        $urlPrefix = DEFAULT_URL_PREFIX;

        if ($region) {
            $urlPrefix .= ".$region";
        }

        self::$baseUrl = "$urlPrefix." . DEFAULT_DOMAIN;
        self::updatePaths();
    }

    private static function extractRegionFromProjectId(string $projectId): ?string
    {
        if (strlen($projectId) >= 32) {
            $region = substr($projectId, 1, 5);
            return !empty($region) ? $region : null;
        }
    }

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
        self::$USERS_SEARCH_PATH = self::$baseUrl . "/v1/mgmt/user/search";
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
    }
}

class LoginOptions
{
    public bool $stepup;
    public bool $mfa;
    public ?array $customClaims;
    public ?array $templateOptions;

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

    public function toArray()
    {
        return [
            'stepup' => $this->stepup,
            'mfa' => $this->mfa,
            'customClaims' => $this->customClaims,
            'templateOptions' => $this->templateOptions
        ];
    }
}

class DeliveryMethod
{
    public const WHATSAPP = 1;
    public const SMS = 2;
    public const EMAIL = 3;
    public const EMBEDDED = 4;
    public const VOICE = 5;

    private int $value;

    private function __construct(int $value)
    {
        $this->value = $value;
    }

    public static function WHATSAPP(): self
    {
        return new self(self::WHATSAPP);
    }

    public static function SMS(): self
    {
        return new self(self::SMS);
    }

    public static function EMAIL(): self
    {
        return new self(self::EMAIL);
    }

    public static function EMBEDDED(): self
    {
        return new self(self::EMBEDDED);
    }

    public static function VOICE(): self
    {
        return new self(self::VOICE);
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
