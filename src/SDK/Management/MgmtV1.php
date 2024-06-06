<?php

namespace Descope\SDK\Management;

use Descope\SDK\EndpointsV1;

const DEFAULT_URL_PREFIX = "https://api";
const DEFAULT_DOMAIN = "descope.com";

private static $baseUrl;

public static function setBaseUrl(string $projectId): void
{
    $region = self::extractRegionFromProjectId($projectId);
    $urlPrefix = self::DEFAULT_URL_PREFIX;

    if ($region) {
        $urlPrefix .= ".$region";
    }

    self::$baseUrl = "$urlPrefix." . self::DEFAULT_DOMAIN;
}

private static function extractRegionFromProjectId(string $projectId): ?string
{
    // Extract the region based on the given logic
    $region = substr($projectId, 1, -27);
    return !empty($region) ? $region : null;
}

public static function getBaseUrl(): string
{
    return self::$baseUrl ?? self::DEFAULT_URL_PREFIX . "." . self::DEFAULT_DOMAIN;
}

class MgmtV1
{
    // Tenant
    public const TENANT_CREATE_PATH = self::getBaseUrl() . "/v1/mgmt/tenant/create";
    public const TENANT_UPDATE_PATH = self::getBaseUrl() . "/v1/mgmt/tenant/update";
    public const TENANT_DELETE_PATH = self::getBaseUrl() . "/v1/mgmt/tenant/delete";
    public const TENANT_LOAD_PATH = self::getBaseUrl() . "/v1/mgmt/tenant";
    public const TENANT_LOAD_ALL_PATH = self::getBaseUrl() . "/v1/mgmt/tenant/all";
    public const TENANT_SEARCH_ALL_PATH = self::getBaseUrl() . "/v1/mgmt/tenant/search";

    // SSO Applications
    public const SSO_APPLICATION_OIDC_CREATE_PATH = self::getBaseUrl() . "/v1/mgmt/sso/idp/app/oidc/create";
    public const SSO_APPLICATION_SAML_CREATE_PATH = self::getBaseUrl() . "/v1/mgmt/sso/idp/app/saml/create";
    public const SSO_APPLICATION_OIDC_UPDATE_PATH = self::getBaseUrl() . "/v1/mgmt/sso/idp/app/oidc/update";
    public const SSO_APPLICATION_SAML_UPDATE_PATH = self::getBaseUrl() . "/v1/mgmt/sso/idp/app/saml/update";
    public const SSO_APPLICATION_DELETE_PATH = self::getBaseUrl() . "/v1/mgmt/sso/idp/app/delete";
    public const SSO_APPLICATION_LOAD_PATH = self::getBaseUrl() . "/v1/mgmt/sso/idp/app/load";
    public const SSO_APPLICATION_LOAD_ALL_PATH = self::getBaseUrl() . "/v1/mgmt/sso/idp/apps/load";

