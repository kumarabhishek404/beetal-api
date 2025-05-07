<?php

function form_download_htm_code($name, $description, $link)
{
    $output_html = "<div class='list-group col-12 col-md-6 p-2'>";
    $output_html .= "<div class='card'>";
    $output_html .= "<div class='card-body'>";
    $output_html .= "<h6 class='card-title d-flex justify-content-between m-0 text-start'>";
    $output_html .= $name;
    $output_html .= "<a href='" . $link . "' class='text-blue ms-1' target='_blank'><i class='fa-solid fa-download'></i></a>";
    $output_html .= "</h6>";
    $output_html .= "<p class='card-text my-1 text-start'>";
    $output_html .= "<small>";
    $output_html .= $description;
    $output_html .= "</small>";
    $output_html .= "</p>";
    $output_html .= "</div>";
    $output_html .= "</div>";
    $output_html .= "</div>";
    return $output_html;
}

function show_company_download_forms($company)
{
    if (empty($company)) {
        return '';
    }
    $output_html = '';
    $company_output_html = '';

    $output_html = "
     <div class='d-flex flex-column text-start border-bottom w-100 pb-2 mb-2'>
                            <p class='m-0 h4 fw-bold'>Services and Resources</p>
                        </div>
                        <p class='p-0 mb-3 text-justify'>Access key documents and resources, including application
                            forms, allotment queries, prospectuses, public announcements, FAQs, and other shareholder-related
                            services to streamline your investment process.</p>
    ";

    // error_log($company);
    // error_log($company . '=>' . print_r([
    //     'where' => "name = '" . $company . "'",
    //     'limit' => 1
    // ],true));
    $pods = pods('ipo_company')->find([
        'where' => "company_code = '" . $company . "'",
        'limit' => 1
    ]);

    if (
        empty($pods->display('physical_application_form')) &&
        empty($pods->display('demat_application_form')) &&
        empty($pods->display('prospectus_letter_offer')) &&
        empty($pods->display('public_announcements')) &&
        empty($pods->display('corrigendum')) &&
        empty($pods->display('faq')) &&
        empty($pods->display('sh4'))
    ) {
        $output_html .= '<div class="d-flex show-tab2-resources">';
        $output_html .= "<p class='m-0 text-justify'>It looks like the downloadable form isn’t available right now. Need help? Reach out to our support team!</p>";
    } else {
        $output_html .= '<div class="d-flex flex-wrap my-2 show-tab2-resources">';
        if ($pods->total() > 0) {
            if (!empty($pods->display('physical_application_form'))) {
                $output_html .= form_download_htm_code(
                    "Application Form (Physical)",
                    "Download the physical application form here to apply for employment, membership, etc.",
                    $pods->display('physical_application_form')
                );
            }
            if (!empty($pods->display('demat_application_form'))) {
                $output_html .= form_download_htm_code(
                    "Application Form (demat)",
                    "Download our Demat Application Form to easily open a Demat account",
                    $pods->display('demat_application_form')
                );
            }
            if (!empty($pods->display('prospectus_letter_offer'))) {
                $output_html .= form_download_htm_code(
                    "Prospectus Letter Offer",
                    "Obtain essential information about our company's upcoming investment opportunities.",
                    $pods->display('prospectus_letter_offer')
                );
            }
            if (!empty($pods->display('public_announcements'))) {
                $output_html .= form_download_htm_code(
                    "Public Announcements",
                    "Access and download important public announcements, including press releases, regulatory filings, and investor presentations, related to the company.",
                    $pods->display('public_announcements')
                );
            }
            if (!empty($pods->display('corrigendum'))) {
                $output_html .= form_download_htm_code(
                    "Corrigendum",
                    "Downloading a corrigendum provides investors with corrected, up-to-date information.",
                    // "Downloading a corrigendum allows investors and other stakeholders to access the corrected information and ensure they have the most accurate and up-to-date data.",
                    $pods->display('corrigendum')
                );
            }
            if (!empty($pods->display('faq'))) {
                $output_html .= form_download_htm_code(
                    "FAQ's",
                    "Save time and get the information you need quickly with our downloadable FAQs.",
                    $pods->display('faq')
                );
            }
            if (!empty($pods->display('sh4'))) {
                $output_html .= form_download_htm_code(
                    "SH4",
                    "Analyzing the SH4 can help investors understand the company's ownership and assess potential risks and opportunities.",
                    $pods->display('sh4')
                );
            }
        }
    }
    $output_html .= "</div>";

    $image_url = $pods->field('company_logo')['guid'];
    $company_output_html = "<div id='company-data' class='d-flex flex-column flex-lg-row p-2'>";
    $company_output_html .=       "<div class='pt-2 col-12 d-lg-none d-flex'>";
    $company_output_html .=  "<h5 class='mb-2'>" . strtoupper($pods->display('name'));
    $company_output_html .=             "</h5>";
    $company_output_html .=        "</div>";
    $company_output_html .=        "<div class='col-12 d-flex'>";
    $company_output_html .=       "<div class='p-2 col-5 col-sm-4 col-lg-3 col-xl-2 d-flex align-items-center'>";
    $company_output_html .=              "<img src='$image_url ' alt='" . $pods->display('name') . "' />";
    $company_output_html .=        "</div>";
    $company_output_html .=        "<div class='d-flex flex-column justify-content-center py-2 px-3 col-7 col-sm-8 col-lg-9 col-xl-10'>";
    $company_output_html .= "<div class='d-flex flex-wrap align-items-center col-12'>";
    $company_output_html .=          "<div class='d-flex col-12 flex-column justify-content-between'>";
    $company_output_html .=              "<h5 class='mb-2 d-none d-lg-block'>" . strtoupper($pods->display('name'));
    $company_output_html .=             "</h5>";
    $company_output_html .=             "<span class='d-flex gap-2 h6 text-blue'> ";
    if ($pods->display('is_live')) {
        $company_output_html .=   "<i class='bi bi-check-circle-fill text-success'></i>";
    } else {
        $company_output_html .= "<i class='bi bi-check-circle-fill'></i>";
    }

    $company_output_html .=       $pods->display('offer_type') . "</span>";
    $company_output_html .= " </div>";

    $company_output_html .=       " <div class='d-flex col-12 align-items-start align-items-xl-center flex-column flex-xl-row justify-content-between'>";
    $company_output_html .=             "<span class='d-flex gap-2 h6 text-blue justify-content-end'>";
    $company_output_html .=                  "<i class='bi bi-calendar-check'></i>";
    $company_output_html .=                  "<span><strong>Open:</strong> " .  date('d M, Y', strtotime($pods->display('opening_date'))) . "</span>";
    $company_output_html .=              "</span>";
    $company_output_html .=              "<span class='d-flex gap-2 h6 text-blue justify-content-end'>";
    $company_output_html .=                  "<i class='bi bi-calendar2-x'></i>";
    $company_output_html .=                 "<span><strong>Close:</strong> " .  date('d M, Y', strtotime($pods->display('closing_date'))) . "</span>";
    $company_output_html .=              "</span>";
    $company_output_html .=          "</div>";
    $company_output_html .=      "</div>";
    $company_output_html .=      "</div>";
    $company_output_html .=      "</div>";
    $company_output_html .=   "</div>";

    // error_log($output_html);

    return ['download' => $output_html, 'company' => $company_output_html, "company_name" => $pods->display('name')];
}

