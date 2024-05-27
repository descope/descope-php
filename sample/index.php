<?php
require '../vendor/autoload.php';
use Descope\SDK\DescopeSDK;
use Descope\SDK\Management\UserPassword;
use Descope\SDK\Management\AssociatedTenant;
use Descope\SDK\Management\UserObj;

session_start();
if (isset($_SESSION["user"])) {
    // header('Location: dashboard.php');
    // exit();
}

$descopeSDK = new DescopeSDK([
    'projectId' => 'P2OkfVnJi5Ht7mpCqHjx17nV5epH',
    'managementKey' => 'K2gqWEix95sbiQGlkwQPBa1PZNgngMhbzygh09qZ0Ssfwd9pmX0SNkskO8OTs0MuE3MY26C'
]);

// $response = $response = $descopeSDK->password->signUp("gaokevin1", "Password123!");
$response = $descopeSDK->password->signIn("gaokevin", "6ny8UPNgTVtwB,tcjltg");
// print_r($response);
// print($response['refreshSessionToken']);
// Create user
echo "Testing create user:\n";
$response = $descopeSDK->management->user->create(
    "testuser1",
    "testuser1@example.com",
    "+1234567890",
    "Test User",
    "Test",
    "Middle",
    "User",
    ["admin", "user"],
    [new AssociatedTenant("T2SrweL5J2y8YOh8DyDbGpZXejBA", ["Tenant Admin, CRO"])],
    "http://example.com/picture.jpg",
    ["customAttr1" => "value1"],
    true,
    true,
    "http://example.com/invite",
    ["additionalLoginId1"],
    ["SA2ZsUj73JFqUn8iQx9tblndjKCc6"],
    new UserPassword(cleartext: "password123")
);
print_r($response);

// Create test user
echo "Testing create test user:\n";
$response = $descopeSDK->management->user->createTestUser(
    "testuser2",
    "testuser2@example.com",
    "+0987654321",
    "Test User 2",
    "Test",
    "Middle",
    "User",
    ["user"],
    "http://example.com/picture2.jpg",
    ["customAttr2" => "value2"],
    false,
    false,
    "http://example.com/invite2",
    ["additionalLoginId2"],
    new UserPassword(cleartext: "password456")
);
print_r($response);

// Invite user
echo "Testing invite user:\n";
$response = $descopeSDK->management->user->invite(
    "testuser3",
    "testuser3@example.com",
    "1122334455",
    "Test User 3",
    "Test",
    "Middle",
    "User",
    ["user"],
    "http://example.com/picture3.jpg",
    ["customAttr3" => "value3"],
    true,
    true,
    "http://example.com/invite3",
    true,
    true,
    ["additionalLoginId3"],
    ["ssoAppId3"],
    new UserPassword(hashed: new UserPasswordBcrypt("$2y$10$/brZw23J/ya5sOJl8vm7H.BqhDnLqH4ohtSKcZYvSVP/hE6veK.0K"))
);
print_r($response);

// Invite batch users
echo "Testing invite batch users:\n";
$users = [
    new UserObj(
        "batchuser1",
        "batchuser1@example.com",
        "1231231234",
        "Batch User 1",
        "Batch",
        "Middle",
        "User",
        ["user"],
        true,
        true,
        ["additionalLoginId1"],
        new UserPassword(cleartext: "password123")
    ),
    new UserObj(
        "batchuser2",
        "batchuser2@example.com",
        "4321432143",
        "Batch User 2",
        "Batch",
        "Middle",
        "User",
        ["user"],
        "http://example.com/picture2.jpg",
        ["customAttr2" => "value2"],
        true,
        true,
        ["additionalLoginId2"],
        new UserPassword(cleartext: "password456")
    )
];
$response = $descopeSDK->management->user->inviteBatch($users, "http://example.com/invitebatch", true, true);
print_r($response);

// Update user
echo "Testing update user:\n";
$descopeSDK->management->user->update(
    "testuser1",
    "newtestuser1@example.com",
    "1234567899",
    "Updated Test User",
    "Updated",
    "Middle",
    "User",
    ["admin", "user"],
    "http://example.com/newpicture.jpg",
    ["customAttr1" => "newvalue1"],
    true,
    true,
    ["additionalLoginId1"],
    new UserPassword(cleartext: "newpassword123")
);

