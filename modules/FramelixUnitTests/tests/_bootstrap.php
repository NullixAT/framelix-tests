<?php

use Framelix\Framelix\Config;

include __DIR__ . "/../public/index.php";

// override the default database in case if something goes wrong and the tests would run on the default db rather than the test db
Config::set('database[default]', null);
// disable system event log, it should be tested explicitely
Config::set('systemEventLog', null);
// use a fixed timezone
ini_set("date.timezone", "Europe/Berlin");
// disable time limit
ini_set("max_execution_time", -1);