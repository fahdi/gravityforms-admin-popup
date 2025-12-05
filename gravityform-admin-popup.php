<?php
/*
Plugin Name: Gravity Forms Admin Popup Debug
Description: Render a Gravity Form fully functional inside a WordPress admin popup (ThickBox) with debug.
Version: 1.2
Author: Fahad Murtaza aka iSuperCoder
Plugin URI: https://isupercoder.com/contact
Author URI: https://isupercoder.com/contact
*/

if (!defined('ABSPATH')) exit;

class GF_Admin_ThickBox_Debug {

    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_page']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
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
    }

    public function render_page() {
        $form_id = 1; // your GF ID
        ?>
        <div class="wrap">
            <h1>Gravity Form in Admin Popup (ThickBox Debug)</h1>

            <a href="#TB_inline?width=700&height=700&inlineId=gf-form-popup" 
               class="thickbox button button-primary" id="open-gf-popup">
               Open Form
            </a>

            <div id="gf-form-popup" style="display:none;">
                <?php
                if (!class_exists('GFForms') || !class_exists('GFFormDisplay')) {
                    echo '<p>Gravity Forms not active!</p>';
                    echo '<script>console.error("GF classes not found");</script>';
                } else {
                    echo '<script>console.log("GF classes loaded");</script>';
                    GFForms::enqueue_form_scripts($form_id, true);
                    $form_html = GFFormDisplay::get_form($form_id, true, true, true);
                    if (!$form_html) {
                        echo '<p style="color:red;">Form HTML is empty!</p>';
                        echo '<script>console.error("GFFormDisplay::get_form returned empty");</script>';
                    } else {
                        echo $form_html;
                        echo '<script>console.log("Form HTML rendered");</script>';
                    }
                }
                ?>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($){
            console.log("GF Admin ThickBox Debug loaded");

            $('#open-gf-popup').on('click', function(e){
                console.log("Open Form clicked");
                // TB_show is provided by add_thickbox()
                try {
                    TB_show("Gravity Form", $(this).attr('href'), false);
                    console.log("ThickBox TB_show called");
                } catch(err) {
                    console.error("Error opening ThickBox:", err);
                }
            });
        });
        </script>
        <?php
    }
}

new GF_Admin_ThickBox_Debug();
