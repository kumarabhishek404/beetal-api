<?php
/**
 * Plugin Name: Form Submission API
 * Description: Exposes a custom REST API endpoint to handle TDS exemption form submissions.
 * Version: 1.0
 * Author: Your Name
 */

if (!defined('ABSPATH')) {
    exit;
}


function handle_cfp_submit_tds_form_custome_fn()
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


function fetch_ipo_allotment_details_custom_fn()
{
    // Step 0: Decode input and sanitize
    $raw_input = file_get_contents("php://input");
    $body = json_decode($raw_input, true);

    $company_isin = sanitize_text_field($body['company_isin'] ?? '');
    $login_option_raw = sanitize_text_field($body['login_option'] ?? '');
    $option_value = sanitize_text_field($body['option_value'] ?? '');
    $api_key = sanitize_text_field($body['api_key'] ?? '');

    $login_option_map = [
        'application_no' => 'appl_no',
        'pan_number'     => 'acc_pan1',
        'client_id'      => 'dp_cl'
    ];

    $login_option = $login_option_map[$login_option_raw] ?? '';

    if (empty($company_isin) || empty($login_option) || empty($option_value)) {
        wp_send_json_error("Missing required fields.");
        return;
    }

    // Step 1: Get Token
    $token_url = 'https://tzbydeizb5.execute-api.ap-south-1.amazonaws.com/dev/v1/beetal/auth/login';
    $token_payload = json_encode([
        'username' => 'BTLAPP01',
        'password' => 'Beetal@123',
    ]);

    $token_response = wp_remote_post($token_url, [
        'headers' => ['Content-Type' => 'application/json'],
        'body' => $token_payload,
        'timeout' => 15,
    ]);

    if (is_wp_error($token_response)) {
        $err = $token_response->get_error_message();
        error_log("❌ Token fetch error: $err");
        wp_send_json_error("Token fetch failed: $err");
        return;
    }

    $token_data = json_decode(wp_remote_retrieve_body($token_response), true);
    if (!isset($token_data['access_token'])) {
        error_log("❌ Token missing in response: " . wp_remote_retrieve_body($token_response));
        wp_send_json_error("Failed to retrieve access token: " . json_encode($token_data));
        return;
    }

    $access_token = $token_data['access_token'];

    // Step 2: Prepare API call to IPO endpoint
    $params = [
        'company_code' => "AVL",
        "appl_no"  => "211I7759628302",
    ];

    $headers = [
        "Content-Type: application/json",
        "Authorization: Bearer " . $access_token,
        "X-API-KEY: " . $api_key
    ];

    error_log("📤 IPO Request: " . json_encode($params));
    error_log("📤 Headers: " . json_encode($headers));

    $ch = curl_init("https://tzbydeizb5.execute-api.ap-south-1.amazonaws.com/dev/v1/beetal/b2b/ipo/allotments/all");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));

    $api_response = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($api_response === false || !$api_response) {
        error_log("❌ CURL error: $curl_error");
        wp_send_json_error("Request failed: " . $curl_error);
        return;
    }

    $response_data = json_decode($api_response, true);
    error_log("📥 IPO API ($http_status): " . $api_response);

    // ✅ If API returned error message in body
    if (isset($response_data['error']) || isset($response_data['message'])) {
        $error_msg = $response_data['error'] ?? $response_data['message'];
        wp_send_json_error("External API Error: " . $error_msg);
        return;
    }

    // ❌ API success but no data
    if (empty($response_data['data'])) {
        wp_send_json_error("No allotment data found for the provided details.");
        return;
    }

    // // ✅ Filter by exact match
    // $filtered_data = array_filter($response_data['data'], function ($entry) use ($login_option, $option_value) {
    //     return isset($entry[$login_option]) && $entry[$login_option] === $option_value;
    // });

    // if (empty($filtered_data)) {
    //     wp_send_json_error("No matching records found.");
    //     return;
    // }

    // ✅ Send only the first matched record
    // $first_match = reset($filtered_data);
    wp_send_json_success($response_data['data']);
}

