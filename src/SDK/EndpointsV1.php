<?php
// phpcs:ignoreFile

namespace Descope\SDK;

use Descope\SDK\Exception\AuthException;

const DEFAULT_URL_PREFIX = "https://api";
const DEFAULT_DOMAIN = "descope.com";
const DEFAULT_TIMEOUT_SECONDS = 60;

const PHONE_REGEX = '/^(?:(?:\(?(?:00|\+)([1-4]\d\d|[1-9]\d?)\)?)?[\-\.\ \\\/]?){0,}((?:\(?\d{1,}\)?[\-\.\ \\\/]?){0,})(?:[\-\.\ \\\/]?(?:#|ext\.?|extension|x)[\-\.\ \\\/]?(\d+))?$/';

class EndpointsV1
{
    public static $baseUrl;

    public static $REFRESH_TOKEN_PATH;
    public static $SELECT_TENANT_PATH;
    public static $LOGOUT_PATH;
    public static $LOGOUT_ALL_PATH;
    public static $ME_PATH;
    public static $HISTORY_PATH;
    public static $EXCHANGE_AUTH_ACCESS_KEY_PATH;
    public static $SIGN_UP_AUTH_OTP_PATH;
    public static $SIGN_IN_AUTH_OTP_PATH;
    public static $SIGN_UP_OR_IN_AUTH_OTP_PATH;
    public static $VERIFY_CODE_AUTH_PATH;
    public static $UPDATE_USER_EMAIL_OTP_PATH;
    public static $UPDATE_USER_PHONE_OTP_PATH;
    public static $SIGN_UP_AUTH_MAGICLINK_PATH;
    public static $SIGN_IN_AUTH_MAGICLINK_PATH;
    public static $SIGN_UP_OR_IN_AUTH_MAGICLINK_PATH;
    public static $VERIFY_MAGICLINK_AUTH_PATH;
    public static $GET_SESSION_MAGICLINK_AUTH_PATH;
    public static $UPDATE_USER_EMAIL_MAGICLINK_PATH;
    public static $UPDATE_USER_PHONE_MAGICLINK_PATH;
    public static $SIGN_UP_AUTH_ENCHANTEDLINK_PATH;
    public static $SIGN_IN_AUTH_ENCHANTEDLINK_PATH;
    public static $SIGN_UP_OR_IN_AUTH_ENCHANTEDLINK_PATH;
    public static $VERIFY_ENCHANTEDLINK_AUTH_PATH;
    public static $GET_SESSION_ENCHANTEDLINK_AUTH_PATH;
    public static $UPDATE_USER_EMAIL_ENCHANTEDLINK_PATH;
    public static $OAUTH_START_PATH;
    public static $OAUTH_EXCHANGE_TOKEN_PATH;
    public static $AUTH_SSO_START_PATH;
    public static $SSO_EXCHANGE_TOKEN_PATH;
    public static $SIGN_UP_AUTH_TOTP_PATH;
    public static $VERIFY_TOTP_PATH;
    public static $UPDATE_TOTP_PATH;
    public static $SIGN_UP_AUTH_WEBAUTHN_START_PATH;
    public static $SIGN_UP_AUTH_WEBAUTHN_FINISH_PATH;
    public static $SIGN_IN_AUTH_WEBAUTHN_START_PATH;
    public static $SIGN_IN_AUTH_WEBAUTHN_FINISH_PATH;
    public static $SIGN_UP_OR_IN_AUTH_WEBAUTHN_START_PATH;
    public static $UPDATE_AUTH_WEBAUTHN_START_PATH;
    public static $UPDATE_AUTH_WEBAUTHN_FINISH_PATH;
    public static $SIGN_UP_PASSWORD_PATH;
    public static $SIGN_IN_PASSWORD_PATH;
    public static $SEND_RESET_PASSWORD_PATH;
    public static $UPDATE_PASSWORD_PATH;
    public static $REPLACE_PASSWORD_PATH;
    public static $PASSWORD_POLICY_PATH;

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

    public static function extractRegionFromProjectId(string $projectId): ?string
    {
        if (strlen($projectId) >= 32) {
            $region = substr($projectId, 1, 4);
            return !empty($region) ? $region : null;
        }
        return null;
    }

