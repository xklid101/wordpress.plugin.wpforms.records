<?php
/**
 * Plugin Name: xklid101 - WP Forms records
 * Version: 0.0.1
 * Description: Extends WP Forms by saving submitted forms with possibility to administer these records
 * License: MIT
 * License URI: http://opensource.org/licenses/MIT
 * Author: xklid101
 * Requires at least: 5.6
 * Tested up to: 5.6
 * Requires PHP: 7.4
 */

use Xklid101\Wprecords\Loader;
use Xklid101\Wprecords\Di\Container;

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
    return;
}

/**
 * The full path to the main file of this plugin
 *
 * This can later be passed to functions such as
 * plugin_dir_path(), plugins_url() and plugin_basename()
 * to retrieve information about plugin paths
 */
define( 'XKLID101_WPRECORDS_FILE', __FILE__ );

/**
 * Enable autoloading of plugin classes
 * @param $className
 */
function xklid101WpRecordsAutoload($className) {

    $namespace = "Xklid101\\Wprecords";
    /* Only autoload classes from this plugin */
    if (strpos($className, $namespace) !== 0) {
        return;
    }

    $subpath = trim(
        str_replace(
            "\\",
            DIRECTORY_SEPARATOR,
            str_replace($namespace, '', $className)
        ),
        DIRECTORY_SEPARATOR
    );

    /* Load the class */
    require_once
        __DIR__
        . DIRECTORY_SEPARATOR
        . 'src'
        . DIRECTORY_SEPARATOR
        . $subpath
        . '.php';
}

try {
    spl_autoload_register('xklid101WpRecordsAutoload');
} catch (Exception $e) {
    new WP_Error($e->getCode(), $e->getMessage());
}

/**
 * Retrieve the instance of the main plugin class
 *
 * @return xklid101WprecordsLoader
 */
function xklid101WprecordsLoader() {
    static $plugin;

    if (is_null($plugin) ) {
        $plugin = new Loader(
            new Container(),
            __DIR__ . '/src'
        );
    }

    return $plugin;
}

xklid101WprecordsLoader()->loadPlugin();
