<?php
/**
 * Plugin Name: Ticket Management API
 * Description: A custom REST API for managing tickets in WordPress.
 * Version: 1.4
 * Author: Abhishek Kumar
 */


 header("Access-Control-Allow-Origin: *");
 header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
 header("Access-Control-Allow-Headers: Authorization, Content-Type, x-api-key");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Fix CORS issue for WordPress REST API
 */
// function allow_all_cors() {
//     header("Access-Control-Allow-Origin: *");
//     header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
//     header("Access-Control-Allow-Credentials: true");
// }

// // Apply CORS headers to all REST API responses
// add_action('rest_api_init', function () {
//     remove_filter('rest_pre_serve_request', 'rest_send_cors_headers'); // Remove default WP CORS handling
//     add_filter('rest_pre_serve_request', function ($value) {
//         allow_all_cors();
//         return $value;
//     });
// });
// /**
//  * Handle preflight OPTIONS request to prevent CORS issues
//  */
// function handle_preflight() {
//     if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
//         allow_all_cors(); // Apply CORS headers
//         status_header(200);
//         exit();
//     }
// }
// add_action('init', 'handle_preflight');

// if (!defined('ABSPATH')) {
//     exit; // Prevent direct access
// }


function add_ticket($request) {
    global $wpdb;

    // Extract form data
    $data = [
        'subject' => sanitize_text_field($_POST['subject'] ?? ''),
        'email' => sanitize_email($_POST['email'] ?? ''),
        'phone' => sanitize_text_field($_POST['phone'] ?? ''),
        'company' => sanitize_text_field($_POST['company'] ?? ''),
        'folio_number' => sanitize_text_field($_POST['folio_number'] ?? ''),
        'sub_services' => sanitize_text_field($_POST['request_type'] ?? ''),
        'post_content' => sanitize_textarea_field($_POST['description'] ?? ''),
        'status' => 'new',
        'created' => current_time('mysql', 1),
        'modified' => current_time('mysql', 1),
        'files' => json_encode([]), // Store as an empty JSON array by default
    ];

    // Define upload directory
    $upload_dir = wp_upload_dir();
    $custom_upload_dir = rtrim($upload_dir['basedir'], '/') . '/2025/04';
    $custom_upload_url = rtrim($upload_dir['baseurl'], '/') . '/2025/04';

    // Ensure directory exists
    if (!file_exists($custom_upload_dir)) {
        wp_mkdir_p($custom_upload_dir);
    }

    // Debugging: Log raw $_FILES
    error_log("[DEBUG] Raw Files: " . print_r($_FILES, true));

    // Check if files exist
    if (!empty($_FILES['files']['name'][0])) {
        $uploaded_files = [];

        // Normalize files array
        $files = [];
        foreach ($_FILES['files'] as $key => $values) {
            foreach ($values as $index => $value) {
                $files[$index][$key] = $value;
            }
        }

        // Debugging: Log normalized files array
        error_log("[DEBUG] Normalized Files: " . print_r($files, true));

        // Process each file
        foreach ($files as $file) {
            $filename = sanitize_file_name($file['name']);
            error_log("[INFO] Processing file: $filename");

            // Validate file before processing
            if ($file['error'] !== UPLOAD_ERR_OK) {
                error_log("[ERROR] File upload error: " . $file['error']);
                continue;
            }

            // Generate a unique filename
            $file_ext = pathinfo($filename, PATHINFO_EXTENSION);
            $unique_filename = uniqid() . '.' . $file_ext;
            $target_file = $custom_upload_dir . '/' . $unique_filename;
            $target_url = $custom_upload_url . '/' . $unique_filename;

            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $target_file)) {
                $clean_url = esc_url_raw($target_url);
                $uploaded_files[] = $clean_url;
                error_log("[SUCCESS] File uploaded: $clean_url");
            } else {
                error_log("[ERROR] Failed to move file: $filename");
            }
        }

        // Store file URLs in the database as a JSON array
        if (!empty($uploaded_files)) {
            $data['files'] = json_encode($uploaded_files, JSON_UNESCAPED_SLASHES);
            error_log("[INFO] JSON stored in DB: " . $data['files']);
        }
    } else {
        error_log("[ERROR] No valid files detected.");
    }

    // Insert into database
    $inserted = $wpdb->insert('wp_pods_ticket', $data);

    if ($inserted) {
        error_log("[SUCCESS] Ticket added with ID: " . $wpdb->insert_id);
        return rest_ensure_response(['message' => 'Ticket added successfully', 'ticket_id' => $wpdb->insert_id]);
    } else {
        error_log("[ERROR] Database insert failed");
        return new WP_Error('insert_failed', 'Failed to add ticket', ['status' => 500]);
    }
}

// Function to retrieve all tickets
function get_tickets($request) {
    global $wpdb;
    $tickets = $wpdb->get_results("SELECT * FROM wp_pods_ticket ORDER BY created DESC");
    return rest_ensure_response($tickets);
}

