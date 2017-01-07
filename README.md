# Ride Totals
A simple web application that uses the Uber API to retrieve your rides and create a stats summary. It uses [Slim Framework](http://www.slimframework.com/), [Eloquent ORM](https://github.com/illuminate/database) and [php-oauth-client](https://github.com/fkooman/php-oauth-client).

## Install
To install it just dump `schema.sql` in your database, and update the database details and your Uber API credentials in `app/bootstrap.php`.

## ToDo
* Update stats automatically
* Implement a template engine
* Multilanguage support
* Remove any remaining logic in views
* Process stats in the background