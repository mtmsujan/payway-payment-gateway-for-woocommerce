<?php

namespace BOILERPLATE\Inc;

use BOILERPLATE\Inc\Traits\Singleton;

class Add_To_Cart {

    use Singleton;

    public function __construct() {
        $this->setup_hooks();
    }

    public function setup_hooks() {
        add_action( 'wp_ajax_add_to_cart', [ $this, 'handle_add_to_cart' ] );
        add_action( 'wp_ajax_nopriv_add_to_cart', [ $this, 'handle_add_to_cart' ] );
    }

    public function handle_add_to_cart() {
        try {
            // Verify the nonce
            if ( !isset( $_POST['security'] ) || !wp_verify_nonce( $_POST['security'], 'add_to_cart_nonce' ) ) {
                throw new Exception( 'Invalid nonce' );
            }

            $product_id = 2065;
            $form_data  = $_POST['data'] ?? null;

            if ( $form_data ) {
                $form_data = str_replace( "\\", "", $form_data );
                $this->put_program_logs( $form_data );
            }

            // Example: Add product to cart with custom data
            $quantity = 1; // Default quantity

            // Store the form data in custom cart item data
            $custom_data = array(
                'form_data' => $form_data,
            );

            // Add product to cart and pass the custom data
            $added = WC()->cart->add_to_cart( $product_id, $quantity, 0, array(), $custom_data );

            if ( !$added ) {
                throw new Exception( 'Failed to add product to cart' );
            }

            // Get checkout page url
            $checkout_page_url = wc_get_checkout_url();

            // Respond with success
            wp_send_json_success(
                array(
                    'message'  => 'Product added to cart',
                    'redirect' => $checkout_page_url,
                )
            );
        } catch (Exception $e) {
            // Respond with error message
            wp_send_json_error( $e->getMessage() );
        }

        wp_die();
    }


    public function put_program_logs( $data ) {
        // Ensure the directory for logs exists
        $directory = PLUGIN_BASE_PATH . '/program_logs/';
        if ( !file_exists( $directory ) ) {
            mkdir( $directory, 0777, true );
        }

        // Construct the log file path
        $file_name = $directory . 'program_logs.log';

        // Append the current datetime to the log entry
        $current_datetime = date( 'Y-m-d H:i:s' );
        $data             = $data . ' - ' . $current_datetime;

        // Write the log entry to the file
        if ( file_put_contents( $file_name, $data . "\n\n", FILE_APPEND | LOCK_EX ) !== false ) {
            return "Data appended to file successfully.";
        } else {
            return "Failed to append data to file.";
        }
    }
}