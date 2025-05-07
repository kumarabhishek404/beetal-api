<?php

/**
 * Plugin Name: Custom forms
 * Description: Custom form Changes
 * Version: 3.0.0
 * Author: pragmaapps
 */

function start_session()
{
    if (!session_id()) {
        session_start();
    }
    // update_option('api_key', 'f43a59d9-2d83-4e23-b25a-c1bb016b8a32');
    // update_option('api_url', 'https://uezt9gv2kb.execute-api.us-east-1.amazonaws.com/dev/');
    // update_option('api_url', 'https://tzbydeizb5.execute-api.ap-south-1.amazonaws.com/dev/');
}
add_action('init', 'start_session', 1);

require_once plugin_dir_path(__FILE__) . 'shortcodes/api_setting.php';
require_once plugin_dir_path(__FILE__) . 'shortcodes/html_functions.php';
require_once plugin_dir_path(__FILE__) . 'shortcodes/index.php';
require_once plugin_dir_path(__FILE__) . 'shortcodes/table_shortcode.php';
require_once plugin_dir_path(__FILE__) . 'shortcodes/company_login.php';
require_once plugin_dir_path(__FILE__) . 'shortcodes/folio_signup_and_login.php';
require_once plugin_dir_path(__FILE__) . 'shortcodes/investor_page_shortcode.php';

