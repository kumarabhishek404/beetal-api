<?php

add_shortcode('folio_summary', function () {
    // Replace placeholders with actual data
    $folio_data = [
        'folio_number' => '1247879',
        'name' => 'GAGANDEEP SINGH REEN',
        'address_list' => [
            array('address' => 'WZ-10A STREET NO 10, SHIV NAGAR NEW DELHI', "is_primary" => false),
            array('address' => 'WZ-10A STREET NO 1, SHIV NAGAR NEW DELHI',  "is_primary" => true)
        ],
        'bank_details' => [
            array(
                'holder_name' => 'GAGANDEEP SINGH REEN',
                'account_number' => '',
                'bank_name' => 'BANK OF INDIA',
                'ifsc' => '',
            ),
            array(
                'holder_name' => 'BALBIR SINGH',
                'account_number' => '1024001799920168',
                'bank_name' => 'BANK OF BADODRA',
                'ifsc' => 'BOB124023',
            ),
        ],
        'holders' => [
            [
                'name' => 'GAGANDEEP SINGH',
                'pan' => 'AJHPR3305F',
                'aadhaar' => 'N/A',
                'uid' => 'N/A',
            ],
            [
                'name' => 'BALBIR SINGH',
                'pan' => 'N/A',
                'aadhaar' => 'N/A',
                'uid' => 'N/A',
            ],
            [
                'name' => 'BHUPENDRA SINGH',
                'pan' => 'N/A',
                'aadhaar' => 'N/A',
                'uid' => 'N/A',
            ]
        ],
        'nominees' => ['Add Nominee 1', 'Add Nominee 2', 'Add Nominee 3'],
        'guardian' => array(
            array(
                'name' => 'John Doe',
                'address' => '123 Main Street, Anytown, CA 12345',
                'phone' => '123-456-7890',
                'email' => 'johndoe@example.com'
            ),
        ),
        'kyc_status' => 'KYC Incompleted',
        'company' => 'NTPC LIMITED',
    ];

    ob_start();
?>

    <div class="row">
        <div class="col-12 d-flex flex-column">
            <label>Folio Number</label>
            <div class="d-flex gap-3">

                <input type="text" class="form-control flex-grow-1" name="folio_number" />
                <button type="submit" class="btn btn-warning col-3">Submit</button>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <div class="card shadow-sm">
            <div class="card-header bg-warning text-white">
                <h4 class="mb-0"><i class="bi bi-folder-fill"></i> Folio Summary</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><i class="bi bi-person-fill"></i> <strong>Name:</strong> <?php echo $folio_data['name']; ?></p>
                        <p><i class="bi bi-hash"></i> <strong>Folio Number:</strong> <?php echo $folio_data['folio_number']; ?></p>
                    </div>
                    <div class="col-md-6 text-end">
                        <p><i class="bi bi-shield-fill-exclamation"></i> <strong>KYC Status:</strong> <?php echo $folio_data['kyc_status']; ?></p>
                        <p><i class="bi bi-building"></i> <strong>Company:</strong> <?php echo $folio_data['company']; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Address List -->
        <div class="card mt-3">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="bi bi-geo-alt-fill"></i> Address List</h5>
            </div>
            <div class="card-body">
                <?php foreach ($folio_data['address_list'] as $address) : ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <h6 class="card-title d-flex justify-content-between mb-0">
                                <span>
                                    <i class="bi bi-geo-fill"></i> <?php echo $address['address']; ?>
                                </span>
                                <?php if ($address['is_primary']) : ?>
                                    <span class="badge bg-primary text-end">Primary</span>
                                <?php endif; ?>
                            </h6>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Bank Account Details -->
        <div class="card mt-3">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="bi bi-bank"></i> Bank Account Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($folio_data['bank_details'] as $bank_detail) : ?>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <p><i class="bi bi-person-check-fill"></i> <strong>Holder Name:</strong> <?php echo $bank_detail['holder_name']; ?></p>
                                    <p><i class="bi bi-credit-card-2-back-fill"></i> <strong>Account Number:</strong> <?php echo $bank_detail['account_number']; ?></p>
                                    <p><i class="bi bi-building"></i> <strong>Bank Name:</strong> <?php echo $bank_detail['bank_name']; ?></p>
                                    <p class="mb-0"><i class="bi bi-key-fill"></i> <strong>IFSC Code:</strong> <?php echo $bank_detail['ifsc']; ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Holders Details -->
        <div class="card mt-3">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="bi bi-people-fill"></i> Holders Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($folio_data['holders'] as $index => $holder) : ?>
                        <div class="col-md-4">
                            <div class="card mb-3 shadow-sm">
                                <div class="card-body">
                                    <h6 class="card-title"><i class="bi bi-person-circle"></i> <strong>Holder <?php echo $index + 1; ?></strong></h6>
                                    <p class="card-text"><i class="bi bi-person"></i> <strong>Name:</strong> <?php echo $holder['name']; ?></p>
                                    <p class="card-text"><i class="bi bi-file-earmark-text-fill"></i> <strong>PAN:</strong> <?php echo $holder['pan']; ?></p>
                                    <p class="card-text"><i class="bi bi-file-lock2-fill"></i> <strong>Aadhaar:</strong> <?php echo $holder['aadhaar']; ?></p>
                                    <p class="card-text"><i class="bi bi-ui-checks-grid"></i> <strong>UID:</strong> <?php echo $holder['uid']; ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Nomination List -->
        <div class="card mt-3">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="bi bi-person-plus-fill"></i> Nomination List</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-horizontal m-0">
                    <?php foreach ($folio_data['nominees'] as $nominee) : ?>
                        <li class="list-group-item"><i class="bi bi-person-plus"></i> <?php echo $nominee; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <!-- Guardian Details -->
        <div class="card mt-3">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="bi bi-person-badge-fill"></i> Guardian's Details</h5>
            </div>
            <div class="card-body">
                <?php foreach ($folio_data['guardian'] as $guardian) : ?>
                    <div class="card mb-3">
                        <div class="card-body row">
                            <p><i class="bi bi-person-fill me-2 "></i> <?php echo $guardian['name']; ?></p>
                            <p><i class="bi bi-house-fill me-2 "></i> <?php echo $guardian['address']; ?></p>
                            <p><i class="bi bi-telephone-fill me-2"></i> <?php echo $guardian['phone']; ?></p>
                            <p><i class="bi bi-envelope-fill me-2"></i> <?php echo $guardian['email']; ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

<?php
    return ob_get_clean();
});

