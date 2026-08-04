# <a title="Descope PHP SDK" href="https://www.php.net/"><img width="64" alt="php logo" src="https://upload.wikimedia.org/wikipedia/commons/2/27/PHP-logo.svg"></a> by Descope

[![License](https://img.shields.io/:license-MIT-blue.svg?style=flat)](https://opensource.org/licenses/MIT)

## Overview

The Descope SDK for PHP provides convenient access to Descope authentication. You can read more on the [Descope Website](https://descope.com).

## Getting started

### Requirements

- [PHP 7.3+](https://www.php.net/)

### Installation

Install the package with `Composer`:

```
composer require descope/descope-php
```

You'll need to set up a `.env` file in the root directory with your Descope Project ID, which you can get from the [Console](https://app.descope.com/settings/project) like this:

```
DESCOPE_PROJECT_ID=<Descope Project ID>
DESCOPE_MANAGEMENT_KEY=<Descope Management Key>
```

## Using the SDK

In order to use the SDK you will need to initialize a `DescopeSDK` object with your Descope Project ID you defined in your `.env` file, like this:

```php
require 'vendor/autoload.php';
use Descope\SDK\DescopeSDK;

$descopeSDK = new DescopeSDK([
    'projectId' => $_ENV['DESCOPE_PROJECT_ID'],
    'managementKey' => $_ENV['DESCOPE_MANAGEMENT_KEY'], // Optional, only used for Management functions
    'debug' => false, // Optional, enables verbose error logging (default: false)
    'requestTimeout' => 60, // Optional, HTTP request timeout in seconds (default: 60)
]);
```

### HTTP Timeouts

Every HTTP call the SDK makes is bounded so a slow or unresponsive network peer
cannot hold a PHP worker indefinitely. By default each request times out after
**60 seconds**, with a **10 second** connection-establishment timeout. Set
`requestTimeout` (a positive number of seconds, may be fractional) to change the
overall per-request deadline:

```php
$descopeSDK = new DescopeSDK([
    'projectId' => $_ENV['DESCOPE_PROJECT_ID'],
    'requestTimeout' => 10, // Fail requests that take longer than 10 seconds
]);
```

For full control over the transport (proxies, TLS, custom handlers, or your own
timeout strategy) you can supply a pre-configured Guzzle-compatible client as
`httpClient`. When you do, the SDK uses it as-is and does **not** apply its own
timeout settings, so configure timeouts on the client itself:

```php
use GuzzleHttp\Client;

$descopeSDK = new DescopeSDK([
    'projectId' => $_ENV['DESCOPE_PROJECT_ID'],
    'httpClient' => new Client([
        'timeout' => 10,
        'connect_timeout' => 5,
        // Custom handler, proxy, or other transport configuration.
    ]),
]);
```

### Debug/Verbose Logging

The SDK supports optional debug/verbose logging to help troubleshoot API request issues. **Debug logging is disabled by default** to keep your application logs clean in production.

When enabled, the SDK will log detailed error information to PHP's error log (via `error_log()`) when API requests fail, including:

- HTTP status codes
- Error response bodies
- Request exceptions

You can enable debug logging in three ways:

1. **Via Config Array** (recommended):

   ```php
   $descopeSDK = new DescopeSDK([
       'projectId' => $_ENV['DESCOPE_PROJECT_ID'],
       'debug' => true  // Enable verbose logging
   ]);
   ```

2. **Via Environment Variable**:

   ```bash
   export DESCOPE_DEBUG=true
   ```

   Then initialize the SDK normally (it will automatically detect the environment variable):

   ```php
   $descopeSDK = new DescopeSDK([
       'projectId' => $_ENV['DESCOPE_PROJECT_ID']
   ]);
   ```

3. **Via `.env` file**:
   ```
   DESCOPE_DEBUG=true
   ```

**Note:** Debug logging uses PHP's `error_log()` function, so logs will appear in your configured PHP error log location (typically defined by `error_log` in `php.ini` or your server configuration).

### Error Handling

When an API request fails (e.g. 4xx/5xx response, network error), the SDK throws exceptions instead of returning error arrays.

- **Success:** Methods return the decoded JSON response (or `void` where applicable).
- **Failure:** The SDK throws:
  - **`Descope\SDK\Exception\AuthException`** — for most request failures (bad request, unauthorized, server errors, etc.). The exception includes the HTTP status code, error type, and message (from the API when available).
  - **`Descope\SDK\Exception\RateLimitException`** — for HTTP 429 (rate limit) responses.

You can catch these and react accordingly:

```php
use Descope\SDK\DescopeSDK;
use Descope\SDK\Exception\AuthException;
use Descope\SDK\Exception\RateLimitException;

$descopeSDK = new DescopeSDK([
    'projectId' => $_ENV['DESCOPE_PROJECT_ID'],
    'managementKey' => $_ENV['DESCOPE_MANAGEMENT_KEY'],
]);

try {
    $descopeSDK->management->user->update('loginId', 'new@example.com', ...);
    // Success — update completed
} catch (AuthException $e) {
    // Request failed — use $e->getMessage(), status code, etc.
} catch (RateLimitException $e) {
    // Rate limited (429)
}
```

The original Guzzle `RequestException` is available via `$e->getPrevious()` for logging or debugging.

### Caching Mechanism

The Descope PHP SDK uses a caching mechanism to store frequently accessed data, such as JSON Web Key Sets (JWKs) for session token validation. By default, the SDK uses **APCu** for caching, provided it is enabled and configured in your environment. If APCu is not available, and no other caching mechanism is provided, caching is disabled.

By using the `CacheInterface`, you can integrate the Descope PHP SDK with any caching mechanism that suits your application, ensuring optimal performance in both small and large-scale deployments.

#### Custom Caching with `CacheInterface`

The SDK allows you to provide a custom caching mechanism by implementing the `CacheInterface`. This interface defines three methods that any cache implementation should support:

- `get(string $key)`: Retrieve a value by key.
- `set(string $key, $value, int $ttl = 3600): bool`: Store a value with a specified time-to-live (TTL).
- `delete(string $key): bool`: Remove a value by key.

You can provide your custom caching implementation by creating a class that implements `CacheInterface`. Here's an example using Laravel's cache system:

```php
namespace App\Cache;

use Descope\SDK\Cache\CacheInterface;
use Illuminate\Support\Facades\Cache;

class LaravelCache implements CacheInterface
{
    public function get(string $key)
    {
        return Cache::get($key);
    }

    public function set(string $key, $value, int $ttl = 3600): bool
    {
        // Laravel TTL is in minutes
        return Cache::put($key, $value, max(1, ceil($ttl / 60)));
    }

    public function delete(string $key): bool
    {
        return Cache::forget($key);
    }
}
```

To use the Laravel cache in the SDK:

```php
use Descope\SDK\DescopeSDK;
use App\Cache\LaravelCache;

$descopeSDK = new DescopeSDK([
    'projectId' => $_ENV['DESCOPE_PROJECT_ID'],
    'managementKey' => $_ENV['DESCOPE_MANAGEMENT_KEY'],
], new LaravelCache());
```

Once you've configured your caching, you're ready to use the SDK. This SDK will easily allow you integrate Descope functionality with the following built-in functions:

## Authentication Methods

### Passwords

#### Sign Up

```php
$response = $descopeSDK->auth->password->signUp("loginId", "password123");
print_r($response);
```

#### Sign In

```php
$response = $descopeSDK->auth->password->signIn("loginId", "password123");
print_r($response);
```

#### Send Reset Password

```php
$response = $descopeSDK->auth->password->sendReset("loginId", "https://example.com/reset");
print_r($response);
```

#### Update Password

```php
$descopeSDK->auth->password->update("loginId", "newPassword123", "refreshToken");
```

#### Replace Password

```php
$response = $descopeSDK->auth->password->replace("loginId", "oldPassword123", "newPassword123");
print_r($response);
```

#### Get Password Policy

```php
$response = $descopeSDK->auth->password->getPolicy();
print_r($response);
```

### SSO

#### SSO Sign In

```php
$response = $descopeSDK->auth->sso->signIn(
    "tenant",
    "https://example.com/callback",
    "prompt",
    true,
    true,
    ["custom" => "claim"],
    "ssoAppId"
);
print_r($response);
```

#### Exchange Token

```php
$response = $descopeSDK->auth->sso->exchangeToken("code");
print_r($response);
```

### OTP (One-Time Password)

The delivery method is one of `"email"`, `"sms"`, `"whatsapp"` or `"voice"`.

```php
// Send a code to a new or existing user
$descopeSDK->otp->signUp("email", "loginId", ["email" => "user@example.com"]);
$descopeSDK->otp->signIn("email", "loginId");
$descopeSDK->otp->signUpOrIn("email", "loginId");

// Verify the received code and get a session
$response = $descopeSDK->otp->verifyCode("email", "loginId", "123456");
print_r($response);
```

### Magic Link

```php
// Send a magic link containing the given redirect URI
$descopeSDK->magicLink->signUp("email", "loginId", "https://example.com/verify", ["email" => "user@example.com"]);
$descopeSDK->magicLink->signIn("email", "loginId", "https://example.com/verify");
$descopeSDK->magicLink->signUpOrIn("email", "loginId", "https://example.com/verify");

// Verify the token extracted from the magic link and get a session
$response = $descopeSDK->magicLink->verify("token");
print_r($response);
```

### Session Management

1. `DescopeSDK->verify($sessionToken)` - will validate the session token and return either **TRUE** or **FALSE**, depending on if the JWT is valid and expired.
2. `DescopeSDK->refreshSession($refreshToken)` - will refresh your session and return a new session token, with the refresh token.
3. `DescopeSDK->verifyAndRefreshSession($sessionToken, $refreshToken)` - will validate the session token and return either **TRUE** or **FALSE**, and will refresh your session and return a new session token.
4. `DescopeSDK->logout($refreshToken)` - will invalidate the refresh token and log the user out of the current session.
5. `DescopeSDK->logoutAll($refreshToken)` - will invalidate all refresh tokens associated with a given project, thereby signing out of all sessions across multiple applications.

---

6. `DescopeSDK->getClaims($sessionToken)` - will validate the JWT signature and return all of the verified claims in an array format.
7. `DescopeSDK->getUserDetails($refreshToken)` - will return all of the user information (email, phone, verification status, etc.) using a provided refresh token.
8. `DescopeSDK->selectTenant($tenantId, $refreshToken)` - will return a new set of tokens scoped to the selected tenant.
9. `DescopeSDK->exchangeAccessKey($accessKey, $loginOptions)` - will exchange an access key for a session JWT.
10. `DescopeSDK->history($refreshToken)` - will return the current user's authentication history.

### User Management Functions

Each of these functions have code examples on how to use them.

> Some of these values may be incorrect for your environment, they exist purely as an example for your own implementation.

#### Create User

```php
$response = $descopeSDK->management->user->create(
    'testuser1',                // loginId
    'newemail@example.com',     // email
    '+1234567890',              // phone
    'Updated User',             // displayName
    'Updated',                  // givenName
    'Middle',                   // middleName
    'User',                     // familyName
    null,                       // picture
    null,                       // customAttributes
    true,                       // verifiedEmail
    true,                       // verifiedPhone
    null,                       // inviteUrl
    ['altUser1'],               // additionalLoginIds
    ['app123'],                 // ssoAppIds
    null,                       // password
    ['admin', 'editor'],        // roleNames
    [['tenantId' => 'tenant1']] // userTenants
);
print_r($response);
```

#### Update User

```php
$response = $descopeSDK->management->user->update(
    'testuser1',                // loginId
    'updatedemail@example.com', // email
    '+1234567890',              // phone
    'Updated User',             // displayName
    'Updated',                  // givenName
    'Middle',                   // middleName
    'User',                     // familyName
    'https://example.com/newpic.jpg', // picture
    ['department' => 'HR'],     // customAttributes
    true,                       // verifiedEmail
    true,                       // verifiedPhone
    ['altUser1'],               // additionalLoginIds
    [''],                 // ssoAppIds
);
```

#### Invite User

```php
$response = $descopeSDK->management->user->invite(
    'newuser1',                       // loginId
    'invite@example.com',             // email
    '+1234567890',                    // phone
    'New User',                       // displayName
    'John',                           // givenName
    'Middle',                        // middleName
    'Doe',                           // familyName
    'https://example.com/profile.jpg', // picture
    ['department' => 'Engineering'], // customAttributes
    true,                           // verifiedEmail
    true,                           // verifiedPhone
    'https://myapp.com/invite',     // inviteUrl
    true,                           // sendMail
    true                            // sendSms
);
print_r($response);
```

#### Batch Invite

```php
$users = [
    new Descope\SDK\Management\UserObj(
        'batchuser1',                 // loginId
        'batch1@example.com',         // email
        null,                         // phone
        'Batch User One',             // displayName
        null,                         // givenName
        null,                         // middleName
        null,                         // familyName
        ['admin'],                    // roleNames
        [['tenantId' => 'tenant1']],   // userTenants (can be an empty array if no tenant)
        null,                         // picture
        null,                         // customAttributes
        null,                         // verifiedEmail
        null,                         // verifiedPhone
        null,                         // additionalLoginIds
        null,                         // ssoAppIds
        null,                         // password
        'enabled'                     // status (optional: "enabled", "disabled", "invited")
    ),

    new Descope\SDK\Management\UserObj(
        'batchuser2',                 // loginId
        'batch2@example.com',         // email
        null,                         // phone
        'Batch User Two',             // displayName
        null,                         // givenName
        null,                         // middleName
        null,                         // familyName
        ['viewer'],                   // roleNames
        [['tenantId' => 'tenant2']],   // userTenants (can be an empty array if no tenant)
        null,                         // picture
        null,                         // customAttributes
        null,                         // verifiedEmail
        null,                         // verifiedPhone
        null,                         // additionalLoginIds
        null,                         // ssoAppIds
        null,                         // password
        'disabled'                    // status (optional: "enabled", "disabled", "invited")
    )
];

$response = $descopeSDK->management->user->inviteBatch(
    $users,
    'https://myapp.com/batch-invite',  // inviteUrl
    true,                              // sendMail
    true                               // sendSms
);

print_r($response);
```

#### Delete User

```php
$descopeSDK->management->user->delete("testuser1");
```

#### Search All Users

```php
$response = $descopeSDK->management->user->searchAll(
    "",                       // loginId
    [],                       // tenantIds
    ['admin', 'viewer'],      // roleNames
    50,                       // limit
    "",                       // text
    1,                        // page
    false,                    // ssoOnly
    false,                    // testUsersOnly
    false,                    // withTestUser
    null,                     // customAttributes
    ['enabled'],              // statuses
    ['user@example.com'],     // emails
    ['+1234567890'],          // phones
    ['ssoApp123'],            // ssoAppIds
    [                         // sort
        ['field' => 'displayName', 'desc' => true]
    ],
    null,                     // tenantRoleIds
    null,                     // tenantRoleNames
    1700000000000,            // fromCreatedTime (Unix epoch milliseconds)
    1800000000000,            // toCreatedTime (Unix epoch milliseconds)
    1700000000000,            // fromModifiedTime (Unix epoch milliseconds)
    1800000000000             // toModifiedTime (Unix epoch milliseconds)
);
print_r($response);
```

#### Add Tenant

```php
$response = $descopeSDK->management->user->addTenant("testuser1", "tenantId1");
print_r($response);
```

#### Remove Tenant

```php
$response = $descopeSDK->management->user->removeTenant("testuser1", "tenantId1");
print_r($response);
```

#### Set Tenant Roles

```php
$response = $descopeSDK->management->user->setTenantRoles("testuser1", "tenantId1", ["admin"]);
print_r($response);
```

#### Add Tenant Roles

```php
$response = $descopeSDK->management->user->addTenantRoles("testuser1", "tenantId1", ["user"]);
print_r($response);
```

#### Remove Tenant Roles

```php
$response = $descopeSDK->management->user->removeTenantRoles("testuser1", "tenantId1", ["admin"]);
print_r($response);
```

#### Set Temporary Password

```php
$descopeSDK->management->user->setTemporaryPassword("testuser1", new UserPassword(cleartext: "temporaryPassword123"));
```

#### Set Active Password

```php
$descopeSDK->management->user->setActivePassword("testuser1", new UserPassword(cleartext: "activePassword123"));
```

#### Logout User (Management)

Management (service-to-service) logout that terminates a user's sessions on all devices. This is distinct from the auth-side `$descopeSDK->logout($refreshToken)`, which logs out the caller using their own refresh token. Use `logoutUser` to log out by login ID and `logoutUserByUserId` to log out by user ID.

```php
// By login ID (optionally scope to specific session types)
$descopeSDK->management->user->logoutUser("testuser1", []);

// By user ID
$descopeSDK->management->user->logoutUserByUserId("U2abc...", []);
```

#### Create Batch

```php
$users = [
    new Descope\SDK\Management\UserObj('batchuser1', 'batch1@example.com'),
    new Descope\SDK\Management\UserObj('batchuser2', 'batch2@example.com'),
];
$response = $descopeSDK->management->user->createBatch($users);
print_r($response);
```

#### Patch User

Patches a single user; only the non-null fields provided are updated.

```php
$response = $descopeSDK->management->user->patch(
    'testuser1',              // loginId
    'patched@example.com',    // email
    null,                     // phone
    'Patched Name',           // displayName
    null,                     // givenName
    null,                     // middleName
    null,                     // familyName
    ['admin'],                // roleNames
    null,                     // userTenants
    ['department' => 'HR'],   // customAttributes
);
print_r($response);
```

#### Patch Batch

```php
$response = $descopeSDK->management->user->patchBatch($users); // array of UserObj (only non-null fields patched)
print_r($response);
```

#### Delete Batch

```php
// IMPORTANT: irreversible. Deletes users by their user IDs.
$descopeSDK->management->user->deleteBatch(["U2abc...", "U2def..."]);
```

#### Import Users

```php
$response = $descopeSDK->management->user->import(
    'auth0',        // source
    $usersJson,     // users (raw source payload, optional)
    $hashesJson,    // hashes (optional)
    true            // dryrun
);
print_r($response);
```

#### Load Users

```php
$response = $descopeSDK->management->user->loadUsers(["U2abc...", "U2def..."], false); // userIds, includeInvalidUsers
print_r($response);
```

#### Search All Test Users

```php
$response = $descopeSDK->management->user->searchAllTestUsers(
    "",                   // loginId
    [],                   // tenantIds
    ['admin'],            // roleNames
    50,                   // limit
    "",                   // text
    0                     // page
);
print_r($response);
```

#### Update Recovery Email / Phone

```php
$descopeSDK->management->user->updateRecoveryEmail("testuser1", "recovery@example.com", true); // loginId, email, verified
$descopeSDK->management->user->updateRecoveryPhone("testuser1", "+1234567890", true);          // loginId, phone, verified
```

#### Custom Attribute Schema

```php
$attributes = $descopeSDK->management->user->getCustomAttributes();
$descopeSDK->management->user->createCustomAttributes([
    ['name' => 'department', 'type' => 'string'],
]);
$descopeSDK->management->user->deleteCustomAttributes(['department']);
```

#### Update User Names

```php
$response = $descopeSDK->management->user->updateUserNames(
    "testuser1",  // loginId
    "Jane",       // givenName
    null,         // middleName
    "Doe"         // familyName
);
print_r($response);
```

#### Add Tenant Roles (append)

```php
$response = $descopeSDK->management->user->addTenantRoles("testuser1", "tenantId1", ["user"]);
print_r($response);
```

#### Passkeys

```php
$passkeys = $descopeSDK->management->user->listPasskeys("testuser1");           // loginId
$descopeSDK->management->user->removePasskey("testuser1", "credentialId123");   // loginId, credentialId
```

#### Remove TOTP Seed

```php
$descopeSDK->management->user->removeTotpSeed("testuser1");
```

#### Get Provider Token (with options)

```php
$response = $descopeSDK->management->user->getProviderTokenWithOptions(
    "testuser1",  // loginId
    "google",     // provider
    true,         // withRefreshToken
    false         // forceRefresh
);
print_r($response);
```

#### Generate Embedded Link (Sign Up)

```php
$token = $descopeSDK->management->user->generateEmbeddedLinkSignUp(
    "newuser1",                       // loginId
    ['email' => 'new@example.com'],   // user (optional)
    ['customClaims' => ['k' => 'v']]  // signUpOptions (optional)
);
```

#### Trusted Devices

```php
$devices = $descopeSDK->management->user->listTrustedDevices(["testuser1"]);        // list of loginIds or userIds
$descopeSDK->management->user->removeTrustedDevices("testuser1", ["deviceId123"]);  // loginId, deviceIds
```

### Tenant Management Functions

Manage tenants for multi-tenant applications.

```php
// Create a tenant (id is optional; one is generated if omitted)
$response = $descopeSDK->management->tenant->create("My Tenant", null, ["example.com"], ["plan" => "pro"]);
$tenantId = $response["id"];

// Update a tenant (overwrites all fields)
$descopeSDK->management->tenant->update($tenantId, "My Renamed Tenant", ["example.com"]);

// Load a single tenant / all tenants
$tenant = $descopeSDK->management->tenant->load($tenantId);
$all = $descopeSDK->management->tenant->loadAll();

// Search tenants
$found = $descopeSDK->management->tenant->searchAll([], ["My Renamed Tenant"]);

// Delete a tenant (cascade removes its users/keys)
$descopeSDK->management->tenant->delete($tenantId, false);

// Create a tenant with a specific ID
$descopeSDK->management->tenant->createWithId("my-tenant-id", "My Tenant", ["example.com"], ["plan" => "pro"]);

// Read / configure tenant settings
$settings = $descopeSDK->management->tenant->getSettings($tenantId);
$descopeSDK->management->tenant->configureSettings($tenantId, [
    "enabled" => true,
    "authType" => "saml",
    "domains" => ["example.com"],
    "sessionTokenExpiration" => 60,
    "sessionTokenExpirationUnit" => "minutes",
]);

// Update the tenant's default roles
$descopeSDK->management->tenant->updateDefaultRoles($tenantId, ["user"]);

// Generate / revoke an SSO self-service configuration link
$link = $descopeSDK->management->tenant->generateSSOConfigurationLink(
    $tenantId,   // tenantId
    3600,        // expireDuration (seconds)
    "",          // ssoId (optional)
    "",          // email (optional)
    "",          // templateId (optional)
    ""           // actorId (optional)
);
$descopeSDK->management->tenant->revokeSSOConfigurationLink($tenantId);
```

### Role Management Functions

```php
$descopeSDK->management->role->create("My Role", "role description", ["Read", "Write"]);
$descopeSDK->management->role->update("My Role", "My Renamed Role", "updated", ["Read"]);
$roles = $descopeSDK->management->role->loadAll();
$matches = $descopeSDK->management->role->search([], ["My Renamed Role"]);
$descopeSDK->management->role->delete("My Renamed Role");

// Create multiple roles at once (each entry is an associative array of role fields)
$descopeSDK->management->role->createBatch([
    ['name' => 'Role A', 'description' => 'first', 'permissionNames' => ['Read']],
    ['name' => 'Role B', 'permissionNames' => ['Write']],
]);

// Update a role by ID (tenantId empty for a project-level role)
$descopeSDK->management->role->updateWithId(
    "roleId123",   // id
    "",            // tenantId
    "New Name",    // newName
    "updated",     // description
    ["Read"],      // permissionNames
    false,         // defaultRole
    false          // private
);

// Update multiple roles at once
$response = $descopeSDK->management->role->updateBatch([/* RoleUpdateRequest entries */]);

// Delete by ID or in batch
$descopeSDK->management->role->deleteWithId("roleId123", "");          // id, tenantId (optional)
$descopeSDK->management->role->deleteBatch(["Role A", "Role B"], "");  // roleNames, tenantId (optional), roleIds
```

### Permission Management Functions

```php
$descopeSDK->management->permission->create("Read", "can read");
$descopeSDK->management->permission->update("Read", "ReadOnly", "can read only");
$permissions = $descopeSDK->management->permission->loadAll();
$descopeSDK->management->permission->delete("ReadOnly");

// Create multiple permissions at once (each entry is an associative array of permission fields)
$descopeSDK->management->permission->createBatch([
    ['name' => 'Read', 'description' => 'can read'],
    ['name' => 'Write', 'description' => 'can write'],
]);

// Update a permission by ID
$descopeSDK->management->permission->updateWithId("permissionId123", "ReadOnly", "can read only");

// Update multiple permissions at once
$response = $descopeSDK->management->permission->updateBatch([/* permission update entries */]);

// Delete by ID or in batch
$descopeSDK->management->permission->deleteWithId("permissionId123");
$descopeSDK->management->permission->deleteBatch(["Read", "Write"], []); // names, ids
```

### Access Key Management Functions

```php
// Create an access key; the cleartext is only returned once, on creation
$response = $descopeSDK->management->accessKey->create("My Key", 0, ["My Role"]);
$cleartext = $response["cleartext"];
$keyId = $response["key"]["id"];

$descopeSDK->management->accessKey->load($keyId);
$descopeSDK->management->accessKey->searchAll();
$descopeSDK->management->accessKey->update($keyId, "My Renamed Key");
$descopeSDK->management->accessKey->deactivate($keyId);
$descopeSDK->management->accessKey->activate($keyId);
$descopeSDK->management->accessKey->delete($keyId);

// Batch operations over multiple keys by ID
$descopeSDK->management->accessKey->activateBatch([$keyId, "keyId2"]);
$descopeSDK->management->accessKey->deactivateBatch([$keyId, "keyId2"]);
$descopeSDK->management->accessKey->deleteBatch([$keyId, "keyId2"]); // IMPORTANT: irreversible

// Rotate a key; the new cleartext is returned once
$rotated = $descopeSDK->management->accessKey->rotate($keyId);
$newCleartext = $rotated["cleartext"];
```

### SSO Application Management Functions

Manage SSO (IdP) applications your project exposes to relying parties.

```php
// OIDC application
$response = $descopeSDK->management->ssoApplication->createOidcApplication("My OIDC App", "https://login.example.com");
$appId = $response["id"];
$descopeSDK->management->ssoApplication->updateOidcApplication($appId, "My OIDC App", "https://login.example.com");

// SAML application
$descopeSDK->management->ssoApplication->createSamlApplication("My SAML App", "https://login.example.com");

$descopeSDK->management->ssoApplication->load($appId);
$descopeSDK->management->ssoApplication->loadAll();
$descopeSDK->management->ssoApplication->delete($appId);

// WS-Fed application
$response = $descopeSDK->management->ssoApplication->createWSFedApplication(
    "My WS-Fed App",              // name
    "https://login.example.com", // loginPageUrl
    null,                        // id (optional)
    true                         // enabled
);
$wsFedAppId = $response["id"];
$descopeSDK->management->ssoApplication->updateWSFedApplication(
    $wsFedAppId,                 // id
    "My WS-Fed App",             // name
    "https://login.example.com", // loginPageUrl
    true                         // enabled
);

// Read / rotate the application secret (cleartext returned under the 'cleartext' key)
$secret = $descopeSDK->management->ssoApplication->getApplicationSecret($appId);
$rotated = $descopeSDK->management->ssoApplication->rotateApplicationSecret($appId);
```

### SSO Settings (Tenant SSO Configuration)

Configure the SSO provider used by a tenant. `management->sso` is the tenant SSO configuration component (distinct from the auth-flow `$descopeSDK->sso`).

```php
$settings = $descopeSDK->management->sso->loadSettings("tenantId1");

// Configure OIDC for a tenant
$descopeSDK->management->sso->configureOIDCSettings("tenantId1", [
    "name" => "MyOIDC",
    "clientId" => "clientId",
    "clientSecret" => "clientSecret",
    "redirectUrl" => "https://example.com/callback",
    "authUrl" => "https://idp.example.com/authorize",
    "tokenUrl" => "https://idp.example.com/token",
    "userDataUrl" => "https://idp.example.com/userinfo",
    "scope" => ["openid", "email"],
], ["example.com"]);

// Configure SAML for a tenant
$descopeSDK->management->sso->configureSAMLSettings("tenantId1", [
    "idpUrl" => "https://idp.example.com/sso",
    "entityId" => "entityId",
    "idpCert" => "-----BEGIN CERTIFICATE----- ...",
], "https://example.com/callback", ["example.com"]);

$descopeSDK->management->sso->deleteSettings("tenantId1");

// Load every SSO configuration for a tenant (a tenant may have multiple)
$all = $descopeSDK->management->sso->loadAllSettings("tenantId1");

// Configure the SAML/OAuth redirect URLs for a tenant
$descopeSDK->management->sso->configureSSORedirectURL(
    "tenantId1",                            // tenantId
    "https://example.com/saml/callback",    // samlRedirectUrl (optional)
    "https://example.com/oauth/callback",   // oauthRedirectUrl (optional)
    ""                                      // ssoId (optional)
);

// Create a new named SSO configuration for a tenant
$newCfg = $descopeSDK->management->sso->newSettings("tenantId1", "ssoId1", "My SSO");

// Legacy v1 configure helpers
$descopeSDK->management->sso->getSettings("tenantId1");
$descopeSDK->management->sso->configureSettings(
    "tenantId1",                    // tenantId
    "https://idp.example.com/sso",  // idpURL
    "-----BEGIN CERTIFICATE-----",  // idpCert
    "entityId",                     // entityID
    "https://example.com/callback", // redirectURL
    ["example.com"]                 // domains (optional)
);
$descopeSDK->management->sso->configureMetadata(
    "tenantId1",                              // tenantId
    "https://idp.example.com/metadata.xml",   // idpMetadataURL
    "https://example.com/callback",           // redirectURL
    ["example.com"]                           // domains (optional)
);
$descopeSDK->management->sso->configureMapping(
    "tenantId1",                                                 // tenantId
    [['groups' => ['admins'], 'roleName' => 'admin']],           // roleMappings
    ['name' => 'displayName', 'email' => 'email']                // attributeMapping (optional)
);

// Recalculate the SSO role/attribute mappings for a tenant
$descopeSDK->management->sso->recalculateSSOMappings("tenantId1", ""); // tenantId, ssoId (optional)
```

### JWT Management Functions

```php
// Update a JWT with custom claims
$newJwt = $descopeSDK->management->jwt->updateJWT($jwt, ["myClaim" => "value"]);

// Impersonate a user (requires the impersonator to have permission)
$impersonatedJwt = $descopeSDK->management->jwt->impersonate("impersonatorId", "targetLoginId", true);

// Impersonate with a step-up JWT (optionally select a tenant / custom claims)
$stepupJwt = $descopeSDK->management->jwt->impersonateStepup(
    "impersonatorId",   // impersonatorId
    "targetLoginId",    // loginId
    false,              // validateConsent
    null,               // customClaims
    "",                 // tenantId
    0                   // refreshDuration (seconds)
);

// Stop an active impersonation, returning a JWT for the original impersonator
$originalJwt = $descopeSDK->management->jwt->stopImpersonation($impersonatedJwt);

// Generate sessions via the management API
$signInResp = $descopeSDK->management->jwt->signIn("testuser1", null); // loginId, loginOptions
$signUpResp = $descopeSDK->management->jwt->signUp(
    "newuser1",                                          // loginId
    ['email' => 'new@example.com'],                      // user (optional)
    ['customClaims' => ['k' => 'v']]                     // signUpOptions (optional)
);
$signUpOrInResp = $descopeSDK->management->jwt->signUpOrIn("user1", null, null);

// Generate an anonymous session
$anon = $descopeSDK->management->jwt->anonymous(null, "", 0); // customClaims, selectedTenant, refreshDuration
```

### Flow & Theme Management Functions

```php
$flows = $descopeSDK->management->flow->listFlows();
$exported = $descopeSDK->management->flow->exportFlow("sign-up-or-in");
$descopeSDK->management->flow->importFlow("sign-up-or-in", $exported["flow"], $exported["screens"] ?? []);
$descopeSDK->management->flow->delete(["old-flow-id"]);

$theme = $descopeSDK->management->flow->exportTheme();
$descopeSDK->management->flow->importTheme($theme["theme"]);

// Delete flows by ID
$descopeSDK->management->flow->deleteFlows(["old-flow-id", "another-flow-id"]);

// Run a management flow synchronously and wait for its output
$result = $descopeSDK->management->flow->runManagementFlow("my-flow-id", [
    "input"   => ["key" => "value"], // input values passed to the flow
    "preview" => false,               // run in preview mode
    "tenant"  => "tenantId1",         // tenant to run the flow for
]);

// Run a management flow asynchronously, then poll for the result
$started = $descopeSDK->management->flow->runManagementFlowAsync("my-flow-id", [
    "input" => ["key" => "value"],
]);
$asyncResult = $descopeSDK->management->flow->getManagementFlowAsyncResult($started["executionId"]);
```

## Password Management

The SDK provides several classes for handling different types of passwords and password hashes. Here's how to use them:

### Cleartext Passwords

For cleartext (plain text) passwords:

```php
use Descope\SDK\Management\Password\UserPassword;

// Create a password with cleartext
$password = new UserPassword(cleartext: "mysecretpassword");

// Use it in user creation
$response = $descopeSDK->management->user->create(
    "user123",                // loginId
    "user@example.com",       // email
    "+1234567890",           // phone
    "John Doe",              // displayName
    "John",                  // givenName
    null,                    // middleName
    "Doe",                   // familyName
    null,                    // picture
    null,                    // customAttributes
    true,                    // verifiedEmail
    true,                    // verifiedPhone
    null,                    // inviteUrl
    null,                    // additionalLoginIds
    null,                    // ssoAppIds
    $password,               // password
    ["user"],               // roleNames
    null                     // userTenants
);
```

### Hashed Passwords

The SDK supports multiple hash types. Here's how to use each:

#### BCrypt

```php
use Descope\SDK\Management\Password\UserPassword;
use Descope\SDK\Management\Password\UserPasswordBcrypt;

// Create a bcrypt hashed password
$hashedPassword = new UserPasswordBcrypt('$2a$12$XlQwF3/7ohdzYrE0LC4A.O');
$password = new UserPassword(null, $hashedPassword);

// Use it in user creation
$response = $descopeSDK->management->user->create(
    "user123",                // loginId
    "user@example.com",       // email
    null,                     // phone
    "John Doe",              // displayName
    null,                    // givenName
    null,                    // middleName
    null,                    // familyName
    null,                    // picture
    null,                    // customAttributes
    true,                    // verifiedEmail
    false,                   // verifiedPhone
    null,                    // inviteUrl
    null,                    // additionalLoginIds
    null,                    // ssoAppIds
    $password,               // password
    ["user"],               // roleNames
    null                     // userTenants
);
```

#### SHA

```php
use Descope\SDK\Management\Password\UserPassword;
use Descope\SDK\Management\Password\UserPasswordSha;

// Create a SHA hashed password
$hashedPassword = new UserPasswordSha(
    '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8',    // hash
    'sha256'                                                               // type
);
$password = new UserPassword(null, $hashedPassword);

// Use it in user creation or password replacement
...
```

#### MD5

```php
use Descope\SDK\Management\Password\UserPassword;
use Descope\SDK\Management\Password\UserPasswordMD5;

// Create an MD5 hashed password
$hashedPassword = new UserPasswordMD5('87f77988ccb5aa917c93201ba314fcd4');
$password = new UserPassword(null, $hashedPassword);

// Use it in user creation or password replacement
...
```

#### PBKDF2

```php
use Descope\SDK\Management\Password\UserPassword;
use Descope\SDK\Management\Password\UserPasswordPbkdf2;

// Create a PBKDF2 hashed password
$hashedPassword = new UserPasswordPbkdf2(
    'hashvalue',    // hash
    'saltvalue',    // salt
    10000,          // iterations
    'sha256'        // variant (sha1, sha256, sha512)
);
$password = new UserPassword(null, $hashedPassword);

// Use it in user creation or password replacement
...
```

#### Django

```php
use Descope\SDK\Management\Password\UserPassword;
use Descope\SDK\Management\Password\UserPasswordDjango;

// Create a Django hashed password
$hashedPassword = new UserPasswordDjango('pbkdf2_sha256$30000$hashvalue');
$password = new UserPassword(null, $hashedPassword);

// Use it in user creation or password replacement
...
```

#### Firebase

```php
use Descope\SDK\Management\Password\UserPassword;
use Descope\SDK\Management\Password\UserPasswordFirebase;

// Create a Firebase hashed password
$hashedPassword = new UserPasswordFirebase(
    'hashvalue',    // hash
    'saltvalue',    // salt
    'saltsep',      // salt separator
    'signerkey',    // signer key
    14,             // memory cost
    8               // rounds
);
$password = new UserPassword(null, $hashedPassword);

// Use it in user creation or password replacement
...
```

### Outbound Apps

The SDK also supports **Outbound Apps** management via `management->outboundApps`. This allows you to fetch and manage user tokens for third-party outbound applications configured in Descope.

#### Fetch outbound app user token

```php
$response = $descopeSDK->management->outboundApps->fetchUserToken(
    'app123',            // appId
    'user123',           // userId
    ['read', 'write'],   // scopes (optional)
    true,                // withRefreshToken (optional)
    false,               // forceRefresh (optional)
    'tenant123'          // tenantId (optional)
);
print_r($response);
```

#### Delete outbound app user tokens (by appId and/or userId)

```php
// Delete by appId
$descopeSDK->management->outboundApps->deleteUserTokens('app123', null);

// Delete by userId
$descopeSDK->management->outboundApps->deleteUserTokens(null, 'user123');

// Delete by both
$descopeSDK->management->outboundApps->deleteUserTokens('app123', 'user123');
```

#### Delete outbound app token by token id

```php
$descopeSDK->management->outboundApps->deleteTokenById('token123');
```

#### Manage outbound applications

```php
// Create an application (see the OutboundApps class for all recognized appRequest keys:
// id, name, description, templateId, clientId, logo, discoveryUrl, authorizationUrl,
// authorizationUrlParams, tokenUrl, tokenUrlParams, revocationUrl, defaultScopes,
// defaultRedirectUrl, callbackDomain, pkce, accessType, prompt, clientSecret)
$response = $descopeSDK->management->outboundApps->createApplication([
    'name'         => 'My Outbound App',
    'clientId'     => 'clientId',
    'clientSecret' => 'clientSecret',
]);
$appId = $response['app']['id'];

// Update an application (clientSecret is optional; omit to keep the existing one)
$descopeSDK->management->outboundApps->updateApplication(
    ['id' => $appId, 'name' => 'My Renamed App'],
    null
);

// Load / load all / delete
$app = $descopeSDK->management->outboundApps->loadApplication($appId);
$all = $descopeSDK->management->outboundApps->loadAllApplications();
$descopeSDK->management->outboundApps->deleteApplication($appId);
```

#### Fetch outbound app tokens

```php
// Latest user token (the "latest" endpoints ignore scopes)
$userToken = $descopeSDK->management->outboundApps->fetchLatestUserToken([
    'appId'  => $appId,
    'userId' => 'user123',
]);

// Tenant tokens (recognized keys: appId, tenantId, scopes, options)
$tenantToken = $descopeSDK->management->outboundApps->fetchTenantToken([
    'appId'    => $appId,
    'tenantId' => 'tenant123',
    'scopes'   => ['read'],
]);
$latestTenantToken = $descopeSDK->management->outboundApps->fetchLatestTenantToken([
    'appId'    => $appId,
    'tenantId' => 'tenant123',
]);

// List apps that have a token for a user
$apps = $descopeSDK->management->outboundApps->listAppsWithUserToken('user123', 'tenant123'); // userId, tenantId
```

#### Upload outbound app tokens / API keys

```php
// Static API keys (apikey-type apps)
$descopeSDK->management->outboundApps->uploadUserApiKey([
    'appId'  => $appId,
    'userId' => 'user123',
    'apiKey' => 'secret-api-key',
]);
$descopeSDK->management->outboundApps->uploadTenantApiKey([
    'appId'    => $appId,
    'tenantId' => 'tenant123',
    'apiKey'   => 'secret-api-key',
]);

// Single OAuth token (recognized keys include appId, userId/tenantId, refreshToken,
// accessToken, accessTokenExpiry, accessTokenType, scopes, externalIdentifier,
// idToken, grantedBy, verifyRefresh)
$descopeSDK->management->outboundApps->uploadUserToken([
    'appId'       => $appId,
    'userId'      => 'user123',
    'accessToken' => 'ya29...',
]);
$descopeSDK->management->outboundApps->uploadTenantToken([
    'appId'       => $appId,
    'tenantId'    => 'tenant123',
    'accessToken' => 'ya29...',
]);

// Batch upload OAuth tokens (returns per-item failures under the 'failures' key)
$descopeSDK->management->outboundApps->batchUploadUserTokens([/* token entries */]);
$descopeSDK->management->outboundApps->batchUploadTenantTokens([/* token entries */]);
```

### Audit Management Functions

```php
// Search the audit trail (supported keys: userIds, actions, excludedActions, devices,
// methods, geos, remoteAddresses, loginIds, tenants, noTenants, text, from, to, limit, page)
$response = $descopeSDK->management->audit->searchAll([
    'actions' => ['LoginSucceed'],
    'limit'   => 100,
    'page'    => 0,
]);
$audits = $response['audits'];
$total = $response['total'];

// Create an audit webhook connector ('name' is required)
$descopeSDK->management->audit->createAuditWebhook([
    'name'    => 'My Audit Webhook',
    'url'     => 'https://example.com/audit',
    'filters' => ['actions' => ['LoginSucceed']],
]);
```

## Unit Testing

The PHP directory includes unit testing using PHPUnit. You can insert values for session token and refresh tokens in the `src/tests/DescopeSDKTest.php` file, and run to validate whether or not the functions are operating properly.

To run the tests, run this command:

```
./vendor/bin/phpunit --bootstrap bootstrap.php --verbose src/tests/DescopeSDKTest.php
```

## Running the PHP Sample App

In the `sample/static/descope.js`, replace the **projectId** with your Descope Project ID, which you can find in the [Descope Console](https://app.descope.com/settings/project).

If you haven't already, make sure you run the composer command listed above, to install the necessary SDK packages.

Then, run this command from the root directory, to start the sample app:

```
php -S localhost:3000 -t sample/
```

The app should now be accessible at http://localhost:3000/ from your web browser.

This sample app showcases a Descope Flow using the WebJS SDK and PHP sessions to retain user information across multiple pages. It also showcases initializing the SDK and using it to validate the session token from formData sent from `login.php`.

## Feedback

### Contributing

We appreciate feedback and contribution to this repository!

### Raise an issue

To provide feedback or report a bug, please [raise an issue on our issue tracker](https://github.com/descope/php-sdk/issues).

This project is licensed under the MIT license. See the <a href="./LICENSE"> LICENSE</a> file for more info.</p>
