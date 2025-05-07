<?php
/**
 * Plugin Name: Company Login
 * Description: Handles company login via custom REST API.
 * Version: 1.1
 * Author: Your Name
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Start session
add_action('init', function () {
    if (!session_id()) {
        session_start();
    }
});

/**
 * REST API for company login (without company name requirement)
 */
function handle_company_login_api(WP_REST_Request $request)
{
    $username = sanitize_text_field($request->get_param('username'));
    $password = sanitize_text_field($request->get_param('password'));

    // Validate inputs
    if (empty($username) || empty($password)) {
        return new WP_REST_Response(['success' => false, 'message' => 'Please enter both username and password.'], 400);
    }

    $user = get_user_by('login', $username);

    if ($user && wp_check_password($password, $user->user_pass)) {
        if (!empty($user->roles) && is_array($user->roles) && !empty($user->ID)) {
            if (in_array('wpas_company_admin', $user->roles) || in_array('wpas_company_user', $user->roles)) {
                // Store session if needed
                $_SESSION['company_user_id'] = $user->ID;

                // Send OTP to email
                $email = $user->user_email;
                $otp = rand(100000, 999999);

                // Store OTP using transient
                $transient_key = 'otp_' . md5($email);
                set_transient($transient_key, $otp, 5 * MINUTE_IN_SECONDS);

                error_log('OTP for ' . $email . ' => ' . $otp);

                $subject = 'Your Company Login OTP Code';
                $message = "Your OTP is: <strong>$otp</strong><br>Please use this OTP to continue with your login. Do not share it with anyone.";
                $headers = ['Content-Type: text/html; charset=UTF-8'];

                if (wp_mail($email, $subject, $message, $headers)) {
                    $maskedEmail = preg_replace('/(^.)(.*?)(.@.*)/', '$1*****$3', $email);
                    return new WP_REST_Response([
                        'success' => true,
                        'email'   => $email,
                        'message' => "Login successful. OTP sent to your email ($maskedEmail)."
                    ], 200);
                } else {
                    return new WP_REST_Response([
                        'success' => false,
                        'message' => 'Login successful, but failed to send OTP to your email.'
                    ], 500);
                }

            } else {
                return new WP_REST_Response([
                    'success' => false,
                    'message' => 'The user is not registered as a company member.'
                ], 403);
            }
        } else {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Invalid user roles.'
            ], 403);
        }
    } else {
        return new WP_REST_Response([
            'success' => false,
            'message' => 'Incorrect login credentials. Please try again.'
        ], 401);
    }
}

function company_verify_otp(WP_REST_Request $request)
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

    error_log("OTP Verification: Provided OTP = $otp_verify, Stored OTP = $stored_otp");

    if (!$stored_otp || $otp_verify !== strval($stored_otp)) {
        wp_send_json_error('Invalid OTP. Please check the code and try again.');
        return;
    }

    delete_transient($transient_key);

    if ($type === 'phone') {
        set_transient('verified_phone_' . md5($recipient), $recipient, 30 * MINUTE_IN_SECONDS);
    } elseif ($type === 'email') {
        set_transient('verified_email_' . md5($recipient), $recipient, 30 * MINUTE_IN_SECONDS);
    }

    if ($type === 'email') {
        $user = get_user_by('email', $recipient);
    } elseif ($type === 'phone') {
        $users = get_users([
            'meta_key'   => 'phone_number',
            'meta_value' => $recipient,
            'number'     => 1,
        ]);
        $user = !empty($users) ? $users[0] : null;
    } else {
        $user = null;
    }

    if (!$user || !in_array('wpas_company_admin', $user->roles) && !in_array('wpas_company_user', $user->roles)) {
        wp_send_json_error('User not found or not authorized as a company user.');
        return;
    }

    $company_data = [
        'company_name'    => get_user_meta($user->ID, 'company_name', true),
        'company_address' => get_user_meta($user->ID, 'company_address', true),
        'pan_number'      => get_user_meta($user->ID, 'pan_number', true),
        'gst_number'      => get_user_meta($user->ID, 'gst_number', true),
        'username'      => get_user_meta($user->ID, 'username', true),
        'contact_person'  => $user->display_name,
        'email'           => $user->user_email,
        'phone'           => get_user_meta($user->ID, 'phone', true),
        'isin'      => get_user_meta($user->ID, 'isin', true), // Assuming SIN number is stored as 'sin_number'
    ];

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
        'company' => $company_data,
    ]);
}

