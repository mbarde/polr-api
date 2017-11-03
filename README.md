Polr API
========

A Restful API for the Polr URL Shortener

Features
--------

This package gives access to all Polr features through a Restful API, excepted the following.

- User creation and deletion.
- Password change.
- Link redirection.

Installation
------------

Add the Github repository and package to the `composer.json` file of your Polr installation, and run `composer update`.

```json
{
    "repositories": [
        {
            "type": "git",
            "url": "https://github.com/lagdo/polr-api"
        }
    ],
    "require": {
        "lagdo/polr-api": "dev-master"
    }
}
```

Register the service provider in the `bootstrap/app.php`.

```php
$app->register(\Lagdo\Polr\Api\PolrApiServiceProvider::class);
```

Documentation
-------------

The API endpoints are [documented here](docs/api). The documentation is generated with [API Doc](http://apidocjs.com/).

Known issues
------------

