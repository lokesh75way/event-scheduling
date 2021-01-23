#### CakePHP Application 

## Pre-requisite
1. Minimum PHP version should be 5.X or above
2. Composer

## Installation
1. Download [Composer](https://getcomposer.org/doc/00-intro.md) or update `composer self-update`.
2. Execute the following command to move the composer.phar to a directory that is in your path
Run `mv composer.phar /usr/local/bin/composer`

## Configuration
1. You might need to enable `(intl, mbstring)` below variables (mentioned in requirements.php also). You can directly add the below commands in "php.ini" to enable these extensions : 
    - extension=php_intl.dll
    - extension=php_mbstring.dll
2. You might need to set the path. If so, follow the below link is helpful
    - https://gist.github.com/irazasyed/5987693

## Create project
If Composer is installed globally, run

```bash
composer create-project --prefer-dist "cakephp/app:~3.9 api"
```

In case you want to use a custom app dir name (e.g. `/myapp/`):

```bash
composer create-project --prefer-dist "cakephp/app:^3.9 myapp"
```

## Run migrations
1. Make sure the migrations plugin is present. If not, the use below command to run it :
- Run `bin/cake plugin load Migrations`
2. Run the migrations
- Run `bin/cake migrations migrate`
3. In case you want to rollback the migrations
- Run `bin/cake migrations rollback`

## Run Seed to create default users
1. To run all seed files
    - Run `bin/cake migrations seed`
2. To run particular seed file (say UserSeeder.php file)
    - Run `bin/cake migrations seed --seed UserSeeder`

## Run project
You can now either use your machine's webserver to view the default home page, or start
up the built-in webserver with:

```bash
bin/cake server -p 8765
```

Then visit `http://localhost:8765` to see the welcome page.

## Test API
- You can use `POSTMAN` to test the API.
- Postman collection json is added at the project root directory `carevision.postman_collection.json` which you can download and `import` to your postman
- Description for API are mentioned in the POSTMAN 

## Run Unit tests with below command
- Run `vendor/bin/phpunit`

## Assumptions to ambiguity or missed requirement(s)
- `frequency` for `weekly` : if `end_date_time` is not provided then API will return next `1 year` of event instances from the given `start_date_time` by default
- `frequency` for `monthly` : if `end_date_time` is not provided then API will return next `1 year` of event instances from the given `start_date_time` by default
- `frequency` should be in string and one out of the following : `["once_off", "weekly", "monthly"]`
- `duration` should be in integer : default set to `60` minutes if not provided to schedule the event instances
- `start_date_time` and all dates should be in `YYYY-MM-DD HH:MM` format
- `invitees` should be in `array` : `[1,2,3]` format for `POST` API
- `invitees` should be in `string` : `"1,2,3"` format for `GET` API

## TODO
- duration should not cause two event instance to overlap.

