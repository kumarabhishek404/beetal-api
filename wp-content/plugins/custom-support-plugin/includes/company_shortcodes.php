<?php

function timestampToDate($timestamp)
{
    if (empty($timestamp)) {
        $timestamp = time();
    }
    // PHP timestamps are in seconds, so divide by 1000
    $seconds = $timestamp / 1000;

    // Check if the timestamp is valid
    if ($seconds < 0 || !is_numeric($seconds)) {
        return "Invalid Timestamp";
    }

    $date = date("Y-m-d", $seconds);
    return $date;
}

function handle_get_date_fn() {
    wp_send_json_success(date('d M, Y', strtotime(timestampToDate($_POST['report_date']))));
}

function request_report_generate_fn()
{
    $user_information = pods('user', get_current_user_id());
    if (empty($user_information)) {
        wp_send_json_error("Request couldn't be saved. Try again, or contact support if the issue persists.");
    }
    $isin_code = $user_information->field('isin');
    $company_mail = $user_information->field('user_email');
    $request_report =
        sanitize_text_field($_POST['reportId']);
    $request_time =
        timestampToDate(sanitize_text_field($_POST['reportTime']));
    $url = get_option('api_url') . 'v1/beetal/b2b/company-requests/new';
    $params = [
        "isin_code" => $isin_code,
        "requested_by" => $company_mail,
        "title" => $request_report,
        "description" => "Requesting a new report for $request_report for the $isin_code.",
        "category" => "Report",
        "status" => "new",
        "rprt_rqst_for_date" => $request_time
    ];
    $headers = [
        "Content-Type: application/json",
        "X-API-KEY: " . get_option('api_key')
    ];
    error_log("Response: " . print_r($params, true));

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));

    $response = curl_exec($ch);
    curl_close($ch);
    error_log("Response: " . print_r($response, true));
    if (is_wp_error($response)) {
        wp_send_json_error("Request couldn't be saved. Try again, or contact support if the issue persists.");
        return;
    } else {

        if (!empty($response['error']) || !empty($response['message'])) {
            wp_send_json_error("Request couldn't be saved. Try again, or contact support if the issue persists.");
            return;
        } else {
            $ticket_id = pods('ticket')->add([
                'post_content' => "Requesting a new report title $request_report for date $request_time for company $isin_code.",
                'name' => $user_information->field('display_name'),
                'folio_number' => '',
                'company' => $isin_code,
                'service_request' => $request_report,
                'sub_services' => $request_time,
                'status' => 'new',
                'email' => $company_mail,
                'phone' => '',
                'files' => '',
                'post_author' => get_current_user_id(),
                'assignee' => get_current_user_id(),
                'subject' => "$request_report report requested"
            ]);
            error_log("Ticket Id for new request => " . print_r($ticket_id, true));
            // Redirect with success or failure
            wp_send_json_success('Success! Your request has been submitted for review.');
            return;
        }
    }
}

function confirmation_modal()
{
    ob_start();
?>
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 shadow p-3">
                <div class="modal-body text-center">
                    <div class="alert-icon mb-3">
                        <img src="https://cdn-icons-png.flaticon.com/512/189/189664.png" width="50" alt="Alert Icon">
                    </div>
                    <h5 class="fw-bold">Please Confirm</h5>
                    <p class="text-muted">That you want to raise a request to generate a new report?</p>
                    <div id="conform_request_details">
                    </div>

                    <div class="d-flex justify-content-center gap-3 mt-4">
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary px-4" id="confirmAction">Confirm</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">Confirm Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <p class="fs-5">Do you want to raise a request for downloading the report?</p>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" id="confirmRequest" class="btn btn-primary">Confirm</button>
                </div>
            </div>
        </div>
    </div> -->
<?php
    return ob_get_clean();
}

