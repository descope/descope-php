<?php

namespace Descope\SDK\Tests\Auth;

use PHPUnit\Framework\TestCase;
use Descope\SDK\Auth\Password;
use Descope\SDK\API;
use Descope\Exception\AuthException;
use GuzzleHttp\Psr7\Response;

class PasswordTest extends TestCase
{
    private $apiMock;
    private $password;

    protected function setUp(): void
    {
        $this->apiMock = $this->createMock(API::class);
        $this->password = new Password($this->apiMock);
    }

    public function testSignUp()
    {
        $loginId = 'test';
        $password = 's3cr3t';
        $user = ['name' => 'admin'];

        $response = new Response(200, [], json_encode(['jwt' => 'jwt_token']));
        $this->apiMock->expects($this->once())
            ->method('doPost')
            ->with('/v1/auth/signup/password', ['loginId' => $loginId, 'password' => $password, 'user' => $user])
            ->willReturn($response);

        $this->apiMock->expects($this->once())
            ->method('generateJwtResponse')
            ->with(['jwt' => 'jwt_token'], null, null)
            ->willReturn(['jwt' => 'jwt_token']);

        $result = $this->password->signUp($loginId, $password, $user);
        $this->assertEquals(['jwt' => 'jwt_token'], $result);
    }

    public function testSignIn()
    {
        $loginId = 'test';
        $password = 's3cr3t';

        $response = new Response(200, [], json_encode(['jwt' => 'jwt_token']));
        $this->apiMock->expects($this->once())
            ->method('doPost')
            ->with('/v1/auth/signin/password', ['loginId' => $loginId, 'password' => $password])
            ->willReturn($response);

        $this->apiMock->expects($this->once())
            ->method('generateJwtResponse')
            ->with(['jwt' => 'jwt_token'], null, null)
            ->willReturn(['jwt' => 'jwt_token']);

        $result = $this->password->signIn($loginId, $password);
        $this->assertEquals(['jwt' => 'jwt_token'], $result);
    }

    public function testSendReset()
    {
        $loginId = 'test';
        $redirectUrl = 'https://example.com/reset';
        $templateOptions = ['template' => 'option'];

        $response = new Response(200, [], json_encode(['status' => 'success']));
        $this->apiMock->expects($this->once())
            ->method('doPost')
            ->with('/v1/auth/password/reset', ['loginId' => $loginId, 'redirectUrl' => $redirectUrl, 'templateOptions' => $templateOptions])
            ->willReturn($response);

        $result = $this->password->sendReset($loginId, $redirectUrl, $templateOptions);
        $this->assertEquals(['status' => 'success'], $result);
    }

    public function testUpdate()
    {
        $loginId = 'test';
        $newPassword = 's3cr3t1';
        $refreshToken = 'refresh_token';

        $this->apiMock->expects($this->once())
            ->method('doPost')
            ->with('/v1/auth/password/update', ['loginId' => $loginId, 'newPassword' => $newPassword], $refreshToken);

        $this->password->update($loginId, $newPassword, $refreshToken);
    }

    public function testReplace()
    {
        $loginId = 'test';
        $oldPassword = 's3cr3t';
        $newPassword = 's3cr3t1';

        $response = new Response(200, [], json_encode(['jwt' => 'jwt_token']));
        $this->apiMock->expects($this->once())
            ->method('doPost')
            ->with('/v1/auth/password/replace', ['loginId' => $loginId, 'oldPassword' => $oldPassword, 'newPassword' => $newPassword])
            ->willReturn($response);

        $this->apiMock->expects($this->once())
            ->method('generateJwtResponse')
            ->with(['jwt' => 'jwt_token'], null, null)
            ->willReturn(['jwt' => 'jwt_token']);

        $result = $this->password->replace($loginId, $oldPassword, $newPassword);
        $this->assertEquals(['jwt' => 'jwt_token'], $result);
    }

    public function testGetPolicy()
    {
        $response = new Response(200, [], json_encode(['policy' => 'password_policy']));
        $this->apiMock->expects($this->once())
            ->method('doGet')
            ->with('/v1/auth/password/policy')
            ->willReturn($response);

        $result = $this->password->getPolicy();
        $this->assertEquals(['policy' => 'password_policy'], $result);
    }
}