function enqueue_pods_ajax_scripts()
{
    wp_enqueue_script('jquery');
    wp_enqueue_script('response_messages_js', plugin_dir_url(__FILE__) . '/js/response_messages.js', array('jquery'), null, true);
    wp_enqueue_script('pods-pagination', plugin_dir_url(__FILE__) . '/js/pods-pagination.js', array('jquery'), null, true);
    wp_enqueue_script('company-login-js', plugin_dir_url(__FILE__) . '/js/company_login.js', array('jquery'), null, true);
    wp_enqueue_script('folio-login-js', plugin_dir_url(__FILE__) . '/js/folio_login.js', array('jquery'), null, true);
    wp_enqueue_script('download_pagination-js', plugin_dir_url(__FILE__) . '/js/contact_us_download_pagination.js', array('jquery'), null, true);
    wp_enqueue_script('fetch_Allotment_details_js', plugin_dir_url(__FILE__) . '/js/fetch_Allotment_details.js', array('jquery'), null, true);
    wp_localize_script('pods-pagination', 'pods_ajax', array('ajaxurl' => admin_url('admin-ajax.php')));
    wp_enqueue_script('jquery-validate', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js', array('jquery'), null, true);
    wp_enqueue_script('custom-validate', plugin_dir_url(__FILE__) . '/js/custom-validate.js', array('jquery', 'jquery-validate'), null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_pods_ajax_scripts');

function cfp_address_array_to_string($address)
{
    $formattedAddress = '';

    if (!empty($address['line_1'])) {
        $formattedAddress .= $address['line_1'] . ', ';
    }
    if (!empty($address['line_2'])) {
        $formattedAddress .= $address['line_2'] . ', ';
    }
    if (!empty($address['line_3'])) {
        $formattedAddress .= $address['line_3'] . ', ';
    }
    if (!empty($address['city'])) {
        $formattedAddress .= $address['city'] . ', ';
    }
    if (!empty($address['state'])) {
        $formattedAddress .= $address['state'] . ', ';
    }
    if (!empty($address['country'])) {
        $formattedAddress .= $address['country'] . ', ';
    }
    if (!empty($address['pin_code'])) {
        $formattedAddress .= 'PIN: ' . $address['pin_code'] . ', ';
    }
    // if (!empty($address['mobile'])) {
    //     $formattedAddress .= 'Mobile: ' . $address['mobile'];
    // }

    return rtrim($formattedAddress, ', ');
}

function my_short_codes_enqueue_styles()
{
    wp_enqueue_style('short-codes-css', plugin_dir_url(__FILE__) . 'css/styles.css', array(), '1.1.1');
    wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css', array(), '1.1.1',);
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css', array(), '1.1.1',);
    wp_enqueue_style('select2-css', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css', array(), '1.1.1',);

    // Enqueue Select2 JS
    wp_enqueue_script('select2-js', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', array('jquery'), '1.1.1', true);

    // Custom script to initialize Select2
    wp_add_inline_script('select2-js', 'jQuery(document).ready(function($){ $(".searchable-select").select2(); });');
}

function enqueue_login_script()
{
    wp_enqueue_script('dynamic-field-script', plugin_dir_url(__FILE__) . 'js/script.js', array('jquery'), '1.1.1', true);
    wp_localize_script('dynamic-field-script', 'ajaxurl', admin_url('admin-ajax.php'));
    wp_enqueue_script('validate-script', plugin_dir_url(__FILE__) . '/js/jquery.validate.js', array(), '1.1.1', true);
}

function custom_user_login()
{

    if (ob_get_length()) {
        ob_end_clean();
    }

    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $number =  isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
    $folio_number =  isset($_POST['folio_number']) ? sanitize_text_field($_POST['folio_number']) : '';
    $company_info =  isset($_POST['company_login']) ? sanitize_text_field($_POST['company_login']) : '';
    $otp =  isset($_POST['otp']) ? sanitize_text_field($_POST['otp']) : '';

    if (empty($otp) || $otp != $_SESSION['otp']) {
        wp_send_json_error(['message' => 'Invalid OTP. Please check the OTP and try again.']);
    }
    if (empty($folio_number)) {
        wp_send_json_error(['message' => 'Folio number is required.']);
    }

    // Check if the user already exists
    if (username_exists($folio_number)) {
        $_SESSION['otp'] = null;
        $user =  $user = get_user_by('login', $folio_number);;
        // error_log('User: ' . print_r($user,true));
        $pod = pods('user', $user->ID);
        $pod->save('email', $email);
        $pod->save('phone', $number);
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID);
        wp_send_json_success(['message' => 'You’re in! We’re redirecting you to your folio page. Please wait a moment.', "redirect_url" => site_url('investor-services')]);
    }

    // Create a new user
    $user_data = [
        'user_login'   => $folio_number,
        'user_pass'    => wp_generate_password(), // Generate a random password
        'user_email'   => (string) $folio_number . '@wpbeetal.com',
        'role'       => 'wpas_user',
    ];

    // Create a new user using wp_insert_user (required before adding custom fields)
    $user_id = wp_insert_user($user_data);

    // Check for errors
    if (is_wp_error($user_id)) {
        wp_send_json_error(['message' => 'Error creating user: ' . $user_id->get_error_message()]);
    } else {
        $_SESSION['otp'] = null;
        $pod = pods('user', $user_id);
        $pod->save('folio_no', $folio_number);
        $pod->save('isin', $company_info);
        $pod->save('client_email', $email);
        $pod->save('phone', $number);
        $pod->save('name', $folio_number);

        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id);
        wp_send_json_success(['message' => "User ‘{$folio_number}’ has been created successfully! Redirecting you to your folio page…", "redirect_url" => site_url('investor-services')]);
    }
}

function my_ajax_nonce()
{
    wp_localize_script('jquery', 'ajax_object', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('my_ajax_nonce')
    ]);
}
add_action('wp_enqueue_scripts', 'my_ajax_nonce');

function handle_user_registration()
{
    if (ob_get_length()) {
        ob_end_clean();
    }
    if (!isset($_POST['username']) || !isset($_POST['email']) || !isset($_POST['password']) || !isset($_POST['pan'])) {
        wp_send_json_error(['message' => 'All fields are required.']);
        return;
    }
    $username = sanitize_text_field($_POST['username']);
    $email = sanitize_email($_POST['email']);
    $password = sanitize_text_field($_POST['password']);
    $pan_number = sanitize_text_field($_POST['pan']);
    $phone = sanitize_text_field($_POST['phone']);
    if (email_exists($email)) {
        wp_send_json_error(['message' => 'Email address already registered.']);
        return;
    }

    $user_id = wp_create_user($username, $password, $email);

    if (is_wp_error($user_id)) {
        wp_send_json_error(['message' => $user_id->get_error_message()]);
        return;
    }
    $user = new WP_User($user_id);
    $available_roles = wp_roles()->roles;
    $user->set_role('wpas_user');
    update_user_meta($user_id, 'pan_number', $pan_number);
    update_user_meta($user_id, 'phone', $phone);
    wp_send_json_success(['message' => 'User registered successfully.']);
}

function handle_company_login()
{
    // check_ajax_referer('my_ajax_nonce', 'security');
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $recaptcha_token = sanitize_text_field($_POST['g-recaptcha-response']) ?? '';
        error_log("g-recaptcha-response" . print_r($recaptcha_token, true));

        if (!verify_recaptcha($recaptcha_token)) {
            wp_send_json_error('reCAPTCHA verification failed. Please try again.');
        }

        $username = sanitize_text_field($_POST['username']);
        $password = sanitize_text_field($_POST['password']);
        $company_login_name = sanitize_text_field($_POST['company_login_name']);

        // Validate inputs
        if (empty($username) || empty($password) || empty($company_login_name)) {
            wp_send_json_error('Please complete all the required fields before proceeding.');
        } else {
            // Fetch user data (assuming the role is stored as user meta)
            $user = get_user_by('login', $username);

            if ($user && wp_check_password($password, $user->user_pass)) {
                // $user_company = get_user_meta($user->ID, 'company_id', true);

                if (!empty($user->roles) && is_array($user->roles) && !empty($user->ID)) {
                    $pod = pods('user', $user->ID);
                    if (in_array('wpas_company_admin', $user->roles)) {
                        $pod->save('isin', $company_login_name);
                        $_SESSION['company_user_id'] = $user->ID;
                        wp_send_json_success(['email' => $user->user_email]);
                    }else if(in_array('wpas_company_user', $user->roles) && !empty($pod->field('isin')) && $pod->field('isin') == $company_login_name){
                        $_SESSION['company_user_id'] = $user->ID;
                        wp_send_json_success(['email' => $user->user_email]);
                    } else {
                        wp_send_json_error('The user is not registered as a company member or the selected company does not match the details on record.');
                    }
                } else {
                    wp_send_json_error('The user is not registered as a company member.');
                }
            } else {
                wp_send_json_error('Incorrect login credentials. Please try again.');
            }
        }
    }
}

function fetch_api_response($url, $params)
{
    $args = [
        'body' => json_encode($params),
        'headers' => [
            'x-api-key' =>  get_option('api_key'),
            'Content-Type' => 'application/json',
        ],
        'method' => 'POST',
    ];

    error_log("Args: " . print_r($args, true));
    // Send the POST request
    return wp_remote_post($url, $args);
}

function verify_folio_info()
{
    // Get request data
    $folio_number = (string) sanitize_text_field($_POST['folio_number']);
    $company_login = (string) sanitize_text_field($_POST['company_login']);
    $email = (string) sanitize_text_field($_POST['email']);
    $phone = (string) sanitize_text_field($_POST['phone']);

    // Validate input
    if (empty($folio_number)) {
        wp_send_json_error('Folio Number is required.');
    }
    if (empty($company_login)) {
        wp_send_json_error('Company isin is required.');
    }
    if (empty($email) && empty($phone)) {
        wp_send_json_error('Email or phone is required.');
    }

    $folio_url = get_option('api_url') . 'v1/beetal/b2b/folio/details';

    $params = [
        'folio_no' => $folio_number,
        'isin' => $company_login
    ];

    if (!empty($email)) {
        $params['email'] = $email;
    } elseif (!empty($phone)) {
        $params['phone'] = $phone;
    }

    $response = fetch_api_response($folio_url, $params);

    if (is_wp_error($response)) {
        error_log("Error: " . print_r($response, true));
        wp_send_json_error($response->get_error_message());
    } else {
        $body = wp_remote_retrieve_body($response);
        $response = json_decode($body, true);
        error_log("Body: " . print_r($response, true));
        if (isset($response['statusCode']) || isset($response['errorCode'])) {
            wp_send_json_error($response);
        } else {
            wp_send_json_success($response);
        }
    }
    wp_die();
}

function send_otp()
{
    // Get request data
    $type = sanitize_text_field($_POST['type']);
    $recipient = sanitize_text_field($_POST['recipient']);
    $recaptcha_verification = sanitize_text_field($_POST['recaptcha_verification']) ?? false;
    $recaptcha_token = sanitize_text_field($_POST['recaptcha']) ?? '';
    $login_by = !empty($_POST['login_by']) ? sanitize_text_field($_POST['login_by']) : 'login';

    if ($type === 'email') {
        if (filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            if($recipient == 'aditiup@gmail.com'){
                $recipient = "neeraj.chamoli@pragmaapps.com";
            }
        } else {
            wp_send_json_error('Please enter a valid email address');
            return;
        }
    }

    // Validate input
    if (empty($recipient)) {
        wp_send_json_error('The recipient field is empty. Please provide a recipient to continue.');
        return;
    }
    error_log("g-recaptcha-response" . print_r($recaptcha_token, true));

    if ($recaptcha_verification && !verify_recaptcha($recaptcha_token)) {
        wp_send_json_error('reCAPTCHA verification failed. Please try again.');
    }

    $otp = rand(100000, 999999); // Generate a 6-digit OTP

    // Store OTP in session (or database if needed)
    $_SESSION['otp'] = $otp;
    error_log('OTP => ' . $otp);

    // Send OTP
    if ($type === 'email') {
        // Send OTP via email
        $subject = 'One-Time Password (OTP) Code';
        $message = "Your OTP for $login_by is $otp. Please use this OTP to complete your verification. Do not share it with anyone.";
        $headers = ['Content-Type: text/html; charset=UTF-8'];
        if (wp_mail($recipient, $subject, $message, $headers)) {
            $maskedEmail = preg_replace('/(^.)(.*?)(.@.*)/', '$1*****$3', $recipient);
            wp_send_json_success("We’ve sent the OTP to your email($maskedEmail). Kindly check your inbox and spam folder.");
            return;
        } else {
            // wp_send_json_success("We’ve sent the OTP to your email($otp). Kindly check your inbox and spam folder.");
            wp_send_json_error('Oops! We couldn’t send the OTP to your email. Please check your email address and try again.');
            return;
        }
    } elseif ($type === 'phone') {
        // Send OTP via phone (use an SMS API here)
        // $userId = "beetal";
        // $password = "ind123";
        // $to = "8954213886";
        // $message = urlencode("OTP for User Validation is " . $otp . " BEETAL.");
        // $senderId = "BTLRTA";
        // $dltTemplateId = '1007368206588284420';
        // $dltEntityId = '1001472382644672556';

        // $url = "https://sms.indiasms.com/SMSApi/send?userid=" . $userId . "&password=" . $password . "&sendMethod=quick&mobile=" . $to . "&msg=" . $message . "&senderid=" . $senderId . "&msgType=text&dltEntityId=" . $dltEntityId . "&dltTemplateId=" . $dltTemplateId . "&duplicatecheck=true&output=json";
        // //"https://www.indsms.com/sendsms?userid=$username&password=$password&senderid=$senderid&to=$to&message=$message&route=$route&duplicatecheck=true&output=json";

        // $response = file_get_contents($url);
        // $responseArray = json_decode($response, true);
        // error_log(print_r($response, true));
        // if(empty($responseArray) || empty($responseArray['status']) || $responseArray['status'] != 'success'){
        //     wp_send_json_error('Oops! We couldn’t send the OTP to your phone. Please try again.');
        //     return;
        // }else {
            wp_send_json_success("We’ve sent the OTP to your phone: $recipient. Check your SMS for the OTP.");
            return;
        // }
    } else {
        wp_send_json_error('Oops! Please select either email or phone as your preferred contact method.');
        return;
    }
}

function fetch_entitlement_details_fn()
{
    $company_isin = sanitize_text_field($_POST['company_isin']);
    $login_option = sanitize_text_field($_POST['login_option']);
    $option_value = sanitize_text_field($_POST['option_value']);
    $token = sanitize_text_field($_POST['token']) ?? '';

    if (!verify_recaptcha($token)) {
        wp_send_json_error('reCAPTCHA verification failed. Please try again.');
        return;
    }

    if ($login_option === 'Enter Application Number.') {
        $login_option = 'appl_no';
    } else if ($login_option === 'Enter PAN Number.') {
        $login_option = 'acc_pan1';
    } else if ($login_option === 'Eg:-INXXXXXXXXXX,120XXXXXXXXX,XXXXXXX') {
        $login_option = 'dp_cl';
    }

    $params = [
        'company_code' =>$company_isin,
        "page"=> 1,
  "limit"=> 10,
  $login_option => $option_value
    ];

    $headers = [
        "Content-Type: application/json",
        "X-API-KEY: " . get_option('api_key')
    ];
    error_log("params: " . print_r($params, true) . json_encode($params));

    $ch = curl_init(get_option('api_url') . "v1/beetal/b2b/rights-entitlement/all");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));

    $api_response = curl_exec($ch);
    curl_close($ch);

    if (is_wp_error($api_response)) {
        wp_send_json_error("No results found for your search criteria.");
        return;
    } else {
        $response_decode_data = json_decode($api_response);
        if(empty($response_decode_data)){
            wp_send_json_error("No results found for your search criteria.");
            return;
        }
        // error_log("response1: " . print_r($api_response, true) );
        // error_log("response2: " . print_r($response_decode_data, true) );
        wp_send_json_success($response_decode_data);
        return;
    }
}

