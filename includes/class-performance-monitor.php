<?php
/**
 * Performance monitoring functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class WPO_Performance_Monitor {
    
    private $start_time;
    private $query_count;
    
    public function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    public function init() {
        $settings = WPO()->core->get_settings();
        
        if (!empty($settings['enable_performance_monitoring'])) {
            $this->start_monitoring();
        }
    }
    
    public function start_monitoring() {
        $this->start_time = microtime(true);
        $this->query_count = 0;
        
        add_action('shutdown', array($this, 'log_performance'), 0);
        add_filter('query', array($this, 'count_queries'));
    }
    
    public function count_queries($query) {
        if ($query && !str_starts_with($query, '#')) {
            $this->query_count++;
        }
        return $query;
    }
    
    public function log_performance() {
        global $wpdb;
        
        $load_time = microtime(true) - $this->start_time;
        $memory_usage = memory_get_peak_usage(true);
        
        // Only log if page took more than 1 second to load
        if ($load_time < 1) {
            return;
        }
        
        $log_data = array(
            'timestamp' => current_time('mysql'),
            'page_url' => $this->get_current_url(),
            'load_time' => $load_time,
            'query_count' => $this->query_count,
            'memory_usage' => $memory_usage
        );
        
        $this->save_log($log_data);
        $this->cleanup_old_logs();
    }
    
    private function get_current_url() {
        $protocol = is_ssl() ? 'https://' : 'http://';
        return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }
    
    private function save_log($log_data) {
        global $wpdb;
        
        $wpdb->insert(
            $wpdb->prefix . 'wpo_performance_logs',
            $log_data,
            array('%s', '%s', '%f', '%d', '%d')
        );
    }
    
    private function cleanup_old_logs() {
        global $wpdb;
        
        $settings = WPO()->core->get_settings();
        $keep_days = !empty($settings['keep_logs_days']) ? $settings['keep_logs_days'] : 7;
        
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->prefix}wpo_performance_logs 
                 WHERE timestamp < DATE_SUB(NOW(), INTERVAL %d DAY)",
                $keep_days
            )
        );
    }
    
    public function get_performance_stats() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wpo_performance_logs';
        
        $stats = $wpdb->get_results(
            "SELECT 
                AVG(load_time) as avg_load_time,
                MAX(load_time) as max_load_time,
                MIN(load_time) as min_load_time,
                AVG(query_count) as avg_query_count,
                COUNT(*) as total_logs
             FROM $table_name
             WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
        );
        
        return $stats ? $stats[0] : false;
    }
}