add_shortcode('show_search_folio', function () {
    $folio_results = empty($_SESSION['folio_search_result']) ? [] : $_SESSION['folio_search_result'];
    // if (!empty($_GET['folio_no']) || !empty($_GET['pan']) || !empty($_GET['search_name'])) {
    //     $folio_results = $_SESSION['folio_search_result'];
    // }
    $folio_selected = [];
    $folio_data = [];

    $display_folio = isset($_GET['folio']) ? (string) $_GET['folio'] : 1;
    // error_log('Display Folio => ' . $display_folio);

    if (!empty($folio_results) && $display_folio != 1) {
        $user_information = pods('user', get_current_user_id());
        if (empty($user_information->field('isin'))) {
            redirect_if_not_logged_in();
        } else {
            $search_selected =  array_filter($folio_results, function ($record) use ($display_folio) {
                return $record['folio_no'] == $display_folio;
            });
            $folio_selected = reset($search_selected);
            error_log("Search Result => " . print_r($search_selected, true));
            error_log("Folio Result => " . print_r($folio_selected, true));
            if (!empty($folio_selected)) {
                $folio_params = [
                    'folio_no' => (string) $display_folio,
                    'isin' => $user_information->field('isin'),
                ];

                $folio_data = csp_fetch_api_response($folio_params);
                // error_log(print_r($folio_data, true));
            } else {
                $folio_data = [];
            }
        }
    }
    if (empty($folio_data) && $display_folio != 1) {
        return error_html();
    }
    $folio_tabs = [
        'Summary',
        'Certificates',
    ];

    if (isset($_GET['folio'])) {
        echo '<script> jQuery(document).ready(function($) { $("#folioModal").modal("show"); }); </script>';
    }

    $page_url = "?tab=search_folio";
    if (!empty($_GET['folio_no'])) {
        $page_url .= "&folio_no=" . $_GET['folio_no'];
    }
    if (!empty($_GET['pan'])) {
        $page_url .= "&pan=" . $_GET['pan'];
    }
    if (!empty($_GET['search_name'])) {
        $page_url .= "&search_name=" . $_GET['search_name'];
    }
    $rows_per_page = 10; // Number of rows per page
    $current_page = isset($_GET['page_no']) ? (int)$_GET['page_no'] : 1; // Get current page number from URL
    $offset = ($current_page - 1) * $rows_per_page; // Calculate offset

    // Get paginated data
    $paginated_data = count($folio_results) > $rows_per_page ? array_slice($folio_results, $offset, $rows_per_page) : $folio_results;

    // Total pages
    $total_pages = ceil(count($folio_results) / $rows_per_page);
    error_log("Total pages: " . print_r($folio_results, true));
    ob_start();
?>
    <div class="bg-white py-4 card rounded-0 border-0 px-md-5 px-4">
        <h2 class="text-start border-bottom pb-3 ">Folio Search</h2>
        <form id="folio-search-form" class="mt-4">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="folio_no" class="form-label">Folio Number</label>
                    <input type="text" name="folio_no" value="<?= !empty($_GET['folio_no']) ? $_GET['folio_no'] : '' ?>" class="form-control" placeholder="Enter Folio Number">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="pan_no" class="form-label">PAN Number</label>
                    <input type="text" name="pan_no" value="<?= !empty($_GET['pan']) ? $_GET['pan'] : '' ?>" class="form-control" placeholder="Enter PAN Number">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" name="cmp_name" value="<?= !empty($_GET['search_name']) ? (string) $_GET['search_name'] : '' ?>" class="form-control" placeholder="Enter Name">
                </div>
            </div>
            <div class="row d-flex justify-content-end">
                <div class="d-flex justify-content-end" style="width: 120px;">
                    <button type="submit" id="search-btn" class="btn btn-blue">Search</button>
                </div>
            </div>
        </form>
        <?php if (count($folio_results) > 0) : ?>
            <div id="search-results" class="mt-4">
                <table class="tax-forms-table table-striped rounded-top d-none d-md-table">
                    <thead class="rounded-top">
                        <tr>
                            <th class="col-1 text-center">No.</th>
                            <th>Name</th>
                            <th>Folio Number</th>
                            <th>PAN Number</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($paginated_data as $index => $folio) : ?>
                            <?php $holder_info = reset($folio['holders']); ?>
                            <tr>
                                <td class="col-1 text-center"><?= $offset > 0 ? ++$index + $offset : ++$index ?></td>
                                <td>
                                    <a href="<?= home_url(add_query_arg(array('folio' => $folio['folio_no']))); ?>" class="company-name">
                                        <?= !empty($holder_info['name']) ? $holder_info['name'] : 'N/A' ?>
                                    </a>
                                </td>
                                <td><?= $folio['folio_no'] ?></td>
                                <td><?= !empty($holder_info['pan']) ? $holder_info['pan'] : 'N/A' ?></td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
                <div class="h6 mb-4 d-md-none border-bottom pb-2">Search Results</div>
                <?php foreach ($paginated_data as $index => $folio) : ?>
                    <div class="card shadow-md border-color d-md-none mb-4">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex gap-2 align-items-center"><i class="bi bi-person-fill fs-4 text-warning"></i>
                                <a href="?tab=search_folio&folio=<?= $folio['folio_no'] ?>" class="company-name"><span class="h6"><?= !empty($holder_info['name']) ? $holder_info['name'] : 'N/A' ?></span> </a>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex gap-2 align-items-center"><i class="bi bi-file-binary-fill fs-4 text-info" data-toggle="tooltip" data-placement="top" title="Folio No."></i>
                                    <span class=""><?= $folio['folio_no'] ?></span>
                                </div>
                                <div class="d-flex gap-2 align-items-center">
                                    <i class="bi bi-person-vcard-fill fs-4 text-info" data-toggle="tooltip" data-placement="top" title="PAN"></i>
                                    <?= !empty($holder_info['pan']) ? $holder_info['pan'] : 'N/A' ?>
                                </div>
                            </div>
                        </div>

                    </div>
                <?php endforeach ?>
                <nav aria-label="Pagination">
                    <?php if ($total_pages > 1) : ?>
                        <ul class="pagination flex-wrap justify-content-center m-0">
                            <!-- Previous Button -->
                            <?php if ($current_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= $page_url ?>&page_no=<?php echo $current_page - 1; ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled">
                                    <span class="page-link" aria-hidden="true">&laquo;</span>
                                </li>
                            <?php endif; ?>

                            <!-- Page Numbers -->
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                                    <a class="page-link" href="<?= $page_url ?>&page_no=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <!-- Next Button -->
                            <?php if ($current_page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= $page_url ?>&page_no=<?php echo $current_page + 1; ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled">
                                    <span class="page-link" aria-hidden="true">&raquo;</span>
                                </li>
                            <?php endif; ?>
                        </ul>
                    <?php endif; ?>
                </nav>
            </div>
        <?php else : ?>
            <div id="show-alert" class="mt-4">
                <div class="alert alert-info" role="alert">
                    No data available at the moment. Please modify your search criteria or try again later.
                </div>
            </div>
        <?php endif ?>
        <div id="show-alert-2" class="mt-4"></div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="folioModal" tabindex="-1" aria-labelledby="folioModalLabel" aria-hidden="true" style="z-index: 9999;">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="d-flex align-items-center gap-3">
                        <h3 class="modal-title" id="folioModalLabel">Folio Information</h3>
                        <span class="badge badge-blue p-2">
                            <!-- <i class="bi bi-file-earmark-text-fill me-1"></i> -->
                            No.
                            #<?= $display_folio ?? "N/A" ?></span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="h6 text-dark">Personal Details</p>
                    <div class="card shadow-none p-3 d-flex gap-3">
                        <div class="d-flex gap-4">
                            <div class="mt-2 flex-fill flex-wrap">
                                <div class="d-flex flex-wrap justify-content-between align-items-center">
                                    <div class="d-flex gap-2 align-items-center">
                                        <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center" style="min-width: 40px; height: 40px;">
                                            <p class="mb-0" style="font-size: x-large;"><?= strtoupper(substr($folio_data['name'], 0, 1) ?? "N/A") ?></p>
                                        </div>
                                        <p class="mb-1 h5 d-flex justify-content-between"><?php echo esc_html($folio_data['name']) ?? "N/A" ?>
                                            <?php if (isset($folio_data['is_kyc_completed']) && (int)$folio_data['is_kyc_completed'] > 0) : ?>
                                                <i class="bi bi-patch-check-fill text-success d-flex d-md-none ms-2" data-toggle="tooltip" data-placement="top" title="KYC Completed"></i>
                                            <?php else : ?>
                                                <i class="bi bi-patch-exclamation-fill text-danger d-flex d-md-none ms-2" data-toggle="tooltip" data-placement="top" title="KYC Incomplete"></i>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <div class="ms-5 mt-2 mt-md-0 ms-md-0">
                                        <?php if (isset($folio_data['is_kyc_completed']) && (int)$folio_data['is_kyc_completed'] > 0) : ?>
                                            <span class="badge badge-success p-2 d-none d-md-flex">
                                                KYC Completed
                                            </span>
                                        <?php else : ?>
                                            <span class="badge badge-danger p-2 d-none d-md-flex">
                                                KYC Incomplete
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 ms-5 gap-md-4 flex-wrap text-muted">
                                    <div class="d-flex gap-2 me-2 me-md-0">
                                        <i class="bi bi-graph-up" data-toggle="tooltip" data-placement="top" title="No. Of Shares"></i>
                                        <?php echo !empty($folio_data['shares']) ? esc_html($folio_data['shares']) : "N/A" ?>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <i class="bi bi-telephone" data-toggle="tooltip" data-placement="top" title="Mobile Number"></i>
                                        <?php echo !empty($folio_data['phone']) ? esc_html($folio_data['phone']) : "N/A" ?>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <i class="bi bi-envelope" data-toggle="tooltip" data-placement="top" title="Email Id"></i>
                                        <?php echo !empty($folio_data['email']) ? esc_html($folio_data['email']) : "N/A" ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php if (!empty($folio_data['address_list']['primary']['line_1'])) : ?>
                            <div class="bg-light p-3 rounded">
                                <p class="text-muted h6">Primary Address</p>
                                <p class="mb-0 text-xs d-flex gap-2">
                                    <i class="bi bi-pin-map-fill me-2 text-success"></i>
                                    <?= csp_address_array_to_string($folio_data['address_list']['primary']); ?>
                                </p>
                            </div>
                        <?php endif; ?>

                    </div>
                    <div class="d-flex justify-content-md-start justify-content-around align-items-center my-3 border-bottom">
                        <?php foreach ($folio_tabs as $index => $tab) : ?>
                            <div id="<?= $tab ?>" class="px-4 py-3 col-md-2 text-center company-tabs <?php echo $index == 0 ? 'folio-active' : '' ?> cursor-pointer" style="font-size: 18px; text-wrap: nowrap;" onclick="changeFolioTab('<?= $tab ?>')"><?= $tab ?></div>
                        <?php endforeach; ?>
                    </div>
                    <div id="show-Summary" class="mt-3 show-folio-info">
                        <div class="px-3">
                            <p class="h6 text-dark mt-3">Bank Details</p>
                            <div class="d-none d-md-flex">
                                <table class="tax-forms-table table-bordered table-striped">
                                    <thead class="">
                                        <tr>
                                            <th class="text-center">No.</th>
                                            <th class="text-center">Holder Name</th>
                                            <th class="text-center">Account No</th>
                                            <th class="text-center">Bank Name</th>
                                            <th class="text-center">IFSC Code</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($folio_data['bank_details'])) : ?>
                                            <?php foreach ($folio_data['bank_details'] as $index => $row) : ?>
                                                <tr>
                                                    <td class="text-center"><?php echo ++$index; ?></td>
                                                    <td class="text-center"><?php echo !empty($row['holder_name']) ? $row['holder_name'] : 'N/A' ?></td>
                                                    <td class="text-center"><?php echo empty($row['account_number']) ? 'N/A' : $row['account_number'] ?></td>
                                                    <td class="text-center"><?php echo empty($row['bank_name']) ? 'N/A' : $row['bank_name'] ?></td>
                                                    <td class="text-center"><?php echo empty($row['ifsc']) ? 'N/A' : $row['ifsc'] ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="alert alert-info mt-4" role="alert">
                                                No data available at the moment.
                                            </div>
                                        <?php endif ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php foreach ($folio_data['bank_details'] as $index => $row) : ?>
                                <?php if (!empty($row['bank_name'])) : ?>
                                    <div class="card flex-fill shadow-md border-0 rounded d-md-none f-flex mb-2">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-1 gap-3">
                                                <i class="bi bi-person-fill text-secondary fs-4 text-warning" data-toggle="tooltip" data-placement="top" title="Account holder name"></i>
                                                <p class="h5 m-0"><?= esc_html($row['holder_name']) ?? 'N/A'; ?></p>
                                            </div>
                                            <div class="d-flex align-items-center ms-1 mb-1 gap-3">
                                                <i class="bi bi-bank text-secondary text-info" data-toggle="tooltip" data-placement="top" title="Bank Name and IFSC"></i>
                                                <p class="m-0 text-break text-wrap"><?= !empty($row['bank_name']) ? esc_html($row['bank_name']) : 'N/A'; ?>
                                                    (<?= !empty($row['ifsc']) ? esc_html($row['ifsc']) : 'N/A'; ?>)</p>
                                            </div>
                                            <div class="d-flex align-items-center ms-1 mb-1 gap-3">
                                                <i class="bi bi-123 text-secondary text-info" data-toggle="tooltip" data-placement="top" title="Account Number"></i>
                                                <p class="m-0 text-break text-wrap"><?= !empty($row['account_number']) ? esc_html($row['account_number']) : 'N/A'; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <hr />
                        <div class="px-3">

                            <p class="h6 text-dark mt-3">Holder Details</p>
                            <div class="d-none d-md-flex">
                                <table class="tax-forms-table table-bordered table-striped">
                                    <thead class="">
                                        <tr>
                                            <th class="text-center">No.</th>
                                            <th class="text-center">Name</th>
                                            <th class="text-center">UID</th>
                                            <th class="text-center">PAN Number</th>
                                            <th class="text-center">Aadhaar Number</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($folio_data['holders'])) : ?>
                                            <?php foreach ($folio_data['holders'] as $index => $row) : ?>
                                                <tr>
                                                    <td class="text-center"><?php echo ++$index; ?></td>
                                                    <td class="text-center"><?php echo empty($row['name']) ? 'N/A' : $row['name'] ?></td>
                                                    <td class="text-center"><?php echo empty($row['uid']) ? 'N/A' : $row['uid'] ?></td>
                                                    <td class="text-center"><?php echo empty($row['pan']) ? 'N/A' : $row['pan'] ?></td>
                                                    <td class="text-center"><?php echo empty($row['aadhar']) ? 'N/A' : $row['aadhar'] ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="alert alert-info mt-4" role="alert">
                                                No data available at the moment.
                                            </div>
                                        <?php endif ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php foreach ($folio_data['holders'] as $index => $row) : ?>
                                <?php if (!empty($row['name'])) : ?>
                                    <div class="card flex-fill shadow-md border-0 rounded d-md-none f-flex mb-3">
                                        <div class="card-body">
                                            <div class="d-flex mb-1">
                                                <div class="d-flex align-items-center flex-fill gap-3 col-6">
                                                    <i class="bi bi-person fs-4 text-warning"></i>
                                                    <p class="h5 m-0 holder-name-font"><?= esc_html($row['name']) ?? 'N/A'; ?></p>
                                                </div>
                                                <div class="d-flex align-items-center flex-fill justify-content-end gap-3 col-6">
                                                    <i class="bi bi-credit-card text-info" data-toggle="tooltip" data-placement="top" title="PAN"></i>
                                                    <p class="m-0 text-break text-wrap"><?= !empty($row['pan']) ? esc_html($row['pan']) : 'N/A'; ?></p>
                                                </div>
                                            </div>
                                            <div class="d-flex">
                                                <div class="d-flex align-items-center ms-1 flex-fill gap-3 col-6">
                                                    <i class="bi bi-suit-heart text-info" data-toggle="tooltip" data-placement="top" title="UID"></i>
                                                    <p class="m-0 text-break text-wrap"><?= !empty($row['uid']) ? esc_html($row['uid']) : 'N/A'; ?></p>
                                                </div>
                                                <div class="d-flex align-items-center flex-fill justify-content-end gap-3 col-6">
                                                    <i class="bi bi-person-vcard text-info" data-toggle="tooltip" data-placement="top" title="Aadhaar No."></i>
                                                    <p class="m-0 text-break text-wrap"><?= !empty($row['aadhar']) ? esc_html($row['aadhar']) : 'N/A'; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div id="show-Certificates" class="accordion mt-2 show-folio-info" id="reportAccordion" style="display:none;">
                        <?= certificate_html($folio_data['certificates']) ?>
                    </div>
                    <!-- <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div> -->
                </div>
            </div>
        </div>
    <?php
    return ob_get_clean();
});