function ipo_allotment_form()
{
    $company_selected = !empty($_GET['company']) ? $_GET['company'] : '';
    $allotment_atts = array(
        'label' => 'Select an Option:',
        'options' => array(
            'Application Number' => 'Enter Application Number.',
            'PAN' => 'Enter PAN Number.',
            'DPID/Client ID' => 'Eg:-INXXXXXXXXXX,120XXXXXXXXX,XXXXXXX',
        )
    );
    ob_start();
?>
    <script src="https://www.google.com/recaptcha/api.js?render=6Lezg8wqAAAAAIze3YgWt7kkGfNhbklSHwRLpi0O"></script>
    <form id="ipo-allotment-form" class="ipo-allotment-form p-3">
        <!-- Company Name Dropdown -->
        <div class="form-margin">
            <label for="company_name" class="form-label required-label">Company Name</label>
            <select name="ipo-allotment-company_name" id="company_name" class="form-select" required>
                <option value="">----Select----</option>
                <?php
                $pods = pods('ipo_company')->find([
                    'where' => 'allotment_status IN ("active")',
                    'limit' => -1
                ]);
                if ($pods->total() > 0):
                    while ($pods->fetch()):
                        $company_name = $pods->field('name');
                        $company_code = $pods->field('company_code');
                        $selected = $company_selected == $company_name ? 'selected' : '';
                        echo "<option value='$company_code' $selected>$company_name</option>";
                    endwhile;
                endif;
                ?>
            </select>
        </div>

        <!-- Radio Button Group -->
        <div class="">
            <div class="d-flex flex-wrap">
                <?php foreach ($allotment_atts['options'] as $option => $value): ?>
                    <div class="form-check me-3">
                        <input
                            type="radio"
                            class="form-check-input"
                            name="ipo-allotment-radio-option"
                            id="<?php echo sanitize_title($option); ?>"
                            value="<?php echo $value; ?>"
                            <?php echo $option == 'Application Number' ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="<?php echo sanitize_title($option); ?>">
                            <?php echo $option; ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Dynamic Value Field -->
        <div class="mb-4 form-margin">
            <input type="text"
                name="ipo-allotment-dynamic-field"
                id="ipo-allotment-dynamic-field"
                class="form-control"
                placeholder="Enter Application Number" required>
        </div>

        <!-- Submit Button -->
        <div class="text-start form-margin">
            <button type="submit" class="btn btn-blue ipo_submit_btn" style="width: 160px;">Submit</button>
            <button class="btn btn-blue fetch_ipo_submit_loader text-white" type="button" disabled style="display:none; width: 165px;">
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Fetching Details...
            </button>
        </div>

        <div id="allotment_result"></div>
    </form>
<?php
    return ob_get_clean();
}

