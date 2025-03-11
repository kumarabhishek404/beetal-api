<?php
/**
 * Plugin Name: User Authentication API
 * Description: A custom REST API to register and login users in WordPress.
 * Version: 1.1
 * Author: Abhishek Kumar
 */

if (!defined('ABSPATH')) {
    exit; // Prevent direct access
}

// Function to register or update a user based on PAN
function my_custom_register_user($request) {
    $params = $request->get_json_params();

    $pan = sanitize_text_field($params['name'] ?? ''); // PAN is used as the user_login
    $phone = sanitize_text_field($params['phone'] ?? '');
    $email = sanitize_email($params['email'] ?? '');
    $isin = sanitize_text_field($params['isin'] ?? '');
    $company_logo = sanitize_text_field($params['company_logo'] ?? '');

    // Validate required fields
    if (empty($pan) || empty($phone) || empty($email)) {
        return new WP_Error('missing_fields', 'name, phone, and email are required', ['status' => 400]);
    }

    // Validate PAN format (Standard PAN pattern)
    if (!preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', $pan)) {
        return new WP_Error('invalid_pan', 'Invalid PAN format', ['status' => 400]);
    }

    // Validate email format
    if (!is_email($email)) {
        return new WP_Error('invalid_email', 'Invalid email format', ['status' => 400]);
    }

    // Validate phone number (10-digit format)
    if (!preg_match('/^[6-9][0-9]{9}$/', $phone)) {
        return new WP_Error('invalid_mobile', 'Invalid phone number', ['status' => 400]);
    }

    // Check if a user exists with the same PAN
    $user = get_user_by('login', $pan);

    if ($user) {
        // Update existing user
        $user_id = $user->ID;
        update_user_meta($user_id, 'phone', $phone);
        update_user_meta($user_id, 'email', $email);
        update_user_meta($user_id, 'isin', $isin);
        update_user_meta($user_id, 'company_logo', $company_logo);
        wp_update_user(['ID' => $user_id, 'user_email' => $email]);

        return rest_ensure_response([
            'status' => 'success',
            'message' => 'User details updated successfully',
            'user_id' => $user_id,
        ]);
    }

    // Create new user with PAN as user_login
    $user_id = wp_insert_user([
        'user_login' => $pan, // PAN is the unique identifier
        'user_pass' => wp_generate_password(), // Admin sets password later
        'user_email' => $email,
        'display_name' => $pan, // PAN as display name
        'role' => 'subscriber',
    ]);

    if (is_wp_error($user_id)) {
        return new WP_Error('user_registration_failed', 'User registration failed', ['status' => 500]);
    }

    // Save additional user details in meta
    update_user_meta($user_id, 'phone', $phone);
    update_user_meta($user_id, 'isin', $isin);
    update_user_meta($user_id, 'company_logo', $company_logo);

    return rest_ensure_response([
        'status' => 'success',
        'message' => 'User registered successfully',
        'user_id' => $user_id,
    ]);
}

// Function to log in a user using PAN and password
function my_custom_login_user($request) {
    $params = $request->get_json_params();

    $pan = sanitize_text_field($params['pan'] ?? '');
    $password = $params['password'] ?? '';

    // Validate required fields
    if (empty($pan) || empty($password)) {
        return new WP_Error('missing_fields', 'PAN and password are required', ['status' => 400]);
    }

    // Get user by PAN (stored as user_login)
    $user = get_user_by('login', $pan);

    if (!$user || !wp_check_password($password, $user->user_pass, $user->ID)) {
        return new WP_Error('invalid_credentials', 'Invalid PAN or password', ['status' => 401]);
    }

    // Fetch all user meta
    $user_meta = get_user_meta($user->ID);

    // Convert meta fields to key-value format
    $user_data = [];
    foreach ($user_meta as $key => $value) {
        $user_data[$key] = is_array($value) ? $value[0] : $value;
    }

    // Validate company_logo as a URL (if provided)
    if (!empty($user_data['company_logo']) && !filter_var($user_data['company_logo'], FILTER_VALIDATE_URL)) {
        $user_data['company_logo'] = ''; // Reset if not a valid URL
    }

    // Construct response
    return rest_ensure_response([
        'status' => 'success',
        'message' => 'Login successful',
        'user' => array_merge([
            'id' => $user->ID,
            'name' => $user->display_name, // PAN stored as display_name
            'email' => $user->user_email,
        ], $user_data), // Merge all meta fields
    ]);
}

// Register API endpoints
function my_custom_register_api_routes() {
    register_rest_route('api/v1', '/register', [
        'methods' => 'POST',
        'callback' => 'my_custom_register_user',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('api/v1', '/login', [
        'methods' => 'POST',
        'callback' => 'my_custom_login_user',
        'permission_callback' => '__return_true',
    ]);
}

// Hook into WordPress REST API
add_action('rest_api_init', 'my_custom_register_api_routes');