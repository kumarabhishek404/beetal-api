<?php
add_shortcode('show_ipo_allotment_form', function () {
    $menuList = array(
        // array(
        //     'image_url' => 'https://pragmaappscstg.wpengine.com/wp-content/uploads/2024/11/images-2-2.jpg',
        //     'title' => 'IPO Allotment Status',
        //     'sub_title' => 'Click here to open form of acceptance from open offer.',
        //     'form' => 'form5',
        //     'tab' => 'tab5',
        //     'hash' => 'ipo-allotment-form',
        //     'icon' => 'file-earmark-text-fill'
        // )
    );
    ob_start();
?>
    <div id="main-section" class="d-flex align-items-start py-1 px-4 px-md-5 flex-md-nowrap flex-wrap">
        <div class="align-items-start d-flex h-100 flex-column text-white hidden" id="left-section"
            style="background-color: #06102A;">
            <?= show_menu($menuList, false) ?>
        </div>
        <div class="flex-grow-1 flex-fill" id="center-section">
            <h2 class="m-0 px-3 py-2">IPO Allotment Form</h2>
            <hr class="m-0 form-margin">
            <?= ipo_allotment_form(); ?>
        </div>
        <div class="align-items-start h-100 d-flex hidden text-white" id="right-section" style="background-color: #06102A;">
            <?= show_active_company_list() ?>
        </div>
    </div>

<?php
    return ob_get_clean();
});

add_shortcode('show_company_offers', function () {
    $menuList = array(
        // array(
        //     'image_url' => 'https://pragmaappscstg.wpengine.com/wp-content/uploads/2024/11/buyback-2.webp',
        //     'title' => 'Open/Buy Back/Rights Issue',
        //     'sub_title' => 'Click here to open offer handeled by as Registrar.',
        //     'form' => 'form2',
        //     'tab' => 'tab2',
        //     'hash' => 'open-buyback-right-issue',
        //     'icon' => 'cash-stack'
        // ),
    );
    $company_selected = !empty($_GET['company']) ? $_GET['company'] : '';
    $show_data = show_company_download_forms($company_selected);
    ob_start();
?>
    <div id="main-section" class="d-flex align-items-start flex-md-nowrap flex-wrap">
        <div class="align-items-start d-flex flex-column h-100 text-white hidden" id="left-section"
            style="background-color: #06102A;  ">
            <?php echo show_menu($menuList, false) ?>
        </div>
        <div class="flex-grow-1 px-4 py-1 px-md-5" id="center-section">
            <div class="border-bottom py-2 form-margin px-3">
                <h2 class="fw-bold m-0">Check Entitlements Status</h2>
            </div>
            <div id="form2-company-name" class="m-0">
                <?php if (!empty($show_data['company'])): ?>
                    <?= $show_data['company']; ?>
                <?php endif; ?>
            </div>
            <!-- <hr class="mt-0"> -->
            <?= form2_html_fn(); ?>
            <div class="align-items-start d-flex flex-column pb-3">
                <div id="download-company-forms-data" class="w-100">
                    <?php
                    $show_data = show_company_download_forms($company_selected);
                    if (!empty($show_data['download'])) { ?>
                    <?php
                        echo $show_data['download'];
                    }
                    ?>
                </div>
            </div>
        </div>
        <div class="align-items-start d-flex flex-column h-100 text-white hidden" id="right-section"
            style="background-color: #06102A; ">
            <?= show_active_company_list() ?>
        </div>
    </div>
<?php
    return ob_get_clean();
});