function ipo_allotment_data_display()
{
    ob_start();
?>
    <!-- <div class="py-4 pb-5">
        <h3 class="m-0 ms-md-4 ms-1" style="padding: 21.1px">Allotment Status</h3>
        <hr class="m-0">
        <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
            <div class="d-flex border-end px-md-5 px-4 align-items-center flex-fill col-6 gap-3">
                <i class="bi bi-123 fs-4 text-warning"></i>
                <p class="m-0">JVO76890idcd890</p>
            </div>
            <div class="d-flex align-items-center px-4 flex-fill col-6 gap-3">
                <i class="bi bi-person-fill fs-4 text-warning"></i>
                <p class="m-0">Ankit Kumar</p>
            </div>
        </div>
        <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
            <div class="d-flex border-end align-items-center px-md-5 px-4 flex-fill col-6 gap-3">
                <i class="bi bi-person-lines-fill fs-4 text-warning"></i>
                <p class="m-0">JVO76890idcd890</p>
            </div>
            <div class="d-flex align-items-center px-4 flex-fill col-6 gap-3">
                <i class="bi bi-diagram-3-fill fs-4 text-warning"></i>
                <p class="m-0">100</p>
            </div>
        </div>
        <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
            <div class="d-flex border-end align-items-center px-md-5 px-4 flex-fill col-6 gap-3">
                <i class="bi bi-person-vcard-fill fs-4 text-warning"></i>
                <p class="m-0">JVO76890890</p>
            </div>
            <div class="d-flex align-items-center px-4 flex-fill col-6 gap-3">
                <i class="bi bi-graph-up-arrow fs-4 text-warning"></i>
                <p class="m-0">60</p>
            </div>
        </div>
        <div class="d-flex justify-content-center mt-4">
            <button class="btn btn-warning text-white" disabled style="width: 100px;">Details</button>
        </div>
    </div> -->
    <div class="card mx-4 mx-md-5 mb-5">
        <div class="card-body">
            <h4>Allotment Status</h4>
            <div class="d-flex justify-content-between">
                <div class="h5">Ankit Kumar</div>
                <div class="h5 d-flex gap-3">
                    <i class="bi bi-check-circle-fill"></i>
                    0 out of 6000
                </div>
            </div>
            <div class="d-flex justify-content-between">
                <div class="d-flex flex-column">
                    <div class="d-flex">PAN</div>
                    <div class="d-flex">DPID/Client</div>
                </div>
                <div class="">
                    Application No.
                </div>
            </div>
        </div>
    </div>
<?php
    return ob_get_clean();
}

