<?php

if (! defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

define('SYS_DIR', dirname(__FILE__));
define('APP_DIR', SYS_DIR . DS . '..' . DS . 'app');
define('ADDON_DIR', APP_DIR . DS . 'addons');
define('STORAGE_DIR', APP_DIR . DS . 'storage');
define('VENDOR_DIR', SYS_DIR . DS . '..' . DS . 'vendor');

// -------------------------------------------------------------------
// load registry
// -------------------------------------------------------------------
if (file_exists(SYS_DIR . DS . 'app.php')) {

    require_once(SYS_DIR . DS . 'app.php');
} else {

    die("Fatal Error: System app file not found!");
}

// -------------------------------------------------------------------
// load composer
// -------------------------------------------------------------------
if (file_exists(VENDOR_DIR . DS . 'autoload.php')) {

    require_once(VENDOR_DIR . DS . 'autoload.php');
} else {

    die("Fatal Error: Composer autoload file not found!");
}

// -------------------------------------------------------------------
// setup error reporting
// -------------------------------------------------------------------
if (file_exists(SYS_DIR . DS . '../..' . DS . 'inc/inc.php')) {

    require_once(SYS_DIR . DS . '../..' . DS . 'inc/inc.php');
} else {

    die("Fatal Error: System inc file not found!");
}

if (defined('DEV_MODE') && DEV_MODE) {

    error_reporting(E_ALL);
} else {

    error_reporting(0);
}

// -------------------------------------------------------------------
// start the system registry
// register the system files
// -------------------------------------------------------------------
App::start();

// -------------------------------------------------------------------
// start logger so we can log anything that goes wrong
// -------------------------------------------------------------------
$log_file = STORAGE_DIR . DS . 'logs' . DS . 'logfile-' . strtoupper(date('dMY')) . '.log';

App::factory('\Monolog\Logger', 'log')->pushHandler(
    new \Monolog\Handler\StreamHandler($log_file, \Monolog\Logger::WARNING)
);

// -------------------------------------------------------------------
// start session handler
// -------------------------------------------------------------------
App::factory('\Core\Session');

// -------------------------------------------------------------------
// register the configs
// -------------------------------------------------------------------
App::factory('\Core\Configs', APP_DIR . DS . 'configs');

// -------------------------------------------------------------------
// start up hook system
// -------------------------------------------------------------------
App::factory('\Core\Hooks');

// -------------------------------------------------------------------
// register addons configs / routes (if any exist)
// -------------------------------------------------------------------
App::registerAddons();

// -------------------------------------------------------------------
// load db config / init db/orm
// -------------------------------------------------------------------
$db_config = App::get('configs')->get('database.mysql');

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;
$capsule->addConnection(array(
    'driver'    => 'mysql',
    'host'      => $db_config['host'],
    'database'  => $db_config['name'],
    'username'  => $db_config['user'],
    'password'  => $db_config['pass'],
    'charset'   => 'utf8',
    'collation' => 'utf8_general_ci',
    'prefix'    => $db_config['prefix']
));

// Setup the Eloquent ORM...
$capsule->bootEloquent();
$capsule->setAsGlobal();

// -------------------------------------------------------------------
// register models (main and addons)
// -------------------------------------------------------------------
App::get('autoloader')->registerModels();

// -------------------------------------------------------------------
// start router
// -------------------------------------------------------------------
App::factory('\Core\Router')->loadRoutes();

// -------------------------------------------------------------------
// load app bootstrap
// -------------------------------------------------------------------
if (file_exists(APP_DIR . DS . 'bootstrap.php')) {

    require_once(APP_DIR . DS . 'bootstrap.php');
}
