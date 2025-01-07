<?php

namespace Descope\Tests\Auth;

use PHPUnit\Framework\TestCase;
use Descope\SDK\Auth\Password;
use Descope\SDK\API;
use Descope\Exception\AuthException;
use Descope\SDK\EndpointsV1;

class PasswordTest extends TestCase
{
    private $apiMock;
    private $password;

    protected function setUp(): void
    {
        $this->apiMock = $this->createMock(API::class);
        $this->password = new Password($this->apiMock);
        EndpointsV1::setBaseUrl('descope_project_id');
    }

    public function testSignUp()
    {
        $loginId = 'test';
        $password = 's3cr3t';
        $user = ['name' => 'admin'];

        $response = ['jwt' => 'jwt_token'];
        $this->apiMock->expects($this->once())
            ->method('doPost')
            ->with('/v1/auth/signup/password', ['loginId' => $loginId, 'password' => $password, 'user' => $user])
            ->willReturn($response);

        $this->apiMock->expects($this->once())
            ->method('generateJwtResponse')
            ->with($response, null, null)
            ->willReturn($response);

        $result = $this->password->signUp($loginId, $password, $user);
        $this->assertEquals($response, $result);
    }

    public function testSignIn()
    {
        $loginId = 'test';
        $password = 's3cr3t';

        $response = ['jwt' => 'jwt_token'];
        $this->apiMock->expects($this->once())
            ->method('doPost')
            ->with('/v1/auth/signin/password', ['loginId' => $loginId, 'password' => $password])
            ->willReturn($response);

        $this->apiMock->expects($this->once())
            ->method('generateJwtResponse')
            ->with($response, null, null)
            ->willReturn($response);

        $result = $this->password->signIn($loginId, $password);
        $this->assertEquals($response, $result);
    }

    public function testSendReset()
    {
        $loginId = 'test';
        $redirectUrl = 'https://example.com/reset';
        $templateOptions = ['template' => 'option'];

        $response = ['status' => 'success'];
        $this->apiMock->expects($this->once())
            ->method('doPost')
            ->with('/v1/auth/password/reset', ['loginId' => $loginId, 'redirectUrl' => $redirectUrl, 'templateOptions' => $templateOptions])
            ->willReturn($response);

        $result = $this->password->sendReset($loginId, $redirectUrl, $templateOptions);
        $this->assertEquals($response, $result);
    }

    public function testUpdate()
    {
        $loginId = 'test';
        $newPassword = 's3cr3t1';
        $refreshToken = 'refresh_token';

        $this->apiMock->expects($this->once())
            ->method('doPost')
            ->with('/v1/auth/password/update', ['loginId' => $loginId, 'newPassword' => $newPassword], $refreshToken)
            ->willReturn([]);

        $this->password->update($loginId, $newPassword, $refreshToken);
    }

    public function testReplace()
    {
        $loginId = 'test';
        $oldPassword = 's3cr3t';
        $newPassword = 's3cr3t1';

        $response = ['jwt' => 'jwt_token'];
        $this->apiMock->expects($this->once())
            ->method('doPost')
            ->with('/v1/auth/password/replace', ['loginId' => $loginId, 'oldPassword' => $oldPassword, 'newPassword' => $newPassword])
            ->willReturn($response);

        $this->apiMock->expects($this->once())
            ->method('generateJwtResponse')
            ->with($response, null, null)
            ->willReturn($response);

        $result = $this->password->replace($loginId, $oldPassword, $newPassword);
        $this->assertEquals($response, $result);
    }

    public function testGetPolicy()
    {
        $response = ['policy' => 'password_policy'];
        $this->apiMock->expects($this->once())
            ->method('doGet')
            ->with('/v1/auth/password/policy')
            ->willReturn($response);

        $result = $this->password->getPolicy();
        $this->assertEquals($response, $result);
    }
}