function kyc_complience_html()
{
    ob_start();
?>
    <div class="px-3">
        <p class="h4 mb-4">
            For the Attention of Shareholders Holding Shares in Physical Form
        </p>
        <p class="mb-4">The SEBI vide its Circular No. SEBI/HO/MIRSD/MIRSD RTAMB/P/CIR/2021/655 dated November 3, 2021 read together with SEBI Circular No. SEBI/HO/MIRSD/MIRSD RTAMB/P/CIR/2021/687 dated December 14, 2021 (the "SEBI Circulars") has mandated for furnishing/ updating PAN, KYC details (Address, Mobile No., E-mail ID, Bank Details) and Nomination details by all the holders of physical securities in listed company.</p>
        <p>Further In case of non-updation of PAN or Choice of Nomination or Contact Details or Mobile Number or Bank Account Details or Specimen Signature in respect of physical folios, dividend/interest etc. shall be paid only through electronic mode with effect from April 01, 2024 upon furnishing all the aforesaid details in entirety.</p>
        <?= registration_details_table_shortcode(); ?>
        <p>Shareholders holding shares in physical form are requested to submit the duly filled in documents along with the related proofs as mentioned above to the Company at its Registered Office or Registrar and Transfer Agent at the below mentioned address at the earliest:</p>

        <div class="card my-4">
            <div class="card-body p-0 m-0">
                <div class="bg-blue text-white rounded-t px-4 py-3 h5">REGD. OFFICE</div>
                <div class="d-flex flex-column px-4 py-2 pb-4">
                    <div class="d-flex h6">
                        <i class="bi bi-geo-alt fs-4 text-blue"></i>
                        <p class="m-0 ms-1">Beetal House, 3rd Floor, 99 Madangir, Behind Local Shopping Centre, Near Dada Harsukh Dass Mandir, New Delhi – 110062</p>
                    </div>
                    <div class="d-flex ps-1 flex-wrap">
                        <div class="d-flex gap-2 flex-nowrap fs-6 pe-4">
                            <i class="bi bi-telephone text-blue"></i> 011-29961281
                        </div>
                        <div class="d-flex gap-2 flex-nowrap fs-6 pe-4">
                            <i class="bi bi-printer text-blue"></i> 011-29961284
                        </div>
                        <div class="d-flex gap-2 flex-nowrap fs-6 pe-4">
                            <i class="bi bi-envelope text-blue"></i> beetalrta@gmail.com
                        </div>
                        <div class="d-flex gap-2 flex-nowrap fs-6">
                            <i class="bi bi-globe2 text-blue"></i> www.beetalfinancial.com
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
    return ob_get_clean();
}

function registration_details_table_shortcode()
{
    ob_start(); // Buffer the output

?>
    <table class="registration-details-table my-4">
        <thead>
            <tr>
                <th>No.</th>
                <th>Particulars</th>
                <th>Details of documents that are to be submitted</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1.</td>
                <td>PAN</td>
                <td rowspan="6">
                    For registration / updation in the PAN, Bank details, Address, Email, Mobile number or signature, please provide the details in the prescribed Form ISR-1, along with related documents as stated therein, self-attested by the shareholder(s).
                    <ul>
                        <li> PAN shall be valid only if it is linked to Aadhar by March 31, 2022 or any date as may be specified by the Authority.</li>
                        <li>In case it is not provided, the details available in the Client Master List ("CML") will be updated in the folio.</li>
                    </ul>


                </td>
            </tr>
            <tr>
                <td>2.</td>
                <td>Bank Details</td>
            </tr>
            <tr>
                <td>3.</td>
                <td>Mobile No.</td>
            </tr>
            <tr>
                <td>4.</td>
                <td>E-mail ID</td>
            </tr>
            <tr>
                <td>5.</td>
                <td>Address</td>
            </tr>
            <tr>
                <td>6.</td>
                <td>Signature</td>
            </tr>
            <tr>
                <td>7.</td>
                <td>Confirmation of Signature</td>
                <td>Please provide details in Form ISR-2, along with original cancelled cheque with name of the security holder printed on it / Bank Passbook/Bank Statement attested by the Bank, and Banker's attestation of the signature.</td>
            </tr>
            <tr>
                <td>8.</td>
                <td>Nomination</td>
                <td>Please provide duly completed prescribed forms as applicable:<br>
                    <ul>
                        <li>Form SH-13 - for registration of Nomination;</li>
                        <li>Form ISR-3 - Declaration for opting out from Nomination;</li>
                        <li>Forms SH-14 and ISR-3 - for cancellation of existing nomination;</li>
                        <li>Form SH-14 - for change in existing nomination.</li>
                    </ul>
                </td>
            </tr>
        </tbody>
    </table>
<?php

    return ob_get_clean();
}

