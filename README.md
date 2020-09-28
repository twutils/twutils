<p align="center"><img style="display: inline;" width="80px" src="resources/images/twutils3.png" alt="TwUtils"></p>

# TwUtils: Twitter Utilities

Twitter Utilities (TwUtils) is a set of utilities for managing your twitter account.. Built with Laravel, VueJs.

## Local Development: Prerequisites

### 1. Create Twitter Application

TwUtils uses two different Twitter applications, One for Read-Only operations, One for Read-Write operations.

Read-Only operations includes: User Data, Backup Tweets/Likes/Followings/Followers.

Read-Write operations includes: Delete Tweets, Delete Likes.

Head over to [Twitter Developers](https://developer.twitter.com/en/apps) to create two Twitter applications.

After creating the twitter application, you can set it's Permissions (Read-Only, Read-Write,..) from the **Permissions** tab.

For each application, In the **Keys and tokens** tab, you will have **Consumer API keys** for each permission, which is two keys:

1. API key
2. API secret key

This keys will be used later to authenticate users and to be used as an access point for TwUtils.

Consumer API Keys will be used in the `.env` file, [4. Updating Environment Variables (Twitter OAuth)](#4-updating-environment-variables-twitter-oauth).

> Note: Do not forget to **"Enable Sign in with Twitter"** while creating the applications.

- Tutorial: [Create Twitter Developer Account & App](https://medium.com/@divyeshardeshana/create-twitter-developer-account-app-4ac55e945bf4)

### 2. Laravel-Ready Environment

This application is built on top of Laravel Framework v7, It assumes `composer` is installed and `php` has the needed extensions. For details please refer to [Laravel 7 Installation documentation](https://laravel.com/docs/7.x#installation).


## Local Development: Setup

### 1. Cloning and Installing Dependencies

``` bash
git clone https://github.com/MohannadNaj/twutils/ twutils

cd twutils

composer install

npm install
```

### 2. Preparing Laravel App

```
cp .env.example .env

php artisan key:generate

php artisan storage:link
```

### 3. Update Environment Variables (Database)

Update the following environment variables in your `.env` file to match your database.

```

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=twutils
DB_USERNAME=
DB_PASSWORD=
```

### 4. Updating Environment Variables (Twitter OAuth)

Here you will set the **Consumer API Keys** you created when you created the twitter application.


For Read-Only Application, Set the following keys in your `.env` file:

```
# API key
TWITTER_CLIENT_ID="XXXX"

# API secret key
TWITTER_CLIENT_SECRET="XXXX"
```

For Read-Write Application, Set the following keys in your `.env` file:
```
TWITTER_READ_WRITE_CLIENT_ID="XXXX"
TWITTER_READ_WRITE_CLIENT_SECRET="XXXX"
```

### 5. Update Environment Variables (Twitter Callbacks URLs)

In the Twitter Developers portal, Under the **App details** tab or upon creation of a new Twitter Application, it asks you to set callbacks URLs.

This URLs are where Twitter users will be redirected after they grant the permission for your application to access their Twitter account.


```
APP_PORT="8000"
TWITTER_REDIRECT="http://localhost:%s/login/twitter/callback"
TWITTER_REDIRECT_READ_WRITE="http://localhost:%s/login/twitter/rw/callback"
```

### 6. Test it!

```
php artisan serve
```

Visit:
http://localhost:8000/



## Security

If you discover any security related issues, please email mohannadnaj@me.com instead of using the issue tracker.

## Credits

- [Mohannad Najjar](https://github.com/mohannadnaj)
- [All Contributors](../../contributors)

## Running Tests

- Backend:

``` bash
vendor/bin/phpunit
```

- Frontend:

``` bash
composer testData # to generate dummy data from the backend
npm test
```
