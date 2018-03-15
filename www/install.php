<?php

date_default_timezone_set('UTC');

// Version that will be installed
define('INSTALL_VERSION', '1.2.1');

if (! defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

// include the setup
if (file_exists('inc' . DS . 'inc.php')) {
    require_once('inc' . DS . 'inc.php');
} else {
    die("Fatal Error: System includes file not found!");
}

// Go!
if (file_exists(INSTALLER_DIR . DS . 'bootstrap.php')) {
    require_once(INSTALLER_DIR . DS . 'bootstrap.php');
} else {
    die("Fatal Error: System bootstrap file not found!");
}
