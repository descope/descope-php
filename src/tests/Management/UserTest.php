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
use Descope\SDK\DeliveryMethod;

class UserPasswordTest extends TestCase
{
    private $descopeSDK;

    protected function setUp(): void
    {
        // Assuming $config is an array with necessary configuration
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
            new UserPassword(cleartext: "password123"),
            [],
            [new AssociatedTenant("T2SrweL5J2y8YOh8DyDbGpZXejBA", ["Tenant Admin"])]
        );
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
            [],
            new UserPassword(hashed: new UserPasswordBcrypt("$2y$10$/brZw23J/ya5sOJl8vm7H.BqhDnLqH4ohtSKcZYvSVP/hE6veK.0K")),
            [new AssociatedTenant("T2SrweL5J2y8YOh8DyDbGpZXejBA", ["Tenant User"])]
        );
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
            true
        );
    }

    public function testDeleteUser()
    {
        $this->descopeSDK->management->user->delete("testuser1");
    }

    public function testLoadUser()
    {
        $response = $this->descopeSDK->management->user->load("gaokevin1");
        print_r($response);
    }

    public function testLoadUserByUserId()
    {
        $response = $this->descopeSDK->management->user->loadByUserId("U2goH2ldn4SzXoFm6IWKlRiEq6JV");
        print_r($response);
    }

    public function testSignIn()
    {
        $response = $this->descopeSDK->password->signIn("gaokevin", "6ny8UPNgTVtwB,tcjltg");
        print_r($response);
    }

    public function testLogoutUser()
    {
        $response = $this->descopeSDK->password->signIn("gaokevin", "6ny8UPNgTVtwB,tcjltg");
        $this->descopeSDK->logout($response['refreshSessionToken']);
    }

    public function testSearchAllUsers()
    {
        $response = $this->descopeSDK->management->user->searchAll(
            [],
            [],
            10,
            1,
            false,
            false,
            [],
            ["enabled"],
            ["testuser1@example.com"],
            ["+14152464801"],
            []
        );
        print_r($response);
    }

    public function testGetProviderToken()
    {
        $response = $this->descopeSDK->management->user->getProviderToken("gaokevin1", "google");
        print_r($response);
    }

    public function testActivateUser()
    {
        $response = $this->descopeSDK->management->user->activate("gaokevin1");
        print_r($response);
    }

    public function testDeactivateUser()
    {
        $response = $this->descopeSDK->management->user->deactivate("testuser1");
        print_r($response);
    }

    public function testUpdateLoginId()
    {
        $response = $this->descopeSDK->management->user->updateLoginId("testuser1", "newtestuser1");
        print_r($response);
    }

    public function testUpdateEmail()
    {
        $response = $this->descopeSDK->management->user->updateEmail("testuser1", "newtestuser1@example.com", true);
        print_r($response);
    }

    public function testUpdatePhone()
    {
        $response = $this->descopeSDK->management->user->updatePhone("testuser1", "+14152464801", true);
        print_r($response);
    }

    public function testUpdateDisplayName()
    {
        $response = $this->descopeSDK->management->user->updateDisplayName("testuser1", "Updated Display Name", "Updated Given Name", "Updated Middle Name", "Updated Family Name");
        print_r($response);
    }

    public function testUpdatePicture()
    {
        $response = $this->descopeSDK->management->user->updatePicture("testuser1", "http://example.com/newpicture.jpg");
        print_r($response);
    }

    public function testUpdateCustomAttribute()
    {
        $response = $this->descopeSDK->management->user->updateCustomAttribute("testuser1", "customAttr1", "newvalue1");
        print_r($response);
    }

    public function testSetRoles()
    {
        $response = $this->descopeSDK->management->user->setRoles("testuser1", ["user"]);
        print_r($response);
    }

    public function testAddRoles()
    {
        $response = $this->descopeSDK->management->user->addRoles("testuser1", ["admin"]);
        print_r($response);
    }

    public function testRemoveRoles()
    {
        $response = $this->descopeSDK->management->user->removeRoles("testuser1", ["admin"]);
        print_r($response);
    }

    public function testSetTenants()
    {
        $tenants = [
            new AssociatedTenant("T2SrweL5J2y8YOh8DyDbGpZXejBA", ["Tenant Admin"]),
            new AssociatedTenant("T2SrweL5J2y8YOh8DyDbGpZXejBB", ["Tenant User"])
        ];
        $response = $this->descopeSDK->management->user->setTenants("testuser1", $tenants);
        print_r($response);
    }

    public function testAddTenants()
    {
        $tenants = [
            new AssociatedTenant("T2SrweL5J2y8YOh8DyDbGpZXejBC", ["Tenant Viewer"])
        ];
        $response = $this->descopeSDK->management->user->addTenants("testuser1", $tenants);
        print_r($response);
    }

    public function testRemoveTenants()
    {
        $response = $this->descopeSDK->management->user->removeTenants("testuser1", ["T2SrweL5J2y8YOh8DyDbGpZXejBB"]);
        print_r($response);
    }

    public function testUpdateDisplay()
    {
        $response = $this->descopeSDK->management->user->updateDisplay("testuser1", "Updated Display");
        print_r($response);
    }
}
