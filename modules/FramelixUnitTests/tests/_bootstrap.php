<?php

use Framelix\Framelix\Config;

include __DIR__ . "/../public/index.php";

// override the default database in case if something goes wrong and the tests would run on the default db rather than the test db
Config::set('database[default]', null);
// disable system event log, it should be tested explicitely
Config::set('systemEventLog', null);
// check if env exist, if so, we trying to get database config from that config
if (getenv("DB_CONNECTION") === "mysql") {
    Config::set('database[test]', [
        "host" => getenv("DB_HOST"),
        "username" => getenv("DB_USERNAME"),
        "password" => getenv("DB_PASSWORD"),
        "database" => getenv("DB_DATABASE"),
        "port" => getenv("DB_PORT"),
        "socket" => ""
    ]);
}