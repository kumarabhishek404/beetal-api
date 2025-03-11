<?php

function count_ticket_replies_shortcode($ticket_id)
{

    $reply_pod = pods('ticketreply');
    $reply_count = $reply_pod->find(array(
        'where' => 'ticket_id =' . $ticket_id
    ))->total();

    return $reply_count;
}

function csp_get_company_name_by_id($company_id)
{
    if (!$company_id) {
        return 'N/A';
    }
    $company = pods('company')->find(['where' => 'isin_codes ="' . $company_id . '"','limit' => 1]);
    $company_data = $company->data();
    // error_log('Company ' . print_r($company_data,true));
    if (!empty($company_data)) {
        return esc_html($company->field('name'));
    }
    return 'Unknown Company';
}

function company_full_address($company_info)
{
    $company_address = '';
    if (!empty($company_info['address'])) {
        $company_address .= $company_info['address'];
    }
    if (!empty($company_info['city'])) {
        $company_address .= ', ' . $company_info['city'];
    }
    if (!empty($company_info['state_name'])) {
        $company_address .= ', ' . $company_info['state_name'];
    }
    if (!empty($company_info['country_name'])) {
        $company_address .= ', ' . $company_info['country_name'];
    }
    return empty($company_address) ? 'N/A' : $company_address;
}

function csp_address_array_to_string($address)
{
    error_log('csp_address_array_to_string' . $address);
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

    return rtrim($formattedAddress, ', ') ?? "N/A";
}

function get_folio_information($folio_Info)
{
    if (empty($folio_Info['folio_no'])) {
        return [];
    }
    return [
        'folio_number' => $folio_Info['folio_no'],
        'name' =>  reset($folio_Info['holders'])['name'] ?? 'N/A',
        'address_list' => $folio_Info['addresses'],
        'bank_details' => [
            [
                'holder_name' => reset($folio_Info['holders'])['name'] ?? 'N/A',
                'account_number' => $folio_Info['bank_details']['account_no'] ?? 'N/A',
                'bank_name' => $folio_Info['bank_details']['bank_name'] ?? 'N/A',
                'ifsc' => $folio_Info['bank_details']['ifsc_code']  ?? 'N/A',
            ]
        ],
        'holders' => $folio_Info['holders'] ?? [],
        'nominees' => $folio_Info['nominees'] ?? [],
        'guardian' => [
            'name' => $folio_Info['guardians_name'] ?? 'N/A',
            'pan' => $folio_Info['pan_of_guardian'] ?? 'N/A',
            'uuid' => $folio_Info['uid_of_guardian'] ?? 'N/A',
            'relation' => reset($folio_Info['nominees'])['relationship_description'] ?? 'N/A',
        ],
        'kyc_status' => $folio_Info['is_kyc_completed'] ? 'KYC Completed' : 'KYC Incomplete',
        'address' => csp_address_array_to_string($folio_Info['addresses']['primary'])  ?? 'N/A',
        'phone' => empty($folio_Info['addresses']['primary']['mobile']) ? 'N/A' : $folio_Info['addresses']['primary']['mobile'],
        'pan_number' => reset($folio_Info['holders'])['pan'] ?? 'N/A',
        'email' => $folio_Info['primary_email'] ?? 'N/A',
        'shares' => $folio_Info['total_holding'] ?? 'N/A',
        'source_type' => $folio_Info['source_type'] ?? 'N/A',
        'description' => $folio_Info['description']  ?? 'N/A',
        "company" => [
            'name' => $folio_Info['company']['name'] ?? 'N/A',
            'address' => $folio_Info['company']['address'] ?? 'N/A',
            'company_code' => $folio_Info['company']['company_code'] ?? 'N/A',
            'email' => $folio_Info['company']['email'] ?? 'N/A',
            'equity' => $folio_Info['company']['equity'] ?? 'N/A',
        ],
        "certificates" => $folio_Info['certificates'] ?? []
    ];
}