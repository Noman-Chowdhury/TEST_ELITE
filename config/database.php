<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DATABASE_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => false,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
        ],

        'sales_assistant' => [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST_SALES_ASSISTANT'),
            'port'      => env('DB_PORT_SALES_ASSISTANT'),
            'database'  => env('DB_DATABASE_SALES_ASSISTANT'),
            'username'  => env('DB_USERNAME_SALES_ASSISTANT'),
            'password'  => env('DB_PASSWORD_SALES_ASSISTANT'),
            'charset' => 'utf8mb4',
        ],

        'poultry_pro' => [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST_POULTRY_PRO'),
            'port'      => env('DB_PORT_POULTRY_PRO'),
            'database'  => env('DB_DATABASE_POULTRY_PRO'),
            'username'  => env('DB_USERNAME_POULTRY_PRO'),
            'password'  => env('DB_PASSWORD_POULTRY_PRO'),
            'charset' => 'utf8mb4',
        ],

        'aci_cattle' => [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST_ACI_CATTLE'),
            'port'      => env('DB_PORT_ACI_CATTLE'),
            'database'  => env('DB_DATABASE_ACI_CATTLE'),
            'username'  => env('DB_USERNAME_ACI_CATTLE'),
            'password'  => env('DB_PASSWORD_ACI_CATTLE'),
            'charset' => 'utf8mb4',
        ],

        'tamim_main' => [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST_TAMIM_MAIN'),
            'port'      => env('DB_PORT_TAMIM_MAIN'),
            'database'  => env('DB_DATABASE_TAMIM_MAIN'),
            'username'  => env('DB_USERNAME_TAMIM_MAIN'),
            'password'  => env('DB_PASSWORD_TAMIM_MAIN'),
            'charset' => 'utf8mb4',
        ],

        'tamim_electropac' => [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST_TAMIM_ELECTROPAC'),
            'port'      => env('DB_PORT_TAMIM_ELECTROPAC'),
            'database'  => env('DB_DATABASE_TAMIM_ELECTROPAC'),
            'username'  => env('DB_USERNAME_TAMIM_ELECTROPAC'),
            'password'  => env('DB_PASSWORD_TAMIM_ELECTROPAC'),
            'charset' => 'utf8mb4',
        ],

        'elite' => [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST_ELITE'),
            'port'      => env('DB_PORT_ELITE'),
            'database'  => env('DB_DATABASE_ELITE'),
            'username'  => env('DB_USERNAME_ELITE'),
            'password'  => env('DB_PASSWORD_ELITE'),
            'charset' => 'utf8mb4',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],

    ],

];
