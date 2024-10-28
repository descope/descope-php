<?php

namespace Descope\Tests\Management;

use PHPUnit\Framework\TestCase;
use Descope\SDK\DescopeSDK;
use Descope\SDK\Management\Password\UserPassword;
use Descope\SDK\Management\Password\UserPasswordBcrypt;
use Descope\SDK\Management\Password\UserPasswordFirebase;
use Descope\SDK\Management\Password\UserPasswordPbkdf2;
use Descope\SDK\Management\Password\UserPasswordDjango;
use Descope\SDK\Management\User;
use Descope\SDK\Management\AssociatedTenant;
use Descope\SDK\Management\UserObj;
use Descope\SDK\Management\LoginOptions;
use Descope\SDK\Common\DeliveryMethod;
use Descope\SDK\Exception\AuthException;
use GuzzleHttp\Exception\RequestException;

class UserTest extends TestCase
{
    private DescopeSDK $descopeSDK;
    private string $createdUserLoginId;
    private string $createdUserId;

    protected function setUp(): void
    {
        $config = [
            'projectId' => 'YOUR_PROJECT_ID',
            'managementKey' => 'YOUR_MANAGEMENT_KEY',
        ];

        $this->descopeSDK = new DescopeSDK($config);
    }

    public function testCreateUser()
    {
        $response = $this->descopeSDK->management->user->create(
            "testuser1",
            "testuser1@example.com",
            "+14152464801",
            "Test User",
            "Test",
            "Middle",
            "User",
            "https://example.com",
            ["customAttr1" => "value1"],
            true,
            true,
            "http://example.com/invite",
            ["additionalLoginId1"],
            ["SA2ZsUj73JFqUn8iQx9tblndjKCc6"],
            new UserPassword("Password123!"),
            ["user"],
            [new AssociatedTenant("T2o2zKibuWuCVH4lqJrSfFuXss06", ["Tenant Admin"])]
        );
        $this->createdUserLoginId = $response['user']['loginIds'][0] ?? null;
        $this->createdUserId = $response['user']['userId'] ?? null;

        $this->assertArrayHasKey('userId', $response);
    }

    public function testCreateTestUser()
    {
        $response = $this->descopeSDK->management->user->createTestUser(
            "testuser2",
            "testuser2@example.com",
            "+14152464801",
            "Test User 2",
            "Test",
            "Middle",
            "User",
            "http://example.com/picture2.jpg",
            ["customAttr2" => "value2"],
            false,
            false,
            "http://example.com/invite2",
            ["additionalLoginId2"],
            ["SA2ZsUj73JFqUn8iQx9tblndjKCc6"],
            new UserPassword("Password456!"),
            ["user"]
        );
        $this->assertArrayHasKey('userId', $response);
    }

    public function testInviteUser()
    {
        $response = $this->descopeSDK->management->user->invite(
            "testuser3",
            "testuser3@example.com",
            "+14152464801",
            "Test User 3",
            "Test",
            "Middle",
            "User",
            "http://example.com/picture3.jpg",
            ["customAttr3" => "value3"],
            true,
            true,
            "http://example.com/invite3",
            true,
            true,
            ["additionalLoginId3"],
            ["SA2ZsUj73JFqUn8iQx9tblndjKCc6"],
            new UserPassword("", new UserPasswordBcrypt("$2y$10$/brZw23J/ya5sOJl8vm7H.BqhDnLqH4ohtSKcZYvSVP/hE6veK.0K")),
            ["user"],
            [new AssociatedTenant("T2o2zKibuWuCVH4lqJrSfFuXss06", ["Tenant Admin"])]
        );
        $this->assertArrayHasKey('userId', $response);
    }

    public function testInviteBatchUsers()
    {
        $users = [
            new UserObj(
                "batchuser1",
                "batchuser1@example.com",
                "+14152464801",
                "Batch User 1",
                "Batch",
                "Middle",
                "User",
                ["user"],
                [new AssociatedTenant("T2o2zKibuWuCVH4lqJrSfFuXss06", ["Tenant Admin"])],
                "http://example.com/picture1.jpg",
                [],
                true,
                true,
                ["additionalLoginId1"],
                [],
                new UserPassword("Password123!"),
            ),
            new UserObj(
                "batchuser2",
                "batchuser2@example.com",
                "+14152464801",
                "Batch User 2",
                "Batch",
                "Middle",
                "User",
                ["user"],
                [],
                "http://example.com/picture2.jpg",
                [],
                true,
                true,
                ["additionalLoginId2"],
                [],
                new UserPassword("Password456!"),
            )
        ];

        $response = $this->descopeSDK->management->user->inviteBatch($users, "http://example.com/invitebatch", true, true);
        $this->assertArrayHasKey('createdUsers', $response);
    }

    public function testUpdateUser()
    {
        $this->descopeSDK->management->user->update(
            "use login id from previously created user",
            "newtestuser1@example.com",
            "",
            "Updated Test User",
            "",
            "",
            "",
            "http://example.com/newpicture.jpg",
            [],
            true,
            false,
            ["additionalLoginId1"]
        );
        print("Hello!");
        $this->assertTrue(true);
    }

    public function testLoadUser()
    {
        $response = $this->descopeSDK->management->user->load($this->createdUserLoginId);
        $this->assertArrayHasKey('user', $response);
    }

    public function testLoadUserByUserId()
    {
        $response = $this->descopeSDK->management->user->loadByUserId($this->createdUserId);
        $this->assertArrayHasKey('user', $response);
    }

    public function testGenerateEmbeddedLink()
    {
        $response = $this->descopeSDK->management->user->generateEmbeddedLink("kevin+1@descope.com");
        $this->assertIsString($response);
    }

    public function testGenerateEnchantedLinkForTestUser()
    {
        $loginOptions = new LoginOptions(true, true);
        $response = $this->descopeSDK->management->user->generateEnchantedLinkForTestUser(
            "testuser1",
            "http://example.com/redirect",
            $loginOptions
        );
        $this->assertArrayHasKey('link', $response);
        $this->assertArrayHasKey('pendingRef', $response);
    }

    public function testSearchAllUsers()
    {
        $response = $this->descopeSDK->management->user->searchAll(
            [],
            [],
            10,
            0,
            false,
            false,
            [],
            ["enabled"],
            ["testuser1@example.com"],
            ["+14152464801"],
            []
        );
        $this->assertArrayHasKey('users', $response);
    }

    public function testActivateUser()
    {
        $response = $this->descopeSDK->management->user->activate("testuser1");
        $this->assertTrue(true);
    }

    public function testDeactivateUser()
    {
        $response = $this->descopeSDK->management->user->deactivate("testuser1");
        $this->assertTrue(true);
    }

    public function testDeleteUser()
    {
        $this->descopeSDK->management->user->delete("testuser1");
        $this->assertTrue(true);
    }

    public function testDeleteAllTestUsers()
    {
        $this->descopeSDK->management->user->deleteAllTestUsers();
        $this->assertTrue(true);
    }
}