    // User
    public const USER_CREATE_PATH = self::getBaseUrl() . "/v1/mgmt/user/create";
    public const USER_CREATE_BATCH_PATH = self::getBaseUrl() . "/v1/mgmt/user/create/batch";
    public const USER_UPDATE_PATH = self::getBaseUrl() . "/v1/mgmt/user/update";
    public const USER_DELETE_PATH = self::getBaseUrl() . "/v1/mgmt/user/delete";
    public const USER_LOGOUT_PATH = self::getBaseUrl() . "/v1/mgmt/user/logout";
    public const USER_DELETE_ALL_TEST_USERS_PATH = self::getBaseUrl() . "/v1/mgmt/user/test/delete/all";
    public const USER_LOAD_PATH = self::getBaseUrl() . "/v1/mgmt/user";
    public const USERS_SEARCH_PATH = self::getBaseUrl() . "/v1/mgmt/user/search";
    public const USER_GET_PROVIDER_TOKEN = self::getBaseUrl() . "/v1/mgmt/user/provider/token";
    public const USER_UPDATE_STATUS_PATH = self::getBaseUrl() . "/v1/mgmt/user/update/status";
    public const USER_UPDATE_LOGIN_ID_PATH = self::getBaseUrl() . "/v1/mgmt/user/update/loginid";
    public const USER_UPDATE_EMAIL_PATH = self::getBaseUrl() . "/v1/mgmt/user/update/email";
    public const USER_UPDATE_PHONE_PATH = self::getBaseUrl() . "/v1/mgmt/user/update/phone";
    public const USER_UPDATE_NAME_PATH = self::getBaseUrl() . "/v1/mgmt/user/update/name";
    public const USER_UPDATE_PICTURE_PATH = self::getBaseUrl() . "/v1/mgmt/user/update/picture";
    public const USER_UPDATE_CUSTOM_ATTRIBUTE_PATH = self::getBaseUrl() . "/v1/mgmt/user/update/customAttribute";
    public const USER_SET_ROLE_PATH = self::getBaseUrl() . "/v1/mgmt/user/update/role/set";
    public const USER_ADD_ROLE_PATH = self::getBaseUrl() . "/v1/mgmt/user/update/role/add";
    public const USER_REMOVE_ROLE_PATH = self::getBaseUrl() . "/v1/mgmt/user/update/role/remove";
    public const USER_ADD_SSO_APPS = self::getBaseUrl() . "/v1/mgmt/user/update/ssoapp/add";
    public const USER_SET_SSO_APPS = self::getBaseUrl() . "/v1/mgmt/user/update/ssoapp/set";
    public const USER_REMOVE_SSO_APPS = self::getBaseUrl() . "/v1/mgmt/user/update/ssoapp/remove";
    public const USER_SET_PASSWORD_PATH = self::getBaseUrl() . "/v1/mgmt/user/password/set";  // Deprecated
    public const USER_SET_TEMPORARY_PASSWORD_PATH = self::getBaseUrl() . "/v1/mgmt/user/password/set/temporary";
    public const USER_SET_ACTIVE_PASSWORD_PATH = self::getBaseUrl() . "/v1/mgmt/user/password/set/active";
    public const USER_EXPIRE_PASSWORD_PATH = self::getBaseUrl() . "/v1/mgmt/user/password/expire";
    public const USER_REMOVE_ALL_PASSKEYS_PATH = self::getBaseUrl() . "/v1/mgmt/user/passkeys/delete";
    public const USER_ADD_TENANT_PATH = self::getBaseUrl() . "/v1/mgmt/user/update/tenant/add";
    public const USER_REMOVE_TENANT_PATH = self::getBaseUrl() . "/v1/mgmt/user/update/tenant/remove";
    public const USER_GENERATE_OTP_FOR_TEST_PATH = self::getBaseUrl() . "/v1/mgmt/tests/generate/otp";
    public const USER_GENERATE_MAGIC_LINK_FOR_TEST_PATH = self::getBaseUrl() . "/v1/mgmt/tests/generate/magiclink";
    public const USER_GENERATE_ENCHANTED_LINK_FOR_TEST_PATH = self::getBaseUrl() . "/v1/mgmt/tests/generate/enchantedlink";
    public const USER_GENERATE_EMBEDDED_LINK_PATH = self::getBaseUrl() . "/v1/mgmt/user/signin/embeddedlink";
    public const USER_HISTORY_PATH = self::getBaseUrl() . "/v1/mgmt/user/history";

    // Access Keys
    public const ACCESS_KEY_CREATE_PATH = self::getBaseUrl() . "/v1/mgmt/accesskey/create";
    public const ACCESS_KEY_LOAD_PATH = self::getBaseUrl() . "/v1/mgmt/accesskey";
    public const ACCESS_KEYS_SEARCH_PATH = self::getBaseUrl() . "/v1/mgmt/accesskey/search";
    public const ACCESS_KEY_UPDATE_PATH = self::getBaseUrl() . "/v1/mgmt/accesskey/update";
    public const ACCESS_KEY_DEACTIVATE_PATH = self::getBaseUrl() . "/v1/mgmt/accesskey/deactivate";
    public const ACCESS_KEY_ACTIVATE_PATH = self::getBaseUrl() . "/v1/mgmt/accesskey/activate";
    public const ACCESS_KEY_DELETE_PATH = self::getBaseUrl() . "/v1/mgmt/accesskey/delete";

    // SSO
    public const SSO_SETTINGS_PATH = self::getBaseUrl() . "/v1/mgmt/sso/settings";
    public const SSO_METADATA_PATH = self::getBaseUrl() . "/v1/mgmt/sso/metadata";
    public const SSO_MAPPING_PATH = self::getBaseUrl() . "/v1/mgmt/sso/mapping";
    public const SSO_LOAD_SETTINGS_PATH = self::getBaseUrl() . "/v2/mgmt/sso/settings";  // v2 only
    public const SSO_CONFIGURE_OIDC_SETTINGS = self::getBaseUrl() . "/v1/mgmt/sso/oidc";
    public const SSO_CONFIGURE_SAML_SETTINGS = self::getBaseUrl() . "/v1/mgmt/sso/saml";
    public const SSO_CONFIGURE_SAML_BY_METADATA_SETTINGS = self::getBaseUrl() . "/v1/mgmt/sso/saml/metadata";

    // JWT
    public const UPDATE_JWT_PATH = self::getBaseUrl() . "/v1/mgmt/jwt/update";
    public const IMPERSONATE_PATH = self::getBaseUrl() . "/v1/mgmt/impersonate";

