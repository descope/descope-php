<?php

namespace Descope\Tests\Management;

use PHPUnit\Framework\TestCase;
use Descope\SDK\DescopeSDK;
use Descope\SDK\Management\Password\UserPassword;
use Descope\SDK\Management\Password\UserPasswordBcrypt;
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
    private ?string $createdUserLoginId = null;
    private ?string $createdUserId = null;

    protected function setUp(): void
    {
        $projectId = $_ENV['DESCOPE_PROJECT_ID'] ?? null;
        $managementKey = $_ENV['DESCOPE_MANAGEMENT_KEY'] ?? null;

        if (empty($projectId) || empty($managementKey)) {
            $this->markTestSkipped('Management integration tests require DESCOPE_PROJECT_ID and DESCOPE_MANAGEMENT_KEY in env.');
        }

        $config = [
            'projectId' => $projectId,
            'managementKey' => $managementKey,
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

    public function testInviteBatchUsersWithStatus()
    {
        $users = [
            new UserObj(
                "batchstatususer1",
                "batchstatususer1@example.com",
                "+14152464801",
                "Batch Status User 1",
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
                "enabled"  // status set to enabled
            ),
            new UserObj(
                "batchstatususer2",
                "batchstatususer2@example.com",
                "+14152464801",
                "Batch Status User 2",
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
                "disabled"  // status set to disabled
            )
        ];

        $response = $this->descopeSDK->management->user->inviteBatch($users, "http://example.com/invitebatch", true, true);
        $this->assertArrayHasKey('createdUsers', $response);
        
        // Verify that users were created with the correct status
        foreach ($response['createdUsers'] as $createdUser) {
            $this->assertArrayHasKey('status', $createdUser);
            $this->assertContains($createdUser['status'], ['enabled', 'disabled']);
        }
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
            [],
            [
                "tenant1" => ["roleA", "roleB"]
            ],
            [
                "tenant1" => ["Admin", "Editor"]
            ]
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

    public function testLogoutUser()
    {
        $this->descopeSDK->management->user->logoutUser("testuser1");
        $this->assertTrue(true);
    }

    public function testLogoutUserByUserId()
    {
        $this->descopeSDK->management->user->logoutUserByUserId("U2abc123");
        $this->assertTrue(true);
    }

    public function testCreateBatch()
    {
        $response = $this->descopeSDK->management->user->createBatch([
            new UserObj("batchuser1", "batchuser1@example.com"),
        ]);
        $this->assertIsArray($response);
    }

    public function testPatch()
    {
        $response = $this->descopeSDK->management->user->patch(
            "testuser1",
            null,
            null,
            "Patched Name"
        );
        $this->assertIsArray($response);
    }

    public function testPatchBatch()
    {
        $response = $this->descopeSDK->management->user->patchBatch([
            new UserObj("testuser1", null, null, "Patched Batch Name"),
        ]);
        $this->assertIsArray($response);
    }

    public function testDeleteBatch()
    {
        $this->descopeSDK->management->user->deleteBatch(["U2abc123"]);
        $this->assertTrue(true);
    }

    public function testImport()
    {
        $response = $this->descopeSDK->management->user->import("my-source", null, null, true);
        $this->assertIsArray($response);
    }

    public function testLoadUsers()
    {
        $response = $this->descopeSDK->management->user->loadUsers(["U2abc123"], false);
        $this->assertIsArray($response);
    }

    public function testSearchAllTestUsers()
    {
        $response = $this->descopeSDK->management->user->searchAllTestUsers(null, null, null, 10);
        $this->assertIsArray($response);
    }

    public function testUpdateRecoveryEmail()
    {
        $response = $this->descopeSDK->management->user->updateRecoveryEmail("testuser1", "recovery@example.com", true);
        $this->assertIsArray($response);
    }

    public function testUpdateRecoveryPhone()
    {
        $response = $this->descopeSDK->management->user->updateRecoveryPhone("testuser1", "+14152464801", true);
        $this->assertIsArray($response);
    }

    public function testGetCustomAttributes()
    {
        $response = $this->descopeSDK->management->user->getCustomAttributes();
        $this->assertIsArray($response);
    }

    public function testCreateCustomAttributes()
    {
        $response = $this->descopeSDK->management->user->createCustomAttributes([
            ['name' => 'myAttr', 'type' => 'string'],
        ]);
        $this->assertIsArray($response);
    }

    public function testDeleteCustomAttributes()
    {
        $response = $this->descopeSDK->management->user->deleteCustomAttributes(["myAttr"]);
        $this->assertIsArray($response);
    }

    public function testUpdateUserNames()
    {
        $response = $this->descopeSDK->management->user->updateUserNames("testuser1", "Given", "Middle", "Family");
        $this->assertIsArray($response);
    }

    public function testAddTenantRoles()
    {
        $response = $this->descopeSDK->management->user->addTenantRoles(
            "testuser1",
            "T2o2zKibuWuCVH4lqJrSfFuXss06",
            ["Tenant Admin"]
        );
        $this->assertIsArray($response);
    }

    public function testRemovePasskey()
    {
        $this->descopeSDK->management->user->removePasskey("testuser1", "cred-123");
        $this->assertTrue(true);
    }

    public function testListPasskeys()
    {
        $response = $this->descopeSDK->management->user->listPasskeys("testuser1");
        $this->assertIsArray($response);
    }

    public function testRemoveTotpSeed()
    {
        $this->descopeSDK->management->user->removeTotpSeed("testuser1");
        $this->assertTrue(true);
    }

    public function testGetProviderTokenWithOptions()
    {
        $response = $this->descopeSDK->management->user->getProviderTokenWithOptions(
            "testuser1",
            "google",
            true,
            false
        );
        $this->assertIsArray($response);
    }

    public function testGenerateEmbeddedLinkSignUp()
    {
        $token = $this->descopeSDK->management->user->generateEmbeddedLinkSignUp(
            "testuser1",
            ['user' => ['email' => 'testuser1@example.com'], 'emailVerified' => true],
            ['timeout' => 3600]
        );
        $this->assertNotEmpty($token);
    }

    public function testListTrustedDevices()
    {
        $response = $this->descopeSDK->management->user->listTrustedDevices(["testuser1"]);
        $this->assertIsArray($response);
    }

    public function testRemoveTrustedDevices()
    {
        $this->descopeSDK->management->user->removeTrustedDevices("testuser1", ["device-123"]);
        $this->assertTrue(true);
    }
}
