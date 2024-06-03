<?php

namespace Descope\SDK;

use Descope\SDK\Exception\AuthException;

const DEFAULT_URL_PREFIX = "https://api";
const DEFAULT_DOMAIN = "descope.com";
const DEFAULT_TIMEOUT_SECONDS = 60;

const PHONE_REGEX = '/^(?:(?:\(?(?:00|\+)([1-4]\d\d|[1-9]\d?)\)?)?[\-\.\ \\\/]?){0,}((?:\(?\d{1,}\)?[\-\.\ \\\/]?){0,})(?:[\-\.\ \\\/]?(?:#|ext\.?|extension|x)[\-\.\ \\\/]?(\d+))?$/';

private static $baseUrl;

public static function setBaseUrl(string $projectId): void {
    $region = self::extractRegionFromProjectId($projectId);
    $urlPrefix = self::DEFAULT_URL_PREFIX;

    if ($region) {
        $urlPrefix .= ".$region";
    }

    self::$baseUrl = "$urlPrefix." . self::DEFAULT_DOMAIN;
}

private static function extractRegionFromProjectId(string $projectId): ?string {
    // Extract the region based on the given logic
    if (strlen($projectId) >= 32) {
        $region = substr($projectId, 1, 4);
        return !empty($region) ? $region : null;
    }
    return null;
}

public static function getBaseUrl(): string {
    return self::$baseUrl ?? self::DEFAULT_URL_PREFIX . "." . self::DEFAULT_DOMAIN;
}

class EndpointsV1 {
    public const REFRESH_TOKEN_PATH = self::getBaseUrl() . "/v1/auth/refresh";
    public const SELECT_TENANT_PATH = self::getBaseUrl() . "/v1/auth/tenant/select";
    public const LOGOUT_PATH = self::getBaseUrl() . "/v1/auth/logout";
    public const LOGOUT_ALL_PATH = self::getBaseUrl() . "/v1/auth/logoutall";
    public const ME_PATH = self::getBaseUrl() . "/v1/auth/me";
    public const HISTORY_PATH = self::getBaseUrl() . "/v1/auth/me/history";

    // Access Keys
    public const EXCHANGE_AUTH_ACCESS_KEY_PATH = self::getBaseUrl() . "/v1/auth/accesskey/exchange";

    // OTP
    public const SIGN_UP_AUTH_OTP_PATH = self::getBaseUrl() . "/v1/auth/otp/signup";
    public const SIGN_IN_AUTH_OTP_PATH = self::getBaseUrl() . "/v1/auth/otp/signin";
    public const SIGN_UP_OR_IN_AUTH_OTP_PATH = self::getBaseUrl() . "/v1/auth/otp/signup-in";
    public const VERIFY_CODE_AUTH_PATH = self::getBaseUrl() . "/v1/auth/otp/verify";
    public const UPDATE_USER_EMAIL_OTP_PATH = self::getBaseUrl() . "/v1/auth/otp/update/email";
    public const UPDATE_USER_PHONE_OTP_PATH = self::getBaseUrl() . "/v1/auth/otp/update/phone";

    // Magic Link
    public const SIGN_UP_AUTH_MAGICLINK_PATH = self::getBaseUrl() . "/v1/auth/magiclink/signup";
    public const SIGN_IN_AUTH_MAGICLINK_PATH = self::getBaseUrl() . "/v1/auth/magiclink/signin";
    public const SIGN_UP_OR_IN_AUTH_MAGICLINK_PATH = self::getBaseUrl() . "/v1/auth/magiclink/signup-in";
    public const VERIFY_MAGICLINK_AUTH_PATH = self::getBaseUrl() . "/v1/auth/magiclink/verify";
    public const GET_SESSION_MAGICLINK_AUTH_PATH = self::getBaseUrl() . "/v1/auth/magiclink/pending-session";
    public const UPDATE_USER_EMAIL_MAGICLINK_PATH = self::getBaseUrl() . "/v1/auth/magiclink/update/email";
    public const UPDATE_USER_PHONE_MAGICLINK_PATH = self::getBaseUrl() . "/v1/auth/magiclink/update/phone";

    // Enchanted Link
    public const SIGN_UP_AUTH_ENCHANTEDLINK_PATH = self::getBaseUrl() . "/v1/auth/enchantedlink/signup";
    public const SIGN_IN_AUTH_ENCHANTEDLINK_PATH = self::getBaseUrl() . "/v1/auth/enchantedlink/signin";
    public const SIGN_UP_OR_IN_AUTH_ENCHANTEDLINK_PATH = self::getBaseUrl() . "/v1/auth/enchantedlink/signup-in";
    public const VERIFY_ENCHANTEDLINK_AUTH_PATH = self::getBaseUrl() . "/v1/auth/enchantedlink/verify";
    public const GET_SESSION_ENCHANTEDLINK_AUTH_PATH = self::getBaseUrl() . "/v1/auth/enchantedlink/pending-session";
    public const UPDATE_USER_EMAIL_ENCHANTEDLINK_PATH = self::getBaseUrl() . "/v1/auth/enchantedlink/update/email";

