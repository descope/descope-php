<?php

namespace Descope\Tests\Management;

use PHPUnit\Framework\TestCase;
use Descope\SDK\API;
use Descope\SDK\Management\User;
use Descope\SDK\Management\MgmtV1;
use Descope\SDK\Management\Password\UserPassword;
use Descope\SDK\Management\Password\UserPasswordBcrypt;

class UserPasswordEndpointsTest extends TestCase
{
    private $apiMock;
    private User $user;

    protected function setUp(): void
    {
        $this->apiMock = $this->createMock(API::class);
        $this->user = new User($this->apiMock);
        MgmtV1::setBaseUrl('descope_project_id');
    }

    public function testSetTemporaryPasswordSendsCleartextAsPlainString(): void
    {
        $this->apiMock->expects($this->once())
            ->method('doPost')
            ->with(
                MgmtV1::$USER_SET_TEMPORARY_PASSWORD_PATH,
                ['loginId' => 'testuser1', 'password' => 'newPassword123'],
                true
            );

        $this->user->setTemporaryPassword('testuser1', new UserPassword('newPassword123'));
    }

    public function testSetActivePasswordSendsCleartextAsPlainString(): void
    {
        $this->apiMock->expects($this->once())
            ->method('doPost')
            ->with(
                MgmtV1::$USER_SET_ACTIVE_PASSWORD_PATH,
                ['loginId' => 'testuser1', 'password' => 'activePassword123'],
                true
            );

        $this->user->setActivePassword('testuser1', new UserPassword('activePassword123'));
    }

    public function testSetActivePasswordSendsHashedPasswordObject(): void
    {
        $hashed = new UserPasswordBcrypt('$2a$12$XlQwF3/7ohdzYrE0LC4A.O');

        $this->apiMock->expects($this->once())
            ->method('doPost')
            ->with(
                MgmtV1::$USER_SET_ACTIVE_PASSWORD_PATH,
                ['loginId' => 'testuser1', 'hashedPassword' => $hashed->toArray()],
                true
            );

        $this->user->setActivePassword('testuser1', new UserPassword(null, $hashed));
    }
}
