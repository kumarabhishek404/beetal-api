<?php
/**
 * Plugin Name: IPO Allotment API
 * Description: A custom REST API for managing IPO allotments in WordPress.
 * Version: 1.0
 * Author: Abhishek Kumar
 */

if (!defined('ABSPATH')) {
    exit; // Prevent direct access
}

// Function to add a new IPO allotment
function add_ipo_allotment($request) {
    global $wpdb;
    $params = $request->get_json_params();

    $name = sanitize_text_field($params['name'] ?? '');
    $permalink = sanitize_text_field($params['permalink'] ?? '');
    $application_number = sanitize_text_field($params['application_number'] ?? '');
    $pan_number = sanitize_text_field($params['pan_number'] ?? '');
    $client_id = sanitize_text_field($params['client_id'] ?? '');
    $amount_reference = sanitize_text_field($params['amount_reference'] ?? '');
    $company = sanitize_text_field($params['company'] ?? '');
    $alloted_shares = sanitize_text_field($params['alloted_shares'] ?? '');
    $applied_shares = sanitize_text_field($params['applied_shares'] ?? '');
    $remarks = sanitize_text_field($params['remarks'] ?? '');
    $application_amount = sanitize_text_field($params['application_amount'] ?? '');
    $order_number = sanitize_text_field($params['order_number'] ?? '');
    $created_at = current_time('mysql');
    $modified_at = current_time('mysql');

    if (empty($name) || empty($application_number) || empty($company)) {
        return new WP_Error('missing_fields', 'Name, application number, and company are required', ['status' => 400]);
    }

    $inserted = $wpdb->insert(
        'wp_pods_ipo_allotment_data',
        [
            'name' => $name,
            'permalink' => $permalink,
            'application_number' => $application_number,
            'pan_number' => $pan_number,
            'client_id' => $client_id,
            'amount_reference' => $amount_reference,
            'company' => $company,
            'alloted_shares' => $alloted_shares,
            'applied_shares' => $applied_shares,
            'remarks' => $remarks,
            'application_amount' => $application_amount,
            'order_number' => $order_number,
            'created' => $created_at,
            'modified' => $modified_at
        ],
        ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']
    );

    return $inserted ? rest_ensure_response(['message' => 'IPO allotment added successfully', 'id' => $wpdb->insert_id]) :
        new WP_Error('insert_failed', 'Failed to add IPO allotment', ['status' => 500]);
}

// Function to retrieve all IPO allotments
function get_ipo_allotments($request) {
    global $wpdb;
    $id = $request->get_param('id');

    if ($id) {
        $ipo = $wpdb->get_row($wpdb->prepare("SELECT * FROM wp_pods_ipo_allotment_data WHERE id = %d", $id));
        return $ipo ? rest_ensure_response($ipo) : new WP_Error('not_found', 'IPO allotment not found', ['status' => 404]);
    }

    $ipos = $wpdb->get_results("SELECT * FROM wp_pods_ipo_allotment_data ORDER BY created DESC");
    return rest_ensure_response($ipos);
}

// Register API routes
function register_ipo_api_routes() {
    register_rest_route('api/v1', '/ipo/add', [
        'methods' => 'POST',
        'callback' => 'add_ipo_allotment',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('api/v1', '/ipo/allotments', [
        'methods' => 'GET',
        'callback' => 'get_ipo_allotments',
        'permission_callback' => '__return_true',
    ]);
}

// Hook API routes into WordPress REST API
add_action('rest_api_init', 'register_ipo_api_routes');