// Fetch company details from external API
function fetch_company_details(WP_REST_Request $request)
{
    // Get 'isin_code' from the request
    $isin_code = sanitize_text_field($request->get_param('isin_code'));

    // Log incoming ISIN code
    error_log("Received ISIN Code: " . $isin_code);

    if (empty($isin_code)) {
        error_log("ISIN Code is missing");
        return new WP_REST_Response(['success' => false, 'message' => 'Please provide an ISIN code.'], 400);
    }

    // Get the token from the request headers
    $token = $request->get_header('Authorization');

    // Log token retrieval
    error_log("Received Authorization token: " . $token);

    if (empty($token)) {
        error_log("Authorization token is missing");
        return new WP_REST_Response(['success' => false, 'message' => 'Authorization token is missing.'], 401);
    }

    // Remove 'Bearer ' prefix from token if present
    $token = str_replace('Bearer ', '', $token);

    // Log the cleaned token (ensure not to log sensitive information in production)
    error_log("Cleaned Authorization token: " . $token);

    // Prepare data for external API request
    $url = 'https://tzbydeizb5.execute-api.ap-south-1.amazonaws.com/dev/v1/beetal/b2b/company/details';
    $headers = [
        'x-api-key' => 'f43a59d9-2d83-4e23-b25a-c1bb016b8a32',
        'Authorization' => 'Bearer ' . $token,  // Use the token from the headers
        'Content-Type' => 'application/json',
    ];

    // Log the API request headers
    error_log("API Request Headers: " . json_encode($headers));

    // Prepare the POST body
    $body = json_encode([
        'isin_code' => $isin_code
    ]);

    // Log the POST body
    error_log("API Request Body: " . $body);

    // Send the request using wp_remote_post
    $response = wp_remote_post($url, [
        'method'    => 'POST',
        'body'      => $body,
        'headers'   => $headers,
        'timeout'   => 15,
    ]);

    // Handle the response
    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        error_log("API Request Failed: " . $error_message);
        return new WP_REST_Response([
            'success' => false,
            'message' => 'Failed to fetch company details. Error: ' . $error_message
        ], 500);
    }

    $http_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);

    // Log the HTTP response code and body
    error_log("API Response Code: " . $http_code);
    error_log("API Response Body: " . $body);

    $data = json_decode($body, true);

    // Check if response contains the 'company' key
    if (isset($data['company'])) {
        // Success response with company details
        return new WP_REST_Response([
            'success' => true,
            'company_details' => $data['company'] // Use 'company' instead of 'company_details'
        ], 200);
    } else {
        // Error response if no company details found
        error_log("No company details found for ISIN Code: " . $isin_code);
        return new WP_REST_Response([
            'success' => false,
            'message' => 'No company details found for the provided ISIN code.'
        ], 404);
    }
}