    // OAuth
    public const OAUTH_START_PATH = self::getBaseUrl() . "/v1/auth/oauth/authorize";
    public const OAUTH_EXCHANGE_TOKEN_PATH = self::getBaseUrl() . "/v1/auth/oauth/exchange";

    // SSO (SAML / OIDC)
    public const AUTH_SSO_START_PATH = self::getBaseUrl() . "/v1/auth/sso/authorize";
    public const SSO_EXCHANGE_TOKEN_PATH = self::getBaseUrl() . "/v1/auth/sso/exchange";

    // TOTP
    public const SIGN_UP_AUTH_TOTP_PATH = self::getBaseUrl() . "/v1/auth/totp/signup";
    public const VERIFY_TOTP_PATH = self::getBaseUrl() . "/v1/auth/totp/verify";
    public const UPDATE_TOTP_PATH = self::getBaseUrl() . "/v1/auth/totp/update";

    // WebAuthn
    public const SIGN_UP_AUTH_WEBAUTHN_START_PATH = self::getBaseUrl() . "/v1/auth/webauthn/signup/start";
    public const SIGN_UP_AUTH_WEBAUTHN_FINISH_PATH = self::getBaseUrl() . "/v1/auth/webauthn/signup/finish";
    public const SIGN_IN_AUTH_WEBAUTHN_START_PATH = self::getBaseUrl() . "/v1/auth/webauthn/signin/start";
    public const SIGN_IN_AUTH_WEBAUTHN_FINISH_PATH = self::getBaseUrl() . "/v1/auth/webauthn/signin/finish";
    public const SIGN_UP_OR_IN_AUTH_WEBAUTHN_START_PATH = self::getBaseUrl() . "/v1/auth/webauthn/signup-in/start";
    public const UPDATE_AUTH_WEBAUTHN_START_PATH = self::getBaseUrl() . "/v1/auth/webauthn/update/start";
    public const UPDATE_AUTH_WEBAUTHN_FINISH_PATH = self::getBaseUrl() . "/v1/auth/webauthn/update/finish";

    // Password
    public const SIGN_UP_PASSWORD_PATH = self::getBaseUrl() . "/v1/auth/password/signup";
    public const SIGN_IN_PASSWORD_PATH = self::getBaseUrl() . "/v1/auth/password/signin";
    public const SEND_RESET_PASSWORD_PATH = self::getBaseUrl() . "/v1/auth/password/reset";
    public const UPDATE_PASSWORD_PATH = self::getBaseUrl() . "/v1/auth/password/update";
    public const REPLACE_PASSWORD_PATH = self::getBaseUrl() . "/v1/auth/password/replace";
    public const PASSWORD_POLICY_PATH = self::getBaseUrl() . "/v1/auth/password/policy";
}

class EndpointsV2 {
    public const PUBLIC_KEY_PATH = self::getBaseUrl() . "/v2/keys";
}

class DeliveryMethod {
    public const WHATSAPP = 1;
    public const SMS = 2;
    public const EMAIL = 3;
    public const EMBEDDED = 4;
    public const VOICE = 5;
}

class LoginOptions {
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
}

class AccessKeyLoginOptions {
    public ?array $customClaims;

    public function __construct(
        ?array $customClaims = null
    ) {
        $this->customClaims = $customClaims;
    }
}

function validate_refresh_token_provided(
    ?LoginOptions $loginOptions = null, ?string $refreshToken = null
) {
    $refreshRequired = $loginOptions !== null && ($loginOptions->mfa || $loginOptions->stepup);
    $refreshMissing = $refreshToken === null || $refreshToken === "";

    if ($refreshRequired && $refreshMissing) {
        throw new AuthException(
            400,
            'ERROR_TYPE_INVALID_ARGUMENT',
            "Missing refresh token for stepup/mfa"
        );
    }
}

class SignUpOptions {
    public ?array $customClaims;
    public ?array $templateOptions;

    public function __construct(
        ?array $customClaims = null,
        ?array $templateOptions = null
    ) {
        $this->customClaims = $customClaims;
        $this->templateOptions = $templateOptions;
    }
}

function signup_options_to_dict(?SignUpOptions $signupOptions = null): array {
    $res = [];
    if ($signupOptions !== null) {
        if ($signupOptions->customClaims !== null) {
            $res['customClaims'] = $signupOptions->customClaims;
        }
        if ($signupOptions->templateOptions !== null) {
            $res['templateOptions'] = $signupOptions->templateOptions;
        }
    }
    return $res;
}