add_shortcode('company_profile', function () {

    $user_information = pods('user', get_current_user_id());
    $_SESSION['request_report_generate_no'] = 0;
    if (empty($user_information->field('isin'))) {
        // error_log(empty($user_information['isin']));
        redirect_if_not_logged_in();
    }
    error_log("User info: " . print_r($user_information, true));
    error_log("User Company logo: " . print_r($user_information->field('company_logo')['guid'], true));

    $company_logo = $user_information->field('company_logo')['guid'];
    $fetch_all_company_details = fetch_all_company_details($user_information->field('isin'));
    // error_log("Company Details: " . print_r($report_dates, true));

    $company_dashboard_data = $fetch_all_company_details['company_dashboard'];
    $company_info = $fetch_all_company_details['company_dashboard']['company'];
    // error_log("Company Info: " . print_r($company_info, true));
    if (empty($company_dashboard_data) || empty($company_info) || $company_info['name'] == 'N/A') {
        return error_html();
    }
    // error_log("Company Information: " . print_r($company_info, true));

    $reports_list = [];
    if (!empty($_SESSION['report_details'])) {
        $reports_list = $_SESSION['report_details'];
    }
    // error_log("Reports: " . print_r($reports_list, true));

    $company_data = [
        'name' => $company_info['name'] ?? 'N/A',
        'identifier' => $user_information->field('isin'),
        'contact_info' => [
            'email' => !empty($company_info['email']) ? $company_info['email'] : 'N/A',
            'phone' => !empty($company_info['phone']) ? $company_info['phone'] : 'N/A',
            'location' => company_full_address($company_info),
            'equity' => !empty($company_info['equity']) ? $company_info['equity'] : 'N/A',
            'face_value' => !empty($company_info['face_value']) ? $company_info['face_value'] : 'N/A',
            'depository_code' => !empty($company_info['depository_code']) ? $company_info['depository_code'] : 'N/A',
        ],
        'tabs' => [
            'Summary',
            'Download Reports',
        ],
        'standard_reports' => $reports_list,
    ];

    $dashboard_data = [
        'date' => $company_dashboard_data['reportDate'] ?? "N/A",
        'total_folio' => $company_dashboard_data['totalFolios'] ?? 'N/A',
        'total_equity' => $company_dashboard_data['equity'] ?? 'N/A',
        'total_shares' => $company_dashboard_data['totalShares'] ?? 'N/A',
        'distribution_details' => get_description_and_equity_data($company_dashboard_data['distributionSummary']),
        'shareholding_details' => get_description_and_equity_data($company_dashboard_data['shareHoldingPattern']),
    ];

    wp_localize_script('csp-script', 'report_dates', $reports_list);
    wp_localize_script('csp-script', 'distribution_details', $dashboard_data['distribution_details']);
    wp_localize_script('csp-script', 'shareholding_details', $dashboard_data['shareholding_details']);

    // error_log("Standard Reports: " . print_r($dashboard_data['distribution_details'], true));
    // error_log("Dashboard Data: " . print_r($company_dashboard_data, true));

    ob_start();
    ?>
        <?= confirmation_modal() ?>
        <div class="bg-white py-4 card border-0">
            <div class="d-flex align-items-center justify-content-between px-md-5 px-4 pb-4 pt-2">
                <div class="d-flex col-12 align-items-center gap-4">
                    <?php if (!empty($company_logo)) : ?>
                        <div class="col-4 col-md-2">
                            <img src="<?= $company_logo ?>" alt="logo" />
                        </div>
                    <?php else : ?>
                        <div class="col-4 rounded-circle bg-info text-white d-flex align-items-center justify-content-center" style="min-width: 60px; height: 60px;">
                            <p class="mb-0" style="font-size: x-large;"><i class="bi bi-buildings"></i></p>
                        </div>
                    <?php endif; ?>
                    <div class="col-8 col-md-10">
                        <h4 class="mb-1"><?= esc_html($company_data['name']); ?></h4>
                        <p class="mb-0 text-muted d-flex align-items-center"><i class="bi bi-person-vcard-fill fs-5 me-2 text-muted" data-toggle="tooltip" data-placement="top" title="Company ISIN"></i></i><?= esc_html($company_data['identifier']); ?></p>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-wrap text-secondary">
                <div class="col-12 col-md-6 border-top border-end d-flex align-items-center py-3 py-md-2 px-md-5 px-4">
                    <p class="m-0 align-items-center d-flex"><i class="bi bi-telephone-fill text-primary me-3 fs-4" data-toggle="tooltip" data-placement="top" title="Mobile Number"></i> <?= esc_html($company_data['contact_info']['phone']); ?></p>
                </div>
                <div class="col-12 col-md-6 border-top d-flex align-items-center py-3 py-md-2 px-4">
                    <p class="m-0 align-items-center d-flex text-break"><i class="bi bi-envelope-fill text-warning me-3 fs-4" data-toggle="tooltip" data-placement="top" title="Email id"></i> <?= esc_html($company_data['contact_info']['email']); ?></p>
                </div>
                <div class="col-12 col-md-6 border-top border-bottom border-end d-flex align-items-center py-3 py-md-2 px-md-5 px-4">
                    <p class="m-0 align-items-center d-flex text-break"><i class="bi bi-geo-alt-fill text-success me-3 fs-4" data-toggle="tooltip" data-placement="top" title="Company Address"></i> <?= esc_html($company_data['contact_info']['location']); ?></p>
                </div>
                <div class="col-12 col-md-6 border-bottom border-top d-flex remove-border-top align-items-center py-3 py-md-2 px-4">
                    <p class="m-0 align-items-center d-flex"><i class="bi bi-diagram-3-fill text-danger me-3 fs-4" data-toggle="tooltip" data-placement="top" title="Equity"></i> <?= esc_html($company_data['contact_info']['equity']); ?></p>
                </div>
                <div class="col-12 col-md-6 border-bottom border-end d-flex align-items-center py-3 py-md-2 px-md-5 px-4">
                    <p class="m-0 align-items-center d-flex"><i class="bi bi-123 text-info me-3 fs-4" data-toggle="tooltip" data-placement="top" title="Face value"></i> <?= esc_html($company_data['contact_info']['face_value']); ?></p>
                </div>
                <div class="col-12 col-md-6 border-bottom d-flex align-items-center py-3 py-md-2 px-4">
                    <p class="m-0 align-items-center d-flex"><i class="bi bi-code text-blue me-3 fs-4" data-toggle="tooltip" data-placement="top" title="Depository Code"></i> <?= esc_html($company_data['contact_info']['depository_code']); ?></p>
                </div>
            </div>
            <div class="d-flex justify-content-md-start justify-content-around align-items-center my-2 mt-md-5 mt-3 border-bottom">
                <?php foreach ($company_data['tabs'] as $index => $tab) : ?>
                    <div id="<?= $tab ?>" class="px-4 py-3 col-md-2 text-center company-tabs <?php echo $index == 0 ? 'folio-active' : '' ?> cursor-pointer" style="font-size: 18px; text-wrap: nowrap;" onclick="changeFolioTab('<?= $tab ?>')"><?= $tab ?></div>
                <?php endforeach; ?>
            </div>
            <div id="show-Summary" class="mx-md-5 mx-4 mt-md-3 mt-2 show-folio-info">
                <?php if (!empty($company_dashboard_data)) : ?>
                    <div id="display_date" class="d-block alert alert-info">
                        <p class="m-0">Displaying reports for the date: <strong><?= date('d M, Y', strtotime($dashboard_data['date'])) ?></strong></p>
                    </div>

                    <div class="row gap-3 gap-md-0">
                        <div class="col-md-4">
                            <div class="card shadow-sm py-3 px-4">
                                <i class="bi bi-check-lg text-success mb-3" style="font-size: 40px;"></i>
                                <p class="mb-2 text-muted">Equities Matched</p>
                                <h4 class="text-black"><?= $dashboard_data['total_equity'] ?></h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card shadow-sm py-3 px-4">
                                <i class="bi bi-stack text-warning mb-3" style="font-size: 40px;"></i>
                                <p class="mb-2 text-muted">Total Shares</p>
                                <h4 class="text-black"><?= $dashboard_data['total_shares'] ?></h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card shadow-sm py-3 px-4">
                                <i class="bi bi-bar-chart-line text-primary mb-3" style="font-size: 40px;"></i>
                                <p class="mb-2 text-muted">Total Folios</p>
                                <h4 class="text-black"><?= $dashboard_data['total_folio'] ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4 gap-3 gap-md-0">
                        <!-- Distribution Summary -->
                        <div class="col-md-6">
                            <div class="card shadow-sm p-4">
                                <h5>Distribution Summary</h5>
                                <div class="d-flex align-items-center flex-wrap flex-lg-nowrap gap-lg-2 justify-content-start justify-content-lg-center w-100 mt-2">
                                    <div class="col-12 col-lg-7 d-flex align-items-center justify-content-center" style="min-height: 300px; overflow:hidden;">
                                        <canvas id="distributionChart"></canvas>
                                    </div>
                                    <div id="distribution-chart-label" class="d-flex flex-column gap-2 col-12 col-lg-5 px-3 mt-lg-0 mt-4"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Shareholding Pattern -->
                        <div class="col-md-6">
                            <div class="card shadow-sm p-4">
                                <h5>Shareholding Pattern</h5>
                                <div class="d-flex align-items-center flex-wrap flex-lg-nowrap gap-lg-2 justify-content-center w-100 mt-2">
                                    <div class="col-12 col-lg-6 d-flex align-items-center justify-content-center" style="min-height: 300px; overflow:hidden;">
                                        <canvas id="shareholdingChart"></canvas>
                                    </div>
                                    <div id="shareholding-chart-label" class="d-flex flex-column gap-2 col-12 col-lg-6 px-3 mt-4 mt-lg-0"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else :  ?>
                    <div class="alert alert-info mt-4 alert-error" role="alert">
                        No data available on the dashboard at the moment.
                    </div>
                <?php endif; ?>
            </div>
            <div id="show-Download Reports" class="accordion mt-md-3 mt-2 px-md-5 px-4 show-folio-info show-download-reports" style="display:none;">
                <?php if (!empty($company_data['standard_reports'])) : ?>
                    <?php foreach ($company_data['standard_reports'] as $index => $report): ?>
                        <div class="card mb-3">
                            <div class="card-header d-flex flex-wrap flex-md-nowrap justify-content-between align-items-center bg-white">
                                <div class="d-flex align-items-center">
                                    <div class="d-flex align-items-start align-items-md-center" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo $index; ?>" aria-expanded="false">
                                        <i class="bi bi-filetype-xls text-dark fs-4"></i>
                                        <span class="p-2 d-flex flex-column" style="text-wrap: nowrap;">
                                            <?php echo esc_html($report['report_type']); ?>
                                            <small class="font-weight-light text-muted text-break text-wrap" style="font-weight: 500;">
                                                This comprehensive Excel sheet provides you with <?= esc_html($report['report_type']); ?></small>
                                        </span>
                                        <?php if (!empty($report['sub_reports'])): ?>
                                            <i class="bi bi-chevron-down ms-1"></i>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="d-flex gap-md-2 align-items-center justify-content-between width-md-full mb-2 mb-md-0">
                                    <div class="d-flex align-items-center ms-4 gap-4">
                                        <div class="input-group bg-white" style="max-width: 160px;">
                                            <input type="text" id="<?= $report['report_type'] ?>" class="datepicker-input bg-white form-control border-end-0" placeholder="Select a date">
                                            <span class="input-group-text bg-white">
                                                <i class="bi bi-calendar4-week"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <button id="show-<?= $report['report_type'] ?>" onclick="downloadReport('<?= $report['report_type'] ?>')" class="btn btn-sm me-2 p-1" disabled title="Download" style="background: #212d45; color: white;">
                                            <i class="bi bi-download fs-5"></i>
                                        </button>
                                    </div>
                                    <!-- <button class="btn btn-warning btn-sm" title="Regenerate">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button> -->
                                </div>
                            </div>
                            <?php if (!empty($report['sub_reports'])): ?>
                                <div id="collapse-<?php echo $index; ?>" class="collapse" data-bs-parent="#reportAccordion">
                                    <div class="card-body">
                                        <div class="d-flex flex-column gap-2">
                                            <?php foreach ($report['sub_reports'] as $sub_report): ?>
                                                <div class="shadow-sm rounded border p-3">
                                                    <?php echo esc_html($sub_report); ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else :  ?>
                    <div class="alert alert-info mt-4 alert-error" role="alert">
                        Oops! No reports found. Click here to <span id="fetch-reports" class="text-warning" style="cursor: pointer;">refresh </span> and see if new reports are available.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php
    return ob_get_clean();
});
