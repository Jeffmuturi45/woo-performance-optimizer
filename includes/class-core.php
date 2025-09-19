<?php
/**
 * Core functionality for Woo Performance Optimizer
 */

if (!defined('ABSPATH')) {
    exit;
}

class WPO_Core {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    public function init() {
        // Initialize core functionality
    }
    
    public function get_settings() {
        return wp_parse_args(
            get_option('wpo_settings', array()),
            array(
                'enable_query_optimization' => true,
                'enable_caching' => true,
                'optimization_schedule' => 'weekly',
                'keep_logs_days' => 7
            )
        );
    }
    
    public function update_settings($new_settings) {
        $current_settings = $this->get_settings();
        $updated_settings = wp_parse_args($new_settings, $current_settings);
        return update_option('wpo_settings', $updated_settings);
    }
}