// Delete user
echo "Testing delete user:\n";
$descopeSDK->management->user->delete("testuser1");

// Load user
echo "Testing load user:\n";
$response = $descopeSDK->management->user->load("testuser1");
print_r($response);

// Load user by user ID
echo "Testing load user by user ID:\n";
$response = $descopeSDK->management->user->loadByUserId("userId1");
print_r($response);

// Logout user
echo "Testing logout user:\n";
$descopeSDK->management->user->logoutUser("testuser1");

// Search all users
echo "Testing search all users:\n";
$response = $descopeSDK->management->user->searchAll(["tenant1"], ["admin"], 10, 1, false, false, ["customAttr1" => "value1"], ["active"], ["testuser1@example.com"], ["1234567890"], ["ssoAppId1"], [["field" => "loginId", "desc" => true]], "Test");
print_r($response);

// Get provider token
echo "Testing get provider token:\n";
$response = $descopeSDK->management->user->getProviderToken("testuser1", "provider1");
print_r($response);

// Activate user
echo "Testing activate user:\n";
$response = $descopeSDK->management->user->activate("testuser1");
print_r($response);

// Deactivate user
echo "Testing deactivate user:\n";
$response = $descopeSDK->management->user->deactivate("testuser1");
print_r($response);

// Update login ID
echo "Testing update login ID:\n";
$response = $descopeSDK->management->user->updateLoginId("testuser1", "newtestuser1");
print_r($response);

// Update email
echo "Testing update email:\n";
$response = $descopeSDK->management->user->updateEmail("testuser1", "newtestuser1@example.com", true);
print_r($response);

// Update phone
echo "Testing update phone:\n";
$response = $descopeSDK->management->user->updatePhone("testuser1", "9876543210", true);
print_r($response);

// Update display name
echo "Testing update display name:\n";
$response = $descopeSDK->management->user->updateDisplayName("testuser1", "Updated Display Name", "Updated Given Name", "Updated Middle Name", "Updated Family Name");
print_r($response);

// Update picture
echo "Testing update picture:\n";
$response = $descopeSDK->management->user->updatePicture("testuser1", "http://example.com/newpicture.jpg");
print_r($response);

// Update custom attribute
echo "Testing update custom attribute:\n";
$response = $descopeSDK->management->user->updateCustomAttribute("testuser1", "customAttr1", "newvalue1");
print_r($response);

// Set roles
echo "Testing set roles:\n";
$response = $descopeSDK->management->user->setRoles("testuser1", ["admin"]);
print_r($response);

// Add roles
echo "Testing add roles:\n";
$response = $descopeSDK->management->user->addRoles("testuser1", ["editor"]);
print_r($response);

// Remove roles
echo "Testing remove roles:\n";
$response = $descopeSDK->management->user->removeRoles("testuser1", ["user"]);
print_r($response);

// Set SSO apps
echo "Testing set SSO apps:\n";
$response = $descopeSDK->management->user->setSsoApps("testuser1", ["ssoAppId1"]);
print_r($response);

// Add SSO apps
echo "Testing add SSO apps:\n";
$response = $descopeSDK->management->user->addSsoApps("testuser1", ["ssoAppId2"]);
print_r($response);

// Remove SSO apps
echo "Testing remove SSO apps:\n";
$response = $descopeSDK->management->user->removeSsoApps("testuser1", ["ssoAppId1"]);
print_r($response);

// Add tenant
echo "Testing add tenant:\n";
$response = $descopeSDK->management->user->addTenant("testuser1", "tenantId1");
print_r($response);

// Remove tenant
echo "Testing remove tenant:\n";
$response = $descopeSDK->management->user->removeTenant("testuser1", "tenantId1");
print_r($response);

// Set tenant roles
echo "Testing set tenant roles:\n";
$response = $descopeSDK->management->user->setTenantRoles("testuser1", "tenantId1", ["admin"]);
print_r($response);

// Add tenant roles
echo "Testing add tenant roles:\n";
$response = $descopeSDK->management->user->addTenantRoles("testuser1", "tenantId1", ["user"]);
print_r($response);

// Remove tenant roles
echo "Testing remove tenant roles:\n";
$response = $descopeSDK->management->user->removeTenantRoles("testuser1", "tenantId1", ["admin"]);
print_r($response);

