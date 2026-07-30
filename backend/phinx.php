<?php
require_once __DIR__ . "/vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__."/../");
$dotenv->load();

return
[
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/db/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/db/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'development',
        'production' => [
            'adapter' => $_ENV["DB_CONNECTION"],
            'host' => $_ENV["DB_HOST"],
            'name' => $_ENV["DB_NAME"],
            'user' => $_ENV["DB_USER"],
            'pass' => $_ENV["DB_PASSWORD"],
            'port' => $_ENV["DB_PORT"],
            'charset' => $_ENV["DB_CHARSET"],
        ],
        'development' => [
            'adapter' => $_ENV["DB_CONNECTION"],
            'host' => $_ENV["DB_HOST"],
            'name' => $_ENV["DB_NAME"],
            'user' => $_ENV["DB_USER"],
            'pass' => $_ENV["DB_PASSWORD"],
            'port' => $_ENV["DB_PORT"],
            'charset' => $_ENV["DB_CHARSET"],
        ],
        'testing' => [
            'adapter' => $_ENV["DB_CONNECTION"],
            'host' => $_ENV["DB_HOST"],
            'name' => $_ENV["DB_NAME"],
            'user' => $_ENV["DB_USER"],
            'pass' => $_ENV["DB_PASSWORD"],
            'port' => $_ENV["DB_PORT"],
            'charset' => $_ENV["DB_CHARSET"],
        ]
    ],
    'version_order' => 'creation'
];
