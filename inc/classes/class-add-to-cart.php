<?php

namespace BOILERPLATE\Inc;

use BOILERPLATE\Inc\Traits\Singleton;

class Add_To_Cart {

    use Singleton;

    public function __construct() {
        $this->setup_hooks();
    }

    public function setup_hooks() {
        // Hook to handle AJAX request
        add_action( 'wp_ajax_add_to_cart', [ $this, 'handle_add_to_cart' ] );
        add_action( 'wp_ajax_nopriv_add_to_cart', [ $this, 'handle_add_to_cart' ] );

        // Hook to dynamically update the product price in the cart
        add_action( 'woocommerce_before_calculate_totals', [ $this, 'update_cart_item_price' ], 1000, 1 );

        // Hook to pre-fill checkout fields with custom form data
        add_filter( 'woocommerce_checkout_fields', [ $this, 'pre_fill_checkout_fields' ], 999 );

        // Change place order button Text
        add_filter( 'woocommerce_order_button_text', [ $this, 'woo_custom_order_button_text' ] );
    }

    // Function to handle the add-to-cart AJAX request
    public function handle_add_to_cart() {
        try {
            // Verify the nonce for security
            if ( !isset( $_POST['security'] ) || !wp_verify_nonce( $_POST['security'], 'add_to_cart_nonce' ) ) {
                wp_send_json_error( 'Invalid nonce' );
                throw new \Exception( 'Invalid nonce' );
            }

            // Static product ID for cart
            $product_id = 2065;
            // $product_id = 608;

            // Retrieve and sanitize form data
            $form_data = $_POST['data'] ?? null;

            if ( $form_data ) {
                // Replace backslashes and decode the JSON string
                $form_data = str_replace( "\\", "", $form_data );
                // $this->put_program_logs( 'Form Data: ' . $form_data );
                $form_decoded_data = json_decode( $form_data, true );

                // Extract form data
                $school_name     = sanitize_text_field( $form_decoded_data['text-1'] );
                $amount          = floatval( $form_decoded_data['number-1'] );
                $message         = sanitize_textarea_field( $form_decoded_data['textarea-1'] );
                $full_name       = sanitize_text_field( $form_decoded_data['name-1'] );
                $phone           = sanitize_text_field( $form_decoded_data['phone-1'] );
                $street_address1 = sanitize_text_field( $form_decoded_data['address-1-street_address'] );
                $address_line    = sanitize_text_field( $form_decoded_data['address-1-address_line'] );
                $city            = sanitize_text_field( $form_decoded_data['address-1-city'] );
                $state           = sanitize_text_field( $form_decoded_data['address-1-state'] );
                $zip_code        = sanitize_text_field( $form_decoded_data['address-1-zip'] );
                $country         = sanitize_text_field( $form_decoded_data['address-1-country'] );
                $email           = sanitize_email( $form_decoded_data['email-1'] );

                // Split full name into first and last names
                $full_name_array = explode( ' ', $full_name );
                $first_name      = $full_name_array[0] ?? '';
                $mid_name        = $full_name_array[1] ?? '';
                $last_name       = $full_name_array[2] ?? '';
                $last_name       = $mid_name . ' ' . $last_name;
            }

            // Quantity for the product in the cart
            $quantity = 1;

            // Store custom form data in custom cart item data
            $custom_data = array(
                'school_name'     => $school_name,
                'amount'          => $amount,
                'message'         => $message,
                'first_name'      => $first_name,
                'last_name'       => $last_name,
                'full_name'       => $full_name,
                'phone'           => $phone,
                'street_address1' => $street_address1,
                'address_line'    => $address_line,
                'city'            => $city,
                'state'           => $state,
                'zip_code'        => $zip_code,
                'country'         => $country,
                'email'           => $email,
            );

            // $this->put_program_logs( 'Structure Form Data:' . json_encode( $custom_data ) );
            // Save to cookie $custom_data for 12 hours
            // setcookie( '_custom_data', json_encode( $custom_data ), time() + 43200, '/' );

            // Add product to cart with the custom data
            $added = WC()->cart->add_to_cart( $product_id, $quantity, 0, array(), $custom_data );

            if ( !$added ) {
                throw new \Exception( 'Failed to add product to cart' );
            }

            // Store checkout form data in the session for use during checkout
            WC()->session->set( 'custom_checkout_data', $custom_data );

            // Get checkout page URL and respond with success
            $checkout_page_url = wc_get_checkout_url();

            // Send success response
            wp_send_json_success(
                array(
                    'message'  => 'Product added to cart',
                    'redirect' => $checkout_page_url,
                )
            );

        } catch (\Exception $e) {
            wp_send_json_error( $e->getMessage() );
        }

        wp_die();
    }