add_shortcode('show_kyc_compliance', function () {
    $menuList = array(
        // array(
        //     'image_url' => 'https://pragmaappscstg.wpengine.com/wp-content/uploads/2024/11/Sekuritance-KYC-Compliance-600x300-1.jpg',
        //     'title' => 'KYC Compliance',
        //     'sub_title' => 'Click here to open KYC Compliances form.',
        //     'form' => 'form3',
        //     'tab' => 'tab3',
        //     'hash' => 'kyc-compliance',
        //     'icon' => 'person-check-fill'
        // ),
    );

    $kyc_list = array(
        array(
            'title' => 'Form ISR-1',
            'description' => 'Request for registering pan, kyc details or changes/updation.',
            'download_link' => 'https://beetal.in/wp-content/uploads/2025/03/Form-ISR-1.pdf'
        ),
        array(
            'title' => 'Form ISR-2',
            'description' => 'Confirmation of Signature of securities holder by the Banker',
            'download_link' => 'https://beetal.in/wp-content/uploads/2025/03/FORM-ISR-2-2.doc'
        ),
        array(
            'title' => 'Form ISR-3',
            'description' => 'Declaration Form for Opting-out of Nominationby holders of physical securities in Listed Companies',
            'download_link' => 'https://beetal.in/wp-content/uploads/2025/03/FORM-ISR-3-2.doc'
        ),
        array(
            'title' => 'Form ISR-4',
            'description' => 'Request for issue of Duplicate Certificate and other Service Requests.',
            'download_link' => 'https://beetal.in/wp-content/uploads/2025/03/FORM-ISR-4.pdf'
        ),
        array(
            'title' => 'Form SH-13',
            'description' => 'Registration for Nomination Form.',
            'download_link' => 'https://beetal.in/wp-content/uploads/2025/03/FORM-SH-13.doc'
        ),
        array(
            'title' => 'Form SH-14',
            'description' => 'Cancellation or Variation of Nomination.',
            'download_link' => "https://beetal.in/wp-content/uploads/2025/03/FORM-SH-14.doc"
        ),
    );
    ob_start();
?>
    <div id="main-section" class="d-flex align-items-start flex-md-nowrap flex-wrap">
        <div class="align-items-start d-flex h-100 flex-column hidden text-white" id="left-section"
            style="background-color: #06102A; ">
            <?php echo show_menu($menuList, false) ?>
        </div>
        <div class="flex-grow-1 px-md-5 px-4 py-1" id="center-section">
            <div class="d-flex flex-wrap flex-md-nowrap gap-3 gap-md-0 justify-content-between align-items-center form-margin border-bottom px-3 py-2">
                <h2 class="m-0">KYC Compliance</h2>
                <span class="btn btn-blue d-flex px-3"
                    onclick="changeTab('<?= site_url('/investor-services/?request_type=KYC') ?>','Investor_Service_Request','<?= is_user_logged_in() ?>')">Request KYC</span>
            </div>
            <?= kyc_complience_html(); ?>
        </div>
        <div class="align-items-start d-flex h-100 hidden text-white flex-column" id="right-section"
            style="background-color: #06102A; ">
            <?= show_active_company_list() ?>
            <!-- <div class="d-flex flex-column gap-2 w-100 pb-4 text-center text-md-start">
                <div class="px-3 border-bottom">
                    <p class="text-white m-0 py-4 h4 fw-bold">KYC Updation</p>
                </div>
                <div class="d-flex flex-column gap-3 mt-3">
                    <?php foreach ($kyc_list as $list): ?>
                        <div class="list-group px-3">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title d-flex justify-content-between m-0">
                                        <?php echo $list['title'] ?>
                                        <a href="<?= $list['download_link'] ?>" class="text-blue" target="_blank"><i
                                                class="fa-solid fa-download"></i></a>
                                    </h5>
                                    <p class="card-text my-1 text-start">
                                        <small>
                                            <?php echo $list['description'] ?>
                                        </small>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div> -->
        </div>
    </div>

<?php
    return ob_get_clean();
});

