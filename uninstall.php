<?php

// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

$option_name = 'xklid101_wprecords';

delete_option($option_name);

// for site options in Multisite
delete_site_option($option_name);

// drop a custom database table
global $wpdb;
$tbls = $wpdb->get_col("SHOW TABLES LIKE '{$wpdb->prefix}xklid101_wprecords%'");
if ($tbls) {
    foreach ($tbls as $tbl) {
        $wpdb->query("DROP TABLE IF EXISTS {$tbl}");
    }
}
