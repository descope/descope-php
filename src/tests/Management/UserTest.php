<?php

namespace Descope\Tests\Management;

use PHPUnit\Framework\TestCase;
use Descope\SDK\DescopeSDK;
use Descope\SDK\Management\UserPassword;
use Descope\SDK\Management\UserPasswordBcrypt;
use Descope\SDK\Management\UserPasswordFirebase;
use Descope\SDK\Management\UserPasswordPbkdf2;
use Descope\SDK\Management\UserPasswordDjango;
use Descope\SDK\Management\User;
use Descope\SDK\Management\AssociatedTenant;
use Descope\SDK\Management\UserObj;
use Descope\SDK\Auth\LoginOptions;
use Descope\SDK\Common\DeliveryMethod;
use Descope\SDK\Exception\AuthException;
use GuzzleHttp\Exception\RequestException;

class UserPasswordTest extends TestCase
{
    private DescopeSDK $descopeSDK;

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
            new UserPassword("password123"),
            ["user"],
            [new AssociatedTenant("T2SrweL5J2y8YOh8DyDbGpZXejBA", ["Tenant Admin"])]
        );
        $this->assertArrayHasKey('userId', $response);
        print_r($response);
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
            new UserPassword(cleartext: "password456"),
            ["user"],
            [new AssociatedTenant("T2SrweL5J2y8YOh8DyDbGpZXejBA", ["Tenant User"])]
        );
        $this->assertArrayHasKey('userId', $response);
        print_r($response);
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
            new UserPassword(hashed: new UserPasswordBcrypt("$2y$10$/brZw23J/ya5sOJl8vm7H.BqhDnLqH4ohtSKcZYvSVP/hE6veK.0K")),
            ["user"],
            [new AssociatedTenant("T2SrweL5J2y8YOh8DyDbGpZXejBA", ["Tenant User"])]
        );
        $this->assertArrayHasKey('userId', $response);
        print_r($response);
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
                [new AssociatedTenant("T2SrweL5J2y8YOh8DyDbGpZXejBA", ["Tenant User"])],
                "http://example.com/picture1.jpg",
                ["customAttr1" => "value1"],
                true,
                true,
                ["additionalLoginId1"],
                [],
                new UserPassword(cleartext: "password123")
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
                [new AssociatedTenant("T2SrweL5J2y8YOh8DyDbGpZXejBA", ["Tenant User"])],
                "http://example.com/picture2.jpg",
                ["customAttr2" => "value2"],
                true,
                true,
                ["additionalLoginId2"],
                [],
                new UserPassword(cleartext: "password456")
            )
        ];

        $response = $this->descopeSDK->management->user->inviteBatch($users, "http://example.com/invitebatch", true, true);
        $this->assertArrayHasKey('userIds', $response);
        print_r($response);
    }

    public function testUpdateUser()
    {
        $this->descopeSDK->management->user->update(
            "testuser1",
            "newtestuser1@example.com",
            "+14152464801",
            "Updated Test User",
            "Updated",
            "Middle",
            "User",
            "http://example.com/newpicture.jpg",
            ["dob" => "newvalue1"],
            true,
            true,
            ["additionalLoginId1"],
            ["SA2ZsUj73JFqUn8iQx9tblndjKCc6"]
        );
        $this->assertTrue(true); // Assert no exception thrown
    }

    public function testDeleteUser()
    {
        $this->descopeSDK->management->user->delete("testuser1");
        $this->assertTrue(true); // Assert no exception thrown
    }

    public function testLoadUser()
    {
        $response = $this->descopeSDK->management->user->load("testuser1");
        $this->assertArrayHasKey('userId', $response);
        print_r($response);
    }

    public function testLoadUserByUserId()
    {
        $response = $this->descopeSDK->management->user->loadByUserId("U2goH2ldn4SzXoFm6IWKlRiEq6JV");
        $this->assertArrayHasKey('userId', $response);
        print_r($response);
    }

    public function testGenerateEmbeddedLink()
    {
        $response = $this->descopeSDK->management->user->generateEmbeddedLink("testuser1");
        $this->assertIsString($response);
        print_r($response);
    }

    public function testGenerateEnchantedLinkForTestUser()
    {
        $loginOptions = new LoginOptions(['stepup' => true, 'mfa' => true]);
        $response = $this->descopeSDK->management->user->generateEnchantedLinkForTestUser(
            "testuser1",
            "http://example.com/redirect",
            $loginOptions
        );
        $this->assertArrayHasKey('link', $response);
        $this->assertArrayHasKey('pendingRef', $response);
        print_r($response);
    }

    public function testLogoutUser()
    {
        $response = $this->descopeSDK->management->user->logout("testuser1");
        $this->assertTrue(true); // Assert no exception thrown
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
        print_r($response);
    }

    public function testGetProviderToken()
    {
        $response = $this->descopeSDK->management->user->getProviderToken("testuser1", "google");
        $this->assertArrayHasKey('accessToken', $response);
        print_r($response);
    }

    public function testActivateUser()
    {
        $response = $this->descopeSDK->management->user->activate("testuser1");
        $this->assertArrayHasKey('status', $response);
        print_r($response);
    }

    public function testDeactivateUser()
    {
        $response = $this->descopeSDK->management->user->deactivate("testuser1");
        $this->assertArrayHasKey('status', $response);
        print_r($response);
    }
}