add_shortcode('show_SUBMISSION_OF_FORM', function () {
    $menuList = array(
        // array(
        //     'image_url' => 'https://pragmaappscstg.wpengine.com/wp-content/uploads/2024/11/TDS-Exemption-1.png',
        //     'title' => 'TDS Exemption',
        //     'sub_title' => 'Click here to open TDS Exemption form.',
        //     'form' => 'form4',
        //     'tab' => 'tab4',
        //     'hash' => 'tds-exemption',
        //     'icon' => 'file-earmark-excel-fill'
        // ),
    );
    $form_list = array(
        array(
            'title' => 'Form 15G',
            'description' => 'Resident individual, HUF, trust, or other assessee (excluding companies or firms) under 60 years of age.',
            'url' => '#'
        ),
        array(
            'title' => 'Form 15H',
            'description' => 'Resident individual aged 60 years or more (senior citizen).',
            'url' => '#'
        ),
        array(
            'title' => 'Form 10F',
            'description' => 'To claim tax treaty benefits for income earned in India, a non-resident must provide details in Form 10F and a Tax Residency Certificate (TRC) as per Section 90(5) of the Income Tax Act, 1961.',
            'url' => '#'
        ),
        array(
            'title' => 'Self Declaration Form',
            'description' => 'General self-declaration form.',
            'url' => '#'
        ),
        array(
            'title' => 'Dec Under Rule 37BA',
            'description' => 'Declaration under Rule 37BA.',
            'url' => '#'
        ),
        array(
            'title' => 'FORM ISR-3',
            'description' => 'Declaration form for opting out of nomination.',
            'url' => 'https://beetal.in/wp-content/uploads/2025/03/FORM-ISR-3-2.doc'
        ),
    );
    ob_start();
?>
    <div id="main-section" class="d-flex align-items-start flex-md-nowrap flex-wrap">
        <div class="align-items-start d-flex h-100 flex-column text-white hidden" id="left-section"
            style="background-color: #06102A; ">
            <?php echo show_menu($menuList, false) ?>
        </div>
        <div class="flex-grow-1 px-4 py-1 px-md-5" id="center-section">
            <h2 class="m-0 px-3 py-2">TDS Submission Form</h2>
            <hr class="mt-0 form-margin">
            <form id="tds-exemption-form" class="tds-form form-margin px-3">
                <div id="form4-response-div"></div>
                <div class="mb-3">
                    <label for="select_your_company" class="form-label required-label">Select your Company</label>
                    <select name="tds_company_name" id="select_your_company" class="form-select">
                        <option value="" selected>----Select----</option>
                        <option value="ANDHRA PRADESH STATE BEVERAGES CORPORATION LIMITED">
                            ANDHRA PRADESH STATE BEVERAGES CORPORATION LIMITED
                        </option>
                    </select>
                </div>

                <div class="mb-3 row">
                    <div class="col-lg-6 col-12">
                        <label for="financial_year" class="form-label">Financial Year</label>
                        <select name="financial_year" id="financial_year" class="form-select">
                            <option value="" selected>----Select----</option>
                            <option value="2025-2024">2025-2026</option>
                            <option value="2025-2024">2025-2024</option>
                        </select>
                    </div>
                    <!-- <div class="col-1"></div> -->
                    <div class="col-lg-6 col-12 mt-3 mt-lg-0">
                        <label for="select_exemption_form_type" class="form-label">Select Exemption Form Type</label>
                        <select name="select_exemption_form_type" id="select_exemption_form_type" class="form-select">
                            <option value="" selected>----Select----</option>
                            <option value="Form 15G">Form 15G</option>
                            <option value="Form 15H">Form 15H</option>
                            <option value="Form 10F">Form 10F</option>
                            <option value="ents By Entity Entitled To Exemption From TDS">
                                ents By Entity Entitled To Exemption From TDS
                            </option>
                        </select>
                        <div id="display-download-form" class="mt-2"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="folio_number" class="form-label required-label">Folio Number</label>
                    <input type="text" name="folio_number" id="folio_number" class="form-control" required>
                    <p class="form-text text-muted mb-0">
                        (e.g., NSDL: IN12345XXXXXXXXX & CDSL: 12345XXXXXXXXXXX Folio: 1234XXX)
                    </p>
                </div>

                <div class="mb-3 row">
                    <div class="col-md-6 col-12">
                        <label for="pan_number" class="form-label required-label">Pan Number</label>
                        <input type="text" name="pan_number" id="pan_number1" class="form-control" required>
                    </div>

                    <div class="col-md-6 col-12 mt-3 mt-md-0">
                        <label for="mobile_number" class="form-label required-label">Mobile Number</label>
                        <input type="number" name="mobile_number" id="mobile_number" class="form-control" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email_id" class="form-label required-label">Email Id</label>
                    <input type="email" name="email_id" id="email_id" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="isin" class="form-label required-label">ISIN</label>
                    <input type="text" name="isin" id="isin" class="form-control" required>
                </div>

                <div class="mb-4">
                    <label for="copy_of_form_10f_submitted_at" class="form-label required-label">Upload Document</label>
                    <input type="file" name="copy_of_form_10f_submitted_at" id="copy_of_form_10f_submitted_at"
                        class="form-control" required>
                    <p class="form-text text-muted m-0">
                        Income Tax Portal with its acknowledgement/ FORM15G/ FORM15H Document by entity entitled to
                        exemption from TDS (File Format PDF/JPG/PNG/GIF)
                    </p>
                    <div class="show-uploaded-form2-list"></div>
                </div>

                <div class="mb-3 d-flex justify-content-start">
                    <button type="submit" class="btn btn-blue form4_non_loader" style="width: 160px;">Submit</button>
                    <button class="btn btn-blue form4_submit_loader text-white" type="button" disabled
                        style="display:none; width: 160px;">
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        Saving...
                    </button>
                    <button id="clear-form4-tds" type="button" class="btn btn-light border ms-4"
                        style="width: 160px;">Clear</button>
                </div>
            </form>
        </div>
        <div class="align-items-start d-flex h-100 text-white hidden" id="right-section" style="background-color: #06102A; ">
            <?= show_active_company_list() ?>
            <!-- <div class="">
                <div class="d-flex flex-column gap-2 pb-4">
                    <div class="px-3 border-bottom">
                        <p class="text-white h4 m-0 py-4 fw-bold">Form List</p>
                    </div>
                    <div class="d-flex flex-column gap-3 mt-3">
                        <?php foreach ($form_list as $list): ?>
                            <div class="list-group px-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title d-flex justify-content-between m-0 text-start">
                                            <?php echo $list['title'] ?>
                                            <a href="<?= $list['url'] ?>" class="text-decoration-none text-blue"
                                                target="_blank"><i class="fa-solid fa-download ms-1"></i></a>
                                        </h5>
                                        <p class="card-text my-1 text-start">
                                            <small>
                                                <?php echo $list['description'] ?>
                                            </small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div> -->
        </div>
    </div>

<?php
    return ob_get_clean();
});

