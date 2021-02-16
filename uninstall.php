<?php

use Xklid101\Wprecords\Services\Config;
use Xklid101\Wprecords\Services\Database;

// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

require_once __DIR__ . '/autoloader.php';

$option_name = Config::CONFIG_PARAM_NAME;

delete_option($option_name);

// for site options in Multisite
// delete_site_option($option_name);

// drop a custom database table
global $wpdb;
$tbls = $wpdb->get_col("SHOW TABLES LIKE '{$wpdb->prefix}" . Database::TBL_PREFIX . "%'");
if ($tbls) {
    foreach ($tbls as $tbl) {
        $wpdb->query("DROP TABLE IF EXISTS `{$tbl}`");
    }
}
