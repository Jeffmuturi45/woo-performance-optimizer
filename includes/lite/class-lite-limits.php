<?php
/**
 * Lite version limitations
 */

if (!defined('ABSPATH')) {
    exit;
}

class WPO_Lite_Limits {
    
    public function __construct() {
        // Limit optimization frequency
        add_filter('wpo_optimization_schedule', array($this, 'limit_schedule'));
        
        // Limit historical data retention
        add_filter('wpo_keep_logs_days', array($this, 'limit_log_retention'));
        
        // Disable pro features in UI
        add_action('admin_init', array($this, 'disable_pro_features'));
    }
    
    public function limit_schedule($schedule) {
        // Lite version only allows weekly optimization
        return 'weekly';
    }
    
    public function limit_log_retention($days) {
        // Lite version only keeps 7 days of logs
        return min($days, 7);
    }
    
    public function disable_pro_features() {
        // Remove pro features from UI
        remove_action('wpo_settings_tabs', 'wpo_pro_settings_tab');
        
        // Limit database optimization options
        add_filter('wpo_database_optimization_options', array($this, 'limit_database_options'));
    }
    
    public function limit_database_options($options) {
        // Remove advanced optimization options in Lite version
        unset($options['optimize_images']);
        unset($options['lazy_load_images']);
        unset($options['cdn_integration']);
        
        return $options;
    }
}