function fetch_company_dashboard_details(WP_REST_Request $request)
{
    // Get the ISIN code from the request
    $isin_code = sanitize_text_field($request->get_param('isin_code'));

    // Log incoming ISIN code
    error_log("Received ISIN Code: " . $isin_code);

    if (empty($isin_code)) {
        return new WP_REST_Response(['success' => false, 'message' => 'Please provide an ISIN code.'], 400);
    }

    // Get the token from the request headers
    $token = $request->get_header('Authorization');

    if (empty($token)) {
        return new WP_REST_Response(['success' => false, 'message' => 'Authorization token is missing.'], 401);
    }

    // Clean the token by removing 'Bearer ' prefix
    $token = str_replace('Bearer ', '', $token);

    // Prepare data for external API request
    $url = 'https://tzbydeizb5.execute-api.ap-south-1.amazonaws.com/dev/v1/beetal/b2b/company/dashboard?isinCode=' . $isin_code;
    $headers = [
        'x-api-key' => 'f43a59d9-2d83-4e23-b25a-c1bb016b8a32',
        'Authorization' => 'Bearer ' . $token,  // Use the token from the headers
        'Content-Type' => 'application/json',
    ];

    // Send the request using wp_remote_get
    $response = wp_remote_get($url, [
        'headers' => $headers,
        'timeout' => 15,
    ]);

    // Check for errors in the response
    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        error_log("API Request Failed: " . $error_message);
        return new WP_REST_Response([
            'success' => false,
            'message' => 'Failed to fetch company details. Error: ' . $error_message
        ], 500);
    }

    // Get the response body and decode JSON
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    // Check if response contains the 'company' key
    if (isset($data['company'])) {
        // Return the company details if present in the response
        return new WP_REST_Response([
            'success' => true,
            'company_details' => $data['company'],
            'shareholding_pattern' => $data['shareHoldingPattern'],
            'distribution_summary' => $data['distributionSummary'],
            'total_folios' => $data['totalFolios'],
            'total_shares' => $data['totalShares'],
            'report_date' => $data['reportDate']
        ], 200);
    } else {
        // If company details are not found
        return new WP_REST_Response([
            'success' => false,
            'message' => 'No company details found for the provided ISIN code.'
        ], 404);
    }
}

// Fetch company documents from external API
function fetch_company_documents(WP_REST_Request $request)
{
    // Get parameters from the request
    $isin_code = sanitize_text_field($request->get_param('isin_code'));
    $page = intval($request->get_param('page'));
    $limit = intval($request->get_param('limit'));

    // Log the incoming parameters for debugging
    error_log("Received ISIN Code: " . $isin_code);
    error_log("Page: " . $page . ", Limit: " . $limit);

    // Validate that ISIN code is provided
    if (empty($isin_code)) {
        error_log("ISIN Code is missing");
        return new WP_REST_Response(['success' => false, 'message' => 'Please provide an ISIN code.'], 400);
    }

    // Prepare data for external API request
    $url = 'https://tzbydeizb5.execute-api.ap-south-1.amazonaws.com/dev/v1/beetal/b2b/company/documents/all';
    
    // Set up the headers for the external API request
    $headers = [
        'x-api-key' => 'f43a59d9-2d83-4e23-b25a-c1bb016b8a32',
        'Authorization' => 'Bearer ' . 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6ImQxOTNmYjM3LTgyM2MtNDliYy1hZWMzLTQ4ZmZlZjdhMzZkMiIsIm5hbWUiOiJCZWV0YWwgQXBwIiwiZW1haWwiOiJiZWV0YWwuYXBwQGJlZXRhbC5pbiIsInVzZXJuYW1lIjoiQlRMQVBQMDEiLCJ1c2VyX3R5cGUiOiJhcHAiLCJ0b2tlbl90eXBlIjoiYXV0aCIsImlhdCI6MTc0NTgyNzAwOSwiZXhwIjoxNzQ2NDMxODA5fQ.feGqe9myIWD0zUOEfJNbdUSQDGi_QLFMPalPUduGK70', // Bearer token
        'Content-Type' => 'application/json',
    ];

    // Prepare the POST body with request parameters
    $body = json_encode([
        'isin_code' => $isin_code,
        'page' => $page,
        'limit' => $limit
    ]);

    // Log the request body for debugging
    error_log("API Request Body: " . $body);

    // Send the request using wp_remote_post
    $response = wp_remote_post($url, [
        'method'    => 'POST',
        'body'      => $body,
        'headers'   => $headers,
        'timeout'   => 15,
    ]);

    // Handle the response
    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        error_log("API Request Failed: " . $error_message);
        return new WP_REST_Response([
            'success' => false,
            'message' => 'Failed to fetch company documents. Error: ' . $error_message
        ], 500);
    }

    $http_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);

    // Log the API response for debugging
    error_log("API Response Code: " . $http_code);
    error_log("API Response Body: " . $body);

    $data = json_decode($body, true);

    // Check if response contains the 'data' key
    if (isset($data['data']) && is_array($data['data']) && count($data['data']) > 0) {
        // Success response with documents data
        return new WP_REST_Response([
            'success' => true,
            'documents' => $data['data'], // Return documents data
            'total' => $data['total'],
            'page' => $data['page'],
            'limit' => $data['limit'],
        ], 200);
    } else {
        // Error response if no documents found
        error_log("No documents found for ISIN Code: " . $isin_code);
        return new WP_REST_Response([
            'success' => false,
            'message' => 'No documents found for the provided ISIN code.'
        ], 404);
    }
}

