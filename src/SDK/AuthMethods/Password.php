<?php

declare(strict_types=1);

namespace Descope\Auth;

use Descope\Exception\AuthException;
use Descope\Common\EndpointsV1;

class Password
{
    private $auth;

    public function __construct($auth)
    {
        $this->auth = $auth;
    }

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

    public function getPolicy(): array
    {
        $response = $this->auth->doGet(EndpointsV1::PASSWORD_POLICY_PATH);
        return json_decode($response->getBody(), true);
    }

    private function composeSignupBody(string $loginId, string $password, ?array $user): array
    {
        $body = ['loginId' => $loginId, 'password' => $password];
        if ($user !== null) {
            $body['user'] = $user;
        }
        return $body;
    }
}