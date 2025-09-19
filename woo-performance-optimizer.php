<?php
/**
 * Plugin Name: Woo Performance Optimizer Lite
 * Plugin URI: https://wordpress.org/plugins/woo-performance-optimizer/
 * Description: Optimize your WooCommerce store for maximum performance and speed. Lite version with essential optimization features.
 * Version: 1.0.0
 * Author: Jeff Devs
 * Author URI: https://jeffmuturi.netlify.app
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: woo-performance-optimizer
 * Domain Path: /languages
 * WC requires at least: 3.0
 * WC tested up to: 7.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WPO_VERSION', '1.0.0');
define('WPO_PLUGIN_FILE', __FILE__);
define('WPO_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WPO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WPO_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('WPO_IS_PRO', false);

/**
 * Main plugin class
 */
final class Woo_Performance_Optimizer {
    
    private static $instance = null;
    
    public $core;
    public $database_optimizer;
    public $query_optimizer;
    public $cache_manager;
    public $performance_monitor;
    public $admin;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->includes();
        $this->init_hooks();
    }
    
    private function includes() {
        // Core functionality
        require_once WPO_PLUGIN_PATH . 'includes/class-core.php';
        require_once WPO_PLUGIN_PATH . 'includes/class-database-optimizer.php';
        require_once WPO_PLUGIN_PATH . 'includes/class-query-optimizer.php';
        require_once WPO_PLUGIN_PATH . 'includes/class-cache-manager.php';
        require_once WPO_PLUGIN_PATH . 'includes/class-performance-monitor.php';
        
        // Admin functionality
        if (is_admin()) {
            require_once WPO_PLUGIN_PATH . 'admin/class-admin.php';
        }
        
        // Lite version restrictions and upgrade notices
        require_once WPO_PLUGIN_PATH . 'includes/lite/class-lite-limits.php';
        require_once WPO_PLUGIN_PATH . 'includes/lite/class-upgrade-notice.php';
    }
    
    private function init_hooks() {
        register_activation_hook(WPO_PLUGIN_FILE, array($this, 'activate'));
        register_deactivation_hook(WPO_PLUGIN_FILE, array($this, 'deactivate'));
        
        add_action('plugins_loaded', array($this, 'init_plugin'));
        add_action('init', array($this, 'load_textdomain'));
    }
    
    public function init_plugin() {
        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            return;
        }
        
        // Initialize core components
        $this->core = new WPO_Core();
        $this->database_optimizer = new WPO_Database_Optimizer();
        $this->query_optimizer = new WPO_Query_Optimizer();
        $this->cache_manager = new WPO_Cache_Manager();
        $this->performance_monitor = new WPO_Performance_Monitor();
        
        // Initialize admin
        if (is_admin()) {
            $this->admin = new WPO_Admin();
        }
        
        // Initialize Lite restrictions and upgrade notices
        $this->lite_limits = new WPO_Lite_Limits();
        $this->upgrade_notice = new WPO_Upgrade_Notice();
        
        do_action('wpo_loaded');
    }
    
    public function load_textdomain() {
        load_plugin_textdomain('woo-performance-optimizer', false, dirname(plugin_basename(WPO_PLUGIN_FILE)) . '/languages');
    }
    
    public function activate() {
        // Set default options
        $defaults = array(
            'enable_query_optimization' => true,
            'enable_caching' => true,
            'optimization_schedule' => 'weekly',
            'keep_logs_days' => 7
        );
        
        if (!get_option('wpo_settings')) {
            update_option('wpo_settings', $defaults);
        }
        
        // Schedule cron jobs
        if (!wp_next_scheduled('wpo_daily_optimization')) {
            wp_schedule_event(time(), 'daily', 'wpo_daily_optimization');
        }
        
        // Create required database tables
        $this->create_tables();
    }
    
    public function deactivate() {
        // Clear scheduled events
        wp_clear_scheduled_hook('wpo_daily_optimization');
        
        // Clear any cached data
        $this->cache_manager->clear_all_caches();
    }
    
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'wpo_performance_logs';
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            timestamp datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            page_url varchar(255) DEFAULT '' NOT NULL,
            load_time float NOT NULL,
            query_count int(11) NOT NULL,
            memory_usage int(11) NOT NULL,
            PRIMARY KEY (id),
            KEY timestamp (timestamp)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    public function woocommerce_missing_notice() {
        echo '<div class="error"><p>';
        printf(
            __('Woo Performance Optimizer requires WooCommerce to be installed and active. You can download %s here.', 'woo-performance-optimizer'),
            '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>'
        );
        echo '</p></div>';
    }
}

/**
 * Main instance of plugin
 */
function WPO() {
    return Woo_Performance_Optimizer::instance();
}

// Initialize the plugin
WPO();