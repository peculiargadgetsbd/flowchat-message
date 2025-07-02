<?php
/*
Plugin Name: Flowchat Messenger & WhatsApp Buttons
Plugin URI: https://github.com/peculiargadgetsbd/flowchat-message/
Description: Floating Messenger, WhatsApp, and Call buttons with advanced customization
Version: 1.2
Author: Nabil Amin Hridoy
Author URI: https://facebook.com/nabilaminhridoy/
Text Domain: flowchat-buttons
*/

if (!defined('ABSPATH')) exit;

class Flowchat_Buttons_Plugin {

    public function __construct() {
        add_action('wp_footer', [$this, 'render_frontend_buttons']);
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_styles']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_styles']);
    }

    // Frontend buttons display
    public function render_frontend_buttons() {
        $options = get_option('flowchat_settings');
        $button_types = ['call', 'messenger', 'whatsapp'];
        ?>
        <div class="flowchat-buttons-container">
            <?php foreach ($button_types as $type) : ?>
                <?php if ($options[$type.'_enable'] ?? false) : ?>
                    <?php
                    $show_text = $options[$type.'_text'] ?? true;
                    $gradient_start = $options[$type.'_color_start'] ?? ($type === 'messenger' ? '#006AFF' : '#25D366');
                    $gradient_end = $options[$type.'_color_end'] ?? ($type === 'messenger' ? '#0084FF' : '#128C7E');
                    $text_label = $options[$type.'_text_label'] ?? ucfirst($type);
                    ?>
                    <div class="flowchat-btn-wrapper">
                        <?php if ($show_text) : ?>
                            <span class="flowchat-btn-label"><?php echo esc_html($text_label); ?></span>
                        <?php endif; ?>
                        <a href="<?php echo esc_url($options[$type.'_link'] ?? '#'); ?>" 
                           class="flowchat-btn <?php echo $type; ?>-btn"
                           style="background: linear-gradient(135deg, <?php echo $gradient_start; ?>, <?php echo $gradient_end; ?>);">
                            <i class="<?php echo $this->get_icon_class($type); ?>"></i>
                        </a>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php
    }

    // Get icon class based on button type
    private function get_icon_class($type) {
        $icons = [
            'call' => 'fas fa-phone-alt',
            'messenger' => 'fab fa-facebook-messenger',
            'whatsapp' => 'fab fa-whatsapp'
        ];
        return $icons[$type] ?? 'fas fa-question-circle';
    }

    // Admin menu setup
    public function add_admin_menu() {
        add_menu_page(
            'Flowchat Message Settings',
            'Flowchat Message',
            'manage_options',
            'flowchat-settings',
            [$this, 'render_admin_page'],
            'dashicons-format-chat',
            80
        );
    }

    // Settings registration
    public function register_settings() {
        register_setting('flowchat_settings_group', 'flowchat_settings');

        add_settings_section('flowchat_main_section', 'Button Settings', null, 'flowchat-settings');

        // Enable/disable fields
        $fields = [
            'call_enable' => 'Enable Call Button',
            'messenger_enable' => 'Enable Messenger Button',
            'whatsapp_enable' => 'Enable WhatsApp Button'
        ];

        foreach ($fields as $id => $label) {
            add_settings_field(
                $id,
                $label,
                [$this, 'render_checkbox_field'],
                'flowchat-settings',
                'flowchat_main_section',
                ['id' => $id]
            );
        }

        // Link and text fields
        $link_fields = [
            'call_link' => 'Call Link (tel:+123...)',
            'messenger_link' => 'Messenger Link',
            'whatsapp_link' => 'WhatsApp Link',
            'call_text_label' => 'Call Button Text',
            'messenger_text_label' => 'Messenger Button Text',
            'whatsapp_text_label' => 'WhatsApp Button Text'
        ];

        foreach ($link_fields as $id => $label) {
            add_settings_field(
                $id,
                $label,
                [$this, 'render_text_field'],
                'flowchat-settings',
                'flowchat_main_section',
                ['id' => $id]
            );
        }

        // Text toggle and position fields
        $button_types = ['call', 'messenger', 'whatsapp'];
        foreach ($button_types as $type) {
            // Text toggle
            add_settings_field(
                $type.'_text',
                'Show Text ('.ucfirst($type).')',
                [$this, 'render_toggle_field'],
                'flowchat-settings',
                'flowchat_main_section',
                ['id' => $type.'_text']
            );
            
            // Gradient colors
            add_settings_field(
                $type.'_color_start',
                'Gradient Start ('.ucfirst($type).')',
                [$this, 'render_color_field'],
                'flowchat-settings',
                'flowchat_main_section',
                ['id' => $type.'_color_start']
            );
            
            add_settings_field(
                $type.'_color_end',
                'Gradient End ('.ucfirst($type).')',
                [$this, 'render_color_field'],
                'flowchat-settings',
                'flowchat_main_section',
                ['id' => $type.'_color_end']
            );
        }
    }

    // Field renderers
    public function render_checkbox_field($args) {
        $options = get_option('flowchat_settings');
        echo '<input type="checkbox" name="flowchat_settings['.$args['id'].']" value="1" '.checked(1, $options[$args['id']] ?? false, false).'>';
    }

    public function render_text_field($args) {
        $options = get_option('flowchat_settings');
        echo '<input type="text" class="regular-text" name="flowchat_settings['.$args['id'].']" value="'.esc_attr($options[$args['id']] ?? '').'">';
    }

    public function render_toggle_field($args) {
        $options = get_option('flowchat_settings');
        echo '<label class="switch"><input type="checkbox" name="flowchat_settings['.$args['id'].']" value="1" '.checked(1, $options[$args['id']] ?? true, false).'><span class="slider"></span></label>';
    }
    
    public function render_color_field($args) {
        $options = get_option('flowchat_settings');
        $value = $options[$args['id']] ?? '';
        echo '<input type="text" class="color-picker" name="flowchat_settings['.$args['id'].']" value="'.esc_attr($value).'">';
    }

    // Admin page
    public function render_admin_page() {
        ?>
        <div class="wrap flowchat-admin">
            <h1>Flowchat Message Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('flowchat_settings_group');
                do_settings_sections('flowchat-settings');
                submit_button('Save Settings', 'primary', 'submit', true);
                ?>
            </form>
        </div>
        <?php
    }

    // Frontend styles
    public function enqueue_styles() {
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css');
        wp_enqueue_style('flowchat-frontend', plugin_dir_url(__FILE__) . 'frontend.css');
    }

    // Admin styles
    public function enqueue_admin_styles($hook) {
        if ('toplevel_page_flowchat-settings' !== $hook) return;
        
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_style('flowchat-admin', plugin_dir_url(__FILE__) . 'admin.css');
        
        wp_enqueue_script('flowchat-admin-js', plugin_dir_url(__FILE__) . 'admin.js', ['wp-color-picker'], false, true);
    }
}

new Flowchat_Buttons_Plugin();