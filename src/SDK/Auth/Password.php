<?php

declare(strict_types=1);

namespace Descope\SDK\Auth;

use Descope\SDK\Exception\AuthException;
use Descope\SDK\EndpointsV1;
use Descope\SDK\API;

class Password
{
    private $api;

    /**
     * Constructor for Password class.
     *
     * @param API $api Auth object for making authenticated requests.
     */
    public function __construct(API $api)
    {
        $this->api = $api;
    }

    /**
     * Signs up a new user with a login ID and password.
     *
     * @param string $loginId Login ID for the new user.
     * @param string $password Password for the new user.
     * @param array|null $user Optional user details.
     * @return array JWT response array.
     * @throws AuthException If login ID or password is empty.
     */
    public function signUp(string $loginId, string $password, ?array $user = null, ?array $loginOptions = null): array
    {
        if (empty($loginId)) {
            throw new AuthException(400, 'invalid argument', 'login_id cannot be empty');
        }

        if (empty($password)) {
            throw new AuthException(400, 'invalid argument', 'password cannot be empty');
        }

        $uri = EndpointsV1::SIGN_UP_PASSWORD_PATH;
        $body = $this->composeSignupBody($loginId, $password, $user, $loginOptions);
        $response = $this->api->doPost($uri, $body, false);
        print("Hello");
        return $this->api->generateJwtResponse($response, $response['refreshJwt'], null);
    }

    /**
     * Signs in a user with a login ID and password.
     *
     * @param string $loginId Login ID of the user.
     * @param string $password Password of the user.
     * @return array JWT response array.
     * @throws AuthException If login ID or password is empty.
     */
    public function signIn(string $loginId, string $password): array
    {
        if (empty($loginId)) {
            throw new AuthException(400, 'invalid argument', 'login_id cannot be empty');
        }

        if (empty($password)) {
            throw new AuthException(400, 'invalid argument', 'Password cannot be empty');
        }

        $uri = EndpointsV1::SIGN_IN_PASSWORD_PATH;
        $response = $this->api->doPost($uri, ['loginId' => $loginId, 'password' => $password], false);
        return $this->api->generateJwtResponse($response, $response['refreshJwt'], null);
    }

    /**
     * Sends a password reset request.
     *
     * @param string $loginId Login ID of the user.
     * @param string|null $redirectUrl Optional redirect URL.
     * @param array|null $templateOptions Optional template options.
     * @return array Response array.
     * @throws AuthException If login ID is empty.
     */
    public function sendReset(string $loginId, ?string $redirectUrl = null, ?array $templateOptions = null): array
    {
        if (empty($loginId)) {
            throw new AuthException(400, 'invalid argument', 'login_id cannot be empty');
        }

        $uri = EndpointsV1::SEND_RESET_PASSWORD_PATH;
        $body = [
            'loginId' => $loginId,
            'redirectUrl' => $redirectUrl,
        ];
        if ($templateOptions !== null) {
            $body['templateOptions'] = $templateOptions;
        }

        $response = $this->api->doPost($uri, $body, false);
        return json_decode($response->getBody(), true);
    }

    /**
     * Updates the password of a user.
     *
     * @param string $loginId Login ID of the user.
     * @param string $newPassword New password for the user.
     * @throws AuthException If login ID, new password, or refresh token is empty.
     */
    public function update(string $loginId, string $newPassword, string $refreshToken): void
    {
        if (empty($loginId)) {
            throw new AuthException(400, 'invalid argument', 'login_id cannot be empty');
        }

        if (empty($newPassword)) {
            throw new AuthException(400, 'invalid argument', 'new_password cannot be empty');
        }

        if (empty($refreshToken)) {
            throw new AuthException(400, 'invalid argument', 'Refresh token cannot be empty');
        }

        $uri = EndpointsV1::UPDATE_PASSWORD_PATH;
        $this->api->doPost($uri, ['loginId' => $loginId, 'newPassword' => $newPassword], true);
    }

    /**
     * Replaces the password of a user.
     *
     * @param string $loginId Login ID of the user.
     * @param string $oldPassword Old password of the user.
     * @param string $newPassword New password for the user.
     * @return array JWT response array.
     * @throws AuthException If login ID, old password, or new password is empty.
     */
    public function replace(string $loginId, string $oldPassword, string $newPassword): array
    {
        if (empty($loginId)) {
            throw new AuthException(400, 'invalid argument', 'login_id cannot be empty');
        }

        if (empty($oldPassword)) {
            throw new AuthException(400, 'invalid argument', 'old_password cannot be empty');
        }

        if (empty($newPassword)) {
            throw new AuthException(400, 'invalid argument', 'new_password cannot be empty');
        }

        $uri = EndpointsV1::REPLACE_PASSWORD_PATH;
        $response = $this->api->doPost($uri, [
            'loginId' => $loginId,
            'oldPassword' => $oldPassword,
            'newPassword' => $newPassword,
        ]);

        $resp = json_decode($response->getBody(), true);
        return $this->api->generateJwtResponse($resp, $response->getCookie(REFRESH_SESSION_COOKIE_NAME), true);
    }

    /**
     * Retrieves the password policy.
     *
     * @return array Password policy array.
     */
    public function getPolicy(): array
    {
        $response = $this->api->doGet(EndpointsV1::PASSWORD_POLICY_PATH);
        return json_decode($response->getBody(), true);
    }

    /**
     * Composes the body for the signup request.
     *
     * @param string $loginId Login ID for the new user.
     * @param string $password Password for the new user.
     * @param array|null $user Optional user details.
     * @param array|null $loginOptions Optional login options.
     * @return array Body array for the signup request.
     */
    private function composeSignupBody(string $loginId, string $password, ?array $user, ?array $loginOptions): array
    {
        $body = ['loginId' => $loginId, 'password' => $password];
        if ($user !== null) {
            $body['user'] = $user;
        }
        if ($loginOptions !== null) {
            $body['loginOptions'] = $loginOptions;
        }
        return $body;
    }
}