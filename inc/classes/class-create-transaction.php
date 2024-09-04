<?php

namespace BOILERPLATE\Inc;

use BOILERPLATE\Inc\Traits\Singleton;

class Create_Transaction {

    use Singleton;

    public function __construct() {
        $this->setup_hooks();
    }

    public function setup_hooks() {
        // Actions and filters hooks
        add_shortcode( 'create_transaction', [ $this, 'create_transaction' ] );
        add_action( 'wp_ajax_create_transaction', [ $this, 'handle_create_transaction' ] );
        add_action( 'wp_ajax_nopriv_create_transaction', [ $this, 'handle_create_transaction' ] );
    }

    public function create_transaction() {

        // Api sandbox url
        $sandbox_url     = "https://checkout-sandbox.payway.com.kh/api/payment-gateway/v1/payments/purchase";
        $sandbox_api_key = "e52f2f0e-e5ba-4a36-ae8c-54fee5646c02";

        /**
         * METHOD: POST
         * Content-Type: multipart/form-data
         * Body: form-data
         * 
         * Request Parameters Description
         * req_time: (Timestamp [UTC]) [Format YYYYmmddHis, Example: 20210123234559] // mandatory field
         * merchant_id: (String [30]) [Example: onlinesshop24] // mandatory field
         * tran_id: (String [20]) [Example: 24os-pr0001 or 12345 or OS12345] // mandatory field
         * amount: (decimal (10, 2)) [Example: USD 100.00   KHR 10000] // mandatory field
         * payment_option: (String [20]) [Example: abaypay] // mandatory field
         * hash: (Text) [base64_encode(hash_hmac('sha512', string, $public_key, true));] // mandatory field
         * steps for generate hash:
         * Step 1: generate hash: (req_time + merchant_id + tran_id + amount + items + gdt + shipping + ctid + pwt + firstname + lastname + email + phone + type + payment_option + return_url + cancel_url + 8 continue_success_url + return_deeplink + topup_channel + currency + custom_fields + return_params)
         * Step 2: Encrypt with: "[\"sha512_true\”, \"fb629880e9a741dc9862a4ad260f6668\"]"
         * 
         * Example Request: Submit required parameter as form-data
         * ```JSON
         * 
         * {
            "req_time":"20210123234559",
            "merchant_id":"onlinesshop24",
            "tran_id":"00002894",
            "firstname":"Fristname",
            "lastname":"Customer Last name",
            "email":"ema_il@textdomain.com",
            "phone":"0965965965",
            "amount":5000,
            "type":"purcahse",
            "payment_option":"abapay",
            "items":"W3snbmFtZSc6J3Rlc3QnLCdxdWFudGl0eSc6JzEnLCdwcmljZSc6JzEuMDAnfV0=",
            "currency":"KHR",
            "continue_success_url":"www.staticmerchanturl.com/Success",
            "return_deeplink":,
            "custom_fields":"{"Purcahse order ref":"Po-MX9901", "Customfield2":"value for custom field"}",
            "return_param":"500 Character notes included here will be returned on pushback notification after transaction is successful.",
            "hash":"K3nd/2Z4g45Paoqx06QA3UQeHRC2Ts37zjudG7DqyyU2Cq0cvOFMYqwtEsXkaEmNOSiFh6Y+IHRdwnA2WA/M/Qg=="
        }
         * 
         * ```
         * 
         * Plans:
         * I added mandatory fields above. fill up these value with your own. when create hash first create variable which fields are missing and fill up you own. after that i will make this via dynamic values.
         * 
         */

        ob_start();
        ?>

        <script>

            document.addEventListener('alpine:init', () => {
                Alpine.data('createTransactionData', () => ({
                    async createTransaction() {
                        // Static data for the transaction fields
                        let data = {
                            req_time: '20210123234559',  // Timestamp in format YYYYmmddHis
                            merchant_id: 'onlinesshop24',
                            tran_id: '00002894',
                            firstname: 'John',
                            lastname: 'Doe',
                            email: 'john.doe@example.com',
                            phone: '1234567890',
                            amount: '5000.00',
                            payment_option: 'abapay',
                            items: 'W3snbmFtZSc6J3Rlc3QnLCdxdWFudGl0eSc6JzEnLCdwcmljZSc6JzEuMDAnfV0=', // Base64-encoded string for items
                            gdt: '',        // Add as per your requirement
                            shipping: '',   // Add as per your requirement
                            ctid: '',       // Add as per your requirement
                            pwt: '',        // Add as per your requirement
                            type: 'purchase',
                            return_url: 'https://example.com/return',
                            cancel_url: 'https://example.com/cancel',
                            continue_success_url: 'https://example.com/success',
                            return_deeplink: '',   // Add if applicable
                            topup_channel: '',     // Add if applicable
                            currency: 'USD',
                            custom_fields: '{"Purchase order ref":"Po-MX9901", "Customfield2":"value for custom field"}',
                            return_params: '500 Character notes included here will be returned on pushback notification after transaction is successful.'
                        };

                        // Send request to WordPress PHP handler
                        try {
                            let response = await fetch('<?php echo admin_url( 'admin-ajax.php' ); ?>', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({
                                    action: 'create_transaction',
                                    data: data
                                })
                            });

                            let result = await response.json();
                            console.log('Transaction Created:', result);
                        } catch (error) {
                            console.error('Error:', error);
                        }
                    }
                }));
            });