add_shortcode('show_investor_request_page', function () {
    if (!empty(get_current_user_id())) {
        if (UserHasCompanyRole()) {
            redirect_company_user_shortcode();
        }
    }
    $menuList = array(
        // array(
        //     'image_url' => 'https://pragmaappscstg.wpengine.com/wp-content/uploads/2024/11/pexels-photo-6771900-6771900.jpg',
        //     'title' => 'Investor Service Request',
        //     'sub_title' => 'Click here to open',
        //     'form' => 'form1',
        //     'tab' => 'tab1',
        //     'hash' => 'investor-request',
        //     'icon' => 'person-fill-gear'
        // ),
    );
    ob_start();
?>
    <div id="main-section" class="d-flex align-items-start flex-md-nowrap flex-wrap">
        <div class="align-items-start d-flex h-100 text-white flex-column hidden" id="left-section"
            style="background-color: #06102A; min-width: 25%; max-width: 25%;">
            <?php echo show_menu($menuList, true) ?>
        </div>
        <div class="flex-grow-1 w-100" id="center-section">
            <div class="h-100 w-100">
                <?php if (is_user_logged_in()): ?>
                    <?= do_shortcode('[load_ticket_page]'); ?>
                <?php else: ?>
                    <?= do_shortcode('[login_signup_form]'); ?>
                <?php endif; ?>
            </div>
        </div>
        <?php if (!is_user_logged_in()): ?>
            <div class="align-items-start d-flex h-100 text-white hidden" id="right-section" style="background-color: #06102A; ">
                <?= show_active_company_list() ?>
            </div>
        <?php endif; ?>

    </div>
<?php
    return ob_get_clean();
});

function filter_by_type($pods, $offerType)
{
    return array_filter($pods, function ($pod) use ($offerType) {
        return isset($pod['offer_type']) && $pod['offer_type'] === $offerType;
    });
}

