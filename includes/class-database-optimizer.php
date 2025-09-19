<?php
/**
 * Database optimization functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class WPO_Database_Optimizer {
    
    public function __construct() {
        add_action('wpo_daily_optimization', array($this, 'run_daily_optimization'));
    }
    
    public function run_daily_optimization() {
        if (!$this->should_run_optimization()) {
            return;
        }
        
        $this->optimize_tables();
        $this->cleanup_transient_options();
        $this->cleanup_post_revisions();
    }
    
    private function should_run_optimization() {
        $settings = WPO()->core->get_settings();
        return !empty($settings['enable_database_optimization']);
    }
    
    public function optimize_tables() {
        global $wpdb;
        
        $tables = $wpdb->get_results("SHOW TABLES LIKE '{$wpdb->prefix}%'", ARRAY_N);
        
        foreach ($tables as $table) {
            $table = $table[0];
            $wpdb->query("OPTIMIZE TABLE $table");
        }
    }
    
    public function cleanup_transient_options() {
        global $wpdb;
        
        $wpdb->query(
            "DELETE FROM $wpdb->options 
             WHERE option_name LIKE '%\_transient\_%' 
             OR option_name LIKE '%\_site\_transient\_%'"
        );
    }
    
    public function cleanup_post_revisions() {
        global $wpdb;
        
        // Keep only the latest 5 revisions for each post
        $wpdb->query(
            "DELETE FROM $wpdb->posts 
             WHERE post_type = 'revision' 
             AND ID NOT IN (
                 SELECT * FROM (
                     SELECT ID FROM $wpdb->posts 
                     WHERE post_type = 'revision' 
                     ORDER BY post_date DESC 
                     LIMIT 5
                 ) AS temp
             )"
        );
    }
    
    public function get_database_size() {
        global $wpdb;
        
        $size = 0;
        $tables = $wpdb->get_results("SHOW TABLE STATUS LIKE '{$wpdb->prefix}%'", ARRAY_A);
        
        foreach ($tables as $table) {
            $size += $table['Data_length'] + $table['Index_length'];
        }
        
        return size_format($size, 2);
    }
}