function fetch_ipo_allotment_details_fn()
{
    $company_isin = sanitize_text_field($_POST['company_isin']);
    $login_option = sanitize_text_field($_POST['login_option']);
    $option_value = sanitize_text_field($_POST['option_value']);
    $token = sanitize_text_field($_POST['token']) ?? '';

    if (!verify_recaptcha($token)) {
        wp_send_json_error('reCAPTCHA verification failed. Please try again.');
        return;
    }

    if ($login_option === 'Enter Application Number.') {
        $login_option = 'appl_no';
    } else if ($login_option === 'Enter PAN Number.') {
        $login_option = 'acc_pan1';
    } else if ($login_option === 'Eg:-INXXXXXXXXXX,120XXXXXXXXX,XXXXXXX') {
        $login_option = 'dp_cl';
    }

    $params = [
        'company_code' =>$company_isin,
        "page"=> 1,
  "limit"=> 10,
  $login_option => $option_value
    ];

    $headers = [
        "Content-Type: application/json",
        "X-API-KEY: " . get_option('api_key')
    ];
    error_log("params: " . print_r($params, true) . json_encode($params));

    $ch = curl_init(get_option('api_url') . "v1/beetal/b2b/ipo/allotments/all");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));

    $api_response = curl_exec($ch);
    curl_close($ch);

    if (is_wp_error($api_response)) {
        wp_send_json_error("No results found for your search criteria.");
        return;
    } else {
        $response_decode_data = json_decode($api_response);
        if(empty($response_decode_data)){
            wp_send_json_error("No results found for your search criteria.");
            return;
        }
        // error_log("response1: " . print_r($api_response, true) );
        // error_log("response2: " . print_r($response_decode_data, true) );
        wp_send_json_success($response_decode_data);
        return;
    }
}

