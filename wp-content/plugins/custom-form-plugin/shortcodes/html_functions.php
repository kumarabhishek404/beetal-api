<?php

function form_download_htm_code($name, $description, $link)
{
    $output_html = "<div class='list-group px-3'>";
    $output_html .= "<div class='card'>";
    $output_html .= "<div class='card-body'>";
    $output_html .= "<h6 class='card-title d-flex justify-content-between m-0'>";
    $output_html .= $name;
    $output_html .= "<a href='" . $link . "' class='text-blue' target='_blank'><i class='fa-solid fa-download'></i></a>";
    $output_html .= "</h6>";
    $output_html .= "<p class='card-text my-1'>";
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
        $output_html .= '<div class="d-flex flex-column show-tab2-resources">';
        $output_html .= "<p class='px-4 m-0 text-justify'>It looks like the downloadable form isn’t available right now. Need help? Reach out to our support team!</p>";
    } else {
        $output_html .= '<div class="d-flex flex-column gap-3 mt-3 show-tab2-resources">';
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
                    "Downloading a corrigendum allows investors and other stakeholders to access the corrected information and ensure they have the most accurate and up-to-date data.",
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
    $company_output_html = "<div id='company-data' class='d-flex mb-3 flex-row p-2'>";
    $company_output_html .=       "<div class='p-2 col-4 col-md-2 d-flex align-items-center'>";
    $company_output_html .=              "<img src='$image_url ' alt='" . $pods->display('name') . "' style='height: 65px;' />";
    $company_output_html .=        "</div>";
    $company_output_html .=        "<div class='d-flex flex-column flex-md-row justify-content-between py-2 px-3 col-8 col-md-10'>";
    $company_output_html .= "<div class='d-flex flex-wrap align-items-center col-12'>";
    $company_output_html .=          "<div class='d-flex col-12 col-md-8 flex-column justify-content-between'>";
    $company_output_html .=              "<h5 class='m-0 mb-3'>" . $pods->display('name');
    $company_output_html .=             "</h5>";
    $company_output_html .=             "<span class='d-flex gap-2 h6 text-blue'> ";
    if ($pods->display('is_live')) {
        $company_output_html .=   "<i class='bi bi-check-circle-fill text-success'></i>";
    } else {
        $company_output_html .= "<i class='bi bi-check-circle-fill'></i>";
    }

    $company_output_html .=       $pods->display('offer_type') . "</span>";
    $company_output_html .= " </div>";

    $company_output_html .=       " <div class='d-flex col-12 col-md-4 h-100 flex-column justify-content-start align-items-start align-items-md-end justify-content-md-between'>";
    $company_output_html .=             "<span class='d-flex gap-2 h6 mt-1 text-blue justify-content-end'>";
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
    <form id="ipo-allotment-form" class="ipo-allotment-form pb-5">
        <!-- Company Name Dropdown -->
        <div class="mb-3 mt-3 px-md-5 px-4">
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
        <div class="mb-1 px-md-5 px-4">
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
        <div class="mb-4 px-md-5 px-4">
            <input type="text"
                name="ipo-allotment-dynamic-field"
                id="ipo-allotment-dynamic-field"
                class="form-control"
                placeholder="Enter Application Number" required>
        </div>

        <!-- Submit Button -->
        <div class="text-center px-md-5 px-4">
            <button type="submit" class="btn btn-blue ipo_submit_btn">Submit</button>
            <button class="btn btn-blue fetch_ipo_submit_loader text-white" type="button" disabled style="display:none;">
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Fetching Details...
            </button>
        </div>
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
    <div class="px-md-5 px-4">
        <p class="h4 mb-2">FOR ATTENTION OF SHAREHOLDERS HOLDING SHARES IN PHYSICAL FORM</p>
        <p>The SEBI vide its Circular No. SEBI/HO/MIRSD/MIRSD RTAMB/P/CIR/2021/655 dated November 3, 2021 read together with SEBI Circular No. SEBI/HO/MIRSD/MIRSD RTAMB/P/CIR/2021/687 dated December 14, 2021 (the "SEBI Circulars") has mandated for furnishing/ updating PAN, KYC details (Address, Mobile No., E-mail ID, Bank Details) and Nomination details by all the holders of physical securities in listed company.</p>
        <p>Further In case of non-updation of PAN or Choice of Nomination or Contact Details or Mobile Number or Bank Account Details or Specimen Signature in respect of physical folios, dividend/interest etc. shall be paid only through electronic mode with effect from April 01, 2024 upon furnishing all the aforesaid details in entirety.</p>
        <?= registration_details_table_shortcode(); ?>
        <p>Shareholders holding shares in physical form are requested to submit the duly filled in documents along with the related proofs as mentioned above to the Company at its Registered Office or Registrar and Transfer Agent at the below mentioned address at the earliest:</p>

        <div class="card my-4">
            <div class="card-body p-0 m-0">
                <div class="bg-blue text-white rounded-t px-4 py-3 h5">REGD. OFFICE</div>
                <div class="d-flex flex-column px-4 py-2 pb-4">
                    <div class="d-flex h6 gap-3">
                        <i class="bi bi-geo-alt fs-4 text-blue"></i>
                        Beetal House, 3rd Floor, 99 Madangir, Behind Local Shopping Centre, Near Dada Harsukh Dass Mandir, New Delhi – 110062
                    </div>
                    <div class="d-flex gap-3 flex-wrap">
                        <div class="d-flex gap-2 flex-nowrap fs-6">
                            <i class="bi bi-telephone text-blue"></i> 011-29961281
                        </div>
                        <div class="d-flex gap-2 flex-nowrap fs-6">
                            <i class="bi bi-printer text-blue"></i> 011-29961284
                        </div>
                        <div class="d-flex gap-2 flex-nowrap fs-6">
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
    <table class="registration-details-table mt-4">
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
                site_url("investor-request/?company=" . $pods->field('company_code') . "#Open_Buyback_right_issue") : site_url('investor-request/#IPO_Allotment_Status');
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
                    <h5 class="m-0 mt-3 w-100 px-3 text-start"><?= $pods->field('name') ?></h5>
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

function form2_html_fn()
{
    $company_data = [];
    $atts = array(
        'label' => 'Select an Option:',
        'options' => array(
            'Application Number' => 'Enter Application Number.',
            'PAN' => 'Enter PAN Number.',
            'DPID/Client Id' => 'Eg:-INXXXXXXXXXX,120XXXXXXXXX,XXXXXXX',
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
    <form id="form2-fetch" class="px-md-5 px-4 pb-5">
        <!-- Company Selection -->
        <div id="company-select-div" class="mb-3 <?= empty($company_selected) ? '' : 'hidden' ?>">
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

        <div class="radio-button-group">
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
        </div>

        <div id="verify_form2_div" class="form-group hidden">
            <label for="otp_verify" class="required-label">Enter Verify OTP</label>
            <input type="number" id="form2_otp_verify" name="otp_verify" placeholder="xxxxxx">
        </div>

        <!-- Radio Button Group -->
        <div class="mb-1">
            <div id="radio-group" class="d-flex flex-row">
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
        <div class="mb-4">
            <input type="text" name="form2-dynamic-value-field" id="form2-dynamic-field" class="form-control" placeholder="Enter Application Number." required>
        </div>

        <!-- Submit Button -->
        <div class="text-center">
            <button type="submit" class="btn btn-blue ipo_submit_btn">Submit</button>
            <button class="btn btn-blue fetch_ipo_submit_loader text-white" type="button" disabled style="display:none;">
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Fetching Details...
            </button>
        </div>
    </form>
    <div id="form2-result" class="px-md-5 px-4 pb-5"></div>
<?php
    return ob_get_clean();
}
