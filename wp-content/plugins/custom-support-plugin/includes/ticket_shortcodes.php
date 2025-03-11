<?php

function array_filter_folio_no_fn($data_array, $key)
{
    $found_data = [];
    foreach ($data_array as $data) {
        if ($data["isin_code"] === $key) {
            $found_data = $data;
            break;
        }
    }
    return ['folio_no' => $found_data['folio_no'], 'total_holding' => $found_data['total_holding'], 'source_type' => $found_data['source_type']];
}

function company_table_dashboard($companies_data, $folio_data)
{

    if (empty($companies_data)) {
        return "<div class='alert alert-danger'>
            No Data Available
        </div>";
    }
    error_log(print_r($companies_data,true));
    ob_start();
?>

    <table class="tax-forms-table form-container m-0 d-table">
        <tr class="table-head">
            <th class="text-center col-1">#</th>
            <th class="col-8">Company Name</th>
            <th class="text-center col-1">Holding</th>
            <th class="text-center col-1">Type</th>
            <th class="col-1">Actions</th>
        </tr>
        <?php foreach ($companies_data as $index => $company): ?>
            <?php $extracted_folio_data = array_filter_folio_no_fn($folio_data, $company['isin_code']); ?>
            <tr>
                <td class="text-center col-1"><?= ++$index; ?></td>
                <td class="col-8"><?= $company['name'] ?></td>
                <td class="text-center col-1"><?= $extracted_folio_data['total_holding'] ?></td>
                <td class="text-center col-1"><?= $extracted_folio_data['source_type'] ?></td>
                <td class="text-center col-1">
                    <div class="btn-group" role="group" aria-label="Actions">
                        <a href="?tab=submit_ticket&action=new&company=<?= $company['isin_code'] ?>&folio=<?= $extracted_folio_data['folio_no'] ?>#Investor_Service_Request" class="btn btn-sm btn-warning" title="Edit Ticket">
                            <span class="d-flex"><i class="bi bi-pencil-square me-1"></i><span class="d-none d-md-flex">Request</span></span>
                        </a>
                        <a href="?tab=folio_information&company=<?= $company['isin_code'] ?>&folio=<?= $extracted_folio_data['folio_no'] ?>#Investor_Service_Request" class="btn btn-sm btn-info" title="View Ticket">
                            <span class="d-flex"><i class="bi bi-eye me-1"></i><span class="d-none d-md-flex">View</span></span>
                        </a>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

<?php
    return ob_get_clean();
}

function fetch_sub_request_type_fn($code)
{
    $sub_request_array = [];
    $form_array = [];
    $sub_request_pods = pods('request_type')->find(
        [
            'where' => 'request_type = "Sub Request" and request_code = "' . $code . '"',
            'limit' => -1
        ]
    );

    if ($sub_request_pods->total() > 0):
        while ($sub_request_pods->fetch()):
            $form_request_pods = pods('form_download')->find(
                [
                    'where' => 'request_type.name = "' . $sub_request_pods->field('name') . '"',
                    'limit' => -1
                ]
            );
            // error_log(print_r($form_request_pods,true));
            if ($form_request_pods->total() > 0):
                while ($form_request_pods->fetch()):
                    array_push(
                        $form_array,
                        [
                            "name" => $form_request_pods->field('form_title'),
                            "url" => empty($form_request_pods->field('form_url')['grep']) ? '#' : $form_request_pods->field('form_url')['grep'],
                            "description" => $form_request_pods->field('form_description')
                        ]
                    );
                endwhile;
            endif;
            array_push($sub_request_array, ['type' => $sub_request_pods->field('name'), 'upload_forms' => $form_array]);
            $form_array = [];
        endwhile;
    endif;
    return ['sub_type' => $sub_request_array];
}

function fetch_request_type_fn()
{
    $request_array = [];
    $request_pods = pods('request_type')->find(
        [
            'where' => 'request_type = "Main Request"',
            'limit' => -1
        ]
    );
    if ($request_pods->total() > 0):
        while ($request_pods->fetch()):
            array_push($request_array, array_merge([
                "request_type" => $request_pods->field('name')
            ], fetch_sub_request_type_fn($request_pods->field('request_code'))));
        endwhile;
    endif;
    return $request_array;
}