function form2_html_fn()
{
    $company_data = [];
    $atts = array(
        'label' => 'Select an Option:',
        'options' => array(
            'Application Number' => 'Enter Application Number.',
            // 'PAN' => 'Enter PAN Number.',
            'DP/Client ID/Folio No.' => 'Eg:-INXXXXXXXXXX,120XXXXXXXXX,XXXXXXX',
        )
    );
    $company_selected = !empty($_GET['company']) ? $_GET['company'] : '';
    $pods = pods('ipo_company')->find([
        'where' => 'company_status IN ("active")',
        'limit' => -1
    ]);
    if (!empty($company_selected)) {
        $company_data = pods('ipo_company')->find([
            'where' => 'company_status IN ("active") and company_code = "' . $company_selected . '"',
            'limit' => -1
        ]);
    }
    // error_log(print_r($company_selected, true));
    ob_start();
?>
    <script src="https://www.google.com/recaptcha/api.js?render=6Lezg8wqAAAAAIze3YgWt7kkGfNhbklSHwRLpi0O"></script>
    <form id="form2-fetch" class="my-5 px-3">

        <!-- Company Selection -->
        <div id="company-select-div" class="form-margin <?= empty($company_selected) ? '' : 'hidden' ?>">
            <label for="company" class="form-label">Please Select Company:</label>
            <select name="offer_company" id="tab2_company_select_tab" class="form-select">
                <option value="">----Select----</option>
                <?php
                if ($pods->total() > 0):
                    while ($pods->fetch()):
                        $company_name = $pods->field('name');
                        $company_code = $pods->field('company_code');
                        $selected = $company_selected == $company_code ? 'selected' : '';
                        echo "<option value='$company_code' $selected >$company_name</option>";
                    endwhile;
                endif;
                ?>
                <?php
                ?>
            </select>
        </div>

        <!-- <div class="radio-button-group">
            <div class="radio-button-items">
                <div class="radio-button-item">
                    <div class="me-3 d-flex align-items-center gap-2">
                        <input class="form-check-input" type="radio" name="form2_radio_option_otp" id="login_email" value="email" checked />
                        <label class="m-0" for="login_email">Email</label>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <input class="form-check-input" type="radio" name="form2_radio_option_otp" id="login_phone" value="number" />
                        <label class="m-0" for="login_phone">Phone</label>
                    </div>
                </div>
            </div>
            <input type="email" name="dynamic-field-form2-otp" id="dynamic-field-form2-email-otp" placeholder="Enter here..." />
            <input class="hidden" type="number" maxlength="10" name="dynamic-field-form2-phone-otp" id="dynamic-field-form2-phone-otp" placeholder="Enter here..." />
        </div> -->

        <!-- <div id="verify_form2_div" class="form-group hidden">
            <label for="otp_verify" class="required-label">Enter Verify OTP</label>
            <input type="number" id="form2_otp_verify" name="otp_verify" placeholder="xxxxxx">
        </div> -->

        <div class="form-margin">
            <!-- Radio Button Group -->
            <div class="mb-1">
                <div id="radio-group" class="d-flex flex-row flex-wrap">
                    <?php
                    foreach ($atts['options'] as $option => $value) {
                        echo '<div class="form-check form-check-inline me-3">';
                        if ($option == 'Application Number') {
                            echo '<input class="form-check-input" type="radio" name="form2_radio_option" id="' . sanitize_title($option) . '" value="' . $value . '" checked>';
                        } else {
                            echo '<input class="form-check-input" type="radio" name="form2_radio_option" id="' . sanitize_title($option) . '" value="' . $value . '">';
                        }
                        echo '<label class="form-check-label" for="' . sanitize_title($option) . '">' . $option . '</label>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>

            <!-- Dynamic Field -->
            <div class="">
                <input type="text" name="form2-dynamic-value-field" id="form2-dynamic-field" class="form-control" placeholder="Enter Application Number." required>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="text-start">
            <button type="submit" class="btn btn-blue ipo_submit_btn" style="width: 160px;">Submit</button>
            <button class="btn btn-blue fetch_ipo_submit_loader text-white" type="button" disabled style="display:none; width: 165px;">
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Fetching Details...
            </button>
        </div>
        <div id="error-form2-result"></div>
    </form>
    <div id="form2-result"></div>
<?php
    return ob_get_clean();
}

function show_menu_form_tab_fn()
{
    $menuList = array(
        array(
            'title' => 'Download Form',
            'sub_title' => 'Access and download important financial documents, reports, and statements in PDF format for your records and analysis.',
            'icon' => 'bi bi-arrow-right-circle-fill',
            'link' => site_url('contact/#downloads')
        ),
        array(
            'title' => 'Frequently Asked Questions',
            'sub_title' => 'Find answers to common queries about our services, policies, investor related queries, and support.',
            'icon' => 'bi bi-arrow-right-circle-fill',
            'link' => site_url('contact/#faqs')
        ),
        array(
            'title' => 'Escalation Matrix',
            'sub_title' => 'A structured framework ensuring timely resolution of grievances by escalating issues through predefined levels.',
            'icon' => 'bi bi-arrow-right-circle-fill',
            'link' => site_url('contact/#grievance_matrix')
        )
    );
    ob_start();
?>
    <div class="d-flex flex-column">
        <?php foreach ($menuList as $item): ?>
            <a href="<?= $item['link'] ?>"
                style="cursor: pointer;"
                class="d-flex align-items-center justify-content-between gap-3 text-decoration-none  p-4 border-bottom border-secondary">
                <p class="text-white m-0 fs-5"><?= $item['title'] ?></p>
                <i class="<?= $item['icon'] ?> text-white fs-4" aria-hidden="true"></i>

            </a>
        <?php endforeach; ?>
    </div>
<?php
    return ob_get_clean();
}

function show_menu($menuList, $logout = false)
{
    ob_start(); ?>
    <div class="d-flex w-100 justify-content-between align-items-center border-bottom" style="padding: 1.5rem 20px;">
        <a href="<?= $_SERVER['HTTP_REFERER'] ?? site_url('investor-services#service-list') ?>" class="d-flex align-items-center h4 ps-2 text-white justify-content-center m-0 text-decoration-none"><i class="bi bi-arrow-left-circle me-4"></i>Back</a>
        <i id="menuToggleIcon" class="bi bi-chevron-down text-white fs-3 d-md-none" data-bs-toggle="collapse" data-bs-target="#menuCollapse" aria-expanded="false" aria-controls="menuCollapse"></i>
    </div>

    <!-- Collapsible content -->
    <div class="collapse d-md-block w-100" id="menuCollapse">
        <div class="text-black p-0 m-0">
            <ul class="nav flex-column m-0">
                <?php foreach ($menuList as $item): ?>
                    <!-- <a href="<?= site_url('/investor-request#' . $item['hash'] . '') ?>" class="text-decoration-none"> -->
                    <li id="<?php echo $item['form'] . $item['tab'] ?>"
                        class="d-flex align-items-center justify-content-between border-bottom py-3 ps-3 menu-items <?php echo $item['form'] == 'form1' ? 'menu-active' : 'text-white' ?>"
                        style="cursor: pointer;"
                        onclick="changeTab('<?= site_url($item['hash']) ?>'">
                        <span class="d-flex align-items-center justify-space-between gap-2" style="font-weight: 500;">
                            <!-- <img src="<?php echo $item['image_url'] ?>" class="me-2 border-y-1" style="width:40px; height:40px; border-radius: 100%; min-width:40px;" /> -->
                            <span class="text-white rounded-circle d-flex justify-content-center align-items-center" style="min-height: 50px; min-width: 50px; width: 50px; height: 50px;">
                                <i class="bi bi-<?= $item['icon'] ?> fs-2"></i>
                            </span>
                            <p class="m-0 h6"><?php echo esc_html($item['title']); ?></p>
                        </span>
                    </li>
                    <!-- </a> -->
                <?php endforeach; ?>
                <?php if (is_user_logged_in() && $logout) : ?>
                    <li
                        class="d-flex align-items-end justify-content-between border-bottom py-3 ps-3 menu-items text-white"
                        style="cursor: pointer;"
                        onclick="logoutUser()">
                        <span class="d-flex align-items-center justify-space-between gap-2" style="font-weight: 500;">
                            <span class="text-white rounded-circle d-flex justify-content-center align-items-center" style="min-height: 50px; min-width: 50px; width: 50px; height: 50px;">
                                <i class="bi bi-power ?> fs-2"></i>
                            </span>
                            Logout
                        </span>
                    </li>
                <?php endif; ?>
            </ul>
            <?= show_menu_form_tab_fn() ?>
        </div>
    </div>

<?php
    return ob_get_clean();
}

function show_recent_company_list_fn()
{
    $pods = pods('ipo_company')->find([
        'where' => 'company_status IN ("active") and recent_issue = "True"',
        'limit' => 3
    ]);
    if ($pods->total() < 1) {
        return "
         <div id='form-message' class='mx-md-5 mx-4 alert alert-danger mt-4' >
                            No Data Available
                        </div>
        ";
    }
    ob_start(); ?>
    <div class="d-flex flex-wrap">
        <?php while ($pods->fetch()) : ?>
            <?php error_log($pods->field('is_live')); ?>
            <?php $redirect_url = $pods->field('offer_type') != 'IPO - SME' ?
                site_url("investor-services?company=" . $pods->field('company_code')) . "#Open_Buyback_right_issue" : site_url('investor-services/?company=' . $pods->field('company_code') . 'IPO_Allotment_Status');
            $card_text = $pods->field('offer_type');
            switch ($pods->field('offer_type')) {
                case 'Public Issue - SME':
                    $card_text = "SME IPO";
                    break;
                case 'Buy Back Offer':
                    $card_text = "Buyback Offer";
                    break;
            }
            ?>

            <div class="col-12 col-md-4 mb-4 mb-md-0 px-2 d-flex flex-wrap">
                <a class="d-flex flex-column justify-content-between align-items-start border-blue-2 rounded text-decoration-none w-100 bg-white"
                    href="<?= $redirect_url ?>">
                    <h5 class="m-0 mt-3 w-100 px-3 text-start"><?= strtoupper($pods->field('name')); ?></h5>
                    <div class="d-flex py-2 px-3 align-items-center w-100">
                        <div class="col-8 d-flex align-items-center pe-3">
                            <?php $image_url = $pods->field('company_logo')['guid']; ?>
                            <img src="<?= $image_url ?>" alt="<?= $pods->field('name') ?>" />
                        </div>
                        <div class="d-flex gap-1 col-4 align-items-center justify-content-end">
                            <span class="d-flex gap-2 align-items-center justify-content-center text-center rounded-circle h6 text-white m-0 height-recent-circle <?= $pods->field('is_live') == 'True' ? 'bg-success' : 'bg-secondary' ?>">
                                <?= $card_text ?>
                            </span>
                        </div>
                    </div>
                    <div class="w-100 d-flex justify-content-between align-items-center pb-2 px-3">
                        <span class="d-flex gap-2 h6 text-blue">
                            <!-- <i class="bi bi-calendar-check-fill"></i> -->
                            <span><strong>Open:</strong> <?= date('d M, Y', strtotime(esc_html($pods->field('opening_date')))); ?></span>
                        </span>
                        <span class="d-flex gap-2 h6 text-blue">
                            <!-- <i class="bi bi-calendar2-x-fill"></i> -->
                            <span><strong>Close:</strong> <?= date('d M, Y', strtotime(esc_html($pods->field('closing_date')))); ?></span>
                        </span>
                    </div>
                </a>
            </div>
        <?php endwhile; ?>
    </div>
<?php
    return ob_get_clean();
}

function show_active_company_list()
{
    // Fetch Pods data
    $initial_pods = pods('ipo_company', [
        'where' => 'company_status = "Active"',
        'limit' => 5
    ]);
    ob_start();
?>
    <div class="d-flex flex-wrap pb-4">
        <div class="d-flex flex-column text-start w-100 py-4 px-3 change-company-css">
            <p class="m-0 h4 fw-semibold">Recent Issues</p>
        </div>
        <?php
        $sno = 1;
        while ($initial_pods->fetch()) : ?>
            <div class="d-flex align-items-center col-12 cursor-pointer py-3 px-3 text-decoration-none text-black border-muted fs-5 change-company-div change-company-css mycustomClassRight <?= $sno == 1 ? 'first-child' : '' ?>" data-company="<?= $initial_pods->field('company_code') ?>">
                <!-- <div class="d-flex align-items-center justify-content-center bg-white p-1 rounded-circle me-2 circle-50-css">
                    <img src="<?= $initial_pods->field('company_logo')['guid']; ?>" alt="<?= $initial_pods->field('name'); ?>" style="object-fit: contain;" />
                </div> -->
                <?= strtoupper($initial_pods->field('name')); ?>
            </div>
        <?php
            $sno++;
        endwhile; ?>
    </div>
<?php
    return ob_get_clean();
}
