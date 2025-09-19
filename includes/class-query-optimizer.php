<?php
/**
 * Query optimization functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class WPO_Query_Optimizer {
    
    public function __construct() {
        add_action('pre_get_posts', array($this, 'optimize_queries'));
        add_action('wp', array($this, 'disable_unused_features'));
    }
    
    public function optimize_queries($query) {
        if (is_admin() || !$query->is_main_query()) {
            return;
        }
        
        $settings = WPO()->core->get_settings();
        
        if (empty($settings['enable_query_optimization'])) {
            return;
        }
        
        // Optimize WooCommerce product queries
        if ($this->is_woocommerce_query($query)) {
            $this->optimize_product_queries($query);
        }
        
        // Limit posts per page for archive pages
        if ($query->is_archive() || $query->is_search()) {
            $query->set('posts_per_page', 12);
        }
    }
    
    private function is_woocommerce_query($query) {
        return function_exists('is_woocommerce') && 
               (is_shop() || is_product_category() || is_product_tag() || is_product());
    }
    
    private function optimize_product_queries($query) {
        // Only select necessary fields
        $query->set('fields', 'ids');
        
        // Prevent expensive meta queries
        remove_action('pre_get_posts', 'wc_prevent_adjacent_posts');
    }
    
    public function disable_unused_features() {
        if (is_admin()) {
            return;
        }
        
        $settings = WPO()->core->get_settings();
        
        if (empty($settings['enable_query_optimization'])) {
            return;
        }
        
        // Disable emojis
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');
        
        // Disable embeds
        remove_action('wp_head', 'wp_oembed_add_discovery_links');
        remove_action('wp_head', 'wp_oembed_add_host_js');
        
        // Disable XML-RPC
        add_filter('xmlrpc_enabled', '__return_false');
        
        // Remove RSD link
        remove_action('wp_head', 'rsd_link');
        
        // Remove WLW manifest link
        remove_action('wp_head', 'wlwmanifest_link');
    }
}