function csp_process_ticket_form()
{
    // Process form data
    $folio_number =
        sanitize_text_field($_POST['folio_number']);
    $company
        = sanitize_text_field($_POST['folio_company']);
    $service_request = sanitize_text_field($_POST['service_request']);
    $sub_services = sanitize_text_field($_POST['sub_services']);
    $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'new';
    $modified = sanitize_text_field($_POST['modified']);
    $edit_ticket_id = sanitize_text_field($_POST['edit']) ?? 0;
    $subject = sanitize_text_field($_POST['subject']);
    $name = "";
    $email = "";
    $phone = "";

    // error_log($subject);

    $folio_url = get_option('api_url') . 'v1/beetal/b2b/folio/details?folio_no=' . $folio_number . '&isin=' . $company;
    $verify_folio =  handle_folio_verify_fn($folio_url);
    error_log("Verify_folio" . print_r($verify_folio, true));
    if (empty($verify_folio['status']) || !$verify_folio['status']) {
        error_log("Verify_folio mid");
        wp_send_json_error($verify_folio['message']);
        // return;
    } else {
        error_log("Verify_folio last");

        if (!empty(get_current_user_id())) {
            $user_information = pods('user', get_current_user_id());
            $name = $user_information->field('display_name');
            $email = $user_information->field('client_email');
            $phone = $user_information->field('phone');
        } else {
            $name = $_SESSION['pan'];
            $email = $_SESSION['email_id'];
            $phone = $_SESSION['phone_number'];
        }
        $uploaded_files = [];
        // $previous_ticket_where = ['where' => 'service_request = "' . $service_request . '" and sub_services IN ("' . $sub_services . '","null","") and status != "closed"'];
        // if (pods('ticket')->find($previous_ticket_where)->total() > 0) {
        //     wp_send_json_error('Duplicate request detected. Please check your previous submissions before trying again.');
        // }

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
                        // error_log("File upload error: " . $upload['error']);
                        continue;
                    }
                }
            }
        }
        // error_log("Uploaded Files => " . print_r($uploaded_files, true));

        if ($edit_ticket_id > 0) {
            $pods = pods('ticket', $edit_ticket_id);
            if ($pods->exists()) {
                $pods->save('post_content', sanitize_textarea_field($_POST['ticket_description']));
                $pods->save('name', $name);
                $pods->save('folio_number', $folio_number);
                $pods->save('company', $company);
                $pods->save('service_request', $service_request);
                $pods->save('sub_services', $sub_services);
                $pods->save('files', json_encode($uploaded_files));
                $pods->save('modified', $modified);
                $pods->save('subject', $subject);
                wp_send_json_success(['message' => 'Request updated! Track your request in the dashboard.', 'redirect_url' => site_url('investor-request?tab=dashboard#Investor_Service_Request')]);
                return;
            } else {
                wp_send_json_error('Failed to update the request. Please check your input and try again.');
                return;
            }
        } else {
            // Save the ticket in Pods or the database
            $ticket_id = pods('ticket')->add([
                'post_content' => sanitize_textarea_field($_POST['ticket_description']),
                'name' => $name,
                'folio_number' => $folio_number,
                'company' => $company,
                'service_request' => $service_request,
                'sub_services' => $sub_services,
                'status' => $status,
                'email' => $email,
                'phone' => $phone,
                'files' => json_encode($uploaded_files),
                'post_author' => get_current_user_id(),
                'assignee' => get_current_user_id(),
                'modified' => $modified,
                'subject' => $subject
            ]);
            // error_log("Ticket Id => " . print_r($ticket_id, true));


            // Redirect with success or failure
            if ($ticket_id) {
                wp_send_json_success(['message' => 'Request submitted! Your ticket ID is ' . $ticket_id . '. Track your request in the dashboard.', 'redirect_url' => site_url('investor-request?tab=dashboard#Investor_Service_Request')]);
                return;
                // wp_redirect(add_query_arg(['success' => 1, 'ticket_id' => $ticket_id], wp_get_referer()));
            } else {
                wp_send_json_error('Failed to create the request. Please check your input and try again.');
                return;
            }
        }
    }
}

function handle_submit_ticket_reply()
{
    // Check if required fields are set
    if (!isset($_POST['ticket_id']) || !isset($_POST['reply_content'])) {
        wp_send_json_error('Oops! Your reply cannot be empty. Please enter a response.');
        return;
    }

    $ticket_id = intval($_POST['ticket_id']);
    $reply_content = sanitize_textarea_field($_POST['reply_content']);

    // Insert the reply into the 'reply' Pod
    $reply_pod = pods('ticketreply');
    $new_reply_id = $reply_pod->add(array(
        'ticket_id' => $ticket_id, // Relationship to ticket
        'reply_content' => $reply_content,
        'user_name' => wp_get_current_user()->display_name, // Logged-in user's name
        'user_id' => wp_get_current_user()->ID, // Logged-in user's id
        'created_on' => current_time('mysql'), // Current timestamp
    ));

    if (!$new_reply_id) {
        wp_send_json_error("Oops! Something went wrong. Your reply couldn't be saved. Please try again.");
        return;
    }

    $fetch_all_replies = $reply_pod->find([
        'where' => (string) 'ticket_id = ' . $ticket_id,
        'orderby' => 'created DESC',
        'limit' => -1
    ]);

    // Redirect back to the ticket page with success message
    wp_send_json_success(['message' => 'Your response has been saved successfully.', 'replies' => $fetch_all_replies->data(), 'current_user' => get_current_user_id()]);
    return;
}

function show_assignee_info_fn($asignee_id)
{
    if ($asignee_id) {
        $user_information = pods('user', $asignee_id);
        // error_log('show_assignee_info' . print_r($user_information, true));
        $background_list = ['bg-warning', 'bg-danger', 'bg-success'];
    }

?>
    <?php if (empty($asignee_id)) : ?>
        <div class="d-flex justify-content-center justify-content-md-start">
            <span class="bg-info rounded-circle me-2" style="min-height: 20px; min-width: 20px; max-height: 20px; max-width: 20px;"></span>
            Not Assigned yet
        </div>
    <?php else : ?>
        <div class="d-flex justify-content-center align-items-center justify-content-md-start">
            <span class="<?= $background_list[array_rand($background_list)] ?> rounded-circle me-2" style="min-height: 20px; min-width: 20px; max-height: 20px; max-width: 20px;">
            </span>
            <?= $user_information->field('display_name') ?>
        </div>
    <?php endif; ?>
<?php
}