// Folio Information shortcode
add_shortcode('profile_card', function () {
    $company_isin = isset($_GET['company']) ? sanitize_text_field($_GET['company']) : '';
    $folio_number = isset($_GET['folio']) ? sanitize_text_field($_GET['folio']) : '';

    if (empty($company_isin) && empty($folio_number)) {
        $user_information = pods('user', get_current_user_id());
        if (!empty($user_information->field('isin'))) {
            $company_isin = $user_information->field('isin');
        }
        if (!empty($user_information->field('folio_no'))) {
            $folio_number = $user_information->field('folio_no');
        }
    }

    $folio_params = [
        'folio_no' => $folio_number,
        'isin' => $company_isin,
    ];

    if (empty($_SESSION['folio_data']) || count($_SESSION['folio_data']) == 0) {
        $folio_data = csp_fetch_api_response($folio_params);
        $company_info = $folio_data['company'];
    } else {
        foreach ($_SESSION['folio_data'] as $folio) {
            if ($folio["folio_no"] === $folio_number) {
                $folio_data = get_folio_information($folio);
                break;
            }
        }

        if (empty($folio_data) || empty($folio_data['source_type']) || $folio_data['source_type'] == 'PHYSICAL') {
            $folio_data = csp_fetch_api_response($folio_params);
            $company_info = $folio_data['company'];
        } else {
            foreach ($_SESSION['company_data'] as $company) {
                if ($company["isin_code"] === $company_isin) {
                    $company_info = $company;
                    break;
                }
            }
        }
    }
    error_log(print_r($company_info, true));
    if (empty($folio_data['folio_number'])) {
        return error_html();
    }
    ob_start();
?>
    <div class="bg-white py-4 h-100 card border-0">
        <div class="px-4 px-md-5 py-3">
            <?= do_shortcode('[breadcrumbs dashboard_url="' . site_url('/investor-service-request') . '" dashboard_label="Dashboard" ticket_name="Company #' . $company_isin . '"]'); ?>
        </div>
        <div class="d-flex align-items-center justify-content-between px-md-5 px-4 py-2 mb-4">
            <div class="d-flex gap-4  align-items-center">
                <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center" style="min-width: 70px; min-height: 70px;">
                    <p class="mb-0" style="font-size: x-large;"><i class="bi bi-buildings text-white fs-2"></i></p>
                </div>
                <div>
                    <h4 class="mb-1 text-dark"><?= esc_html($company_info['name']); ?></h4>
                    <p class="mb-0 text-muted d-flex align-items-center"><i class="bi bi-person-fill text-warning me-2 fs-5" data-toggle="tooltip" data-placement="top" title="Holder Name"></i><?= esc_html($folio_data['name']); ?></p>
                    <small class="text-muted d-flex align-items-center"><i class="bi bi-file-earmark-text-fill text-secondary me-2 fs-5" data-toggle="tooltip" data-placement="top" title="Folio Number"></i><?= esc_html($folio_data['folio_number']); ?></small>
                </div>
            </div>
        </div>
        <div class="d-flex flex-wrap text-secondary">
            <div class="col-md-6 col-12 border-top border-end align-items-center d-flex py-3 py-md-2 px-md-5 px-4">
                <p class="m-0 d-flex align-items-center"><i class="bi bi-telephone-fill text-info me-3 fs-4" data-toggle="tooltip" data-placement="top" title="Mobile Number"></i> <?= esc_html($folio_data['phone']); ?></p>
            </div>
            <div class="col-md-6 col-12 border-top d-flex align-items-center py-3 py-md-2 px-4">
                <p class="m-0 d-flex align-items-center text-break"><i class="bi bi-envelope-fill text-warning me-3 fs-4" data-toggle="tooltip" data-placement="top" title="Email Id"></i> <?= esc_html($folio_data['email']); ?></p>
            </div>
            <div class="col-md-6 col-12 border-top border-bottom border-end d-flex align-items-center py-3 py-md-2 px-md-5 px-4">
                <p class="m-0 d-flex align-items-center"><i class="bi bi-credit-card-fill text-primary me-3 fs-4" data-toggle="tooltip" data-placement="top" title="PAN Number"></i> <?= esc_html($folio_data['pan_number']); ?></p>
            </div>
            <div class="col-md-6 col-12 border-bottom border-top d-flex remove-border-top align-items-center py-3 py-md-2 px-4">
                <p class="m-0 d-flex align-items-center"><i class="bi bi-person-badge-fill <?= esc_html($folio_data['kyc_status']) == "KYC Completed" ? 'text-success' : 'text-danger' ?> me-3 fs-4" data-toggle="tooltip" data-placement="top" title="KYC Status"></i> <?= esc_html($folio_data['kyc_status']); ?></p>
            </div>
            <div class="col-md-6 col-12 border-bottom border-end d-flex align-items-center py-3 py-md-2 px-md-5 px-4">
                <p class="m-0 d-flex align-items-center"><i class="bi bi-geo-alt-fill text-success me-3 fs-4" data-toggle="tooltip" data-placement="top" title="Address"></i> <?= esc_html($folio_data['address']); ?></p>
            </div>
            <div class="col-md-6 col-12 border-bottom d-flex align-items-center py-3 py-md-2 px-4">
                <p class="m-0 d-flex align-items-center"><i class="bi bi-person-vcard-fill text-secondary me-3 fs-4" data-toggle="tooltip" data-placement="top" title="Folio Description"></i> <?= esc_html($folio_data['description']); ?></p>
            </div>
            <div class="col-md-6 col-12 border-bottom border-end d-flex align-items-center py-3 py-md-2 px-4 px-md-5">
                <p class="m-0 d-flex align-items-center"><i class="bi bi-graph-up text-warning me-3 fs-4" data-toggle="tooltip" data-placement="top" title="No. Of Shares/Total Holding"></i> <?= esc_html($folio_data['shares']); ?></p>
            </div>
            <div class="col-md-6 col-12 border-bottom d-flex align-items-center py-3 py-md-2 px-4">
                <p class="m-0 d-flex align-items-center"><i class="bi bi-file-binary-fill text-info me-3 fs-4" data-toggle="tooltip" data-placement="top" title="Source Type"></i> <?= esc_html($folio_data['source_type']); ?></p>
            </div>
        </div>
        <div class="d-flex align-items-center border-bottom my-md-4 my-3 px-4 px-md-5">
            <div id="folio-tab1" class="p-3 text-center folio-tabs folio-active cursor-pointer" onclick="changeFolioTab('folio-tab1')">Summary</div>
            <div id="folio-tab2" class="p-3 text-center folio-tabs cursor-pointer" onclick="changeFolioTab('folio-tab2')">Certificates</div>
        </div>

        <div id="show-folio-tab1" class="show-folio-info px-4 px-md-5">
            <div class="d-flex mb-4 m-0 p-0 flex-wrap flex-md-nowrap gap-3">
                <!-- Address List Card -->
                <div class="card col-md-6 col-12 flex-fill shadow-md border-0 rounded">
                    <div class="card-body p-0">
                        <div class="d-flex align-items-center card-header-bg border-warning py-3 px-4 gap-3">
                            <p class="h5 m-0">Address List</p>
                        </div>
                        <?php if (!empty($folio_data['address_list']['primary'])) : ?>
                            <div class="m-3 mx-4 mt-4 pb-3">
                                <div class="d-flex align-items-start justify-content-between gap-3">
                                    <span class="d-flex gap-3 align-items-start">
                                        <!-- <span class="d-flex align-items-center justify-content-center bg-light gap-2 me-2 rounded-circle" style="height: 40px; min-width: 40px;">
                                            </span> -->
                                        <i class="bi bi-geo-alt text-info fs-4"></i>
                                        <span class="d-flex align-items-center">
                                            <p class="m-0 address-list">
                                                <?= esc_html($folio_data['address_list']['primary']['line_1']); ?>,
                                                <?= esc_html($folio_data['address_list']['primary']['line_2']); ?><br />
                                                <?= esc_html($folio_data['address_list']['primary']['line_3']); ?>,
                                                <?= esc_html($folio_data['address_list']['primary']['city']); ?><br />
                                                <?= esc_html($folio_data['address_list']['primary']['state']); ?>,
                                                <?= esc_html($folio_data['address_list']['primary']['pin_code']); ?>
                                            </p>
                                        </span>
                                    </span>
                                    <span class="badge bg-primary text-end ms-3" style="height: fit-content;">
                                        <i class="bi bi-star-fill"></i> Primary
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($folio_data['address_list']['correspondence'])) : ?>
                            <div class="m-3 mx-4 border-muted border-top pb-3">
                                <div class="d-flex align-items-start gap-3">
                                    <!-- <span class="d-flex align-items-center justify-content-center bg-light gap-2 me-2 rounded-circle" style="height: 40px; min-width: 40px;">
                                        </span> -->
                                    <i class="bi bi-geo-alt text-info fs-4"></i>
                                    <span class="d-flex align-items-center">
                                        <p class="m-0 address-list">
                                            <?= esc_html($folio_data['address_list']['correspondence']['line_1']); ?>,
                                            <?= esc_html($folio_data['address_list']['correspondence']['line_2']); ?><br />
                                            <?= esc_html($folio_data['address_list']['correspondence']['line_3']); ?>,
                                            <?= esc_html($folio_data['address_list']['correspondence']['city']); ?><br />
                                            <?= esc_html($folio_data['address_list']['correspondence']['state']); ?>,
                                            <?= esc_html($folio_data['address_list']['correspondence']['pin_code']); ?>
                                        </p>
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Nominee List Card -->
                <div class="card col-md-6 col-12 flex-fill shadow-md border-0 rounded">
                    <div class="card-body p-0">
                        <div class="d-flex card-header-bg align-items-center border-warning py-3 px-4 gap-3">
                            <!-- <i class="bi bi-geo-alt text-primary fs-4"></i> -->
                            <p class="h5 m-0">Nominee List</p>
                        </div>
                        <?php if (!empty($folio_data['nominees'])) : ?>
                            <?php foreach ($folio_data['nominees'] as $index => $row) : ?>
                                <div class="<?= $index !=  count($folio_data['nominees']) - 1 ? 'border-bottom' : '' ?> py-3">
                                    <div class="mx-4">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex flex-column">
                                                <div class="d-flex align-items-center mb-2 gap-3">
                                                    <i class="bi bi-people-fill fs-4 text-warning"></i>
                                                    <p class="h5 m-0"><?= esc_html($row['name']); ?></p>
                                                </div>
                                                <?php if (!empty($row['guardian_name'])) : ?>
                                                    <div class="d-flex align-items-center mb-2 ms-1 gap-3">
                                                        <i class="bi bi-heart-fill text-secondary text-info" data-toggle="tooltip" data-placement="top" title="Relation"></i>
                                                        <p class="m-0 ms-1 text-break text-wrap"><?= esc_html($row['relationship_description']); ?> of <?= esc_html($row['guardian_name']); ?></p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="progress-circle" data-toggle="tooltip" data-placement="top" title="Percentage of shares">
                                                <svg width="60" height="60" viewBox="0 0 60 60">
                                                    <circle class="progress-ring" cx="30" cy="30" r="25" stroke-width="4" stroke-dasharray="157 157" stroke-dashoffset="0"></circle>
                                                    <circle class="progress-ring-fill" cx="30" cy="30" r="25" stroke-width="4" stroke-dasharray="157 157" stroke-dashoffset="<?php echo (100 - (int) intval(esc_html($row['percentage_of_shares'])) ?? 0) / 100 * 157; ?>" />
                                                </svg>
                                                <div class="progress-text"><?= intval(esc_html($row['percentage_of_shares'])) ?? 0 ?>%</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <div class="d-flex justify-content-between align-items-center mb-2 card-body px-4">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-person-fill fs-4 text-warning" data-toggle="tooltip" data-placement="top" title="Guardian name"></i>
                                    <p class="h5 m-0">N/A</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-between mb-4 flex-wrap flex-md-nowrap gap-3">
                <!-- Bank Details Card -->
                <?php foreach ($folio_data['bank_details'] as $index => $row) : ?>
                    <div class="card flex-fill col-12 col-md-6 shadow-md border-0 rounded d-flex">
                        <div class="d-flex card-header-bg align-items-center border-warning py-3 px-4 gap-3">
                            <!-- <i class="bi bi-geo-alt text-primary fs-4"></i> -->
                            <p class="h5 m-0">Bank Details</p>
                        </div>
                        <div class="card-body px-4">
                            <?php if (!empty($row['bank_name'])) : ?>
                                <div class="d-flex align-items-center mb-2 gap-2">
                                    <i class="bi bi-person-fill fs-4 text-secondary text-warning" data-toggle="tooltip" data-placement="top" title="Account holder name"></i>
                                    <p class="h5 m-0"><?= esc_html($row['holder_name']) ?? 'N/A'; ?></p>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mb-2 ">
                                    <div class="d-flex align-items-center ms-1 gap-2">
                                        <i class="bi bi-bank text-secondary text-info" data-toggle="tooltip" data-placement="top" title="Bank Name and IFSC"></i>
                                        <p class="m-0 ms-1 text-break text-wrap"><?= !empty($row['bank_name']) ? esc_html($row['bank_name']) : 'N/A'; ?>
                                            (<?= !empty($row['ifsc']) ? esc_html($row['ifsc']) : ''; ?>)
                                        </p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center ms-1 mb-2 gap-2">
                                    <i class="bi bi-123 text-secondary text-info" data-toggle="tooltip" data-placement="top" title="Account Number"></i>
                                    <p class="m-0 ms-1 text-break text-wrap"><?= !empty($row['account_number']) ? esc_html($row['account_number']) : 'N/A'; ?></p>
                                </div>
                            <?php else : ?>
                                <div class="d-flex align-items-center mb-2 gap-2">
                                    <i class="bi bi-bank fs-4 text-secondary text-warning" data-toggle="tooltip" data-placement="top" title="Account holder name"></i>
                                    <p class="h5 m-0">N/A</p>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Guardian Information Card -->
                    <div class="card flex-fill col-12 shadow-md border-0 col-md-6 rounded">
                        <div class="d-flex card-header-bg align-items-center border-warning py-3 px-4 gap-3">
                            <p class="h5 m-0">Guardian Details</p>
                        </div>
                        <div class="card-body m-0 px-4">
                            <?php if (!empty($folio_data['guardian']['name']) && $folio_data['guardian']['name'] != 'N/A') : ?>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-person-fill fs-4 text-warning" data-toggle="tooltip" data-placement="top" title="Guardian name"></i>
                                        <p class="h5 m-0"><?= esc_html($folio_data['guardian']['name']) ?? 'N/A'  ?></p>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-person-heart text-secondary text-info" data-toggle="tooltip" data-placement="top" title="Guardian relation"></i>
                                        <p class="m-0 ms-1"><?= esc_html($folio_data['guardian']['relation']) ?? 'N/A'  ?></p>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center ms-1 gap-2">
                                        <i class="bi bi-person-vcard text-secondary text-info" data-toggle="tooltip" data-placement="top" title="Guardian PAN"></i>
                                        <p class="m-0 ms-1"><?= esc_html($folio_data['guardian']['pan']) ?? 'N/A'  ?></p>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-person-badge text-secondary text-info" data-toggle="tooltip" data-placement="top" title="Guardian UUID"></i>
                                        <p class="m-0"><?= esc_html($folio_data['guardian']['uuid']) ?? 'N/A'  ?></p>
                                    </div>
                                </div>
                            <?php else : ?>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-person-fill fs-4 text-warning" data-toggle="tooltip" data-placement="top" title="Guardian name"></i>
                                        <p class="h5 m-0">N/A</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

            </div>
            <!-- Holder Details Card -->
            <div class="card flex-fill rounded shadow-md border-0 d-flex mb-3">
                <div class="d-flex card-header-bg align-items-center border-warning py-3 px-4 gap-3">
                    <p class="h5 m-0">Holder Details</p>
                </div>
                <div class="card-body flex-column flex-md-row d-flex m-0 p-0 px-2">
                    <?php $holder1 = $folio_data['holders'][0]; ?>
                    <?= holder_html($holder1['name'], $holder1['pan'], $holder1['aadhar'], $holder1['uid'], 1); ?>
                    <?php $holder2 = $folio_data['holders'][1]; ?>
                    <?= holder_html($holder2['name'], $holder2['pan'], $holder2['aadhar'], $holder2['uid'], 2); ?>
                    <?php $holder3 = $folio_data['holders'][2]; ?>
                    <?= holder_html($holder3['name'], $holder3['pan'], $holder3['aadhar'], $holder3['uid'], 3); ?>
                </div>
            </div>
        </div>

        <!-- Certificate -->
        <div id="show-folio-tab2" class="show-folio-info px-4" style="display:none;">
            <?= certificate_html($folio_data['certificates']) ?>
        </div>
    </div>
<?php
    return ob_get_clean();
});

add_shortcode('bg_image_div', function () {
    $image_attributes =
        array(
            'image' => 'https://pragmaappscstg.wpengine.com/wp-content/uploads/2024/11/g9792dd6c68cb8cadfe3380395c513373666da34cd61c6865cd2b7cb9af5526bf810e304cca9436d75c9a78f139a97508f363f790066629ac0ec93b3223b6a057_1280-6682491.jpg',  // URL of the background image
            'height' => '130px',
            'width' => '100%',
            'content' => 'cover',
            'overlay_opacity' => '1',
        );

    return '<div class="header-height border-bottom" style="background: #212D45;"></div>';
});
