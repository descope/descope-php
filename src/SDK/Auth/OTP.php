<?php

declare(strict_types=1);

namespace Descope\SDK\Auth;

use Descope\SDK\Exception\AuthException;
use Descope\SDK\EndpointsV1;
use Descope\SDK\API;

/**
 * Provides one-time-password (OTP) authentication flows.
 *
 * The delivery method is one of "email", "sms", "whatsapp" or "voice" and is
 * appended to the base endpoint path, matching the Descope REST API.
 */
class OTP
{
    private const METHODS = ['email', 'sms', 'whatsapp', 'voice'];

    /**
     * @var API The API object for making authenticated requests.
     */
    private $api;

    /**
     * Constructor for OTP class.
     *
     * @param API $api API object for making authenticated requests.
     */
    public function __construct(API $api)
    {
        $this->api = $api;
    }

    /**
     * Sign up a new user and send them an OTP code.
     *
     * @param  string     $deliveryMethod One of "email", "sms", "whatsapp", "voice".
     * @param  string     $loginId        The login ID of the user being signed up.
     * @param  array|null $user           Optional user details (e.g. email, phone, name).
     * @param  array|null $signUpOptions  Optional sign-up options (customClaims, templateOptions).
     * @return string The masked address the OTP was sent to.
     * @throws AuthException
     */
    public function signUp(string $deliveryMethod, string $loginId, ?array $user = null, ?array $signUpOptions = null): string
    {
        $method = $this->validateMethod($deliveryMethod);
        $this->validateLoginId($loginId);

        $body = ['loginId' => $loginId];
        if ($user !== null) {
            $body['user'] = $user;
        }
        if ($signUpOptions !== null) {
            $body['loginOptions'] = $signUpOptions;
        }

        $uri = EndpointsV1::$SIGN_UP_AUTH_OTP_PATH . '/' . $method;
        $response = $this->api->doPost($uri, $body, false);

        return $response[$method] ?? '';
    }

    /**
     * Sign in an existing user and send them an OTP code.
     *
     * @param  string      $deliveryMethod One of "email", "sms", "whatsapp", "voice".
     * @param  string      $loginId        The login ID of the user signing in.
     * @param  array|null  $loginOptions   Optional login options.
     * @param  string|null $refreshToken   Optional refresh token for step-up/MFA.
     * @return string The masked address the OTP was sent to.
     * @throws AuthException
     */
    public function signIn(string $deliveryMethod, string $loginId, ?array $loginOptions = null, ?string $refreshToken = null): string
    {
        $method = $this->validateMethod($deliveryMethod);
        $this->validateLoginId($loginId);

        $body = ['loginId' => $loginId];
        if ($loginOptions !== null) {
            $body['loginOptions'] = $loginOptions;
        }

        $uri = EndpointsV1::$SIGN_IN_AUTH_OTP_PATH . '/' . $method;
        $response = $this->api->doPost($uri, $body, false, $refreshToken);

        return $response[$method] ?? '';
    }

    /**
     * Sign up or sign in a user (whichever applies) and send them an OTP code.
     *
     * @param  string     $deliveryMethod One of "email", "sms", "whatsapp", "voice".
     * @param  string     $loginId        The login ID of the user.
     * @param  array|null $loginOptions   Optional login options.
     * @return string The masked address the OTP was sent to.
     * @throws AuthException
     */
    public function signUpOrIn(string $deliveryMethod, string $loginId, ?array $loginOptions = null): string
    {
        $method = $this->validateMethod($deliveryMethod);
        $this->validateLoginId($loginId);

        $body = ['loginId' => $loginId];
        if ($loginOptions !== null) {
            $body['loginOptions'] = $loginOptions;
        }

        $uri = EndpointsV1::$SIGN_UP_OR_IN_AUTH_OTP_PATH . '/' . $method;
        $response = $this->api->doPost($uri, $body, false);

        return $response[$method] ?? '';
    }

    /**
     * Verify an OTP code and complete the authentication.
     *
     * @param  string $deliveryMethod One of "email", "sms", "whatsapp", "voice".
     * @param  string $loginId        The login ID of the user.
     * @param  string $code           The OTP code the user received.
     * @return array JWT response array.
     * @throws AuthException
     */
    public function verifyCode(string $deliveryMethod, string $loginId, string $code): array
    {
        $method = $this->validateMethod($deliveryMethod);
        $this->validateLoginId($loginId);

        if (empty($code)) {
            throw new AuthException(400, 'invalid argument', 'code cannot be empty');
        }

        $uri = EndpointsV1::$VERIFY_CODE_AUTH_PATH . '/' . $method;
        $response = $this->api->doPost($uri, ['loginId' => $loginId, 'code' => $code], false);

        return $this->api->generateJwtResponse($response, $response['refreshJwt'] ?? null, null);
    }

    /**
     * Validates the delivery method and returns its normalized string form.
     *
     * @param  string $deliveryMethod The requested delivery method.
     * @return string The normalized (lowercase) delivery method.
     * @throws AuthException
     */
    private function validateMethod(string $deliveryMethod): string
    {
        $method = strtolower($deliveryMethod);
        if (!in_array($method, self::METHODS, true)) {
            throw new AuthException(400, 'invalid argument', "Unknown delivery method: $deliveryMethod");
        }
        return $method;
    }

    /**
     * Validates the login ID.
     *
     * @param  string $loginId The login ID.
     * @throws AuthException
     */
    private function validateLoginId(string $loginId): void
    {
        if (empty($loginId)) {
            throw new AuthException(400, 'invalid argument', 'login_id cannot be empty');
        }
    }
}