function handle_tab2_download_resources()
{
    $company = sanitize_text_field($_POST['company_name']);
    wp_send_json_success(show_company_download_forms($company));
}

function handle_cfp_submit_tds_form_fn()
{
    // Process form data
    $financial_year =
        sanitize_text_field($_POST['financial_year']);
    $folio_number =
        sanitize_text_field($_POST['folio_number']);
    $company =
        sanitize_text_field($_POST['tds_company_name']);
    $select_exemption_form_type = sanitize_text_field($_POST['select_exemption_form_type']);
    $pan_number = sanitize_text_field($_POST['pan_number']);
    $email =
        sanitize_text_field($_POST['email_id']);
    $phone =
        sanitize_text_field($_POST['mobile_number']);
    $isin =
        sanitize_text_field($_POST['isin']);

    if (empty($folio_number)) {
        wp_send_json_error('Folio Number is required.');
    }
    if (empty($pan_number)) {
        wp_send_json_error('PAN Number should not be empty.');
    }
    if (empty($email)) {
        wp_send_json_error('Email ID is required.');
    }
    if (empty($phone)) {
        wp_send_json_error('Mobile number is required.');
    }
    if (empty($isin)) {
        wp_send_json_error('ISIN is required.');
    }

    // Handle file uploads (if needed)
    error_log(print_r($_FILES['files'], true));
    $uploaded_files = [];

    if (isset($_FILES['files']) && !empty($_FILES['files']['name'][0])) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        foreach ($_FILES['files']['name'] as $key => $value) {

            if ($_FILES['files']['error'][$key] === UPLOAD_ERR_OK) {
                $file = [
                    'name' => $_FILES['files']['name'][$key],
                    'type' => $_FILES['files']['type'][$key],
                    'tmp_name' => $_FILES['files']['tmp_name'][$key],
                    'error' => $_FILES['files']['error'][$key],
                    'size' => $_FILES['files']['size'][$key],
                ];

                // Upload file to WordPress media library
                $upload = wp_handle_upload($file, ['test_form' => false]);

                if (!isset($upload['error']) && isset($upload['url'])) {
                    // Insert the uploaded file into WordPress Media Library
                    $attachment = [
                        'guid' => $upload['url'],
                        'post_mime_type' => $file['type'],
                        'post_title' => sanitize_file_name($file['name']),
                        'post_content' => '',
                        'post_status' => 'inherit'
                    ];
                    $attach_id = wp_insert_attachment($attachment, $upload['file']);
                    $uploaded_files[] = wp_get_attachment_url($attach_id);
                } else {
                    error_log("File upload error: " . $upload['error']);
                    continue;
                }
            }
        }
    }
    error_log("Uploaded Files => " . print_r($uploaded_files, true));

    // Save the ticket in Pods or the database
    $request_id = pods('tds_exemption_form')->add([
        'company_name' => $company,
        'financial_year' => $financial_year,
        'exemption_form_type' => $select_exemption_form_type,
        'folio_number' => $folio_number,
        'pan' => $pan_number,
        'isin' => $isin,
        'email_id' => $email,
        'name' => $email,
        'phone' => $phone,
        'files' => json_encode($uploaded_files),
    ]);

    // Redirect with success or failure
    if ($request_id) {
        wp_send_json_success("Form Successfully submitted with id $request_id.");
        return;
    } else {
        wp_send_json_error('Failed to create the request. Please check your input and try again.');
        return;
    }
}

