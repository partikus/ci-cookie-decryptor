# CI Cookie Decryptor

This is a CodeIgniter Session Decryptor microservice. The app was written to connect modern frameworks' session solutions with legacy CodeIgniter Cookie session.
As an author of the app I did my best to provide full compatibile API.
But if you find any problems do not hesitate to submit an issue.

Release plan:
* 1.0.0 - simple cookie encoding & decoding
* 1.1.0 - support for all attributes like domain matching, last activity etc. new options must be optional

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
