<?php
/*
Plugin Name: Gravity Forms Admin Popup
Description: Render a Gravity Form fully functional inside a WordPress admin popup
Version: 1.3
Text Domain: gravityforms-admin-popup
Author: Fahad Murtaza aka iSuperCoder
Plugin URI: https://isupercoder.com/contact
Author URI: https://isupercoder.com/contact
*/

if (!defined('ABSPATH')) exit;

class GF_Admin_ThickBox_Debug {

    const OPTION = 'gf_admin_popup_form_id';

    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_page']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function register_settings() {
        register_setting('gf_admin_popup', self::OPTION, [
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
            'default'           => 1,
        ]);
    }

    /**
     * Form rendered in the popup. Filterable so a site can pick it per user/context.
     */
    public function get_form_id() {
        return (int) apply_filters('gf_admin_popup_form_id', (int) get_option(self::OPTION, 1));
    }

    public function add_admin_page() {
        add_menu_page(
            'GF Admin Popup Debug',
            'GF Popup Debug',
            'manage_options',
            'gf-admin-popup-debug',
            [$this, 'render_page'],
            'dashicons-feedback',
            100
        );
    }

    public function enqueue_scripts($hook) {
        if ($hook !== 'toplevel_page_gf-admin-popup-debug') return;

        // Load ThickBox (built into WP)
        add_thickbox();

        // Enqueue jQuery
        wp_enqueue_script('jquery');

        // Enqueue scripts for the selected form only (File Upload, Signature, etc.)
        if (class_exists('GFForms') && class_exists('GFAPI') && class_exists('GFFormDisplay')) {
            wp_enqueue_script('gform_gravityforms');
            $form = GFAPI::get_form($this->get_form_id());
            if ($form && !is_wp_error($form)) {
                GFFormDisplay::enqueue_form_scripts($form);
            }
        }
    }

    public function render_page() {
        $form_id = $this->get_form_id();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Gravity Form in Admin Popup', 'gravityforms-admin-popup'); ?></h1>

            <form method="post" action="options.php" style="margin-bottom:20px;">
                <?php settings_fields('gf_admin_popup'); ?>
                <label for="<?php echo esc_attr(self::OPTION); ?>"><?php esc_html_e('Form ID to show in the popup', 'gravityforms-admin-popup'); ?></label>
                <input type="number" min="1" class="small-text" id="<?php echo esc_attr(self::OPTION); ?>" name="<?php echo esc_attr(self::OPTION); ?>" value="<?php echo esc_attr($form_id); ?>" />
                <?php submit_button(__('Save', 'gravityforms-admin-popup'), 'secondary', 'submit', false); ?>
            </form>

            <a href="#TB_inline?width=900&height=800&inlineId=gf-form-popup" 
            class="thickbox button button-primary" id="open-gf-popup">
            Open Form
            </a>


            <div id="gf-form-popup" style="display:none;">
                <?php
                if (!class_exists('GFForms') || !class_exists('GFFormDisplay')) {
                    echo '<p>' . esc_html__('Gravity Forms is not active.', 'gravityforms-admin-popup') . '</p>';
                } else {
                    $form_html = GFFormDisplay::get_form($form_id, true, true, true, null, true);
                    if (!$form_html) {
                        echo '<p style="color:red;">' . esc_html(sprintf(__('Form %d could not be rendered. Check the Form ID above.', 'gravityforms-admin-popup'), $form_id)) . '</p>';
                    } else {
                        echo $form_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- GF-rendered markup
                    }
                }
                ?>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($){
            $('#open-gf-popup').on('click', function(e){
                // TB_show is provided by add_thickbox()
                try {
                    TB_show("Gravity Form", $(this).attr('href'), false);
                } catch(err) {
                    console.error("Error opening ThickBox:", err);
                }
            });

            // Scroll to top of modal when form renders (pagination, error, submission)
            $(document).bind('gform_post_render', function() {
                var $modalWindow = $('#TB_window');
                if ($modalWindow.length) {
                    $modalWindow.scrollTop(0);
                }
            });
        });
        </script>
        <?php
    }
}

new GF_Admin_ThickBox_Debug();

add_action('admin_head', function() {
    ?>
    <style>
        /* Reset ThickBox Content Container to allow full scrolling */
        #TB_ajaxContent {
            width: 100% !important;
            height: auto !important;
            overflow: visible !important;
            padding: 20px 30px !important;
            box-sizing: border-box;
        }

        /* Modernize ThickBox Window */
        #TB_window {
            /* Position top/bottom fixed to create full height modal */
            top: 30px !important;
            bottom: 30px !important;
            margin-top: 0 !important; /* Remove legacy centering */
            height: auto !important; /* Allow height to flex */
            
            /* Width & Horizontal Centering */
            width: 80% !important;
            max-width: 800px !important; /* User requested limit */
            
            /* Better Centering Strategy */
            left: 50% !important;
            margin-left: 0 !important; 
            transform: translateX(-50%) !important;
            
            /* Single Scrollbar for the whole window */
            overflow-y: auto !important;
            -webkit-overflow-scrolling: touch;
            
            /* Visual Cleanup */
            border-radius: 4px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.5);
        }

        /* Ensure Editor/Content uses full width */
        #TB_window .mceContentBody {
            max-width: 100% !important;
        }

    </style>
    <?php
});