// Function to get full details of a specific ticket
function get_ticket_detail($request) {
    global $wpdb;
    $id = intval($request->get_param('id'));

    if (!$id) {
        return new WP_Error('missing_fields', 'Ticket ID is required', ['status' => 400]);
    }

    $ticket = $wpdb->get_row($wpdb->prepare("SELECT * FROM wp_pods_ticket WHERE id = %d", $id), ARRAY_A);

    // Decode JSON files field to an array
    if ($ticket && isset($ticket['files'])) {
        $ticket['files'] = json_decode($ticket['files'], true) ?? [];
    }

    return $ticket ? rest_ensure_response($ticket) : new WP_Error('not_found', 'Ticket not found', ['status' => 404]);
}

// Function to edit a ticket
function edit_ticket($request) {
    global $wpdb;

    // Extract ticket ID
    $id = intval($_POST['id'] ?? 0);
    if (!$id) {
        return new WP_Error('missing_fields', 'Ticket ID is required', ['status' => 400]);
    }

    // Prepare data for update
    $data = [
        'subject'       => sanitize_text_field($_POST['subject'] ?? ''),
        'email'         => sanitize_email($_POST['email'] ?? ''),
        'phone'         => sanitize_text_field($_POST['phone'] ?? ''),
        'company'       => sanitize_text_field($_POST['company'] ?? ''),
        'folio_number'  => sanitize_text_field($_POST['folio_number'] ?? ''),
        'sub_services'  => sanitize_text_field($_POST['request_type'] ?? ''),
        'post_content'  => sanitize_textarea_field($_POST['description'] ?? ''),
        'modified'      => current_time('mysql', 1),
    ];

    // Define upload directory
    $upload_dir = wp_upload_dir();
    $custom_upload_dir = rtrim($upload_dir['basedir'], '/') . '/2025/04';
    $custom_upload_url = rtrim($upload_dir['baseurl'], '/') . '/2025/04';

    // Ensure the upload directory exists
    if (!file_exists($custom_upload_dir)) {
        wp_mkdir_p($custom_upload_dir);
    }

    // 🟢 STEP 1: Fix Old Files Handling (Convert CSV string to array)
    $old_files = isset($_POST['oldFiles']) ? array_map('trim', explode(',', $_POST['oldFiles'])) : [];
    error_log("[DEBUG] Old files array: " . json_encode($old_files));

    // 🟢 STEP 2: Process New File Uploads
    $uploaded_files = [];
    if (!empty($_FILES['newFiles']['name'][0])) {
        $files = [];
        foreach ($_FILES['newFiles'] as $key => $values) {
            foreach ($values as $index => $value) {
                $files[$index][$key] = $value;
            }
        }

        foreach ($files as $file) {
            $filename = sanitize_file_name($file['name']);
            error_log("[INFO] Processing file: $filename");

            if ($file['error'] !== UPLOAD_ERR_OK) {
                error_log("[ERROR] File upload error: " . $file['error']);
                continue;
            }

            // Generate unique filename
            $file_ext = pathinfo($filename, PATHINFO_EXTENSION);
            $unique_filename = uniqid() . '.' . $file_ext;
            $target_file = $custom_upload_dir . '/' . $unique_filename;
            $target_url = $custom_upload_url . '/' . $unique_filename;

            if (move_uploaded_file($file['tmp_name'], $target_file)) {
                $clean_url = esc_url_raw($target_url);
                $uploaded_files[] = $clean_url;
                error_log("[SUCCESS] File uploaded: $clean_url");
            } else {
                error_log("[ERROR] Failed to move file: $filename");
            }
        }
    }

    // 🟢 STEP 3: Merge Old & New Files and Update DB
    $data['files'] = json_encode(array_merge($old_files, $uploaded_files), JSON_UNESCAPED_SLASHES);
    error_log("[INFO] JSON stored in DB: " . $data['files']);

    // Perform the database update
    $updated = $wpdb->update('wp_pods_ticket', $data, ['id' => $id]);

    if ($updated !== false) {
        error_log("[SUCCESS] Ticket updated with ID: $id");
        return rest_ensure_response(['message' => 'Ticket updated successfully']);
    } else {
        error_log("[ERROR] Database update failed for Ticket ID: $id");
        return new WP_Error('update_failed', 'Failed to update ticket', ['status' => 500]);
    }
}

// Register API routes
function register_ticket_api_routes() {
    register_rest_route('api/v1', '/ticket/add', [
        'methods' => 'POST',
        'callback' => 'add_ticket',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('api/v1', '/tickets', [
        'methods' => 'GET',
        'callback' => 'get_tickets',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('api/v1', '/ticket/detail', [
        'methods' => 'GET',
        'callback' => 'get_ticket_detail',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('api/v1', '/ticket/edit', [
        'methods' => 'POST',
        'callback' => 'edit_ticket',
        'permission_callback' => '__return_true',
    ]);
}

// Hook API routes into WordPress REST API
add_action('rest_api_init', 'register_ticket_api_routes');