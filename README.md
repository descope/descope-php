# <a title="Descope PHP SDK" href="https://www.php.net/"><img width="64" alt="php logo" src="https://upload.wikimedia.org/wikipedia/commons/2/27/PHP-logo.svg"></a> by Descope

[![License](https://img.shields.io/:license-MIT-blue.svg?style=flat)](https://opensource.org/licenses/MIT)

## Overview

The Descope SDK for PHP provides convenient access to Descope authentication. You can read more on the [Descope Website](https://descope.com).

## Getting started

### Requirements

- [PHP 7.4+](https://www.php.net/)

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
    'debug' => false // Optional, enables verbose error logging (default: false)
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

### Session Management

1. `DescopeSDK->verify($sessionToken)` - will validate the session token and return either **TRUE** or **FALSE**, depending on if the JWT is valid and expired.
2. `DescopeSDK->refreshSession($refreshToken)` - will refresh your session and return a new session token, with the refresh token.
3. `DescopeSDK->verifyAndRefreshSession($sessionToken, $refreshToken)` - will validate the session token and return either **TRUE** or **FALSE**, and will refresh your session and return a new session token.
4. `DescopeSDK->logout($refreshToken)` - will invalidate the refresh token and log the user out of the current session.
5. `DescopeSDK->logoutAll($refreshToken)` - will invalidate all refresh tokens associated with a given project, thereby signing out of all sessions across multiple applications.

---

6. `DescopeSDK->getClaims($sessionToken)` - will return all of the claims from the JWT in an array format.
7. `DescopeSDK->getUserDetails($refreshToken)` - will return all of the user information (email, phone, verification status, etc.) using a provided refresh token.

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
    ]
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

#### Set Password

```php
$descopeSDK->management->user->setPassword("testuser1", new UserPassword(cleartext: "password123"), true);
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

## Outbound Apps Management

Outbound Apps allow users to authenticate with third-party services through Descope. These functions manage OAuth tokens for outbound applications.

### Fetch User Token

Retrieve an access token for a user to interact with a third-party outbound application:

```php
$response = $descopeSDK->management->outboundApps->fetchUserToken(
    'google',                // appId - the outbound application ID
    'user123',               // userId - the Descope user ID
    ['email', 'profile'],    // scopes - requested OAuth scopes (optional)
    true,                    // withRefreshToken - include refresh token (optional, default: false)
    false,                   // forceRefresh - force token refresh (optional, default: false)
    'tenant123'              // tenantId - for multi-tenant apps (optional)
);

// Access the token data
$accessToken = $response['token']['accessToken'];
$scopes = $response['token']['scopes'];
$expiry = $response['token']['accessTokenExpiry'];
```

### Delete User Tokens

Delete outbound application tokens by app ID and/or user ID:

```php
// Delete all tokens for a specific app
$descopeSDK->management->outboundApps->deleteUserTokens('google', null);

// Delete all tokens for a specific user
$descopeSDK->management->outboundApps->deleteUserTokens(null, 'user123');

// Delete tokens for a specific app and user combination
$descopeSDK->management->outboundApps->deleteUserTokens('google', 'user123');
```

### Delete Token By ID

Delete a specific outbound application token by its unique ID:

```php
$descopeSDK->management->outboundApps->deleteTokenById('token_abc123');
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