// Set temporary password
echo "Testing set temporary password:\n";
$descopeSDK->management->user->setTemporaryPassword("testuser1", new UserPassword(cleartext: "temporaryPassword123"));

// Set active password
echo "Testing set active password:\n";
$descopeSDK->management->user->setActivePassword("testuser1", new UserPassword(cleartext: "activePassword123"));

// Set password
echo "Testing set password:\n";
$descopeSDK->management->user->setPassword("testuser1", new UserPassword(cleartext: "password123"), true);

// Expire password
echo "Testing expire password:\n";
$descopeSDK->management->user->expirePassword("testuser1");

// Remove all passkeys
echo "Testing remove all passkeys:\n";
$descopeSDK->management->user->removeAllPasskeys("testuser1");

// Generate OTP for test user
echo "Testing generate OTP for test user:\n";
$response = $descopeSDK->management->user->generateOtpForTestUser(DeliveryMethod::SMS, "testuser1", new LoginOptions());
print_r($response);

// Generate magic link for test user
echo "Testing generate magic link for test user:\n";
$response = $descopeSDK->management->user->generateMagicLinkForTestUser(DeliveryMethod::EMAIL, "testuser1", "http://example.com/magiclink", new LoginOptions());
print_r($response);

// Generate enchanted link for test user
echo "Testing generate enchanted link for test user:\n";
$response = $descopeSDK->management->user->generateEnchantedLinkForTestUser("testuser1", "http://example.com/enchantedlink", new LoginOptions());
print_r($response);

// Generate embedded link
echo "Testing generate embedded link:\n";
$response = $descopeSDK->management->user->generateEmbeddedLink("testuser1", ["customClaim1" => "value1"]);
print_r($response);

// History
echo "Testing history:\n";
$response = $descopeSDK->management->user->history(["testuser1"]);
print_r($response);
    
if ($descopeSDK->verify('eyJhbGciOiJSUzI1NiIsImtpZCI6IlAyT2tmVm5KaTVIdDdtcENxSGp4MTduVjVlcEgiLCJ0eXAiOiJKV1QifQ.eyJhbXIiOlsib2F1dGgiXSwiZHJuIjoiRFMiLCJleHAiOjE3MTY0NDM5ODIsImlhdCI6MTcxNjQ0MzM4MiwiaXNzIjoiUDJPa2ZWbkppNUh0N21wQ3FIangxN25WNWVwSCIsInJleHAiOiIyMDI0LTA2LTIwVDA1OjQ5OjQyWiIsInN1YiI6IlUyZ2JSaG02MEMyMktia2IxNFFTamlNUVh1ZnkiLCJ0ZW5hbnRzIjp7IlQyU3J3ZUw1SjJ5OFlPaDhEeURiR3BaWGVqQkEiOnt9fX0.I3OkPNrkoX0AtP12SV1i3l0nct-dElR6euRc_-jnt_Gg7lSw_p62DjQxOdNAklsloqwWiNw1jyVqu_CEoJ7rTenXIq0zokA2aUvCJfmvyo0dQnO7hn85x03qShSmnSkUt40tWxCX142-qt62I0CPHTJSppTjOp3qkQHjEeuqKCwTY0r5fxlmFE_cjI8e-W0AuCtkb5u5fwal3etRvBA-hfSsY-OFCVL5YLPWhIR4zuQEqpBRokiZY-ymyk3LnBcokBj0_W0KTpZYt0DC5frG2G26y1VcwF0kav3Sd_EMVbVJZRw6Z34yrwrQBpBcQcFumA4Xiphy0fXCfgtY47XZTQ')) {

    $_SESSION["user"] = json_decode($_POST["userDetails"], true);
    $_SESSION["sessionToken"] = $_POST["sessionToken"];

    // Redirect to dashboard
    // header('Location: dashboard.php');
    // exit();
} else {
    // Redirect to login page
    // header('Location: login.php');
    // exit();
}
?>



<!DOCTYPE html>
<html>
<head>
    <title>Welcome to PHP SDK Sample App</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Welcome to PHP SDK Sample App</h1>
    <button class="dashboard-button" onclick="window.location.href='login.php'">Login</button>
</body>
</html>