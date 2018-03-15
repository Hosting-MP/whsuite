<?php

use \Illuminate\Database\Capsule\Manager as Capsule;
use \Symfony\Component\Filesystem\Filesystem;
use \Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use \Symfony\Component\Finder\Finder;
use \Naneau\SemVer\Parser;
use \Naneau\SemVer\Compare;
use \Whsuite\Migrations\Migrations;

/**
* Installer Base Controller
*
* The installer base controller handles any global install/upgrade functions
* such as file system checks, php extension checks, etc.
*
* @package  WHSuite-Controllers
* @author  WHSuite Dev Team <info@whsuite.com>
* @copyright  Copyright (c) 2015, Turn 24 Ltd.
* @license http://whsuite.com/license/ The WHSuite License Agreement
* @link http://whsuite.com
* @since  Version 1.0
*/
class InstallerController extends AppController
{
    public $admin_auth;
    public $filesystem;
    public $finder;
    public $new_version;
    public $parser;
    public $compare;
    public $migrations;
    public $min_php_version = '5.6.34';
    public $min_recommended_php_version = '7.0;

    public $required_extension = array();
    public $recommended_extensions = array();

    public $missing_required_extensions = false;

    public function onLoad()
    {
        // Set client theme
        App::get('view')->setTheme('installer');

        $this->view->set('title', 'WHSuite Installer');

        $this->filesystem = new Filesystem();
        $this->finder = new Finder();

        if (!defined('INSTALL_VERSION')) {
            $this->view->set('error', 'Unable to determin the version of WHSuite to install. Please re-download the WHSuite install files and try again, or contact support for assistance.');
        } else {
            $this->new_version = INSTALL_VERSION;
            $this->view->set('new_version', $this->new_version);
        }

        $this->parser = new Parser();
        $this->compare = new Compare();
        $this->migrations = new Migrations();

        $this->required_extensions = array(
            'mbstring',
            'fileinfo',
            'curl',
            'mcrypt',
            'PDO',
            'pdo_mysql',
            'SimpleXML'
        );

        $this->recommended_extensions = array(
            'mailparse' => 'We recommend that you install the Mailparse extension to take advantage of email piping via our Support Desk addon.'
        );

        // Set up admin authentication
        App::add('admin_auth', \App\Libraries\AdminAuth::auth()); // Load admin auth library
        $this->admin_auth = &App::get('admin_auth'); // Set the auth library into an easier to use variable
    }

    public function _checkPhpExtensions()
    {
        $missing_extensions = array();

        foreach ($this->required_extensions as $extension) {
            if (! extension_loaded($extension)) {
                $missing_extensions[] = array(
                    'extension' => $extension,
                    'type' => 'required'
                );

                $this->missing_required_extensions = true;
            }
        }

        foreach ($this->recommended_extensions as $extension => $message) {
            if (! extension_loaded($extension)) {
                $missing_extensions[] = array(
                    'extension' => $extension,
                    'type' => 'recommended',
                    'message' => $message
                );
            }
        }

        return $missing_extensions;
    }
}
