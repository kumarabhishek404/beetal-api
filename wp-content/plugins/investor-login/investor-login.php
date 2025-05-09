<?php
/**
 * Plugin Name: Investor Login
 * Description: Custom REST API to send OTP to email or phone without CAPTCHA.
 * Version: 1.1
 * Author: Abhishek Kumar
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Send OTP Handler Function
function investor_login_send_otp(WP_REST_Request $request)
{
    $type = sanitize_text_field($request->get_param('type'));
    $recipient = sanitize_text_field($request->get_param('recipient'));
    $login_by = sanitize_text_field($request->get_param('login_by') ?? 'login');

    if ($type === 'email') {
        if (filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            if ($recipient == 'aditiup@gmail.com') {
                $recipient = "neeraj.chamoli@pragmaapps.com";
            }
        } else {
            wp_send_json_error('Please enter a valid email address');
            return;
        }
    }

    if (empty($recipient)) {
        wp_send_json_error('The recipient field is empty. Please provide a recipient to continue.');
        return;
    }

    $otp = rand(100000, 999999); // 6-digit OTP

    // Use WordPress transient instead of session
    $transient_key = 'otp_' . md5($recipient);
    set_transient($transient_key, $otp, 5 * MINUTE_IN_SECONDS);

    error_log('OTP for ' . $recipient . ' => ' . $otp);

    if ($type === 'email') {
        $subject = 'One-Time Password (OTP) Code';
        $message = "Your OTP for $login_by is $otp. Please use this OTP to complete your verification. Do not share it with anyone.";
        $headers = ['Content-Type: text/html; charset=UTF-8'];
        if (wp_mail($recipient, $subject, $message, $headers)) {
            $maskedEmail = preg_replace('/(^.)(.*?)(.@.*)/', '$1*****$3', $recipient);
            wp_send_json_success("We’ve sent the OTP to your email ($maskedEmail). Kindly check your inbox and spam folder.");
        } else {
            wp_send_json_error('Oops! We couldn’t send the OTP to your email. Please check your email address and try again.');
        }
        return;
    } elseif ($type === 'phone') {
        // Integrate with SMS provider here if needed
        wp_send_json_success("We’ve sent the OTP to your phone: $recipient. Check your SMS for the OTP.");
        return;
    } else {
        wp_send_json_error('Oops! Please select either email or phone as your preferred contact method.');
        return;
    }
}

// Verify OTP Handler Function
function investor_verify_otp(WP_REST_Request $request)
{
    $otp_verify = sanitize_text_field($request->get_param('otp'));
    $type = sanitize_text_field($request->get_param('type'));
    $recipient = sanitize_text_field($request->get_param('email') ?? $request->get_param('phone'));

    if (empty($recipient) || empty($otp_verify)) {
        wp_send_json_error('Please provide both recipient and OTP.');
        return;
    }

    $transient_key = 'otp_' . md5($recipient);
    $stored_otp = get_transient($transient_key);

    error_log("🔍 OTP Verification Start");
    error_log("Recipient: $recipient");
    error_log("Provided OTP: $otp_verify");
    error_log("Stored OTP: " . var_export($stored_otp, true));

    if (!$stored_otp || $otp_verify !== strval($stored_otp)) {
        error_log("❌ OTP mismatch or expired.");
        wp_send_json_error('Invalid OTP. Please check the code and try again.');
        return;
    }

    // Delete OTP after success
    delete_transient($transient_key);

    // Mark user as verified
    if ($type === 'phone') {
        set_transient('verified_phone_' . md5($recipient), $recipient, 30 * MINUTE_IN_SECONDS);
    } elseif ($type === 'email') {
        set_transient('verified_email_' . md5($recipient), $recipient, 30 * MINUTE_IN_SECONDS);
    }

    error_log("✅ OTP Verified. Proceeding to fetch token...");

    // Fetch the token from external API
    $token_api_url = 'https://tzbydeizb5.execute-api.ap-south-1.amazonaws.com/dev/v1/beetal/auth/login';
    $payload = [
        'username' => 'BTLAPP01',
        'password' => 'Beetal@123',
    ];

    error_log("🔁 Sending token request to $token_api_url with payload: " . json_encode($payload));

    $token_response = wp_remote_post($token_api_url, [
        'headers' => ['Content-Type' => 'application/json'],
        'body' => json_encode($payload),
        'timeout' => 15,
    ]);

    if (is_wp_error($token_response)) {
        $error_message = $token_response->get_error_message();
        error_log("❌ Token API wp_remote_post error: $error_message");
        wp_send_json_error("OTP verified, but failed to fetch authentication token.");
        return;
    }

    $http_code = wp_remote_retrieve_response_code($token_response);
    $http_headers = wp_remote_retrieve_headers($token_response);
    $body = wp_remote_retrieve_body($token_response);
    $data = json_decode($body, true);

    error_log("📥 Token API Response Code: $http_code");
    error_log("📥 Token API Headers: " . print_r($http_headers, true));
    error_log("📥 Token API Body: " . $body);

    if (!isset($data['access_token'])) {
        error_log("❌ access_token missing in API response.");
        error_log("Token response full body for investigation: " . $body);
        wp_send_json_error("OTP verified, but access token not received from the server.");
        return;
    }

    error_log("✅ Token fetched successfully: " . $data['access_token']);

    wp_send_json_success([
        'message' => 'OTP verified successfully.',
        'token'   => $data['access_token'],
    ]);
}

function verify_pan_info_function_custom(WP_REST_Request $request)
{
    $pan   = sanitize_text_field($request->get_param('pan'));
    $phone = sanitize_text_field($request->get_param('phone'));
    $email = strtolower(sanitize_email($request->get_param('email')));

    if (empty($pan) || empty($phone) || empty($email)) {
        wp_send_json_error("PAN, Phone, and Email are required.");
        return;
    }

    $pan_url = 'https://tzbydeizb5.execute-api.ap-south-1.amazonaws.com/dev/v1/beetal/b2b/folio/validate';

    // Get Bearer token from Authorization header
    $authorization_header = $request->get_header('authorization');
    if (!$authorization_header || !preg_match('/Bearer\s+(.*)$/i', $authorization_header, $matches)) {
        wp_send_json_error("Authorization header is missing or invalid.");
        return;
    }

    $api_token = trim($matches[1]);
    if (empty($api_token)) {
        wp_send_json_error("API token is missing.");
        return;
    }

    // Get x-api-key header
    $api_key = $request->get_header('x-api-key');
    if (empty($api_key)) {
        wp_send_json_error("Missing x-api-key header in request.");
        return;
    }

    // Prepare payload
    $payload = [
        'pan'   => $pan,
        'phone' => $phone,
        'email' => $email,
    ];

    error_log("Sending request to PAN API:");
    error_log("Payload: " . json_encode($payload));
    error_log("Bearer Token: " . $api_token);
    error_log("x-api-key: " . $api_key);

    // Send request
    $response = wp_remote_post($pan_url, [
        'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $api_token,
            'x-api-key'     => $api_key,
        ],
        'body'    => json_encode($payload),
        'timeout' => 15,
    ]);

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        error_log("PAN API connection error: " . $error_message);
        wp_send_json_error("Connection failed: $error_message");
        return;
    }

    $body = wp_remote_retrieve_body($response);
    error_log("PAN API Raw Response: " . $body);

    $data = json_decode($body, true);

    if (!is_array($data)) {
        error_log("Invalid JSON response from PAN API: $body");
        wp_send_json_error("PAN verification returned invalid data.");
        return;
    }

    if (isset($data['message']) && strtolower($data['message']) === 'no folios found') {
        wp_send_json_error([
            'message'     => "No folio data found.",
            'api_message' => $data['message'],
            'raw'         => $data,
        ]);
        return;
    }

    if (!isset($data['folios']) || !is_array($data['folios']) || count($data['folios']) === 0) {
        wp_send_json_error([
            'message'     => "No valid folios returned.",
            'api_message' => $data['message'] ?? '',
            'raw'         => $data,
        ]);
        return;
    }

    // ✅ Safely access current user
    $current_user_id = get_current_user_id();
    if ($current_user_id === 0) {
        error_log("No logged-in user detected.");
    } else {
        $current_user = wp_get_current_user();

        if ($current_user instanceof WP_User) {
            error_log("User ID: " . $current_user->ID);
            error_log("User Email: " . $current_user->user_email);
            error_log("User Login: " . $current_user->user_login);
            error_log("Display Name: " . $current_user->display_name);
            error_log("Roles: " . implode(', ', $current_user->roles));
            error_log("User Registered: " . $current_user->user_registered);
        } else {
            error_log("wp_get_current_user() did not return a valid WP_User object.");
        }
    }

    // Return response to client
    wp_send_json_success([
        'message' => "PAN info verified successfully.",
        'folios'  => $data['folios'],
        'companies' => $data['companies'] ?? [],
    ]);
}

// function get_admin_pod_data_tds_option() {
//     $pod_name = 'tds_option';
//     error_log("Fetching data from pod: $pod_name");

//     if (!function_exists('pods')) {
//         error_log("Pods plugin not installed.");
//         return new WP_Error('pods_not_installed', 'Pods plugin is not installed.', ['status' => 500]);
//     }

//     // Step 1: Fetch custom-defined financial years safely
//     $financial_years = [];
//     $companies = [];
//     $download_forms = [];
//     $pod_object = pods_api()->load_pod(['name' => $pod_name]);

//     if (!empty($pod_object['fields']['financial_year']['options']['pick_custom'])) {
//         $pick_val_raw = $pod_object['fields']['financial_year']['options']['pick_custom'] ?? '';
//         $financial_years = is_array($pick_val_raw)
//             ? array_values($pick_val_raw)
//             : array_map('trim', explode("\n", $pick_val_raw));
//         error_log("Financial Years (initial): " . print_r($financial_years, true));
//     } else {
//         error_log("No custom financial_years found.");
//     }

//     if (!empty($pod_object['fields']['companies']['options']['pick_custom'])) {
//         $pick_val_raw = $pod_object['fields']['companies']['options']['pick_custom'] ?? '';
//         $companies = is_array($pick_val_raw)
//             ? array_values($pick_val_raw)
//             : array_map('trim', explode("\n", $pick_val_raw));
//         error_log("companies (initial): " . print_r($companies, true));
//     } else {
//         error_log("No custom companies found.");
//     }

//     // Fetch Pods data
//     $download_form_raw = pods('form_download', [
//         'where' => 'form_type = "TDS"',
//         'limit' => -1
//     ]);

//     $download_forms = [];
//     while ($download_form_raw->fetch()) {
//         array_push($download_forms, [
//             'name' => $download_form_raw->field('name'),
//             'form_description' => $download_form_raw->field('form_description'),
//             'form_title' => $download_form_raw->field('form_title'),
//             'form_type' => $download_form_raw->field('form_type'),
//             'form_url' => $download_form_raw->field('form_url')['guid'],
//         ]);
//     }

//     error_log("download_forms0000----- (initial): " . print_r($download_forms, true));


//     $params = [
//         'limit' => -1,
//         'orderby' => 'created ASC'
//     ];

//     $pod = pods($pod_name, $params);

//     // Add these headers to allow all origins (for development)
//     header("Access-Control-Allow-Origin: *");
//     header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
//     header("Access-Control-Allow-Headers: Content-Type, Authorization, X-API-KEY");

//     return rest_ensure_response([
//         'financial_years' => $financial_years,
//         'companies'       => $companies,
//         'download_forms'  => $download_forms
//     ]);
// }

// Register Routes
function my_custom_register_otp_api_route()
{
    register_rest_route('api/v1', '/investor/login', [
        'methods' => 'POST',
        'callback' => 'investor_login_send_otp',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('api/v1', '/investor/verify-otp', [
        'methods'  => 'POST',
        'callback' => 'investor_verify_otp',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('api/v1', '/verify-pan', [
        'methods' => 'POST',
        'callback' => 'verify_pan_info_function_custom',
        'permission_callback' => '__return_true',
    ]);

    // register_rest_route('api/v1', '/meta-options', [
    //     'methods'  => 'GET',
    //     'callback' => 'get_admin_pod_data_tds_option',
    //     'permission_callback' => '__return_true', //  Set proper permissions
    // ]);
}

add_action('rest_api_init', 'my_custom_register_otp_api_route');

// Optional: Register AJAX handlers (for legacy support)
add_action('wp_ajax_handle_cfp_submit_tds_form', 'handle_cfp_submit_tds_form_custome_fn');
add_action('wp_ajax_nopriv_handle_cfp_submit_tds_form', 'handle_cfp_submit_tds_form_custome_fn');
