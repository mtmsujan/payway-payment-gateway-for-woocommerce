<?php

/**
 * Enqueue Plugin Admin and Public Assets
 */

namespace BOILERPLATE\Inc;

use BOILERPLATE\Inc\Traits\Singleton;

class Enqueue_Assets {

    use Singleton;

    public function __construct() {
        $this->setup_hooks();
    }

    public function setup_hooks() {
        // Actions for admin assets
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );

        // Actions for public assets
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_public_assets' ] );
    }

    /**
     * Enqueue Admin Assets.
     * @param mixed $page_now Current page
     * @return void
     */
    public function enqueue_admin_assets( $page_now ) {
        // enqueue admin css

        /**
         * enqueue admin js
         * 
         * When you need to enqueue admin assets.
         * first check if the current page is you want to enqueue page
         */
        if ( 'options-general.php' === $page_now ) {
            wp_enqueue_script( "wpb-admin-js", PLUGIN_ASSETS_DIR_URL . "/js/admin-script.js", [ 'jquery' ], time(), true ); // replace time() to version number when in production
        }
    }

    /**
     * Enqueue Public Assets.
     * @return void
     */
    public function enqueue_public_assets() {
        // enqueue public css
        wp_enqueue_style( "wpb-public-css", PLUGIN_PUBLIC_ASSETS_URL . "/css/public-style.css", [], time(), "all" );

        // enqueue public js    
        wp_enqueue_script( "wpb-public-js", PLUGIN_PUBLIC_ASSETS_URL . "/js/public-script.js", [], time(), true );

        // Send admin ajax url and nonce to wpb-public-js
        wp_localize_script(
            'wpb-public-js',
            'ajax_object',
            [
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'add_to_cart_nonce' )
            ]
        );

        // Enqueue alpine.js
        wp_enqueue_script( 'alpine', 'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js', [], time(), true );
    }


}