<?php

namespace Descope\SDK\Management;

class MgmtV1 {
    // Tenant
    public const TENANT_CREATE_PATH = "/v1/mgmt/tenant/create";
    public const TENANT_UPDATE_PATH = "/v1/mgmt/tenant/update";
    public const TENANT_DELETE_PATH = "/v1/mgmt/tenant/delete";
    public const TENANT_LOAD_PATH = "/v1/mgmt/tenant";
    public const TENANT_LOAD_ALL_PATH = "/v1/mgmt/tenant/all";
    public const TENANT_SEARCH_ALL_PATH = "/v1/mgmt/tenant/search";

    // SSO Applications
    public const SSO_APPLICATION_OIDC_CREATE_PATH = "/v1/mgmt/sso/idp/app/oidc/create";
    public const SSO_APPLICATION_SAML_CREATE_PATH = "/v1/mgmt/sso/idp/app/saml/create";
    public const SSO_APPLICATION_OIDC_UPDATE_PATH = "/v1/mgmt/sso/idp/app/oidc/update";
    public const SSO_APPLICATION_SAML_UPDATE_PATH = "/v1/mgmt/sso/idp/app/saml/update";
    public const SSO_APPLICATION_DELETE_PATH = "/v1/mgmt/sso/idp/app/delete";
    public const SSO_APPLICATION_LOAD_PATH = "/v1/mgmt/sso/idp/app/load";
    public const SSO_APPLICATION_LOAD_ALL_PATH = "/v1/mgmt/sso/idp/apps/load";

    // User
    public const USER_CREATE_PATH = "/v1/mgmt/user/create";
    public const USER_CREATE_BATCH_PATH = "/v1/mgmt/user/create/batch";
    public const USER_UPDATE_PATH = "/v1/mgmt/user/update";
    public const USER_DELETE_PATH = "/v1/mgmt/user/delete";
    public const USER_LOGOUT_PATH = "/v1/mgmt/user/logout";
    public const USER_DELETE_ALL_TEST_USERS_PATH = "/v1/mgmt/user/test/delete/all";
    public const USER_LOAD_PATH = "/v1/mgmt/user";
    public const USERS_SEARCH_PATH = "/v1/mgmt/user/search";
    public const USER_GET_PROVIDER_TOKEN = "/v1/mgmt/user/provider/token";
    public const USER_UPDATE_STATUS_PATH = "/v1/mgmt/user/update/status";
    public const USER_UPDATE_LOGIN_ID_PATH = "/v1/mgmt/user/update/loginid";
    public const USER_UPDATE_EMAIL_PATH = "/v1/mgmt/user/update/email";
    public const USER_UPDATE_PHONE_PATH = "/v1/mgmt/user/update/phone";
    public const USER_UPDATE_NAME_PATH = "/v1/mgmt/user/update/name";
    public const USER_UPDATE_PICTURE_PATH = "/v1/mgmt/user/update/picture";
    public const USER_UPDATE_CUSTOM_ATTRIBUTE_PATH = "/v1/mgmt/user/update/customAttribute";
    public const USER_SET_ROLE_PATH = "/v1/mgmt/user/update/role/set";
    public const USER_ADD_ROLE_PATH = "/v1/mgmt/user/update/role/add";
    public const USER_REMOVE_ROLE_PATH = "/v1/mgmt/user/update/role/remove";
    public const USER_ADD_SSO_APPS = "/v1/mgmt/user/update/ssoapp/add";
    public const USER_SET_SSO_APPS = "/v1/mgmt/user/update/ssoapp/set";
    public const USER_REMOVE_SSO_APPS = "/v1/mgmt/user/update/ssoapp/remove";
    public const USER_SET_PASSWORD_PATH = "/v1/mgmt/user/password/set";  // Deprecated
    public const USER_SET_TEMPORARY_PASSWORD_PATH = "/v1/mgmt/user/password/set/temporary";
    public const USER_SET_ACTIVE_PASSWORD_PATH = "/v1/mgmt/user/password/set/active";
    public const USER_EXPIRE_PASSWORD_PATH = "/v1/mgmt/user/password/expire";
    public const USER_REMOVE_ALL_PASSKEYS_PATH = "/v1/mgmt/user/passkeys/delete";
    public const USER_ADD_TENANT_PATH = "/v1/mgmt/user/update/tenant/add";
    public const USER_REMOVE_TENANT_PATH = "/v1/mgmt/user/update/tenant/remove";
    public const USER_GENERATE_OTP_FOR_TEST_PATH = "/v1/mgmt/tests/generate/otp";
    public const USER_GENERATE_MAGIC_LINK_FOR_TEST_PATH = "/v1/mgmt/tests/generate/magiclink";
    public const USER_GENERATE_ENCHANTED_LINK_FOR_TEST_PATH = "/v1/mgmt/tests/generate/enchantedlink";
    public const USER_GENERATE_EMBEDDED_LINK_PATH = "/v1/mgmt/user/signin/embeddedlink";
    public const USER_HISTORY_PATH = "/v1/mgmt/user/history";

    // Access Keys
    public const ACCESS_KEY_CREATE_PATH = "/v1/mgmt/accesskey/create";
    public const ACCESS_KEY_LOAD_PATH = "/v1/mgmt/accesskey";
    public const ACCESS_KEYS_SEARCH_PATH = "/v1/mgmt/accesskey/search";
    public const ACCESS_KEY_UPDATE_PATH = "/v1/mgmt/accesskey/update";
    public const ACCESS_KEY_DEACTIVATE_PATH = "/v1/mgmt/accesskey/deactivate";
    public const ACCESS_KEY_ACTIVATE_PATH = "/v1/mgmt/accesskey/activate";
    public const ACCESS_KEY_DELETE_PATH = "/v1/mgmt/accesskey/delete";