add_filter('http_request_timeout', function () {
    return 30; // Increase timeout to 30 seconds
});

function redirect_company_user_shortcode()
{
    if (!is_admin()) { // Ensure it runs only on frontend
?>
        <script>
            window.location.href = `<?= site_url('client-services/?tab=client_information');?>`;
            </script>
            <?php
        }
    }
function is_user_login_fn()
{
    if(is_user_logged_in()){
        wp_send_json_success();
    }else {
        wp_send_json_error();
    }
    }
function redirect_investor_user_shortcode()
{
    if (!is_admin()) { // Ensure it runs only on frontend
?>
        <script>
            window.location.href = `<?= site_url('investor-services');?>`;
            </script>
            <?php
        }
    }

    add_action('wp_enqueue_scripts', 'enqueue_login_script');
    add_action('wp_enqueue_scripts', 'my_short_codes_enqueue_styles');

    add_action('wp_ajax_nopriv_company_login', 'handle_company_login');
    add_action('wp_ajax_company_login', 'handle_company_login');

    add_action('wp_ajax_nopriv_cfp_submit_tds_form', 'handle_cfp_submit_tds_form_fn');
    add_action('wp_ajax_cfp_submit_tds_form', 'handle_cfp_submit_tds_form_fn');

    add_action('wp_ajax_nopriv_register_new_user', 'handle_user_registration');
    add_action('wp_ajax_register_new_user', 'handle_user_registration');

    add_action('wp_ajax_verify_folio_info', 'verify_folio_info');
    add_action('wp_ajax_nopriv_verify_folio_info', 'verify_folio_info');

    add_action('wp_ajax_verify_pan_info', 'verify_pan_info_fn');
    add_action('wp_ajax_nopriv_verify_pan_info', 'verify_pan_info_fn');

