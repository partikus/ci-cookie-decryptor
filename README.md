# CodeIgniter Cookie Decryptor

[![Build Status](https://travis-ci.com/partikus/ci-cookie-decryptor.svg?token=WPAKr8mvUbkxW4NXsPf6&branch=master)](https://travis-ci.com/partikus/ci-cookie-decryptor)

This is a CodeIgniter Session Decryptor app. The app was written to connect modern frameworks' session solutions with legacy CodeIgniter Cookie session.
As an author of the app, I did my best to provide full compatible API.
But if you find any problems do not hesitate to submit an issue.

The app can be launched under PHP 5.6 or 7.0.

## Supported CI versions

* 2.x - https://github.com/bcit-ci/CodeIgniter/tree/2.2.6

## API docs

Cause the app is REST API styled, you can browse the API documentation [here.](https://petstore.swagger.io/?url=https://raw.githubusercontent.com/partikus/ci-cookie-decryptor/master/src/docs.yml)

## Release plan:
* 1.0.0 - simple cookie encoding & decoding
* 1.1.0 - support for all attributes like domain matching, last activity etc. new options must be optional
* 1.2.0 - support for php 7.1+

## Setup

```bash
# install php5.6
php5.6 composer.phar install
php5.6 -S localhost:8080 -t public/
```

## Tests

```bash
php5.6 vendor/bin/phpunit
```