        </script>

        <div class="create-transaction" x-data="createTransactionData">
            <h2>Create Transaction</h2>
            <button @click="createTransaction" type="button">Create</button>
        </div>

        <?php
        return ob_get_clean();
    }

    function handle_create_transaction() {

        $data = json_decode( file_get_contents( 'php://input' ), true );

        // Extract fields from the input data
        $req_time             = $data['req_time'];
        $merchant_id          = $data['merchant_id'];
        $tran_id              = $data['tran_id'];
        $amount               = $data['amount'];
        $items                = $data['items'];
        $gdt                  = $data['gdt']; // Add or keep empty
        $shipping             = $data['shipping']; // Add or keep empty
        $ctid                 = $data['ctid']; // Add or keep empty
        $pwt                  = $data['pwt']; // Add or keep empty
        $firstname            = $data['firstname'];
        $lastname             = $data['lastname'];
        $email                = $data['email'];
        $phone                = $data['phone'];
        $type                 = $data['type'];
        $payment_option       = $data['payment_option'];
        $return_url           = $data['return_url'];
        $cancel_url           = $data['cancel_url'];
        $continue_success_url = $data['continue_success_url'];
        $return_deeplink      = $data['return_deeplink']; // Add or keep empty
        $topup_channel        = $data['topup_channel']; // Add or keep empty
        $currency             = $data['currency'];
        $custom_fields        = $data['custom_fields'];
        $return_params        = $data['return_params'];

        // Generate the string for hashing (concatenate fields)
        $string_to_hash = $req_time . $merchant_id . $tran_id . $amount . $items . $gdt . $shipping . $ctid . $pwt .
            $firstname . $lastname . $email . $phone . $type . $payment_option . $return_url .
            $cancel_url . $continue_success_url . $return_deeplink . $topup_channel . $currency .
            $custom_fields . $return_params;

        // Generate the hash
        $hash = base64_encode( hash_hmac( 'sha512', $string_to_hash, 'fb629880e9a741dc9862a4ad260f6668', true ) );

        $this->put_program_logs( $hash );

        // Prepare request data
        $post_data = [
            'req_time'             => $req_time,
            'merchant_id'          => $merchant_id,
            'tran_id'              => $tran_id,
            'firstname'            => $firstname,
            'lastname'             => $lastname,
            'email'                => $email,
            'phone'                => $phone,
            'amount'               => $amount,
            'payment_option'       => $payment_option,
            'items'                => $items,
            'type'                 => $type,
            'currency'             => $currency,
            'continue_success_url' => $continue_success_url,
            'return_url'           => $return_url,
            'cancel_url'           => $cancel_url,
            'return_deeplink'      => $return_deeplink,
            'custom_fields'        => $custom_fields,
            'return_params'        => $return_params,
            'hash'                 => $hash,
        ];

        // Send request via cURL
        $ch = curl_init( 'https://checkout-sandbox.payway.com.kh/api/payment-gateway/v1/payments/purchase' );
        curl_setopt( $ch, CURLOPT_POST, 1 );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $post_data );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

        $response = curl_exec( $ch );
        curl_close( $ch );

        // Send response back to the frontend
        wp_send_json_success( $response );
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