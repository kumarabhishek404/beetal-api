<?php

function csp_login_null_check($response)
{
    if (isset($response['statusCode']) || isset($response['errorCode'])) return true;
    if (isset($response['message']) && $response['message'] == 'Endpoint request timed out') return true;
    if (empty($response['isValidFolio'])) return true;
    if (empty($response['folios'])) return true;
    return false;
}

function csp_fetch_company_api_response($params)
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

    $company_api_response =  wp_remote_post(get_option('api_url') . 'v1/beetal/b2b/company/details', $args);

    $company_null_response = [
        'name' => 'N/A',
        'address' => 'N/A',
        'company_code' => 'N/A',
        'email' => 'N/A',
        'equity' => 'N/A',
    ];
    if (is_wp_error($company_api_response)) {
        //  if($_SESSION['api_retry_no'] <  $_SESSION['api_retry_limit'] ){
        //     return fetch_report_api_response($company, $date);
        //     $_SESSION['api_retry_no']++;
        // }else {
        //     $_SESSION['api_retry_no'] = 0;
        return $company_null_response;
        // }
    } else {
        $company_details = json_decode(wp_remote_retrieve_body($company_api_response), true);
        if (!isset($company_details['errorCode'])) {
            $company_details_array = [
                'name' => $company_details['company']['name'] ?? 'N/A',
                'address' => $company_details['company']['address'] ?? 'N/A',
                'company_code' => $company_details['company']['company_code'] ?? 'N/A',
                'email' => $company_details['company']['email'] ?? 'N/A',
                'equity' => $company_details['company']['equity'] ?? 'N/A',
                'face_value' => $company_details['company']['face_value'] ?? 'N/A',
                'depository_code' => $company_details['company']['depository_code'] ?? 'N/A',
            ];
            return $company_details_array;
        } else {
            return $company_null_response;
        }
    }
}

function csp_fetch_folio_using_pan_response()
{
    if (empty(get_current_user_id())) {
        return [];
    }
    if (!empty($_SESSION['folio_data']) && !empty($_SESSION['company_data'])) {
        return ['folios' => $_SESSION['folio_data'], 'companies' => $_SESSION['company_data']];
    }
    $user_information = pods('user', get_current_user_id());
    if(!$user_information->field('verification_state')){
        return [];
    }
    $pan = $user_information->field('pan');
    $email = $user_information->field('client_email');
    $phone = $user_information->field('phone');

    $args = [
        'body' => json_encode(['pan' => $pan, 'email' => $email, 'phone' => $phone]),
        'headers' => [
            'x-api-key' =>  get_option('api_key'),
            'Content-Type' => 'application/json',
        ],
        'method' => 'POST',
    ];

    error_log("Args: " . print_r($args, true));
    // Send the POST request

    $folio_api_response =  wp_remote_post(get_option('api_url') . 'v1/beetal/b2b/folio/validate', $args);
    // error_log("Response: " . print_r($folio_api_response, true));

    // error_log("error: " . is_wp_error($folio_api_response));
    if (is_wp_error($folio_api_response)) {

        //  if($_SESSION['api_retry_no'] <  $_SESSION['api_retry_limit'] ){
        //     return fetch_report_api_response($company, $date);
        //     $_SESSION['api_retry_no']++;
        // }else {
        //     $_SESSION['api_retry_no'] = 0;
        return [];
        // }
    } else {
        return json_decode(wp_remote_retrieve_body($folio_api_response), true);
    }
}
function csp_fetch_api_response($params)
{
    $args = [
        // 'body' => json_encode($params),
        'headers' => [
            'x-api-key' =>  get_option('api_key'),
            'Content-Type' => 'application/json',
        ],
        'method' => 'GET',
    ];

    error_log("Args: " . print_r($args, true) . print_r($params, true));
    // Send the POST request

    // $folio_api_response =  wp_remote_post(get_option('api_url') . 'v1/beetal/b2b/folio/details', $args);
    $folio_api_response =  wp_remote_post(get_option('api_url') . "v1/beetal/b2b/folio/details?folio_no=".$params['folio_no']."&isin=".$params['isin'], $args);
    error_log("Response: " . print_r($folio_api_response, true));

    if (is_wp_error($folio_api_response)) {
        return array();
    } else {
        return get_folio_information(json_decode(wp_remote_retrieve_body($folio_api_response), true));
    }
}
function convert_report_response($report)
{
    if (empty($report)) {
        return [];
    }
    // error_log("convert_report_response => " . print_r($report,true));
    $groupedData = [];

    foreach ($report as $item) {
        $reportType = $item['report_type'];

        if (!isset($groupedData[$reportType])) {
            $groupedData[$reportType] = [
                'report_type' => $reportType,
                'report_dates' => [],
                "report_ids" => [],
                "description" => $item['description']
            ];
        }

        $groupedData[$reportType]['report_dates'][] = $item['report_date'];
        $groupedData[$reportType]["report_ids"][] = $item["id"];
    }

    // Convert associative array to indexed array
    return array_values($groupedData);
}