function convert_date_to_company_format($date)
{
    if ($date == "0000-00-00") {
        return "--/--";
    }
    return date('d M, Y', strtotime($date));
}
add_shortcode('show_rta_issue_list', function () {
    $menuList = array(
        // array(
        //     'image_url' => 'https://pragmaappscstg.wpengine.com/wp-content/uploads/2024/11/pexels-photo-6771900-6771900.jpg',
        //     'title' => 'Investor Service Request',
        //     'sub_title' => 'Click here to open',
        //     'form' => 'form1',
        //     'tab' => 'tab1',
        //     'hash' => 'investor-request',
        //     'icon' => 'person-fill-gear'
        // ),
    );
    $initial_pods = pods('ipo_company', [
        'limit' => -1,
        'orderby' => 'opening_date DESC',
        // 'order' => 'DESC',
    ]);
    // error_log(print_r($initial_pods->data(), true));
    $extracted_array = [];
    while ($initial_pods->fetch()) {
        if ($initial_pods->field('opening_date') != "0000-00-00" && $initial_pods->field('closing_date') != "0000-00-00") {
            array_push($extracted_array, [
                'name' => $initial_pods->field('name'),
                'logo' => $initial_pods->field('company_logo')['guid'],
                'open' => convert_date_to_company_format($initial_pods->field('opening_date')),
                'close' => convert_date_to_company_format($initial_pods->field('closing_date')),
                'code' => $initial_pods->field('company_code'),
                'offer_type' => $initial_pods->field('offer_type'),
            ]);
        }
    }

    $offer_data = filter_by_type($extracted_array, 'Open Offer');
    $buy_back_data = filter_by_type($extracted_array, 'Buy Back Offer');
    $right_issue = filter_by_type($extracted_array, 'Right Issue');
    // error_log(print_r($offer_data, true));
    ob_start();
?>
    <div class="d-flex flex-row flex-wrap">
        <div class="col-lg-4 col-md-6 col-12 pe-2 my-5">
            <div class="rounded border shadow-chart bg-white">
                <div class="badge-floating rounded bg-theme-blue">Right Issue</div>
                <div class="scrollable-height">
                    <?php foreach ($right_issue as $issue) : ?>
                        <div class="d-flex flex-column col-12 border-bottom py-3 px-3 text-decoration-none text-blue fs-5 change-company-div" data-company="<?= $issue['code'] ?>">
                            <div class="d-flex align-items-center">
                                <?php if (!empty($issue['logo'])) : ?>
                                    <div class="d-flex align-items-center justify-content-center border-theme-blue bg-white p-1 rounded-circle me-2 circle-50-css">
                                        <img src="<?= $issue['logo']; ?>" alt="<?= $issue['name']; ?>" style="object-fit: contain;" />
                                    </div>
                                <?php else: ?>
                                    <div class="d-flex align-items-center justify-content-center bg-white border-theme-blue p-1 rounded-circle me-2 circle-50-css">
                                        <i class="bi bi-buildings text-theme-blue fs-4"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="d-flex flex-column w-100">
                                    <?= strtoupper($issue['name']); ?>
                                    <div class="d-flex justify-content-between mt-1">
                                        <div class="fs-6"><span class="fw-semibold fs-6">Open: </span><?= $issue['open']; ?></div>
                                        <div class="fs-6"><span class="fw-semibold fs-6">Close: </span><?= $issue['close']; ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-12 px-2 my-5">
            <div class="rounded border shadow-chart bg-white">
                <div class="badge-floating rounded bg-theme-blue">Open Offer</div>
                <div class="scrollable-height">
                    <?php foreach ($offer_data as $issue) : ?>
                        <div class="d-flex flex-column col-12 border-bottom py-3 px-3 text-decoration-none text-blue fs-5 change-company-div" data-company="<?= $issue['code'] ?>">
                            <div class="d-flex align-items-center">
                                <?php if (!empty($issue['logo'])) : ?>
                                    <div class="d-flex align-items-center justify-content-center border-theme-blue bg-white p-1 rounded-circle me-2 circle-50-css">
                                        <img src="<?= $issue['logo']; ?>" alt="<?= $issue['name']; ?>" style="object-fit: contain;" />
                                    </div>
                                <?php else: ?>
                                    <div class="d-flex align-items-center justify-content-center bg-white border-theme-blue p-1 rounded-circle me-2 circle-50-css">
                                        <i class="bi bi-buildings text-theme-blue fs-4"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="d-flex flex-column w-100">
                                    <?= strtoupper($issue['name']); ?>
                                    <div class="d-flex justify-content-between mt-1">
                                        <div class="fs-6"><span class="fw-semibold fs-6">Open: </span><?= $issue['open']; ?></div>
                                        <div class="fs-6"><span class="fw-semibold fs-6">Close: </span><?= $issue['close']; ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 col-12 ps-2 my-5">
            <div class="rounded border shadow-chart bg-white">
                <div class="badge-floating rounded bg-theme-blue">Buy Back Offer</div>
                <div class="scrollable-height">
                    <?php foreach ($buy_back_data as $issue) : ?>
                        <div class="d-flex flex-column col-12 border-bottom py-3 px-3 text-decoration-none text-blue fs-5 change-company-div" data-company="<?= $issue['code'] ?>">
                            <div class="d-flex align-items-center">
                                <?php if (!empty($issue['logo'])) : ?>
                                    <div class="d-flex align-items-center justify-content-center border-theme-blue bg-white p-1 rounded-circle me-2 circle-50-css">
                                        <img src="<?= $issue['logo']; ?>" alt="<?= $issue['name']; ?>" style="object-fit: contain;" />
                                    </div>
                                <?php else: ?>
                                    <div class="d-flex align-items-center justify-content-center bg-white border-theme-blue p-1 rounded-circle me-2 circle-50-css">
                                        <i class="bi bi-buildings text-theme-blue fs-4"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="d-flex flex-column w-100">
                                    <?= strtoupper($issue['name']); ?>
                                    <div class="d-flex justify-content-between mt-1">
                                        <div class="fs-6"><span class="fw-semibold fs-6">Open: </span><?= $issue['open']; ?></div>
                                        <div class="fs-6"><span class="fw-semibold fs-6">Close: </span><?= $issue['close']; ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
<?php
    return ob_get_clean();
});
