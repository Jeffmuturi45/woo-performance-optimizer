<?php
/**
 * Cache management functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class WPO_Cache_Manager {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('save_post', array($this, 'clear_post_cache'));
        add_action('woocommerce_update_product', array($this, 'clear_product_cache'));
    }
    
    public function init() {
        $settings = WPO()->core->get_settings();
        
        if (!empty($settings['enable_caching'])) {
            $this->enable_browser_caching();
            $this->enable_object_caching();
        }
    }
    
    public function enable_browser_caching() {
        if (!headers_sent()) {
            @header('Cache-Control: public, max-age=86400');
            @header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT');
        }
    }
    
    public function enable_object_caching() {
        if (!wp_using_ext_object_cache()) {
            // Enable transients for database caching
            add_filter('pre_cache_transient', array($this, 'cache_transient'), 10, 2);
        }
    }
    
    public function cache_transient($value, $transient) {
        // Cache transients for faster retrieval
        $cached = wp_cache_get($transient, 'transient');
        
        if (false !== $cached) {
            return $cached;
        }
        
        wp_cache_set($transient, $value, 'transient', 3600);
        return $value;
    }
    
    public function clear_post_cache($post_id) {
        if (wp_is_post_revision($post_id)) {
            return;
        }
        
        clean_post_cache($post_id);
        
        // Clear related transients
        $this->clear_transients();
    }
    
    public function clear_product_cache($product_id) {
        if (function_exists('wc_delete_product_transients')) {
            wc_delete_product_transients($product_id);
        }
        
        $this->clear_transients();
    }
    
    public function clear_transients() {
        global $wpdb;
        
        $wpdb->query(
            "DELETE FROM $wpdb->options 
             WHERE option_name LIKE '%\_transient\_%' 
             OR option_name LIKE '%\_site\_transient\_%'"
        );
        
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
    }
    
    public function clear_all_caches() {
        $this->clear_transients();
        
        // Clear WordPress cache
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
        
        // Clear WooCommerce cache
        if (function_exists('wc_delete_product_transients')) {
            wc_delete_product_transients();
        }
    }
}