/**
 * REST API for fetching document download URL for a company
 */
function handle_document_download_api(WP_REST_Request $request)
{
    // Get the company document ID from the request
    $document_id = sanitize_text_field($request->get_param('id'));

    // Validate document ID
    if (empty($document_id)) {
        return new WP_REST_Response(['success' => false, 'message' => 'Please provide a valid document ID.'], 400);
    }

    // Extract the token from the Authorization header
    $auth_header = $request->get_header('Authorization');
    if (empty($auth_header)) {
        return new WP_REST_Response(['success' => false, 'message' => 'Authorization token is missing.'], 400);
    }

    // Extract the Bearer token from the Authorization header
    $matches = [];
    if (preg_match('/Bearer\s(\S+)/', $auth_header, $matches)) {
        $token = $matches[1];
    } else {
        return new WP_REST_Response(['success' => false, 'message' => 'Invalid authorization token format.'], 400);
    }

    // Prepare the request URL for the external API
    $api_url = 'https://tzbydeizb5.execute-api.ap-south-1.amazonaws.com/dev/v1/beetal/b2b/company/document/download?id=' . $document_id;

    // Set headers for the API call, using the extracted token
    $headers = [
        'x-api-key' => 'f43a59d9-2d83-4e23-b25a-c1bb016b8a32',
        'Authorization' => 'Bearer ' . $token,  // Use the extracted token here
    ];

    // Send the request to the external API
    $response = wp_remote_get($api_url, [
        'headers' => $headers,
        'timeout' => 15,
    ]);

    // Check if the API call was successful
    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        error_log("❌ Document API wp_remote_get error: $error_message");
        return new WP_REST_Response(['success' => false, 'message' => 'Failed to fetch document URL.'], 500);
    }

    // Decode the API response
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    // Check if the document URL is present in the response
    if (isset($data['url'])) {
        // Return the document URL
        return new WP_REST_Response([
            'success' => true,
            'message' => 'Document fetched successfully.',
            'document_url' => $data['url'],
        ], 200);
    } else {
        return new WP_REST_Response(['success' => false, 'message' => 'Document URL not found in the response.'], 404);
    }
}

/**
 * REST API for fetching company folio information
 */