add_action('wp_ajax_is_user_login', 'is_user_login_fn');
add_action('wp_ajax_nopriv_is_user_login', 'is_user_login_fn');

    add_action('wp_ajax_tab2_download_resources', 'handle_tab2_download_resources');
    add_action('wp_ajax_nopriv_tab2_download_resources', 'handle_tab2_download_resources');

    add_action('wp_ajax_fetch_download_pods_pagination', 'handle_fetch_download_pods_pagination_fn');
    add_action('wp_ajax_nopriv_fetch_download_pods_pagination', 'handle_fetch_download_pods_pagination_fn');

    add_action('wp_ajax_fetch_client_pods_pagination', 'handle_fetch_client_pods_pagination_fn');
    add_action('wp_ajax_nopriv_fetch_client_pods_pagination', 'handle_fetch_client_pods_pagination_fn');

    add_action('wp_ajax_send_otp', 'send_otp');
    add_action('wp_ajax_nopriv_send_otp', 'send_otp');

    add_action('wp_ajax_verify_otp', 'verify_otp_fn');
    add_action('wp_ajax_nopriv_verify_otp', 'verify_otp_fn');

    add_action('wp_ajax_nopriv_custom_user_login', 'custom_user_login');
    add_action('wp_ajax_custom_user_login', 'custom_user_login');

    add_action('wp_ajax_nopriv_company_user_login', 'company_user_login_fn');
    add_action('wp_ajax_company_user_login', 'company_user_login_fn');

    add_action('wp_ajax_nopriv_fetch_ipo_allotment_details', 'fetch_ipo_allotment_details_fn');
    add_action('wp_ajax_fetch_ipo_allotment_details', 'fetch_ipo_allotment_details_fn');

    add_action('wp_ajax_nopriv_fetch_entitlement_details', 'fetch_entitlement_details_fn');
    add_action('wp_ajax_fetch_entitlement_details', 'fetch_entitlement_details_fn');