    // SSO
    public const SSO_SETTINGS_PATH = "/v1/mgmt/sso/settings";
    public const SSO_METADATA_PATH = "/v1/mgmt/sso/metadata";
    public const SSO_MAPPING_PATH = "/v1/mgmt/sso/mapping";
    public const SSO_LOAD_SETTINGS_PATH = "/v2/mgmt/sso/settings";  // v2 only
    public const SSO_CONFIGURE_OIDC_SETTINGS = "/v1/mgmt/sso/oidc";
    public const SSO_CONFIGURE_SAML_SETTINGS = "/v1/mgmt/sso/saml";
    public const SSO_CONFIGURE_SAML_BY_METADATA_SETTINGS = "/v1/mgmt/sso/saml/metadata";

    // JWT
    public const UPDATE_JWT_PATH = "/v1/mgmt/jwt/update";
    public const IMPERSONATE_PATH = "/v1/mgmt/impersonate";

    // Permissions
    public const PERMISSION_CREATE_PATH = "/v1/mgmt/permission/create";
    public const PERMISSION_UPDATE_PATH = "/v1/mgmt/permission/update";
    public const PERMISSION_DELETE_PATH = "/v1/mgmt/permission/delete";
    public const PERMISSION_LOAD_ALL_PATH = "/v1/mgmt/permission/all";

    // Role
    public const ROLE_CREATE_PATH = "/v1/mgmt/role/create";
    public const ROLE_UPDATE_PATH = "/v1/mgmt/role/update";
    public const ROLE_DELETE_PATH = "/v1/mgmt/role/delete";
    public const ROLE_LOAD_ALL_PATH = "/v1/mgmt/role/all";
    public const ROLE_SEARCH_PATH = "/v1/mgmt/role/search";

    // Flow
    public const FLOW_LIST_PATH = "/v1/mgmt/flow/list";
    public const FLOW_DELETE_PATH = "/v1/mgmt/flow/delete";
    public const FLOW_IMPORT_PATH = "/v1/mgmt/flow/import";
    public const FLOW_EXPORT_PATH = "/v1/mgmt/flow/export";

    // Theme
    public const THEME_IMPORT_PATH = "/v1/mgmt/theme/import";
    public const THEME_EXPORT_PATH = "/v1/mgmt/theme/export";

    // Group
    public const GROUP_LOAD_ALL_PATH = "/v1/mgmt/group/all";
    public const GROUP_LOAD_ALL_FOR_MEMBER_PATH = "/v1/mgmt/group/member/all";
    public const GROUP_LOAD_ALL_GROUP_MEMBERS_PATH = "/v1/mgmt/group/members";

    // Audit
    public const AUDIT_SEARCH = "/v1/mgmt/audit/search";
    public const AUDIT_CREATE_EVENT = "/v1/mgmt/audit/event";

    // Authz ReBAC
    public const AUTHZ_SCHEMA_SAVE = "/v1/mgmt/authz/schema/save";
    public const AUTHZ_SCHEMA_DELETE = "/v1/mgmt/authz/schema/delete";
    public const AUTHZ_SCHEMA_LOAD = "/v1/mgmt/authz/schema/load";
    public const AUTHZ_NS_SAVE = "/v1/mgmt/authz/ns/save";
    public const AUTHZ_NS_DELETE = "/v1/mgmt/authz/ns/delete";
    public const AUTHZ_RD_SAVE = "/v1/mgmt/authz/rd/save";
    public const AUTHZ_RD_DELETE = "/v1/mgmt/authz/rd/delete";
    public const AUTHZ_RE_CREATE = "/v1/mgmt/authz/re/create";
    public const AUTHZ_RE_DELETE = "/v1/mgmt/authz/re/delete";
    public const AUTHZ_RE_DELETE_RESOURCES = "/v1/mgmt/authz/re/deleteresources";
    public const AUTHZ_RE_HAS_RELATIONS = "/v1/mgmt/authz/re/has";
    public const AUTHZ_RE_WHO = "/v1/mgmt/authz/re/who";
    public const AUTHZ_RE_RESOURCE = "/v1/mgmt/authz/re/resource";
    public const AUTHZ_RE_TARGETS = "/v1/mgmt/authz/re/targets";
    public const AUTHZ_RE_TARGET_ALL = "/v1/mgmt/authz/re/targetall";
    public const AUTHZ_GET_MODIFIED = "/v1/mgmt/authz/getmodified";

    // Project
    public const PROJECT_UPDATE_NAME = "/v1/mgmt/project/update/name";
    public const PROJECT_CLONE = "/v1/mgmt/project/clone";
    public const PROJECT_EXPORT = "/v1/mgmt/project/export";
    public const PROJECT_IMPORT = "/v1/mgmt/project/import";
}


class AssociatedTenant {
    /**
     * Represents a tenant association for a User or Access Key. The tenant will be used to determine permissions and roles for the entity.
     *
     * @var string The Tenant ID
     */
    public $tenantId;

    /**
     * Represents the role names for a user in the Tenant
     *
     * @var array<string> The Role Names
     */
    public $roleNames = [];

    /**
     * Represents the role IDs for a user in the Tenant
     *
     * @var array<string> The Role IDs
     */
    public $roleIds = [];

    public function __construct($tenantId, $roleNames = [], $roleIds = []) {
        $this->tenantId = $tenantId;
        $this->roleNames = $roleNames;
        $this->roleIds = $roleIds;
    }
}