    // Function to dynamically update the product price based on custom form data
    public function update_cart_item_price( $cart ) {
        // Ensure we're only updating the cart items if the cart is not empty
        if ( is_admin() && !defined( 'DOING_AJAX' ) )
            return;

        // Loop through cart items
        foreach ( $cart->get_cart() as $cart_item ) {

            // $this->put_program_logs( 'Cart Item: ' . json_encode( $cart_item ) );

            // Get amount
            $amount = $cart_item['amount'] ?? null;

            // $this->put_program_logs( 'Amount: ' . $amount );

            if ( $amount ) {
                // Set the price dynamically based on the 'amount' field from custom data
                $cart_item['data']->set_price( $amount );
            }

        }
    }


    // Function to pre-fill checkout fields with custom form data stored in session
    public function pre_fill_checkout_fields( $fields ) {

        // $this->put_program_logs( 'Pre-fill Checkout Fields: ' . json_encode( $fields ) );

        // Get custom form data from session
        $checkout_data = WC()->session->get( 'custom_checkout_data' );

        // $this->put_program_logs( 'Checkout Data: ' . json_encode( $checkout_data ) );

        if ( $checkout_data ) {
            // Pre-fill first name field
            $fields['billing']['billing_first_name']['default']      = $checkout_data['first_name'];
            $fields['billing']['billing_first_name']['autocomplete'] = 'no';

            $fields['billing']['billing_last_name']['default']      = $checkout_data['last_name'];
            $fields['billing']['billing_last_name']['autocomplete'] = 'no';

            $fields['billing']['billing_phone']['default']      = $checkout_data['phone'];
            $fields['billing']['billing_phone']['autocomplete'] = 'no';

            $fields['billing']['billing_email']['default']      = $checkout_data['email'];
            $fields['billing']['billing_email']['autocomplete'] = 'no';

            $fields['billing']['billing_address_1']['default']      = $checkout_data['street_address1'];
            $fields['billing']['billing_address_1']['autocomplete'] = 'no';

            $fields['billing']['billing_address_2']['default']      = $checkout_data['address_line'];
            $fields['billing']['billing_address_2']['autocomplete'] = 'no';

            $fields['billing']['billing_city']['default']      = $checkout_data['city'];
            $fields['billing']['billing_city']['autocomplete'] = 'no';

            $fields['billing']['billing_state']['default']      = $checkout_data['state'];
            $fields['billing']['billing_state']['autocomplete'] = 'no';

            $fields['billing']['billing_postcode']['default']      = $checkout_data['zip_code'];
            $fields['billing']['billing_postcode']['autocomplete'] = 'no';

            $fields['billing']['billing_country']['default']      = $checkout_data['country'];
            $fields['billing']['billing_country']['autocomplete'] = 'no';

            $fields['billing']['billing_description_of_what_you_are_paying_for_']['default']      = $checkout_data['message'];
            $fields['billing']['billing_description_of_what_you_are_paying_for_']['autocomplete'] = 'no';
        }

        return $fields;
    }

    public function woo_custom_order_button_text() {
        return __( 'Donate', 'payway-payment-gateway' );
    }


    // Function to log form data
    public function put_program_logs( $data ) {
        $directory = PLUGIN_BASE_PATH . '/program_logs/';
        if ( !file_exists( $directory ) ) {
            mkdir( $directory, 0777, true );
        }

        $file_name        = $directory . 'program_logs.log';
        $current_datetime = date( 'Y-m-d H:i:s' );
        $data             = $data . ' - ' . $current_datetime;

        if ( file_put_contents( $file_name, $data . "\n\n", FILE_APPEND | LOCK_EX ) !== false ) {
            return "Data appended to file successfully.";
        } else {
            return "Failed to append data to file.";
        }
    }
}
