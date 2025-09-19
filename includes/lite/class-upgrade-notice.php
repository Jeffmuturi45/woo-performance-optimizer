<?php
/**
 * Upgrade notices for Lite version
 */

if (!defined('ABSPATH')) {
    exit;
}

class WPO_Upgrade_Notice {
    
    public function __construct() {
        add_action('admin_notices', array($this, 'show_upgrade_notices'));
        add_filter('plugin_action_links_' . WPO_PLUGIN_BASENAME, array($this, 'add_upgrade_link'));
        add_action('wpo_settings_after', array($this, 'show_pro_features_table'));
    }
    
    public function show_upgrade_notices() {
        // Only show on our settings page
        $screen = get_current_screen();
        if (strpos($screen->id, 'woo-performance-optimizer') === false) {
            return;
        }
        
        echo '<div class="notice notice-info"><p>';
        printf(
            __('Unlock advanced performance features with <a href="%s" target="_blank" style="font-weight: bold;">WPO Pro</a>!', 'woo-performance-optimizer'),
            'https://yourwebsite.com/woo-performance-optimizer-pro/'
        );
        echo '</p></div>';
    }
    
    public function add_upgrade_link($links) {
        $links[] = '<a href="https://yourwebsite.com/woo-performance-optimizer-pro/" target="_blank" style="color: #00a32a; font-weight: bold;">' . __('Upgrade to Pro', 'woo-performance-optimizer') . '</a>';
        return $links;
    }
    
    public function show_pro_features_table() {
        ?>
        <div class="wpo-pro-features">
            <h3><?php _e('Upgrade to Pro for Advanced Features', 'woo-performance-optimizer'); ?></h3>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Feature', 'woo-performance-optimizer'); ?></th>
                        <th><?php _e('Lite', 'woo-performance-optimizer'); ?></th>
                        <th><?php _e('Pro', 'woo-performance-optimizer'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php _e('Daily Optimization', 'woo-performance-optimizer'); ?></td>
                        <td><span class="dashicons dashicons-no-alt" style="color: #dc3232;"></span></td>
                        <td><span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span></td>
                    </tr>
                    <tr>
                        <td><?php _e('Image Optimization', 'woo-performance-optimizer'); ?></td>
                        <td><span class="dashicons dashicons-no-alt" style="color: #dc3232;"></span></td>
                        <td><span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span></td>
                    </tr>
                    <tr>
                        <td><?php _e('CDN Integration', 'woo-performance-optimizer'); ?></td>
                        <td><span class="dashicons dashicons-no-alt" style="color: #dc3232;"></span></td>
                        <td><span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span></td>
                    </tr>
                    <tr>
                        <td><?php _e('Advanced Caching', 'woo-performance-optimizer'); ?></td>
                        <td><span class="dashicons dashicons-no-alt" style="color: #dc3232;"></span></td>
                        <td><span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span></td>
                    </tr>
                    <tr>
                        <td><?php _e('Priority Support', 'woo-performance-optimizer'); ?></td>
                        <td><span class="dashicons dashicons-no-alt" style="color: #dc3232;"></span></td>
                        <td><span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span></td>
                    </tr>
                </tbody>
            </table>
            <p style="text-align: center; margin-top: 15px;">
                <a href="https://yourwebsite.com/woo-performance-optimizer-pro/" class="button button-primary" target="_blank">
                    <?php _e('Upgrade to Pro Now', 'woo-performance-optimizer'); ?>
                </a>
            </p>
        </div>
        <?php
    }
}