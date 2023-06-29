# <a title="Descope PHP SDK" href="https://www.php.net/"><img width="64" alt="php logo" src="https://upload.wikimedia.org/wikipedia/commons/2/27/PHP-logo.svg"></a> by Descope

[![License](https://img.shields.io/:license-MIT-blue.svg?style=flat)](https://opensource.org/licenses/MIT)

## Overview

The Descope SDK for PHP provides convenient access to Descope authentication. You can read more on the [Descope Website](https://descope.com).

## Getting started

### Requirements

- [PHP 8.1+](https://www.php.net/)

### Installation

Install the package with `Composer`:

```
composer require descope/descope-php
```

You'll need to set up a `.env` file in the root directory with your Descope Project ID, which you can get from the [Console](https://app.descope.com/settings/project) like this:

```
DESCOPE_PROJECT_ID=<Descope Project ID>
```

## Using the SDK

In order to use the SDK you will need to initialize a `DescopeSDK` object with your Descope Project ID you defined in your `.env` file, like this:

```
require 'vendor/autoload.php';
use Descope\SDK\DescopeSDK;

$descopeSDK = new DescopeSDK([
    'projectId' => $_ENV['DESCOPE_PROJECT_ID']
]);
```

This SDK will easily allow you to handle Descope JWT tokens with the following built in functions:

1. `DescopeSDK->verify($sessionToken)` - will validate the JWT signature and return either **TRUE** or **FALSE**, depending on if the JWT is valid and expired
2. `DescopeSDK->getClaims($sessionToken)` - will return all of the claims from the JWT in an array format
3. `DescopeSDK->getUserDetails($refreshToken)` - will return all of the user information (email, phone, verification status, etc.) using a provided refresh token

> **Note**: To use verify() and getClaims(), you will need to pass in your session token into the function argument. To use getUserDetails() to will need to pass in your refresh token.

## Unit Testing

The PHP directory includes unit testing using PHPUnit. You can insert values for session token and refresh tokens in the `src/tests/DescopeSDKTest.php` file, and run to validate whether or not the functions are operating properly.

To run the tests, run this command:

```
./vendor/bin/phpunit --verbose src/tests/DescopeSDKTest.php
```

## Running the PHP Sample App

Run this command, from the root directory, to install the necessary dependencies and start the sample app:

```
php -S localhost:3000 -t sample/
```

The app should now be accessible at http://localhost:3000/ from your web browser.

This sample app showcases a Descope Flow using the WebJS SDK and PHP sessions to retain user information across multiple pages. It also showcases initializing the SDK and using it to validate the session token from formData sent from `login.php`.

## Other Code Samples

1. [WordPress Plugin](https://github.com/descope-sample-apps/wordpress-plugin)

## Feedback

### Contributing

We appreciate feedback and contribution to this repository!

### Raise an issue

To provide feedback or report a bug, please [raise an issue on our issue tracker](https://github.com/descope/php-sdk/issues).

This project is licensed under the MIT license. See the <a href="./LICENSE"> LICENSE</a> file for more info.</p>