add_shortcode('csp_ticket_table_list', function ($atts) {
    $search_term = $atts['search_term'] ?? '';
    // Query tickets from the Pods
    $page_size = 10; // Adjust the page size as needed
    $page_number = 1; // Adjust the page number as needed

    $tickets = pods('ticket')->find([
        'limit' => $page_size,
        'offset' => ($page_number - 1) * $page_size,
        'where' => 'name LIKE "%' . $search_term . '%" AND (assignee = ' . get_current_user_id() . ' OR assign_to = ' . get_current_user_id() . ')',
        'orderby' => 'created DESC'
    ]);

    // Check if tickets exist
    if (!$tickets->total()) {
        return '<div class="alert alert-info text-center mt-3">No tickets found.</div>';
    }
    ob_start();
?>
    <div class="table-responsive">
        <table class="tax-forms-table form-container m-0 d-table">
            <tr class="table-head">
                <th class="text-center col-1">#
                </th>
                <th class="col-5 col-md-4">
                    Request
                </th>
                <th class="col-4">Sub Request</th>
                <th class="col-2 col-md-1 text-center">Status</th>
                <th class="col-3 col-md-1 text-center">Posted On</th>
                <th class="col-2 col-md-1 text-center">Actions</th>
            </tr>
            <?php
            $sno = 1;
            while ($tickets->fetch()) {
                $ticket_id = $tickets->field('id');
                $name = esc_html($tickets->field('service_request')) ?? '';
                $status = esc_html($tickets->field('status')) ?? '';
                $created_on_date = esc_html($tickets->field('created')) ?? '';
                $created_on = date('d M, Y', strtotime($created_on_date));
                $company = esc_html($tickets->field('post_content'));
                $sub_services = esc_html($tickets->field('sub_services'));
                $status_badge = '';

                switch ($status) {
                    case 'new':
                        $status_badge = '<span class="badge bg-primary">New</span>';
                        break;
                    case 'New':
                        $status_badge = '<span class="badge bg-primary">New</span>';
                        break;
                    case 'in-progress':
                        $status_badge = '<span class="badge bg-warning text-dark">In Progress</span>';
                        break;
                    case 'resolved':
                        $status_badge = '<span class="badge bg-success">Resolved</span>';
                        break;
                    default:
                        $status_badge = '<span class="badge bg-secondary">Unknown</span>';
                        break;
                }

                // Build the action buttons
                $actions = '
        <div class="btn-group" role="group" aria-label="Actions">
            <a href="?tab=submit_ticket&action=edit&ticket_id=' . $ticket_id . '#Investor_Service_Request" class="btn btn-sm btn-warning" title="Edit Ticket">
                <span class="d-flex"><i class="bi bi-pencil-square me-1"></i></span>
            </a>
            <a href="?tab=information&ticket_id=' . $ticket_id . '#Investor_Service_Request" class="btn btn-sm btn-info" title="View Ticket">
                <span class="d-flex"><i class="bi bi-eye me-1"></i></span>
                <span class="position-absolute start-100 translate-middle badge rounded-circle bg-danger" style="top: 2px;">
                    ' . count_ticket_replies_shortcode($ticket_id) . '
                    <span class="visually-hidden">unread messages</span>
                </span>
            </a>
            
        </div>';
                $output_html =  '<tr>';
                $output_html .= '<td class="text-center col-1">' . $sno . ' </td>';
                $output_html .= ' <td class="col-5 col-md-3">' . $name . '</td>';
                $output_html .= '<td class="col-4">' . $sub_services . '</td>';
                $output_html .= ' <td class="col-2 col-md-1 text-center">' . $status_badge . '</td>';
                $output_html .= ' <td class="col-3 col-md-2 text-center">' . $created_on . '</td>';
                $output_html .= ' <td class="col-2 col-md-1 text-center">' . $actions . '</td>';
                $output_html .= ' </tr>';
                $sno++;
                // error_log($output_html);
                echo $output_html;
            }
            ?>
        </table>
    </div>
<?php
    return ob_get_clean();
});

add_shortcode(
    'csp_ticket_list2',
    function () {
        $dashboard_data = csp_fetch_folio_using_pan_response();
        error_log(print_r($dashboard_data, true));
        $tickets = pods('ticket')->find([
            'limit' => '-1',
            'where' => 'assignee = ' . get_current_user_id() . ' OR assign_to = ' . get_current_user_id(),
        ]);
        $pending_tickets = pods('ticket')->find([
            'limit' => '-1',
            'where' => 'status != "closed" AND ( assignee = ' . get_current_user_id() . ' OR assign_to = ' . get_current_user_id() . ')'
        ]);
        ob_start();
?>
    <div class="container my-5">
        <div class="row g-4">
            <!-- Activities Card -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header p-3 d-flex justify-content-start">
                        <h5 class="m-0">Activities</h5>
                    </div>
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span>TOTAL REQUEST</span>
                            <span class="badge bg-success px-3 py-2"><?= $tickets->total() ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span>OVERDUE REQUEST</span>
                            <span class="badge bg-info px-3 py-2"><?= $tickets->total() - $pending_tickets->total() ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span>PENDING REQUEST</span>
                            <span class="badge bg-warning text-dark px-3 py-2"><?= $pending_tickets->total() ?></span>
                        </div>
                    </div>
                    <div class="card-footer px-3 py-2 d-flex justify-content-end">
                        <a href="?tab=activities" class="btn btn-info w-10">Show All</a>
                    </div>

                </div>
            </div>

            <!-- Companies Serviced Card -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header p-3 d-flex justify-content-start">
                        <h5 class="m-0">Companies</h5>
                    </div>
                    <div class="card-body p-3">
                        <?= company_table_dashboard($dashboard_data['companies'], $dashboard_data['folios']); ?>
                    </div>
                    <div class="card-footer px-3 py-2 d-flex justify-content-end">
                        <button class="btn btn-info w-10">Show All</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
        return ob_get_clean();
    }
);

