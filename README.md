# Symfony API

## Configuration
[JWT](https://github.com/lexik/LexikJWTAuthenticationBundle/blob/master/Resources/doc/index.md#generate-the-ssh-keys)

## Available routes

* api_auth_login     POST     /api/auth/login
---
* customers_list     GET      /api/customers
* customers_create   POST     /api/customers
* customers_read     GET      /api/customers/{customerId}
* customers_update   PUT      /api/customers/{customerId}
* customers_delete   DELETE   /api/customers/{customerId}
---
* users_create       POST     /api/users
* users_read         GET      /api/users/{userId}
* users_update       PUT      /api/users/{userId}
* users_delete       DELETE   /api/users/{userId}
---
## Deploy
Have a look at `./deploy.php`

Define `$_ENV` variables in file `./env.local`

## Technologies
- PHP
- Symfony

## Contribute to the project

Symfony API isn't an open source project.

## Authors

Our code squad : Charly & Benjamin
