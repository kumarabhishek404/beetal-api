<?php
/**
 * Plugin Name: Ticket Management API
 * Description: A custom REST API for managing tickets in WordPress.
 * Version: 1.1
 * Author: Abhishek Kumar
 */

if (!defined('ABSPATH')) {
    exit; // Prevent direct access
}

// Function to add a new ticket
function add_ticket($request) {
    global $wpdb;
    $params = $request->get_json_params();

    $data = [
        'name' => sanitize_text_field($params['name'] ?? ''),
        'permalink' => sanitize_text_field($params['permalink'] ?? ''),
        'post_content' => sanitize_text_field($params['post_content'] ?? ''),
        'folio_number' => sanitize_text_field($params['folio_number'] ?? ''),
        'company' => sanitize_text_field($params['company'] ?? ''),
        'service_request' => sanitize_text_field($params['service_request'] ?? ''),
        'sub_services' => sanitize_text_field($params['sub_services'] ?? ''),
        'status' => sanitize_text_field($params['status'] ?? 'open'),
        'post_author' => sanitize_text_field($params['post_author'] ?? ''),
        'assignee' => floatval($params['assignee'] ?? 0),
        'assign_to' => sanitize_text_field($params['assign_to'] ?? ''),
        'email' => sanitize_email($params['email'] ?? ''),
        'phone' => floatval($params['phone'] ?? 0),
        'subject' => sanitize_text_field($params['subject'] ?? ''),
        'created' => current_time('mysql', 1),
        'modified' => current_time('mysql', 1)
    ];

    if (empty($data['name']) || empty($data['service_request'])) {
        return new WP_Error('missing_fields', 'Name and service request are required', ['status' => 400]);
    }

    $inserted = $wpdb->insert('wp_pods_ticket', $data);

    return $inserted ? rest_ensure_response(['message' => 'Ticket added successfully', 'ticket_id' => $wpdb->insert_id]) :
        new WP_Error('insert_failed', 'Failed to add ticket', ['status' => 500]);
}

// Function to retrieve tickets
function get_tickets($request) {
    global $wpdb;
    $id = $request->get_param('id');

    if ($id) {
        $ticket = $wpdb->get_row($wpdb->prepare("SELECT * FROM wp_pods_ticket WHERE id = %d", $id));
        return $ticket ? rest_ensure_response($ticket) : new WP_Error('not_found', 'Ticket not found', ['status' => 404]);
    }

    $tickets = $wpdb->get_results("SELECT * FROM wp_pods_ticket ORDER BY created DESC");
    return rest_ensure_response($tickets);
}

// Function to edit a ticket
function edit_ticket($request) {
    global $wpdb;
    $params = $request->get_json_params();
    $id = intval($params['id'] ?? 0);
    
    if (!$id) {
        return new WP_Error('missing_fields', 'Ticket ID is required', ['status' => 400]);
    }

    $data = [];
    foreach ([
        'name', 'folio_number', 'company', 'service_request', 'sub_services', 'status',
        'post_author', 'assign_to', 'email', 'subject'
    ] as $field) {
        if (isset($params[$field])) {
            $data[$field] = sanitize_text_field($params[$field]);
        }
    }
    if (isset($params['assignee'])) {
        $data['assignee'] = floatval($params['assignee']);
    }
    if (isset($params['phone'])) {
        $data['phone'] = floatval($params['phone']);
    }
    $data['modified'] = current_time('mysql', 1);

    $updated = $wpdb->update('wp_pods_ticket', $data, ['id' => $id]);

    return $updated ? rest_ensure_response(['message' => 'Ticket updated successfully']) :
        new WP_Error('update_failed', 'Failed to update ticket', ['status' => 500]);
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

    register_rest_route('api/v1', '/ticket/edit', [
        'methods' => 'POST',
        'callback' => 'edit_ticket',
        'permission_callback' => '__return_true',
    ]);
}

// Hook API routes into WordPress REST API
add_action('rest_api_init', 'register_ticket_api_routes');
