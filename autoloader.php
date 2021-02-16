<?php

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
    trigger_error('Plugin "xklid101 - WP Forms records" error: ' . $e->getMessage(), E_USER_WARNING);
}
