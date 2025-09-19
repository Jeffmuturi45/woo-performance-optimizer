<?php
/**
 * Admin functionality for Woo Performance Optimizer
 */

if (!defined('ABSPATH')) {
    exit;
}

class WPO_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'settings_init'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __('Woo Performance Optimizer', 'woo-performance-optimizer'),
            __('Performance', 'woo-performance-optimizer'),
            'manage_options',
            'woo-performance-optimizer',
            array($this, 'options_page')
        );
    }
    
    public function settings_init() {
        register_setting('wpo_settings', 'wpo_settings');
        
        // General settings section
        add_settings_section(
            'wpo_general_section',
            __('General Settings', 'woo-performance-optimizer'),
            array($this, 'general_section_callback'),
            'wpo_settings'
        );
        
        add_settings_field(
            'enable_query_optimization',
            __('Enable Query Optimization', 'woo-performance-optimizer'),
            array($this, 'checkbox_field_callback'),
            'wpo_settings',
            'wpo_general_section',
            array(
                'label_for' => 'enable_query_optimization',
                'description' => __('Optimize database queries for better performance', 'woo-performance-optimizer')
            )
        );
        
        add_settings_field(
            'enable_caching',
            __('Enable Caching', 'woo-performance-optimizer'),
            array($this, 'checkbox_field_callback'),
            'wpo_settings',
            'wpo_general_section',
            array(
                'label_for' => 'enable_caching',
                'description' => __('Enable browser and object caching', 'woo-performance-optimizer')
            )
        );
        
        // Optimization settings section
        add_settings_section(
            'wpo_optimization_section',
            __('Optimization Settings', 'woo-performance-optimizer'),
            array($this, 'optimization_section_callback'),
            'wpo_settings'
        );
        
        add_settings_field(
            'optimization_schedule',
            __('Optimization Schedule', 'woo-performance-optimizer'),
            array($this, 'select_field_callback'),
            'wpo_settings',
            'wpo_optimization_section',
            array(
                'label_for' => 'optimization_schedule',
                'options' => array(
                    'daily' => __('Daily', 'woo-performance-optimizer'),
                    'weekly' => __('Weekly', 'woo-performance-optimizer'),
                    'monthly' => __('Monthly', 'woo-performance-optimizer')
                ),
                'description' => __('How often to run optimization tasks', 'woo-performance-optimizer')
            )
        );
        
        // Monitoring settings section
        add_settings_section(
            'wpo_monitoring_section',
            __('Performance Monitoring', 'woo-performance-optimizer'),
            array($this, 'monitoring_section_callback'),
            'wpo_settings'
        );
        
        add_settings_field(
            'enable_performance_monitoring',
            __('Enable Monitoring', 'woo-performance-optimizer'),
            array($this, 'checkbox_field_callback'),
            'wpo_settings',
            'wpo_monitoring_section',
            array(
                'label_for' => 'enable_performance_monitoring',
                'description' => __('Monitor and log site performance', 'woo-performance-optimizer')
            )
        );
        
        add_settings_field(
            'keep_logs_days',
            __('Keep Logs For', 'woo-performance-optimizer'),
            array($this, 'number_field_callback'),
            'wpo_settings',
            'wpo_monitoring_section',
            array(
                'label_for' => 'keep_logs_days',
                'min' => 1,
                'max' => 30,
                'suffix' => __('days', 'woo-performance-optimizer'),
                'description' => __('How long to keep performance logs', 'woo-performance-optimizer')
            )
        );
    }
    
    public function general_section_callback() {
        echo '<p>' . __('General performance optimization settings', 'woo-performance-optimizer') . '</p>';
    }
    
    public function optimization_section_callback() {
        echo '<p>' . __('Database optimization settings', 'woo-performance-optimizer') . '</p>';
    }
    
    public function monitoring_section_callback() {
        echo '<p>' . __('Performance monitoring settings', 'woo-performance-optimizer') . '</p>';
    }
    
    public function checkbox_field_callback($args) {
        $settings = WPO()->core->get_settings();
        $value = isset($settings[$args['label_for']]) ? $settings[$args['label_for']] : false;
        ?>
        <input type="checkbox" id="<?php echo esc_attr($args['label_for']); ?>" name="wpo_settings[<?php echo esc_attr($args['label_for']); ?>]" value="1" <?php checked(1, $value); ?>>
        <?php if (!empty($args['description'])): ?>
            <p class="description"><?php echo esc_html($args['description']); ?></p>
        <?php endif;
    }
    
    public function select_field_callback($args) {
        $settings = WPO()->core->get_settings();
        $value = isset($settings[$args['label_for']]) ? $settings[$args['label_for']] : '';
        ?>
        <select id="<?php echo esc_attr($args['label_for']); ?>" name="wpo_settings[<?php echo esc_attr($args['label_for']); ?>]">
            <?php foreach ($args['options'] as $key => $label): ?>
                <option value="<?php echo esc_attr($key); ?>" <?php selected($value, $key); ?>>
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($args['description'])): ?>
            <p class="description"><?php echo esc_html($args['description']); ?></p>
        <?php endif;
    }
    
    public function number_field_callback($args) {
        $settings = WPO()->core->get_settings();
        $value = isset($settings[$args['label_for']]) ? $settings[$args['label_for']] : '';
        ?>
        <input type="number" id="<?php echo esc_attr($args['label_for']); ?>" name="wpo_settings[<?php echo esc_attr($args['label_for']); ?>]" value="<?php echo esc_attr($value); ?>" min="<?php echo esc_attr($args['min']); ?>" max="<?php echo esc_attr($args['max']); ?>" style="width: 80px;">
        <?php if (!empty($args['suffix'])): ?>
            <span><?php echo esc_html($args['suffix']); ?></span>
        <?php endif; ?>
        <?php if (!empty($args['description'])): ?>
            <p class="description"><?php echo esc_html($args['description']); ?></p>
        <?php endif;
    }
    
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'woo-performance-optimizer') === false) {
            return;
        }
        
        wp_enqueue_style('wpo-admin', WPO_PLUGIN_URL . 'admin/css/admin.css', array(), WPO_VERSION);
        wp_enqueue_script('wpo-admin', WPO_PLUGIN_URL . 'admin/js/admin.js', array('jquery'), WPO_VERSION, true);
    }
    
    public function options_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Save settings if form was submitted
        if (isset($_POST['submit'])) {
            check_admin_referer('wpo_settings');
            
            $settings = isset($_POST['wpo_settings']) ? $_POST['wpo_settings'] : array();
            WPO()->core->update_settings($settings);
            
            echo '<div class="notice notice-success"><p>' . __('Settings saved.', 'woo-performance-optimizer') . '</p></div>';
        }
        
        $settings = WPO()->core->get_settings();
        $database_size = WPO()->database_optimizer->get_database_size();
        $performance_stats = WPO()->performance_monitor->get_performance_stats();
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="wpo-dashboard">
                <div class="wpo-stats">
                    <div class="wpo-stat-card">
                        <h3><?php _e('Database Size', 'woo-performance-optimizer'); ?></h3>
                        <p class="stat-value"><?php echo esc_html($database_size); ?></p>
                    </div>
                    
                    <?php if ($performance_stats): ?>
                    <div class="wpo-stat-card">
                        <h3><?php _e('Avg. Load Time', 'woo-performance-optimizer'); ?></h3>
                        <p class="stat-value"><?php echo number_format($performance_stats->avg_load_time, 2); ?>s</p>
                    </div>
                    
                    <div class="wpo-stat-card">
                        <h3><?php _e('Avg. Queries', 'woo-performance-optimizer'); ?></h3>
                        <p class="stat-value"><?php echo number_format($performance_stats->avg_query_count, 0); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
                
                <form action="" method="post">
                    <?php wp_nonce_field('wpo_settings'); ?>
                    
                    <div class="wpo-settings">
                        <?php settings_fields('wpo_settings'); ?>
                        <?php do_settings_sections('wpo_settings'); ?>
                    </div>
                    
                    <?php submit_button(__('Save Settings', 'woo-performance-optimizer')); ?>
                </form>
                
                <div class="wpo-actions">
                    <h2><?php _e('Quick Actions', 'woo-performance-optimizer'); ?></h2>
                    <p>
                        <button type="button" class="button button-secondary" id="wpo-clear-cache">
                            <?php _e('Clear All Caches', 'woo-performance-optimizer'); ?>
                        </button>
                        <span class="description"><?php _e('Clear transients and object cache', 'woo-performance-optimizer'); ?></span>
                    </p>
                    <p>
                        <button type="button" class="button button-secondary" id="wpo-optimize-now">
                            <?php _e('Run Optimization Now', 'woo-performance-optimizer'); ?>
                        </button>
                        <span class="description"><?php _e('Run database optimization immediately', 'woo-performance-optimizer'); ?></span>
                    </p>
                </div>
            </div>
        </div>
        <?php
    }
}