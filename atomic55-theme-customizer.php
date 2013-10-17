<?php
/*
Plugin Name: Atomic55 Theme Customizer
Description: This plugin help you build customiser for your Wordpress theme.
Author: Alfi Rizka - Atomic55 Developer
Author URI: http://atomic55.net/?utm_source=wordpress-plugin&utm_medium=themecustomizer&utm_term=help&utm_campaign=wordpress+plugin
Version: 0.1
Network: true
Text Domain: atomic55
*/

if(defined('ATOMIC_i18n') === FALSE) {
    define('ATOMIC_i18n', 'atomic55');
}

// register autoloader
spl_autoload_register('_a55_autoload_register');

function _a55_autoload_register($className) {
    $filename = strtolower(str_replace('Atomic55_', '', $className));
    $filepath = dirname(__FILE__) . '/Atomic55/' . $filename . '.php';
    if (file_exists($filepath)) {
        require_once $filepath;
        return true;
    }
    return false;
}

// Plugin initialize
Atomic55_Plugin::initialize(__FILE__);