add_shortcode('csp_ticket_list', function () {
    $user_information = pods('user', get_current_user_id());
    error_log(print_r($user_information, true));
    $search_term = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
    $dashboard_data = csp_fetch_folio_using_pan_response();
    $tickets = pods('ticket')->find([
        'limit' => '-1',
        'where' => 'assignee = ' . get_current_user_id() . ' OR assign_to = ' . get_current_user_id(),
    ]);
    $pending_tickets = pods('ticket')->find([
        'limit' => '-1',
        'where' => 'status != "closed" AND ( assignee = ' . get_current_user_id() . ' OR assign_to = ' . get_current_user_id() . ')'
    ]);
    ob_start();
?>

    <!-- Table Structure -->
    <div class="ticket-list-container p-2 px-4 px-md-2">

        <div class="card shadow-sm border mb-4">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div class="card-section text-center col-4" style="border-right: 2px solid #80808075;">
                    <p class="mb-1 text-secondary d-flex justify-content-center">TOTAL <span class="d-none d-md-block ms-1">REQUEST</span></p>
                    <p class="h3 mb-0"><?= $tickets->total() ?></p>
                </div>
                <div class="card-section text-center col-4" style="border-right: 2px solid #80808075;">
                    <p class="mb-1 text-secondary d-flex justify-content-center">OVERDUE <span class="d-none d-md-block ms-1">REQUEST</span></p>
                    <p class="h3 mb-0">0</p>
                </div>
                <div class="card-section text-center col-4">
                    <p class="mb-1 text-secondary d-flex justify-content-center">PENDING <span class="d-none d-md-block ms-1">REQUEST</span></p>
                    <p class="h3 mb-0"><?= $pending_tickets->total() ?></p>
                </div>
            </div>
        </div>
        <?php if (empty(get_current_user_id()) || !$user_information->field('verification_state')) : ?>
            <div class="alert alert-danger mb-4">
                <i class="bi bi-exclamation-triangle me-1"></i>
                KYC Pending! You are logged in with temporary access.
            </div>
        <?php endif; ?>
        <!-- Search Filter Form -->
        <!-- <div class="btn-info shadow mt-4 py-3 border-0 mb-3 radius-top">
            <div class="px-4 row align-items-center">
                <div class="col-md-6 d-flex text-white align-items-center">

                </div>
                <div class="col-md-6">
                    <div class="input-group row" style="margin: 0 auto;">
                        <input id="search-params" type="text" class="form-control col-md-10" placeholder="Search tickets..." aria-label="Search tickets" aria-describedby="search-icon"
                            value="<?= $search_term ?>">
                        <button id="search-clicked" class="btn btn-warning col-md-2" type="button" id="search-icon">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
            </div>
            <hr class="mt-3">
            <div class="d-flex px-2">
                <div class="col-1 d-flex justify-content-center" style="border-right: 1px solid gray;">S.No</div>
                <div class="col-3 d-flex justify-content-center" style="border-right: 1px solid gray;">Name</div>
                <div class="col-3 d-flex justify-content-center" style="border-right: 1px solid gray;">Company</div>
                <div class="col-2 d-flex justify-content-center" style="border-right: 1px solid gray;">Status</div>
                <div class="col-2 d-flex justify-content-center" style="border-right: 1px solid gray;">Created</div>
                <div class="col-1 d-flex justify-content-center">Actions</div>
            </div>
        </div> -->

        <!-- Tickets Table -->
        <?php echo do_shortcode('[csp_ticket_table_list]') ?>
        <?php if (is_user_logged_in() && $user_information->field('verification_state')) : ?>
            <div class="col-md-12 my-4">
                <h5 class="m-0">Companies</h5>
                <hr class="mb-4">
                <div class="table-responsive">
                    <?= company_table_dashboard($dashboard_data['companies'], $dashboard_data['folios']); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

<?php
    return ob_get_clean();
});