function get_recent_company_list_api() {
    // Check if the request method is POST.  It's good practice to restrict API endpoints to specific methods.
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        wp_send_json(array('error' => 'Invalid request method.  Use POST.'), 405); // Use 405 Method Not Allowed
        return; // IMPORTANT:  Exit after sending the response.
    }

    // Get the input data from the request body.  This is the most robust way to handle data.
    $request_body = file_get_contents('php://input');
    $data = json_decode($request_body, true); // true for associative array

    // Check if the 'type' parameter is present in the request.
    if (!isset($data['type'])) {
        wp_send_json(array('error' => 'Missing required parameter: type'), 400); // Use 400 Bad Request
        return;
    }

    $type = sanitize_text_field($data['type']); // Sanitize the input!  Always.

    // Validate the 'type' parameter.
    if ($type !== 'allotment' && $type !== 'company') {
        wp_send_json(array('error' => 'Invalid type parameter.  Use "allotment" or "company".'), 400);
        return;
    }

    // Construct the Pods query based on the 'type' parameter.
    $pods_args = array(
        'limit' => 3, //Consistent limit of 3
    );

    if ($type === 'allotment') {
        $pods_args['where'] = 'allotment_status = "active"'; // 1 for true, 0 for false in Pods boolean fields.
    } elseif ($type === 'company') {
        $pods_args['where'] = 'company_status = "active"';
    }

    $pods = pods('ipo_company')->find($pods_args);

    // Handle errors from the Pods query.  Check if $pods is a Pods object.
    if (is_object($pods) && $pods->error()) {
        $error_message = $pods->get_error_message(); //get the error message.
        wp_send_json(array('error' => 'Pods error: ' . $error_message), 500); // 500 Internal Server Error
        return;
    }
    
    // Prepare the data to be returned.
     $company_data = array();
     while ($pods->fetch()) {
        $company_data[] = $pods->row();
     }

    // Return the company data as JSON.
    wp_send_json($company_data, 200); // 200 OK
}

function get_company_detail_by_code_api() {
    // Only allow POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        wp_send_json(array('error' => 'Invalid request method. Use POST.'), 405);
        return;
    }

    // Read and decode the request body
    $request_body = file_get_contents('php://input');
    $data = json_decode($request_body, true);

    // Validate the presence of company_code
    if (!isset($data['company_code'])) {
        wp_send_json(array('error' => 'Missing required parameter: company_code'), 400);
        return;
    }

    $company_code = sanitize_text_field($data['company_code']);

    // Construct the Pods query with the given company_code
    $pods = pods('ipo_company', array(
        'where' => "company_code = \"$company_code\"",
        'limit' => 1
    ));

    // Handle Pods errors
    if (is_object($pods) && $pods->error()) {
        $error_message = $pods->get_error_message();
        wp_send_json(array('error' => 'Pods error: ' . $error_message), 500);
        return;
    }

    // Fetch and return the result
    if ($pods->total() > 0 && $pods->fetch()) {
        $company_data = array();
        $fields = $pods->fields(); // Get all field definitions for the Pod

        foreach ($fields as $field_name => $field_data) {
            $company_data[$field_name] = $pods->field($field_name);
        }
        wp_send_json($company_data, 200);
    } else {
        wp_send_json(array('error' => 'Company not found for the provided code.'), 404);
    }
}

// Register REST route
function my_custom_register_form_api_route()
{
    register_rest_route('api/v1', '/tds-form', [
        'methods' => 'POST',
        'callback' => 'handle_cfp_submit_tds_form_custome_fn',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('api/v1', '/entitlement', [
        'methods' => 'POST',
        'callback' => 'fetch_ipo_allotment_details_custom_fn',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('api/v1', '/companies', [
        'methods' => 'POST',
        'callback' => 'get_recent_company_list_api',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('api/v1', '/company/detailsByCode', array(
        'methods'  => 'POST',
        'callback' => 'get_company_detail_by_code_api',
        'permission_callback' => '__return_true' // For public access
    ));
}
add_action('rest_api_init', 'my_custom_register_form_api_route');

// Optional: Register AJAX handlers (for legacy support)
add_action('wp_ajax_handle_cfp_submit_tds_form', 'handle_cfp_submit_tds_form_custome_fn');
add_action('wp_ajax_nopriv_handle_cfp_submit_tds_form', 'handle_cfp_submit_tds_form_custome_fn');