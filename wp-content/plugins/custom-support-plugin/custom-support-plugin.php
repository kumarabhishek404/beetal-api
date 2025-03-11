<?php

/**
 * Plugin Name: Custom Support Plugin
 * Description: A fully custom support plugin for ticket management using Pods.
 * Version: 3.0.1
 * Author: Pragmaapps
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Hook into WordPress init action
add_action('init', 'csp_plugin_init');

// Initialize plugin logic
function csp_plugin_init()
{
    // Load required files
    require_once plugin_dir_path(__FILE__) . 'includes/common_functions.php';
    require_once plugin_dir_path(__FILE__) . 'includes/html_functions.php';
    require_once plugin_dir_path(__FILE__) . 'includes/fetch_api_functions.php';
    require_once plugin_dir_path(__FILE__) . 'includes/folio_info_shortcodes.php';
    require_once plugin_dir_path(__FILE__) . 'includes/company_shortcodes.php';
    require_once plugin_dir_path(__FILE__) . 'includes/ticket_shortcodes.php';
    require_once plugin_dir_path(__FILE__) . 'includes/ticket_admin_shortcode.php';
    require_once plugin_dir_path(__FILE__) . 'includes/index.php';

    // Ensure that translations are loaded at the correct time
    add_action('plugins_loaded', 'csp_load_textdomain');

    // Admin-specific actions (if any)
    if (is_admin()) {
        // Admin-specific actions go here (if needed)
    }

    // Register custom templates and enqueue assets
    add_filter('theme_page_templates', 'csp_register_templates', 10, 3);
    add_filter('template_include', 'csp_load_template', 99);
    add_action('wp_enqueue_scripts', 'csp_enqueue_assets');
    add_action('wp_ajax_submit_ticket_reply', 'handle_submit_ticket_reply');
    add_action('wp_ajax_nopriv_submit_ticket_reply', 'handle_submit_ticket_reply');
    add_action('wp_ajax_csp_submit_ticket', 'csp_process_ticket_form');
    add_action('wp_ajax_nopriv_csp_submit_ticket', 'csp_process_ticket_form');
    add_action('wp_ajax_nopriv_csp_submit_ticket', 'csp_process_ticket_form');
    add_action('wp_ajax_nopriv_my_custom_logout_action', 'my_custom_logout_action');
    add_action('wp_ajax_my_custom_logout_action', 'my_custom_logout_action');
    add_action('wp_ajax_folio_search', 'handle_folio_search');
    add_action('wp_ajax_get_date', 'handle_get_date_fn');
    add_action('wp_ajax_download_report', 'handle_download_report');
    add_action('wp_ajax_fetch_reports', 'handle_fetch_report');
    add_action('wp_ajax_delete_ticket_using_id', 'delete_ticket_using_id_fn');
    add_action('wp_ajax_edit_ticket_using_id', 'edit_ticket_using_id_fn');
    add_action('wp_ajax_request_report_generate', 'request_report_generate_fn');
    add_action('pods_admin_ui_custom_ticket', 'custom_pods_admin_ticket');

    // add_action( 'template_redirect', 'redirect_if_not_logged_in' );
}

function enqueue_select2_scripts()
{
    wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css');
    wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js', array('jquery'), null, true);
    wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
    // wp_enqueue_style('select2-bootstrap5-css', 'https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap-theme@1.1.1/dist/select2-bootstrap.min.css');
}
add_action('wp_enqueue_scripts', 'enqueue_select2_scripts');


// Load text domain for translations (if applicable)
function csp_load_textdomain()
{
    load_plugin_textdomain('csp-text-domain', false, dirname(plugin_basename(__FILE__)) . '/languages');
}

// Register custom page templates
function csp_template_array()
{
    return [
        'show-custom-support.php' => 'Support Tickets',
    ];
}

function get_description_and_equity_data($data)
{
    if (empty($data)) {
        return array(
            'descriptions' => [],
            'equities' => [],
        );;
    }
    $descriptions = array_column($data, 'Description');
    $equities = array_column($data, '%Equity');
    return array(
        'descriptions' => $descriptions,
        'equities' => $equities,
    );
}

function csp_register_templates($page_templates, $theme, $post)
{
    $templates = csp_template_array();
    return array_merge($page_templates, $templates);
}

// Load custom template if applicable
function csp_load_template($template)
{
    global $post;
    if (!$post) return $template;

    $page_template_slug = get_page_template_slug($post->ID);
    $templates = csp_template_array();

    if (isset($templates[$page_template_slug])) {
        $custom_template_path = plugin_dir_path(__FILE__) . 'templates/' . $page_template_slug;
        if (file_exists($custom_template_path)) {
            return $custom_template_path;
        }
    }

    return $template;
}

// Enqueue styles and scripts
function csp_enqueue_assets()
{
    // Enqueue Bootstrap CSS
    wp_enqueue_style('csp-bootstrap', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css');
    wp_enqueue_style('csp-style', plugin_dir_url(__FILE__) . 'assets/style.css');

    // Enqueue custom script
    wp_enqueue_script('csp-script', plugin_dir_url(__FILE__) . 'assets/script.js', ['jquery', 'chart-js'], '1.0.2', true);

    // Enqueue Bootstrap and icons
    wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
    wp_enqueue_style('bootstrap-icons', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css');
    wp_enqueue_script('bootstrap-script', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', [], null, true);
    wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', [], '4.0.0', true);
    wp_enqueue_script('bootstrap-datepicker', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/js/bootstrap-datepicker.min.js', array('jquery'), '1.10.0', true);
    wp_enqueue_style('bootstrap-datepicker', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/css/bootstrap-datepicker.min.css', array(), '1.10.0', 'all');
    // wp_enqueue_script('chartjs-plugin-datalabels', 'https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels', [], null, true);
}

add_action('admin_enqueue_scripts', 'custom_pods_admin_styles');

function custom_pods_admin_styles($hook)
{
    if (isset($_GET['page']) && strpos($_GET['page'], 'pods') !== false) {
        wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
        wp_enqueue_style('bootstrap-icons', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css');
        wp_enqueue_style('custom-pods-admin-css', plugin_dir_url(__FILE__) . 'assets/custom-pods-admin.css');
        wp_enqueue_script('custom-pods-admin-js', plugin_dir_url(__FILE__) . 'assets/custom-admin-script.js',['jquery'], '1.0.2', true);
    }
}

function redirect_if_not_logged_in()
{
    error_log("Redirecting to investor-request/#Investor_Service_Request");
?>
    <script>
        jQuery(document).ready(function($) {
            logoutUser();
        });
    </script>
<?php
}

function my_custom_logout_action()
{
    error_log($_POST['redirect_to']);
    $redirection_url = (!empty($_POST['redirect_to']) && $_POST['redirect_to'] == 'company' ) ? site_url('company-login') : site_url('investor-request/?logout=' . time() . '#Investor_Service_Request') ;
    $_SESSION['folio_search_result'] = null;
    $_SESSION['report_details'] = null;
    $_SESSION['folio_data'] = null;
    set_login_option_values(null, null, null);
    if (is_user_logged_in()) {
        wp_logout();
        wp_send_json_success(['redirect_url' => $redirection_url]);
        return;
    } else {
        wp_send_json_error(['redirect_url' => $redirection_url]);
        return;
    }
}

function remove_admin_bar()
{
    if (is_admin() && is_user_logged_in() && current_user_can('administrator')) {
        show_admin_bar(true);
    } else {
        show_admin_bar(false);
    }
}

add_filter('http_request_timeout', function () {
    return 30; // Increase timeout to 30 seconds
});

add_filter('show_admin_bar', 'remove_admin_bar', 999);

function redirect_non_logged_in_users()
{
    if (!is_user_logged_in()) {
        wp_redirect(site_url('/investor-request/#Investor_Service_Request'));
        exit;
    }
}
// add_action('template_redirect', 'redirect_non_logged_in_users');