function handle_folio_search_api(WP_REST_Request $request)
{
    // Get the parameters from the request
    $isin = sanitize_text_field($request->get_param('isin'));
    $name = sanitize_text_field($request->get_param('name'));
    $page = sanitize_text_field($request->get_param('page'));

    // Validate required parameters
    if (empty($isin) || empty($name) || empty($page)) {
        return new WP_REST_Response(['success' => false, 'message' => 'Please provide valid isin, name, and page parameters.'], 400);
    }

    // Extract the token from the Authorization header
    $auth_header = $request->get_header('Authorization');
    if (empty($auth_header)) {
        return new WP_REST_Response(['success' => false, 'message' => 'Authorization token is missing.'], 400);
    }

    // Extract the Bearer token
    $matches = [];
    if (preg_match('/Bearer\s(\S+)/', $auth_header, $matches)) {
        $token = $matches[1];
    } else {
        return new WP_REST_Response(['success' => false, 'message' => 'Invalid authorization token format.'], 400);
    }

    // Prepare the external API URL
    $api_url = 'https://tzbydeizb5.execute-api.ap-south-1.amazonaws.com/dev/v1/beetal/b2b/company/folio/search?' .
        http_build_query([
            'isin' => $isin,
            'name' => $name,
            'page' => $page,
        ]);

    // Set the headers for the external API call
    $headers = [
        'x-api-key' => 'f43a59d9-2d83-4e23-b25a-c1bb016b8a32',
        'Authorization' => 'Bearer ' . $token,
        'Content-Type' => 'application/json'
    ];

    // Send the request to the external API
    $response = wp_remote_get($api_url, [
        'headers' => $headers,
        'timeout' => 15,
    ]);

    // Check if the API call was successful
    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        error_log("❌ Folio Search API wp_remote_get error: $error_message");
        return new WP_REST_Response(['success' => false, 'message' => 'Failed to fetch folio data.'], 500);
    }

    // Decode the API response
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    // Check if the response contains necessary data
    if (isset($data['physical']) || isset($data['nsdl']) || isset($data['cdsl']) || isset($data['company'])) {
        // Return the external API response as-is
        return new WP_REST_Response([
            'success' => true,
            'message' => 'Folio data fetched successfully.',
            'data' => $data,
        ], 200);
    } else {
        return new WP_REST_Response(['success' => false, 'message' => 'Folio data not found in the response.'], 404);
    }
}

/**
 * REST API for fetching folio details for a company
 */

function handle_folio_details_api(WP_REST_Request $request)
{
     error_log('📥 Received request for folio details API.');
 
     // Parse JSON body
     $params = json_decode($request->get_body(), true);
     $folio_no = sanitize_text_field($params['folio_no'] ?? '');
     $isin = sanitize_text_field($params['isin'] ?? '');
 
     error_log('ℹ️ Request parameters: folio_no = ' . $folio_no . ', isin = ' . $isin);

    // Validate required parameters
    if (empty($folio_no) || empty($isin)) {
        error_log('⚠️ Missing folio number or ISIN in request.');
        return new WP_REST_Response([
            'success' => false,
            'message' => 'Please provide both folio number and ISIN.'
        ], 400);
    }

    // Extract the token from the Authorization header
    $auth_header = $request->get_header('Authorization');
    if (empty($auth_header)) {
        error_log('⚠️ Authorization header is missing.');
        return new WP_REST_Response([
            'success' => false,
            'message' => 'Authorization token is missing.'
        ], 400);
    }

    // Extract the Bearer token from the Authorization header
    $matches = [];
    if (preg_match('/Bearer\s(\S+)/', $auth_header, $matches)) {
        $token = $matches[1];
        error_log('✅ Authorization token extracted successfully.');
    } else {
        error_log('❌ Invalid authorization token format.');
        return new WP_REST_Response([
            'success' => false,
            'message' => 'Invalid authorization token format.'
        ], 400);
    }

    // Prepare the request URL for the external API
    $api_url = 'https://tzbydeizb5.execute-api.ap-south-1.amazonaws.com/dev/v1/beetal/b2b/folio/details?folio_no=' . urlencode($folio_no) . '&isin=' . urlencode($isin);
    error_log('🔗 External API URL: ' . $api_url);

    // Set headers for the API call
    $headers = [
        'x-api-key'    => 'f43a59d9-2d83-4e23-b25a-c1bb016b8a32',
        'Authorization' => 'Bearer ' . $token,
    ];

    // Send the request to the external API
    $response = wp_remote_get($api_url, [
        'headers' => $headers,
        'timeout' => 15,
    ]);

    // Check if the API call was successful
    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        error_log('❌ Folio Details API wp_remote_get error: ' . $error_message);
        return new WP_REST_Response([
            'success' => false,
            'message' => 'Failed to fetch folio details.'
        ], 500);
    }

    // Decode the API response
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (empty($data)) {
        error_log('❌ Empty or invalid response received from the external API.');
        error_log('📄 Raw response body: ' . $body);
        return new WP_REST_Response([
            'success' => false,
            'message' => 'Invalid or empty response from external API.'
        ], 500);
    }

    error_log('✅ Folio details fetched successfully from external API.');
    error_log('📄 Response Data: ' . print_r($data, true));

    // Return the full folio details
    return new WP_REST_Response([
        'success' => true,
        'message' => 'Folio details fetched successfully.',
        'data' => $data,
    ], 200);
}