add_shortcode('csp_ticket_form', function () {
    $id = isset($_GET['ticket_id']) ? sanitize_text_field($_GET['ticket_id']) : 0;
    $company = isset($_GET['company']) ? sanitize_text_field($_GET['company']) : '';
    $folio = isset($_GET['folio']) ? sanitize_text_field($_GET['folio']) : '';
    $email = $_SESSION['email_id'];
    $phone = $_SESSION['phone_number'];
    $request = isset($_GET['request_type']) ? sanitize_text_field($_GET['request_type']) : '';
    $sub_request = '';
    $files = '';
    $desc = '';
    $subject = '';

    if (!empty($id) && $id > 0) {
        $pods = pods('ticket', $id);
        error_log('Edit Tickets: ' . print_r($pods->row(), true));
        if (empty($pods->row())) {
            echo "<div class='mx-md-5 mx-4 alert alert-danger mt-4'>
            No Data Available
        </div>";
            return;
        }
        $email = $pods->field('email');
        $phone = $pods->field('phone');
        $request = $pods->field('service_request');
        $sub_request = $pods->field('sub_services');
        $files = decode_string($pods->field('files'));
        $desc = $pods->field('post_content');
        $company = $pods->field('company');
        $folio = $pods->field('folio_number');
        $subject = str_replace('\\','',$pods->field('subject'));
    }

    if (!empty(get_current_user_id())) {
        $user_information = pods('user', get_current_user_id());
        $email = $user_information->field('client_email');
        $phone = $user_information->field('phone');
    }

    $request_type = fetch_request_type_fn();
    wp_localize_script('csp-script', 'requestTypeArray', $request_type);
    wp_localize_script('csp-script', 'preselectedValues', $request);
    wp_localize_script('csp-script', 'preselectedSubServiceValues', $sub_request);
    ob_start();
?>

    <div class="container mb-4 mt-2">
        <?php if (empty(get_current_user_id()) || !$user_information->field('verification_state')) : ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-1"></i>
                KYC Pending! You are logged in with temporary access.
            </div>
        <?php endif; ?>
        <div id="error-message-div"></div>
        <form id="add-request-form" method="POST" enctype="multipart/form-data">
            <?php wp_nonce_field('csp_ticket_form_action', '_csp_nonce'); ?>
            <input type="hidden" name="action" value="csp_submit_ticket">
            <input type="hidden" name="edit" value="<?= $id ?>">

            <div class="form-group">
                <label for="subject" class="required-label">Subject</label>
                <input class="form-control" id="subject" name="subject" type="text" value="<?= $subject ?>" required>
            </div>
            <div class="form-group">
                <label for="folio_company" class="required-label">Email</label>
                <input class="form-control" type="email" value="<?= $email ?>" disabled />
            </div>
            <div class="form-group">
                <label for="folio_company" class="required-label">Phone</label>
                <input class="form-control" type="text" value="<?= $phone ?>" disabled />
            </div>
            <div class="form-group">
                <label for="folio_company" class="required-label">Select Company</label>
                <select class="form-select searchable-select" id="folio_company" name="folio_company" required>
                    <option value="">Select your company</option>
                    <?php
                    $company_pods = pods('company')->find(['limit' => -1]);
                    if ($company_pods->total() > 0):
                        while ($company_pods->fetch()):
                            $company_isin = $company_pods->field('isin_codes');
                            $company_name = $company_pods->field('name');
                            $selected = $company_isin == $company ? 'selected' : '';
                            echo "<option value='$company_isin' $selected>$company_name</option>";
                        endwhile;
                    endif;
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="folio_number" class="required-label">Folio Number</label>
                <input type="text" id="folio_number" name="folio_number" placeholder="Enter your folio number" value="<?= $folio; ?>" required>
                <p class="m-0 mt-1" style="line-height: 1.2rem; font-size: small;">Physical Folio: Up to 7 digits (e.g., 0000024)
                    NSDL (DP + Client): 16 digits in 8+8 format (e.g., IN30001110096423)
                    CDSL: 16 digits (e.g., 1208160029312940)</p>
            </div>

            <div class="row show-request-type">
                <div class="mb-3 col-12">
                    <label for="service_request" class="form-label required-label">Service Request Type</label>
                    <select id="request-type-select-box" data-placeholder="----Select----" name="service_request[]" class="form-select" multiple required>
                        <?php foreach ($request_type as  $index => $row) : ?>
                            <option value="<?= $row['request_type'] ?>"><?= $row['request_type'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>


            <div class="mb-3 hidden">
                <label for="status" class="form-label">Status</label>
                <input type="text" name="status" class="form-control" value="new">
            </div>

            <div class="mb-3">
                <label for="files" class="form-label">Upload Files</label>
                <input id="fileInput" type="file" name="files[]" class="form-control" multiple>
            </div>
            <div class="my-3 show-uploaded-list h6">
                <?php if (!empty($id)) : ?>
                    <?php if (!empty($files) && count($files) != 0) : ?>
                        <label for="files" class="form-label"> Attachments (<?= count($files) ?>)</label>
                        <div class="form-control">
                            <?=
                            csp_show_edit_attachments($pods->field('files'), false) ?>
                        </div>
                    <?php else : ?>
                        <div class="alert alert-danger"> No Attachments found</div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <div class="my-3 display-documents">
            </div>
            <div class="mb-3">
                <label for="ticket_description" class="form-label required-label">Description</label>
                <textarea name="ticket_description" class="form-control" rows="5" required><?= $desc; ?></textarea>
            </div>

            <input type="text" name="modified" class="form-control hidden" style="display: none;" value="<?= date('Y-m-d H:i:s') ?>">
            <button type="submit" name="csp_submit_ticket" class="btn btn-blue p-2 mt-1 submit_ticket_none_loader"><?= $id > 0 ? 'Update Ticket' : 'Submit Ticket' ?></button>
            <button class="btn btn-blue create_ticket_loader text-white" type="button" disabled style="display:none;">
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Saving Details
            </button>
        </form>
    </div>

<?php
    return ob_get_clean();
});

function csp_ticket_non_edit_reply_form()
{

    $ticket_id = isset($_GET['ticket_id']) ? intval($_GET['ticket_id']) : null;
    $edit = isset($_GET['action']) ? $_GET['action'] : '';
    error_log($edit);

    if (!$ticket_id) {
        return '<div class="alert alert-danger">No ticket selected. Please go back and select a ticket.</div>';
    }

    // Fetch ticket details
    $ticket = pods('ticket', $ticket_id);
    if (!$ticket->exists()) {
        return '<div class="alert alert-danger">Ticket not found.</div>';
    }

    // Fetch replies
    $replies = pods('ticketreply', [
        'where' => 'ticket_id = ' . $ticket_id,
        'orderby' => 'created DESC'
    ]);

    // Ticket details
    $ticket_name = esc_html($ticket->field('name'));
    $ticket_status = esc_html($ticket->field('status'));
    $ticket_description = esc_html($ticket->field('post_content'));
    $ticket_created = esc_html($ticket->field('created'));

    // Start building the ticket reply page UI
    ob_start(); ?>
    <div class="d-flex">
        <div class="d-flex pe-2">
            <span class="bg-light1 rounded-circle d-flex align-items-center justify-content-center circle-bg2-css">
                <i class="bi bi-arrows-angle-expand text-info fs-4"></i>
            </span>
        </div>

        <div class="ps-2 w-100">
            <div class="">

                <div class="">

                    <div class="d-flex justify-content-between align-items-center mt-1 mt-md-3 mb-3">
                        <div class="d-flex">
                            <!-- <i class="bi bi-reply me-2"></i> -->
                            <p class="text-info h6" onclick="openRepliesDiv()">Display all previous replies (<?= $replies->total(); ?>)</p>
                        </div>
                        <!-- <div id="view-reply" class="bg-warning py-1 px-3 text-white rounded-pill">
                            <i class="bi bi-reply-all me-2"></i> Reply
                        </div> -->
                    </div>
                    <!-- <hr> -->
                    <div id="replies-div" class="hidden">
                        <?php if ($replies->total()) : ?>
                            <?php while ($replies->fetch()) : ?>
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <div class="rounded-circle h4 <?= $replies->field('user_id') == get_current_user_id() ? 'bg-info' : 'bg-secondary' ?> text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <?= strtoupper(substr($replies->field('user_name'), 0, 1)) ?>
                                                </div>
                                            </div>
                                            <div class="w-100">
                                                <div class="d-flex flex-wrap justify-content-between mb-0">
                                                    <h6 class="mb-0"><?= esc_html($replies->field('user_name')) ?></h6>
                                                    <small class="text-muted float-end"><?= date('d M h:i A ', strtotime(esc_html($replies->field('created')))) ?></small>
                                                </div>
                                                <p class="mt-0 mb-0"><?= esc_html($replies->field('reply_content')) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
    return ob_get_clean();
};
function csp_ticket_reply_form($files)
{
    $ticket_id = isset($_GET['ticket_id']) ? intval($_GET['ticket_id']) : null;
    $edit = isset($_GET['action']) ? $_GET['action'] : '';
    error_log($edit);

    if (!$ticket_id) {
        return '<div class="alert alert-danger">No ticket selected. Please go back and select a ticket.</div>';
    }

    // Fetch ticket details
    $ticket = pods('ticket', $ticket_id);
    if (!$ticket->exists()) {
        return '<div class="alert alert-danger">Ticket not found.</div>';
    }

    // Fetch replies
    $replies = pods('ticketreply', [
        'where' => 'ticket_id = ' . $ticket_id,
        'orderby' => 'created DESC'
    ]);

    // Ticket details
    $ticket_name = esc_html($ticket->field('name'));
    $ticket_status = esc_html($ticket->field('status'));
    $ticket_description = esc_html($ticket->field('post_content'));
    $ticket_created = esc_html($ticket->field('created'));

    // Start building the ticket reply page UI
    ob_start(); ?>

    <div class="">
        <div class="">
            <!-- <div class="d-flex">
                <h4 class="mb-0 me-2"><?= $ticket_name ?></h4>
                <sup class="badge bg-<?= $ticket_status == 'open' ? 'success' : 'danger' ?>" style="height:fit-content;"><?= ucfirst($ticket_status) ?></sup>
            </div>
            <hr> -->
            <div class="">
                <div class="mb-4 <?= $edit == 'edit' ? 'hidden' : '' ?>">
                    <div class="d-flex">
                        <!-- <i class="bi bi-card-text me-2"></i> -->
                        <h6>Description</h6>
                    </div>
                    <p><?= $ticket_description ?></p>
                </div>
                <hr class="<?= $edit == 'edit' ? 'hidden' : '' ?>">
                <div class=" <?= $edit == 'edit' ? 'hidden' : 'd-flex' ?>">
                    <!-- <i class="bi bi-paperclip me-2"></i> -->
                    <h6 class="">Attachments (<?= count(decode_string($files)) ?>)</h6>
                </div>
                <!-- <hr class="mb-1 mt-2"> -->
                <p class="mb-0"><?= $edit == 'edit' ? '' : csp_show_attachments($files)
                                ?>
                </p>
                <hr>
                <div id="reply-form" class="mt-3 mb-3" style="display: none;">
                    <h6 class="mb-3">Add Your Reply</h6>
                    <!-- <hr> -->
                    <form id="save-reply-form" method="POST">
                        <input type="hidden" name="action" value="submit_ticket_reply">
                        <input type="hidden" name="ticket_id" value="<?= $ticket_id ?>">

                        <!-- <div class="mb-3">
                        <label for="file_attach" class="form-label">Attach File</label>
                        <input type="file" class="form-control" id="file_attach" name="file_attach">
                    </div> -->

                        <div class="mb-3 d-flex gap-2">
                            <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <?= strtoupper(substr(wp_get_current_user()->display_name, 0, 1)) ?>
                            </div>
                            <div class="w-100">
                                <!-- <label for="reply_content" class="form-label">Your Reply</label> -->
                                <textarea class="form-control" id="reply_content" placeholder="write your reply here..." name="reply_content" rows="2" required></textarea>
                            </div>
                        </div>
                        <div class="w-100 d-flex justify-content-end">
                            <button id="cancel-reply" class="btn btn-secondary rounded-pill me-2" style="width: fit-content;">
                                <i class="bi bi-x-circle me-1"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-blue text-white rounded-pill submit-reply-btn" style="width: fit-content;">
                                <i class="bi bi-send me-1"></i> Submit
                            </button>
                            <button type="submit" class="btn btn-blue text-white rounded-pill save-reply-btn" disabled style="width: fit-content; display: none;">
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                Saving
                            </button>
                        </div>
                    </form>
                    <hr>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
                    <div class="d-flex">
                        <!-- <i class="bi bi-reply me-2"></i> -->
                        <h6 class="">Previous Replies</h6>
                    </div>
                    <div id="view-reply" class="bg-warning py-1 px-3 text-white rounded-pill">
                        <i class="bi bi-reply-all me-2"></i> Reply
                    </div>
                </div>
                <!-- <hr> -->
                <?php if ($replies->total()) : ?>
                    <?php while ($replies->fetch()) : ?>
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <div class="rounded-circle <?= $replies->field('user_id') == get_current_user_id() ? 'bg-info' : 'bg-secondary' ?> text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <?= strtoupper(substr($replies->field('user_name'), 0, 1)) ?>
                                        </div>
                                    </div>
                                    <div class="w-100">
                                        <div class="d-flex justify-content-between mb-0">
                                            <h6 class="mb-0"><?= esc_html($replies->field('user_name')) ?></h6>
                                            <small class="text-muted float-end"><?= date('d M h:i A ', strtotime(esc_html($replies->field('created')))) ?></small>
                                        </div>
                                        <p class="mt-0 mb-0"><?= esc_html($replies->field('reply_content')) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else : ?>
                    <div class="alert alert-info">No replies yet.</div>
                <?php endif; ?>

            </div>
        </div>
    </div>

<?php
    return ob_get_clean();
};

add_shortcode('csp_ticket_information', function () {
    // Get the ticket ID from the URL
    $ticket_id = isset($_GET['ticket_id']) ? intval($_GET['ticket_id']) : null;

    if (!$ticket_id) {
        return '<div  class="m-4"><h2>Information</h2> <hr><div class="alert alert-danger">No ticket selected. Please go back and select a ticket.</div>';
    }

    // Fetch ticket details
    $ticket = pods('ticket', $ticket_id);
    if (!$ticket->exists()) {
        return '<div class="m-4"><h2>Information</h2> <hr> <div class="alert alert-danger">Ticket not found.</div>';
    }
    error_log(print_r($ticket->row(), true));

    // Ticket details
    $ticket_name = esc_html($ticket->field('service_request'));
    $subject = esc_html($ticket->field('subject'));
    $ticket_status = esc_html($ticket->field('status'));
    $created_on = date('d M, Y', strtotime(esc_html($ticket->field('created'))));
    $ticket_updated =
        date('d M, Y', strtotime(esc_html($ticket->field('modified'))));
    $folio_number = esc_html($ticket->field('folio_number'));
    $post_content = esc_html($ticket->field('post_content'));
    $company = esc_html($ticket->field('company'));
    $company_name = csp_get_company_name_by_id($company);
    $service_request = esc_html($ticket->field('service_request'));
    $sub_services = esc_html($ticket->field('sub_services'));
    $assign_to = esc_html($ticket->field('assign_to'));
    $files = esc_html($ticket->field('files'));
    $email = esc_html($ticket->field('email'));
    $phone = esc_html($ticket->field('phone'));
    $now = new DateTime();
    $ticket_datetime = new DateTime(esc_html($ticket->field('created')));
    $ticket_diff_hour = $now->diff($ticket_datetime);
    error_log(print_r($ticket_diff_hour, true));
    $show_time_diff = '';
    if (!empty($ticket_diff_hour->y) && (int) $ticket_diff_hour->y > 0) {
        $show_time_diff =  (int) $ticket_diff_hour->y > 1 ? (int) $ticket_diff_hour->y . ' years ago' : (int) $ticket_diff_hour->y . ' year ago';
    } else if (!empty($ticket_diff_hour->m) && (int) $ticket_diff_hour->m > 0) {
        $show_time_diff =  (int) $ticket_diff_hour->m > 1 ? (int) $ticket_diff_hour->m . ' months ago' : (int) $ticket_diff_hour->m . ' month ago';
    } else if (!empty($ticket_diff_hour->d) && (int) $ticket_diff_hour->d > 0) {
        $show_time_diff =  (int) $ticket_diff_hour->d > 1 ? (int) $ticket_diff_hour->d . ' days ago' : (int) $ticket_diff_hour->d . ' day ago';
    } else if (!empty($ticket_diff_hour->h) && (int) $ticket_diff_hour->h > 0) {
        $show_time_diff = (int) $ticket_diff_hour->h > 1 ?  (int) $ticket_diff_hour->h . ' hours ago' :  (int) $ticket_diff_hour->h . ' hour ago';
    } else {
        $show_time_diff =  (int) $ticket_diff_hour->i > 1 ? (int) $ticket_diff_hour->i . ' minutes ago' : (int) $ticket_diff_hour->i . ' minute ago';
        $show_time_diff =  (int) $ticket_diff_hour->i == 0 ? 'few seconds ago' : $show_time_diff;
    }

    if (!empty($sub_services) && $sub_services != 'null') {
        $ticket_name .= ' (' . $sub_services . ')';
    }

    $status_color = 'primary';
    switch ($ticket_status) {
        case 'Open':
            $status_color = 'warning';
            break;
        case 'On Hold':
            $status_color = 'danger';
            break;
        case 'Closed':
            $status_color = 'success';
            break;
        default:
            $status_color = 'primary';
            break;
    }

    // Build the UI
    ob_start(); ?>
    <div class="bg-white">
        <div class="border-bottom px-4 py-3">
            <?php echo do_shortcode('[breadcrumbs dashboard_url="' . site_url('/investor-service-request') . '" dashboard_label="Dashboard" ticket_name="Ticket #' . $ticket_id . '"]'); ?>
            <div class="d-flex mt-4 justify-content-between align-items-center">
                <div class="d-flex">
                    <span class="bg-light rounded-circle d-flex justify-content-center align-items-center circle-bg-css">
                        <i class="bi bi-buildings text-secondary fs-3"></i>
                    </span>
                    <div class="d-flex flex-column px-3">
                        <h3 class="mb-1"><?= $company_name; ?> </h3>
                        <span class="d-flex align-items-center">
                            <?= $company ?>
                            <i class="bi bi-dot"></i>
                            <?= $show_time_diff ?>
                            <i class="bi bi-dot"></i>
                            <span class="badge bg-<?= $status_color ?> ms-1" style="height:fit-content;"><?= ucfirst($ticket_status) ?></span>
                        </span>
                    </div>
                </div>
                <!-- <div class="d-flex align-items-center justify-content-end bg-light1 text-info rounded py-2 px-3 " style="height: fit-content;">
                    <i class="bi bi-x-lg me-2"></i>
                    Close Ticket
                </div> -->
            </div>
        </div>
        <div class="d-flex flex-wrap">
            <div class="col-12 col-md-9 px-5">
                <div class="py-4 h-100 d-flex flex-column justify-content-between">
                    <div class="">
                        <div class="mb-3 d-flex">
                            <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center circle-bg2-css">
                                <p class="m-0 h3"><i class="bi bi-envelope-open"></i></p>
                            </div>
                            <div class="w-100 d-flex flex-column px-3 pb-2 py-md-2 border-bottom">
                                <div class="d-flex flex-wrap justify-content-between">
                                    <h5 class="mb-0"><?= str_replace("\\", "", $subject); ?></h5>
                                    <span class="text-muted"><?= $show_time_diff ?></span>
                                </div>
                                <p class="mt-0" style="white-space: pre-line;">
                                    <?= $post_content ?>
                                </p>
                                <?= csp_show_attachments($files) ?>
                            </div>
                        </div>
                        <?= csp_ticket_non_edit_reply_form() ?>
                    </div>
                    <div id="reply-form" class="mt-5">
                        <form id="save-reply-form" method="POST">
                            <input type="hidden" name="action" value="submit_ticket_reply">
                            <input type="hidden" name="ticket_id" value="<?= $ticket_id ?>">

                            <div class="mb-3 d-flex gap-2">
                                <!-- <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <?= strtoupper(substr(wp_get_current_user()->display_name, 0, 1)) ?>
                                    </div> -->
                                <div class="w-100 textarea-container">
                                    <!-- <label for="reply_content" class="form-label">Your Reply</label> -->
                                    <textarea class="form-control w-100" id="reply_content" placeholder="Reply..." name="reply_content" rows="3" required></textarea>
                                    <button type="submit" class="btn btn-blue text-white rounded submit-reply-btn" style="width: fit-content;">
                                        <i class="bi bi-save me-2"></i>Save
                                    </button>
                                    <button type="submit" class="btn btn-blue text-white rounded save-reply-btn" disabled style="width: fit-content; display: none;">
                                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                        Saving
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-3 text-center text-md-start p-4 bg-light gap-4 d-flex flex-column">
                <h4>Details</h4>
                <div class="">
                    <p class="h6 mb-2 text-secondary">Request Type</p>
                    <div class="d-flex flex-column align-items-center align-items-md-start gap-2">
                        <?php foreach (explode(",", $service_request) as $request) : ?>
                            <span class="badge bg-info px-3 py-2 rounded" style="width: fit-content;"><?= $request ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="">
                    <p class="h6 mb-2 text-secondary">Sub Request Type</p>
                    <div class="d-flex flex-column align-items-center align-items-md-start gap-2">
                        <?php foreach (explode(",", $sub_services) as $request) : ?>
                            <span class="badge bg-warning px-3 py-2 rounded" style="width: fit-content;"><?= $request ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="">
                    <p class="h6 mb-0 text-secondary">Folio Number</p>
                    <span><?= $folio_number ?></span>
                </div>
                <div class="">
                    <p class="h6 mb-0 text-secondary">Email Id</p>
                    <span><?= strtolower($email); ?></span>
                </div>
                <div class="">
                    <p class="h6 mb-0 text-secondary">Phone</p>
                    <span><?= $phone ?></span>
                </div>

                <div class="">
                    <p class="h6 mb-0 text-secondary">Posted On</p>
                    <span><?= $created_on ?></span>
                </div>
                <div class="">
                    <p class="h6 mb-0 text-secondary">Last Updated On</p>
                    <span><?= $ticket_updated ?></span>
                </div>
                <hr class="m-0">
                <div class="">
                    <p class="h6 text-secondary mb-1">Assignee</p>
                    <?= show_assignee_info_fn($assign_to) ?>
                </div>
                <!-- <div class="">
                    <p class="h6 text-secondary">Add Tags</p>
                    <div class="d-flex text-info">
                        <i class="bi bi-plus-lg me-1"></i>
                        Add Tags
                    </div>
                </div> -->
            </div>
        </div>

    </div>

<?php
    return ob_get_clean();
});

add_shortcode('breadcrumbs', function ($attributes) {
    // Define default attributes
    $attributes = shortcode_atts(
        array(
            'dashboard_url' => site_url('/investor-service-request'), // Default dashboard URL
            'dashboard_label' => 'Dashboard',         // Default dashboard label
            'ticket_name' => '',                      // Ticket name
        ),
        $attributes,
        'breadcrumbs'
    );

    // Return the Bootstrap breadcrumbs HTML
    return '
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb ms-1 mb-0">
            <li class="breadcrumb-item h6">
                <a style="color: #212D45; text-decoration: underline !important; text-underline-offset: 2px;" href="?tab=dashboard#Investor_Service_Request">' . esc_html($attributes['dashboard_label']) . '</a>
            </li>
            ' . ($attributes['ticket_name']
        ? '<li class="breadcrumb-item active h6" aria-current="page">' . esc_html($attributes['ticket_name']) . '</li>'
        : ''
    ) . '
        </ol>
    </nav>';
});
