<?php
/*
Plugin Name: Gravity Forms Admin Popup Debug
Description: Render a Gravity Form fully functional inside a WordPress admin popup
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

        // Enqueue scripts for all active forms to ensure fields like File Upload, Signature, etc. work
        if (class_exists('GFForms') && class_exists('GFAPI') && class_exists('GFFormDisplay')) {
            wp_enqueue_script('gform_gravityforms');
            $forms = GFAPI::get_forms(true, false);
            foreach ($forms as $form) {
                GFFormDisplay::enqueue_form_scripts($form);
            }
        }
    }

    public function render_page() {
        $form_id = 1; // your GF ID
        ?>
        <div class="wrap">
            <h1>Gravity Form in Admin Popup</h1>

            <a href="#TB_inline?width=900&height=800&inlineId=gf-form-popup" 
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
                    // GFForms::enqueue_form_scripts($form_id, true); // Moved to enqueue_scripts
                    $form_html = GFFormDisplay::get_form($form_id, true, true, true, null, true);
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
