<?php
/*
Plugin Name: Gravity Forms Admin Popup
Description: Render any Gravity Form fully functional inside a WordPress admin popup (ThickBox).
Version: 1.0
Author: Fahad 
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class GF_Admin_Popup {

    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_page'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_thickbox'));
    }

    // Add a top-level admin page
    public function add_admin_page() {
        add_menu_page(
            'GF Admin Popup',           // Page title
            'GF Popup',                 // Menu title
            'manage_options',           // Capability
            'gf-admin-popup',           // Menu slug
            array($this, 'render_page'),// Callback
            'dashicons-feedback',       // Icon
            100                         // Position
        );
    }

    // Enqueue ThickBox scripts & styles on our admin page
    public function enqueue_thickbox($hook) {
        if ($hook !== 'toplevel_page_gf-admin-popup') return;
        add_thickbox();
    }

    // Render the admin page content
    public function render_page() {
        $form_id = 1; // Change this to your Gravity Form ID
        ?>
        <div class="wrap">
            <h1>Gravity Form in Admin Popup</h1>

            <button class="button thickbox" 
                    href="#TB_inline?width=700&height=700&inlineId=gf-form-popup">
                Open Form
            </button>

            <div id="gf-form-popup" style="display:none;">
                <?php
                if ( class_exists('GFForms') && class_exists('GFFormDisplay') ) {
                    // Enqueue all necessary scripts and styles for this form
                    GFForms::enqueue_form_scripts( $form_id, true );
                    // Render the full form with AJAX enabled
                    echo GFFormDisplay::get_form( $form_id, true, true, true );
                } else {
                    echo '<p>Gravity Forms is not active.</p>';
                }
                ?>
            </div>
        </div>
        <?php
    }
}

// Initialize the plugin
new GF_Admin_Popup();
