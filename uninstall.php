<?php
/**
 * Uninstall Woo Performance Optimizer
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options
delete_option('wpo_settings');

// Drop custom database tables
global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wpo_performance_logs");

// Clear scheduled events
wp_clear_scheduled_hook('wpo_daily_optimization');