function handle_company_dashboard_reports_api(WP_REST_Request $request)
{
    error_log('📥 Received request for company dashboard reports API.');

    // Parse JSON body
    $params = json_decode($request->get_body(), true);
    $isin_code = sanitize_text_field($params['isinCode'] ?? '');

    error_log('ℹ️ Request parameter: isinCode = ' . $isin_code);

    // Validate required parameter
    if (empty($isin_code)) {
        error_log('⚠️ Missing ISIN code in request.');
        return new WP_REST_Response([
            'success' => false,
            'message' => 'Please provide the ISIN code.'
        ], 400);
    }

    // Extract the Authorization header
    $auth_header = $request->get_header('Authorization');
    if (empty($auth_header)) {
        error_log('⚠️ Authorization token is missing.');
        return new WP_REST_Response([
            'success' => false,
            'message' => 'Authorization token is missing.'
        ], 400);
    }

    // Extract Bearer token
    $matches = [];
    if (preg_match('/Bearer\s(\S+)/', $auth_header, $matches)) {
        $token = $matches[1];
    } else {
        error_log('⚠️ Invalid authorization token format.');
        return new WP_REST_Response([
            'success' => false,
            'message' => 'Invalid authorization token format.'
        ], 400);
    }

    // Prepare external API URL
    $api_url = 'https://tzbydeizb5.execute-api.ap-south-1.amazonaws.com/dev/v1/beetal/b2b/company/dashboard/reports?isinCode=' . urlencode($isin_code);

    // Prepare headers
    $headers = [
        'x-api-key'    => 'f43a59d9-2d83-4e23-b25a-c1bb016b8a32',
        'Authorization' => 'Bearer ' . $token,
    ];

    error_log('📡 Sending request to external company dashboard reports API.');

    // Make the external API request
    $response = wp_remote_get($api_url, [
        'headers' => $headers,
        'timeout' => 15,
    ]);

    // Check if external API call failed
    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        error_log("❌ Company Dashboard Reports API wp_remote_get error: $error_message");
        return new WP_REST_Response([
            'success' => false,
            'message' => 'Failed to fetch company dashboard reports.'
        ], 500);
    }

    // Parse the external API response
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (empty($data)) {
        error_log('⚠️ Empty or invalid response from external API.');
        return new WP_REST_Response([
            'success' => false,
            'message' => 'Invalid or empty response from external API.'
        ], 500);
    }

    // Success
    error_log('✅ Successfully fetched company dashboard reports.');

    return new WP_REST_Response([
        'success' => true,
        'message' => 'Company dashboard reports fetched successfully.',
        'data' => $data,
    ], 200);
}