function fetch_report_api_response($company)
{
    $report_url = (string) get_option('api_url') . "v1/beetal/b2b/company/dashboard/reports?isinCode=" . $company;
    $args = [
        // 'body' => json_encode($params),
        'headers' => [
            'x-api-key' => get_option('api_key'),
        ],
        'method' => 'GET',
    ];
    // error_log("Args: " . print_r($args, true));
    // Send the POST request
    $report_api_response = wp_remote_post($report_url, $args);
    if (is_wp_error($report_api_response)) {
        //  if($_SESSION['api_retry_no'] <  $_SESSION['api_retry_limit'] ){
        //     return fetch_report_api_response($company, $date);
        //     $_SESSION['api_retry_no']++;
        // }else {
        //     $_SESSION['api_retry_no'] = 0;
        return array();
        // }
    } else {
        $response_reports = json_decode(wp_remote_retrieve_body($report_api_response), true)['reports'];
        return convert_report_response($response_reports);
    }
}
function handle_fetch_report()
{
    $user_information = pods('user', get_current_user_id());
    $fetch_all_company_details = fetch_report_api_response($user_information->field('isin'));
    $_SESSION['report_details'] = $fetch_all_company_details;
    wp_send_json_success($fetch_all_company_details);
}

function handle_download_report()
{
    $id = (string) sanitize_text_field($_POST['id']);

    $args = [
        'headers' => [
            'x-api-key' =>  get_option('api_key'),
        ],
        'method' => 'GET',
    ];
    $download_url =  get_option('api_url') . "v1/beetal/b2b/company/reports/download-url?id=" . $id;
    // Send the POST request
    $download_url_response = wp_remote_post($download_url, $args);
    if (is_wp_error($download_url_response)) {
        wp_send_json_error($download_url_response->get_error_message());
    } else {
        $response_data_url = json_decode(wp_remote_retrieve_body($download_url_response), true);
        if (empty($response_data_url['errorCode'])) {
            error_log(print_r($response_data_url, true));
            wp_send_json_success($response_data_url['url']);
        } else {
            wp_send_json_error($response_data_url['message']);
        }
    }
}

function fetch_folio_api_response($url)
{
    // error_log("Fetching folio : " . $_SESSION['api_retry_no']);
    $args = [
        // 'body' => json_encode($params),
        'headers' => [
            'x-api-key' =>  get_option('api_key'),
        ],
        'method' => 'GET',
    ];

    error_log("Args: " . $url);
    // Send the POST request
    $folio_api_response = wp_remote_post($url, $args);
    error_log("Response: " . print_r($folio_api_response, true));

    if (is_wp_error($folio_api_response)) {
        // if($_SESSION['api_retry_no'] <  $_SESSION['api_retry_limit'] ){
        //     return fetch_folio_api_response($url);
        //     $_SESSION['api_retry_no']++;
        // }else {
        //     $_SESSION['api_retry_no'] = 0;
        return [];
        // }
    } else {
        return json_decode(wp_remote_retrieve_body($folio_api_response), true);
    }
}

function fetch_report_date_api_response($url)
{
    $args = [
        // 'body' => json_encode($params),
        'headers' => [
            'x-api-key' =>  get_option('api_key'),
        ],
        'method' => 'GET',
    ];

    // error_log("Args: " . print_r($args, true));
    // Send the POST request
    $report_date_api_response = wp_remote_post($url, $args);
    if (is_wp_error($report_date_api_response)) {
        //  if($_SESSION['api_retry_no'] <  $_SESSION['api_retry_limit'] ){
        //     return fetch_report_date_api_response($url);
        //     $_SESSION['api_retry_no']++;
        // }else {
        //     $_SESSION['api_retry_no'] = 0;
        return array();
        // }
    } else {
        return array_column(json_decode(wp_remote_retrieve_body($report_date_api_response), true), 'report_date');
    }
}

