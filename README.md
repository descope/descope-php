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
DESCOPE_PROJECT_ID="<Descope Project ID>"
```

This SDK will easily allow you to handle Descope JWT tokens with the following built in functions:

1. `DescopeSDK->verify()` - will validate the JWT signature and return either **TRUE** or **FALSE**, depending on if the JWT is valid and not expired
2. `DescopeSDK->getClaims()` - will return all of the claims from the JWT in an array format

## Unit Testing

The PHP directory includes unit testing, that uses PHPUnit

## Other Code Samples

1. [PHP Sample App]()
2. [WordPress Plugin](https://github.com/descope-sample-apps/wordpress-plugin)

## Running the PHP Sample App

Run this command to install the necessary dependencies and start the sample app:

```
php -S localhost:3000 -t sample/
```

The app should now be accessible at http://localhost:3000/ from your web browser.

## Feedback

### Contributing

We appreciate feedback and contribution to this repository!

### Raise an issue

To provide feedback or report a bug, please [raise an issue on our issue tracker](https://github.com/descope/passport-descope/issues).

This project is licensed under the MIT license. See the <a href="./LICENSE"> LICENSE</a> file for more info.</p>