    // Permissions
    public const PERMISSION_CREATE_PATH = self::getBaseUrl() . "/v1/mgmt/permission/create";
    public const PERMISSION_UPDATE_PATH = self::getBaseUrl() . "/v1/mgmt/permission/update";
    public const PERMISSION_DELETE_PATH = self::getBaseUrl() . "/v1/mgmt/permission/delete";
    public const PERMISSION_LOAD_ALL_PATH = self::getBaseUrl() . "/v1/mgmt/permission/all";

    // Role
    public const ROLE_CREATE_PATH = self::getBaseUrl() . "/v1/mgmt/role/create";
    public const ROLE_UPDATE_PATH = self::getBaseUrl() . "/v1/mgmt/role/update";
    public const ROLE_DELETE_PATH = self::getBaseUrl() . "/v1/mgmt/role/delete";
    public const ROLE_LOAD_ALL_PATH = self::getBaseUrl() . "/v1/mgmt/role/all";
    public const ROLE_SEARCH_PATH = self::getBaseUrl() . "/v1/mgmt/role/search";

    // Flow
    public const FLOW_LIST_PATH = self::getBaseUrl() . "/v1/mgmt/flow/list";
    public const FLOW_DELETE_PATH = self::getBaseUrl() . "/v1/mgmt/flow/delete";
    public const FLOW_IMPORT_PATH = self::getBaseUrl() . "/v1/mgmt/flow/import";
    public const FLOW_EXPORT_PATH = self::getBaseUrl() . "/v1/mgmt/flow/export";

    // Theme
    public const THEME_IMPORT_PATH = self::getBaseUrl() . "/v1/mgmt/theme/import";
    public const THEME_EXPORT_PATH = self::getBaseUrl() . "/v1/mgmt/theme/export";

    // Group
    public const GROUP_LOAD_ALL_PATH = self::getBaseUrl() . "/v1/mgmt/group/all";
    public const GROUP_LOAD_ALL_FOR_MEMBER_PATH = self::getBaseUrl() . "/v1/mgmt/group/member/all";
    public const GROUP_LOAD_ALL_GROUP_MEMBERS_PATH = self::getBaseUrl() . "/v1/mgmt/group/members";

    // Audit
    public const AUDIT_SEARCH = self::getBaseUrl() . "/v1/mgmt/audit/search";
    public const AUDIT_CREATE_EVENT = self::getBaseUrl() . "/v1/mgmt/audit/event";

    // Authz ReBAC
    public const AUTHZ_SCHEMA_SAVE = self::getBaseUrl() . "/v1/mgmt/authz/schema/save";
    public const AUTHZ_SCHEMA_DELETE = self::getBaseUrl() . "/v1/mgmt/authz/schema/delete";
    public const AUTHZ_SCHEMA_LOAD = self::getBaseUrl() . "/v1/mgmt/authz/schema/load";
    public const AUTHZ_NS_SAVE = self::getBaseUrl() . "/v1/mgmt/authz/ns/save";
    public const AUTHZ_NS_DELETE = self::getBaseUrl() . "/v1/mgmt/authz/ns/delete";
    public const AUTHZ_RD_SAVE = self::getBaseUrl() . "/v1/mgmt/authz/rd/save";
    public const AUTHZ_RD_DELETE = self::getBaseUrl() . "/v1/mgmt/authz/rd/delete";
    public const AUTHZ_RE_CREATE = self::getBaseUrl() . "/v1/mgmt/authz/re/create";
    public const AUTHZ_RE_DELETE = self::getBaseUrl() . "/v1/mgmt/authz/re/delete";
    public const AUTHZ_RE_DELETE_RESOURCES = self::getBaseUrl() . "/v1/mgmt/authz/re/deleteresources";
    public const AUTHZ_RE_HAS_RELATIONS = self::getBaseUrl() . "/v1/mgmt/authz/re/has";
    public const AUTHZ_RE_WHO = self::getBaseUrl() . "/v1/mgmt/authz/re/who";
    public const AUTHZ_RE_RESOURCE = self::getBaseUrl() . "/v1/mgmt/authz/re/resource";
    public const AUTHZ_RE_TARGETS = self::getBaseUrl() . "/v1/mgmt/authz/re/targets";
    public const AUTHZ_RE_TARGET_ALL = self::getBaseUrl() . "/v1/mgmt/authz/re/targetall";
    public const AUTHZ_GET_MODIFIED = self::getBaseUrl() . "/v1/mgmt/authz/getmodified";

    // Project
    public const PROJECT_UPDATE_NAME = self::getBaseUrl() . "/v1/mgmt/project/update/name";
    public const PROJECT_CLONE = self::getBaseUrl() . "/v1/mgmt/project/clone";
    public const PROJECT_EXPORT = self::getBaseUrl() . "/v1/mgmt/project/export";
    public const PROJECT_IMPORT = self::getBaseUrl() . "/v1/mgmt/project/import";
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
?>