function fetch_dashboard_api_response($url)
{
    // error_log("Fetching dashboard : " . $_SESSION['api_retry_no']);
    $args = [
        'headers' => [
            'x-api-key' =>  get_option('api_key'),
        ],
        'method' => 'GET',
    ];

    // Send the POST request
    $dashboard_api_response = wp_remote_post($url, $args);
    if (is_wp_error($dashboard_api_response)) {
        // if($_SESSION['api_retry_no'] <  $_SESSION['api_retry_limit'] ){
        //     return fetch_dashboard_api_response($url);
        //     $_SESSION['api_retry_no']++;
        // }else {
        //     $_SESSION['api_retry_no'] = 0;
        return array();
        // }
    } else {
        return json_decode(wp_remote_retrieve_body($dashboard_api_response), true);
    }
}

function fetch_all_company_details($company_code)
{
    $dashboard_details = fetch_dashboard_api_response(get_option('api_url') . "v1/beetal/b2b/company/dashboard?isinCode=" . $company_code);
    if (empty($dashboard_details['company'])){
        $dashboard_details['company'] = csp_fetch_company_api_response(['isin_code' => $company_code]);
    }
    $_SESSION['report_details'] = null;
    return [
        // "company_reports_url" => fetch_report_date_api_response(get_option('api_url') . "v1/beetal/b2b/company/dashboard/report/dates?isinCode=" . $company_code),
        // "company_reports_list" => fetch_report_api_response($company_code),
        "company_dashboard" => $dashboard_details,
        // "company_details" => csp_fetch_company_api_response(['isin_code' => $company_code])
    ];
}

function handle_folio_search()
{
    unset($_SESSION['folio_search_result']);
    $_SESSION['folio_search_result'] = [];
    $user_information =  pods('user', get_current_user_id());
    if (empty($user_information->field('isin'))) {
        redirect_if_not_logged_in();
    }
    $folio_number = (string) sanitize_text_field($_POST['folio']);
    $name = (string) sanitize_text_field($_POST['name']);
    $pan = (string) sanitize_text_field($_POST['pan']);

    if (empty($folio_number) && empty($name) && empty($pan)) {
        wp_send_json_error('Please fill in atleast one of the parameters before submitting and try again.');
    }

    $url = (string) get_option('api_url') . 'v1/beetal/b2b/company/folio/search?isin=' . $user_information->field('isin');
    if ($folio_number) {
        $url = (string) $url . '&folio=' . $folio_number;
    } else if ($name) {
        $url = (string) $url . '&name=' . (string) $name;
    } else if ($pan) {
        $url = (string) $url . '&pan=' . $pan;
    }

    $response = fetch_folio_api_response($url);

    if (empty($response)) {
        // error_log("Error: " . print_r($response, true));
        wp_send_json_error('We couldn’t find any folio details based on your search. Adjust the parameters and try again.');
    } else {
        // error_log("Body: " . print_r($response, true));
        if (isset($response['statusCode']) || isset($response['errorCode'])) {
            wp_send_json_error($response['message']);
        } else {
            // error_log("Response Data: ".print_r($response, true));
            $_SESSION['folio_search_result'] = [];
            if (is_array($response)) {
                if (!empty($response['physical'])) {
                    $_SESSION['folio_search_result'] = $response['physical'];
                }
                if (!empty($response['nsdl'])) {
                    $_SESSION['folio_search_result'] = array_merge($_SESSION['folio_search_result'], $response['nsdl']);
                }
                if (!empty($response['csdl'])) {
                    $_SESSION['folio_search_result'] = array_merge($_SESSION['folio_search_result'], $response['csdl']);
                }
            }
            wp_send_json_success($response);
        }
    }
}

function handle_folio_verify_fn($url){
    $args = [
        'headers' => [
            'x-api-key' =>  get_option('api_key'),
        ],
        'method' => 'GET',
    ];

    // Send the POST request
    $dashboard_api_response = wp_remote_post($url, $args);
    // error_log($url);
    // error_log(print_r($dashboard_api_response,true));

    if (is_wp_error($dashboard_api_response)) {
        return ["status" => false];
    } else {
        $decode_response = json_decode(wp_remote_retrieve_body($dashboard_api_response), true);
        // error_log(print_r($decode_response,true));
        if(empty($decode_response) || empty($decode_response['folio_no']) || !empty($decode_response['message'])){
            return ["status" => false , "message" => $decode_response['message'] ?? 'The provided Folio number and ISIN do not match. Please verify your details and try again.'];
        } else {
            return [ 'status' => true ,'data' => $decode_response];
        }
    }
}