function handle_company_report_download_url_api(WP_REST_Request $request)
{
    error_log('📥 Received request for company report download URL API.');

    // Parse JSON body
    $params = json_decode($request->get_body(), true);
    $report_id = sanitize_text_field($params['id'] ?? '');

    error_log('ℹ️ Request parameter: id = ' . $report_id);

    // Validate required parameter
    if (empty($report_id)) {
        error_log('⚠️ Missing report ID in request.');
        return new WP_REST_Response([
            'success' => false,
            'message' => 'Please provide the report ID.'
        ], 400);
    }

    // Extract the Authorization header
    $auth_header = $request->get_header('Authorization');
    if (empty($auth_header)) {
        error_log('⚠️ Authorization token is missing.');
        return new WP_REST_Response([
            'success' => false,
            'message' => 'Authorization token is missing.'
        ], 400);
    }

    // Extract Bearer token
    $matches = [];
    if (preg_match('/Bearer\s(\S+)/', $auth_header, $matches)) {
        $token = $matches[1];
    } else {
        error_log('⚠️ Invalid authorization token format.');
        return new WP_REST_Response([
            'success' => false,
            'message' => 'Invalid authorization token format.'
        ], 400);
    }

    // Prepare external API URL
    $api_url = 'https://tzbydeizb5.execute-api.ap-south-1.amazonaws.com/dev/v1/beetal/b2b/company/reports/download-url?id=' . urlencode($report_id);

    // Prepare headers
    $headers = [
        'x-api-key'    => 'f43a59d9-2d83-4e23-b25a-c1bb016b8a32',
        'Authorization' => 'Bearer ' . $token,
    ];

    error_log('📡 Sending request to external company report download URL API.');

    // Make the external API request
    $response = wp_remote_get($api_url, [
        'headers' => $headers,
        'timeout' => 15,
    ]);

    // Check if external API call failed
    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        error_log("❌ Company Report Download URL API wp_remote_get error: $error_message");
        return new WP_REST_Response([
            'success' => false,
            'message' => 'Failed to fetch report download URL.'
        ], 500);
    }

    // Parse the external API response
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (empty($data)) {
        error_log('⚠️ Empty or invalid response from external API.');
        return new WP_REST_Response([
            'success' => false,
            'message' => 'Invalid or empty response from external API.'
        ], 500);
    }

    // Success
    error_log('✅ Successfully fetched company report download URL.');

    return new WP_REST_Response([
        'success' => true,
        'message' => 'Report download URL fetched successfully.',
        'data' => $data,
    ], 200);
}


// Register the REST route
function my_custom_company_login_api_route()
{
    register_rest_route('api/v1', '/company/login', [
        'methods'             => 'POST',
        'callback'            => 'handle_company_login_api',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('api/v1', '/company/verify-otp', [
        'methods'  => 'POST',
        'callback' => 'company_verify_otp',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('api/v1', '/company/details', [
        'methods'             => 'POST',
        'callback'            => 'fetch_company_details',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('api/v1', '/company/dashboard', [
        'methods'             => 'POST',
        'callback'            => 'fetch_company_dashboard_details',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('api/v1', '/company/dashboard/reports', [
        'methods' => 'POST',
        'callback' => 'handle_company_dashboard_reports_api',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('api/v1', '/company/dashboard/reports/download', [
        'methods' => 'POST',
        'callback' => 'handle_company_report_download_url_api',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('api/v1', '/company/document/all', [
        'methods'             => 'POST',
        'callback'            => 'fetch_company_documents',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('api/v1', '/company/document/download', [
        'methods' => 'POST',
        'callback' => 'handle_document_download_api',
        'permission_callback' => '__return_true', // Ensure proper permissions are checked
    ]);

    register_rest_route('api/v1', '/folio/search', [
        'methods' => 'POST',
        'callback' => 'handle_folio_search_api',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('api/v1', '/folio/details', [
        'methods' => 'POST',
        'callback' => 'handle_folio_details_api',
        'permission_callback' => '__return_true',
    ]);
}

add_action('rest_api_init', 'my_custom_company_login_api_route');