<?php

declare(strict_types=1);

namespace Descope\SDK\Auth;

use Descope\SDK\Exception\AuthException;
use Descope\SDK\Common\EndpointsV1;

class Password
{
    private $auth;

    /**
     * Constructor for Password class.
     *
     * @param Auth $auth Auth object for making authenticated requests.
     */
    public function __construct($auth)
    {
        $this->auth = $auth;
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
    public function signUp(string $loginId, string $password, ?array $user = null): array
    {
        if (empty($loginId)) {
            throw new AuthException(400, 'invalid argument', 'login_id cannot be empty');
        }

        if (empty($password)) {
            throw new AuthException(400, 'invalid argument', 'password cannot be empty');
        }

        $uri = EndpointsV1::SIGN_UP_PASSWORD_PATH;
        $body = $this->composeSignupBody($loginId, $password, $user);
        $response = $this->auth->doPost($uri, $body);

        $resp = json_decode($response->getBody(), true);
        return $this->auth->generateJwtResponse($resp, $response->getCookie(REFRESH_SESSION_COOKIE_NAME), null);
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
        $response = $this->auth->doPost($uri, ['loginId' => $loginId, 'password' => $password]);

        $resp = json_decode($response->getBody(), true);
        return $this->auth->generateJwtResponse($resp, $response->getCookie(REFRESH_SESSION_COOKIE_NAME), null);
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

        $response = $this->auth->doPost($uri, $body);
        return json_decode($response->getBody(), true);
    }

    /**
     * Updates the password of a user.
     *
     * @param string $loginId Login ID of the user.
     * @param string $newPassword New password for the user.
     * @param string $refreshToken Refresh token for authentication.
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
        $this->auth->doPost($uri, ['loginId' => $loginId, 'newPassword' => $newPassword], null, $refreshToken);
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
        $response = $this->auth->doPost($uri, [
            'loginId' => $loginId,
            'oldPassword' => $oldPassword,
            'newPassword' => $newPassword,
        ]);

        $resp = json_decode($response->getBody(), true);
        return $this->auth->generateJwtResponse($resp, $response->getCookie(REFRESH_SESSION_COOKIE_NAME), null);
    }

    /**
     * Retrieves the password policy.
     *
     * @return array Password policy array.
     */
    public function getPolicy(): array
    {
        $response = $this->auth->doGet(EndpointsV1::PASSWORD_POLICY_PATH);
        return json_decode($response->getBody(), true);
    }

    /**
     * Composes the body for the signup request.
     *
     * @param string $loginId Login ID for the new user.
     * @param string $password Password for the new user.
     * @param array|null $user Optional user details.
     * @return array Body array for the signup request.
     */
    private function composeSignupBody(string $loginId, string $password, ?array $user): array
    {
        $body = ['loginId' => $loginId, 'password' => $password];
        if ($user !== null) {
            $body['user'] = $user;
        }
        return $body;
    }
}