    public static function updatePaths(): void
    {
        self::$REFRESH_TOKEN_PATH = self::$baseUrl . "/v1/auth/refresh";
        self::$SELECT_TENANT_PATH = self::$baseUrl . "/v1/auth/tenant/select";
        self::$LOGOUT_PATH = self::$baseUrl . "/v1/auth/logout";
        self::$LOGOUT_ALL_PATH = self::$baseUrl . "/v1/auth/logoutall";
        self::$ME_PATH = self::$baseUrl . "/v1/auth/me";
        self::$HISTORY_PATH = self::$baseUrl . "/v1/auth/me/history";
        self::$EXCHANGE_AUTH_ACCESS_KEY_PATH = self::$baseUrl . "/v1/auth/accesskey/exchange";
        self::$SIGN_UP_AUTH_OTP_PATH = self::$baseUrl . "/v1/auth/otp/signup";
        self::$SIGN_IN_AUTH_OTP_PATH = self::$baseUrl . "/v1/auth/otp/signin";
        self::$SIGN_UP_OR_IN_AUTH_OTP_PATH = self::$baseUrl . "/v1/auth/otp/signup-in";
        self::$VERIFY_CODE_AUTH_PATH = self::$baseUrl . "/v1/auth/otp/verify";
        self::$UPDATE_USER_EMAIL_OTP_PATH = self::$baseUrl . "/v1/auth/otp/update/email";
        self::$UPDATE_USER_PHONE_OTP_PATH = self::$baseUrl . "/v1/auth/otp/update/phone";
        self::$SIGN_UP_AUTH_MAGICLINK_PATH = self::$baseUrl . "/v1/auth/magiclink/signup";
        self::$SIGN_IN_AUTH_MAGICLINK_PATH = self::$baseUrl . "/v1/auth/magiclink/signin";
        self::$SIGN_UP_OR_IN_AUTH_MAGICLINK_PATH = self::$baseUrl . "/v1/auth/magiclink/signup-in";
        self::$VERIFY_MAGICLINK_AUTH_PATH = self::$baseUrl . "/v1/auth/magiclink/verify";
        self::$GET_SESSION_MAGICLINK_AUTH_PATH = self::$baseUrl . "/v1/auth/magiclink/pending-session";
        self::$UPDATE_USER_EMAIL_MAGICLINK_PATH = self::$baseUrl . "/v1/auth/magiclink/update/email";
        self::$UPDATE_USER_PHONE_MAGICLINK_PATH = self::$baseUrl . "/v1/auth/magiclink/update/phone";
        self::$SIGN_UP_AUTH_ENCHANTEDLINK_PATH = self::$baseUrl . "/v1/auth/enchantedlink/signup";
        self::$SIGN_IN_AUTH_ENCHANTEDLINK_PATH = self::$baseUrl . "/v1/auth/enchantedlink/signin";
        self::$SIGN_UP_OR_IN_AUTH_ENCHANTEDLINK_PATH = self::$baseUrl . "/v1/auth/enchantedlink/signup-in";
        self::$VERIFY_ENCHANTEDLINK_AUTH_PATH = self::$baseUrl . "/v1/auth/enchantedlink/verify";
        self::$GET_SESSION_ENCHANTEDLINK_AUTH_PATH = self::$baseUrl . "/v1/auth/enchantedlink/pending-session";
        self::$UPDATE_USER_EMAIL_ENCHANTEDLINK_PATH = self::$baseUrl . "/v1/auth/enchantedlink/update/email";
        self::$OAUTH_START_PATH = self::$baseUrl . "/v1/auth/oauth/authorize";
        self::$OAUTH_EXCHANGE_TOKEN_PATH = self::$baseUrl . "/v1/auth/oauth/exchange";
        self::$AUTH_SSO_START_PATH = self::$baseUrl . "/v1/auth/sso/authorize";
        self::$SSO_EXCHANGE_TOKEN_PATH = self::$baseUrl . "/v1/auth/sso/exchange";
        self::$SIGN_UP_AUTH_TOTP_PATH = self::$baseUrl . "/v1/auth/totp/signup";
        self::$VERIFY_TOTP_PATH = self::$baseUrl . "/v1/auth/totp/verify";
        self::$UPDATE_TOTP_PATH = self::$baseUrl . "/v1/auth/totp/update";
        self::$SIGN_UP_AUTH_WEBAUTHN_START_PATH = self::$baseUrl . "/v1/auth/webauthn/signup/start";
        self::$SIGN_UP_AUTH_WEBAUTHN_FINISH_PATH = self::$baseUrl . "/v1/auth/webauthn/signup/finish";
        self::$SIGN_IN_AUTH_WEBAUTHN_START_PATH = self::$baseUrl . "/v1/auth/webauthn/signin/start";
        self::$SIGN_IN_AUTH_WEBAUTHN_FINISH_PATH = self::$baseUrl . "/v1/auth/webauthn/signin/finish";
        self::$SIGN_UP_OR_IN_AUTH_WEBAUTHN_START_PATH = self::$baseUrl . "/v1/auth/webauthn/signup-in/start";
        self::$UPDATE_AUTH_WEBAUTHN_START_PATH = self::$baseUrl . "/v1/auth/webauthn/update/start";
        self::$UPDATE_AUTH_WEBAUTHN_FINISH_PATH = self::$baseUrl . "/v1/auth/webauthn/update/finish";
        self::$SIGN_UP_PASSWORD_PATH = self::$baseUrl . "/v1/auth/password/signup";
        self::$SIGN_IN_PASSWORD_PATH = self::$baseUrl . "/v1/auth/password/signin";
        self::$SEND_RESET_PASSWORD_PATH = self::$baseUrl . "/v1/auth/password/reset";
        self::$UPDATE_PASSWORD_PATH = self::$baseUrl . "/v1/auth/password/update";
        self::$REPLACE_PASSWORD_PATH = self::$baseUrl . "/v1/auth/password/replace";
        self::$PASSWORD_POLICY_PATH = self::$baseUrl . "/v1/auth/password/policy";
    }
}

class EndpointsV2
{
    private static $baseUrl;

    public static function setBaseUrl(string $projectId): void
    {
        $region = EndpointsV1::extractRegionFromProjectId($projectId);
        $urlPrefix = DEFAULT_URL_PREFIX;

        if ($region) {
            $urlPrefix .= ".$region";
        }

        self::$baseUrl = "$urlPrefix." . DEFAULT_DOMAIN;
    }

    public static function getPublicKeyPath(): string
    {
        return self::$baseUrl . "/v2/keys";
    }
}

class DeliveryMethod
{
    public const WHATSAPP = 1;
    public const SMS = 2;
    public const EMAIL = 3;
    public const EMBEDDED = 4;
    public const VOICE = 5;
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
}

class AccessKeyLoginOptions
{
    public ?array $customClaims;

    public function __construct(
        ?array $customClaims = null
    ) {
        $this->customClaims = $customClaims;
    }
}

function validate_refresh_token_provided(
    ?LoginOptions $loginOptions = null,
    ?string $refreshToken = null
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

class SignUpOptions
{
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

function signup_options_to_dict(?SignUpOptions $signupOptions = null): array
{
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
