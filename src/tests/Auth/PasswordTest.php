<?php

namespace Descope\Tests\Auth;

use PHPUnit\Framework\TestCase;
use Descope\SDK\Auth\Password;
use Descope\SDK\API;
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
            ->with(EndpointsV1::$SIGN_UP_PASSWORD_PATH, ['loginId' => $loginId, 'password' => $password, 'user' => $user], false)
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
            ->with(EndpointsV1::$SIGN_IN_PASSWORD_PATH, ['loginId' => $loginId, 'password' => $password], false)
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
            ->with(EndpointsV1::$SEND_RESET_PASSWORD_PATH, ['loginId' => $loginId, 'redirectUrl' => $redirectUrl, 'templateOptions' => $templateOptions], false)
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
            ->with(EndpointsV1::$UPDATE_PASSWORD_PATH, ['loginId' => $loginId, 'newPassword' => $newPassword], false, $refreshToken)
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
            ->with(EndpointsV1::$REPLACE_PASSWORD_PATH, ['loginId' => $loginId, 'oldPassword' => $oldPassword, 'newPassword' => $newPassword], false)
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
            ->with(EndpointsV1::$PASSWORD_POLICY_PATH, false)
            ->willReturn($response);

        $result = $this->password->getPolicy();
        $this->assertEquals($response, $result);
    }
}
