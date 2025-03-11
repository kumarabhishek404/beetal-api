<?php
// if (!session_id()) {
//     session_start();
//     error_log('New Session ID => ' . session_id());
// }

function show_recent_issue()
{
    $list = array(
        array(
            'image_url' => 'https://pragmaappscstg.wpengine.com/wp-content/uploads/2024/11/pixelcut-export-2.png',
            'name' => 'Mayur Uniquoters Limited',
            'issue' => 'Buy-Back Offer'
        ),
        array(
            'image_url' => 'https://pragmaappscstg.wpengine.com/wp-content/uploads/2024/11/pixelcut-export.png',
            'name' => 'Faalcon Concepts Limited',
            'issue' => 'SME-IPO'
        ),
        array(
            'image_url' => 'https://pragmaappscstg.wpengine.com/wp-content/uploads/2024/11/pixelcut-export-1.png',
            'name' => 'Chaman Lal Exports LTD',
            'issue' => 'Buy-Back Offer'
        ),
    );
    ob_start();
?>
    <div class="py-4" style="width: 350px;">
        <div class="px-3 border-bottom">
            <h3 class="text-white px-3">Recent Issues Handled</h3>
        </div>
        <div class="px-3 mt-4">
            <?php foreach ($list as $list) : ?>
                <div class="card mb-3">
                    <div class="row p-3">
                        <div class="col-md-4 d-flex justify-content-center align-items-center">
                            <img src="<?php echo $list['image_url']; ?>" class="card-image" alt="<?php echo $list['name']; ?>">
                        </div>
                        <div class="col-md-8">
                            <div class="card-body">
                                <h6 class="card-title m-0"><?php echo $list['name']; ?></h6>
                                <p class="card-text"><?php echo $list['issue']; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

<?php
    return ob_get_clean();
}

function ipo_and_issue_handled_form()
{
    $company_selected = !empty($_GET['company']) ? $_GET['company'] : '';
    $show_data = show_company_download_forms($company_selected);
    // error_log(is_user_logged_in());
    ob_start();
?>
    <div id="form5" class="form-container list-group" style="display:none; background: white;">
        <h3 class="m-0 ms-md-4 ms-1" style="padding: 21.1px">IPO Allotment Form</h3>
        <hr class="mt-0">
        <?= ipo_allotment_form(); ?>
    </div>
    <div id="form2" class="form-container list-group" style="display:none; background: white;">
        <div id="form2-company-name" class="m-0 ms-md-4 ms-1 pt-3">
            <?php if (!empty($show_data['company'])) : ?>
                <?= $show_data['company']; ?>
            <?php else : ?>
                <h4 class="m-0" style="padding: 14.4px"> IPO and Issue Handled </h4>
            <?php endif; ?>
        </div>
        <hr class="mt-0">
        <?php if (empty($company_selected)) : ?>
            <div class="px-3 px-md-4 pb-4">
                <?= do_shortcode('[ipo_companies_data_table filter="active"]'); ?>
            </div>
        <?php else : ?>
            <?= form2_html_fn(); ?>
        <?php endif; ?>
    </div>
    <div id="form3" class="form-container list-group" style="display:none; background: white;">
        <div class="d-flex justify-content-between align-items-center" style="padding: 14.4px">
            <h3 class="m-0 ms-md-4 ms-1">KYC Compliance</h3>
            <span class="btn btn-blue d-flex px-3" onclick="changeTab('<?= site_url('/investor-request/?request_type=KYC') ?>','Investor_Service_Request')">Request KYC</span>
        </div>
        <hr class="mt-0">
        <?= kyc_complience_html(); ?>
    </div>
    <div id="form4" class="form-container list-group" style="display:none; background: white;">
        <h3 class="m-0 ms-md-4 ms-1" style="padding: 21.1px">SUBMISSION OF FORM 15G/15H/10F</h3>
        <hr class="mt-0">
        <form id="tds-exemption-form" class="tds-form px-4 px-md-5 pb-5">
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
                <div class="col-6">
                    <label for="financial_year" class="form-label">Financial Year</label>
                    <select name="financial_year" id="financial_year" class="form-select">
                        <option value="" selected>----Select----</option>
                        <option value="2025-2024">2025-2024</option>
                        <option value="2024-2023">2024-2023</option>
                        <option value="2023-2022">2023-2022</option>
                        <option value="2022-2021">2022-2021</option>
                        <option value="2021-2020">2021-2020</option>
                    </select>
                </div>
                <!-- <div class="col-1"></div> -->
                <div class="col-6">
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
                <div class="col-6">
                    <label for="pan_number" class="form-label required-label">Pan Number</label>
                    <input type="text" name="pan_number" id="pan_number1" class="form-control" required>
                </div>

                <div class="col-6">
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
                <label for="copy_of_form_10f_submitted_at" class="form-label">Copy of Form 10f Submitted At</label>
                <input type="file" name="copy_of_form_10f_submitted_at" id="copy_of_form_10f_submitted_at" class="form-control">
                <small class="form-text text-muted">
                    Income Tax Portal with its acknowledgement/ FORM15G/ FORM15H Document by entity entitled to exemption from TDS (File Format PDF/JPG/PNG/GIF)
                </small>
                <div class="show-uploaded-form2-list"></div>
            </div>

            <div class="mb-3 text-center">
                <button type="submit" class="btn btn-blue form4_non_loader">Submit</button>
                <button class="btn btn-blue form4_submit_loader text-white" type="button" disabled style="display:none;">
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    Saving Details...
                </button>
            </div>
        </form>
    </div>
    <div id="form1" class="form-container list-group h-100 w-100" style="display:block; background: white;">
        <div class="h-100 w-100">
            <?php if (is_user_logged_in()) : ?>
                <?= do_shortcode('[load_ticket_page]'); ?>
            <?php else : ?>
                <?= do_shortcode('[login_signup_form]'); ?>
            <?php endif; ?>
        </div>
    </div>
<?php
    return ob_get_clean();
}

function ipo_and_issues_list_function()
{
    $company_selected = !empty($_GET['company']) ? $_GET['company'] : '';
    $tab = !empty($_GET['tab']) ? $_GET['tab'] : '';
    $values = ['Application Form (Physical)', 'Prospectus / Letter offer', 'Application Form (Demat)', 'Public Announcements', 'SH4', 'FAQ', 'Corrigendum'];
    $kyc_list = array(
        array(
            'title' => 'Form ISR-1',
            'description' => 'Request for registering pan, kyc details or changes/updation.',
            'download_link' => 'https://pragmaappscstg.wpengine.com/wp-content/uploads/2025/02/Form-ISR-1-1.pdf'
        ),
        array(
            'title' => 'Form ISR-2',
            'description' => 'Confirmation of Signature of securities holder by the Banker',
            'download_link' => 'https://pragmaappscstg.wpengine.com/wp-content/uploads/2025/02/FORM-ISR-2.doc'
        ),
        array(
            'title' => 'Form ISR-3',
            'description' => 'Declaration Form for Opting-out of Nominationby holders of physical securities in Listed Companies',
            'download_link' => 'https://pragmaappscstg.wpengine.com/wp-content/uploads/2025/02/FORM-ISR-3.doc'
        ),
        array(
            'title' => 'Form ISR-4',
            'description' => 'Request for issue of Duplicate Certificate and other Service Requests.',
            'download_link' => 'https://www.beetalmail.com/forms/FORM%20ISR-4.pdf'
        ),
        array(
            'title' => 'Form SH-13',
            'description' => 'Registration for Nomination Form.',
            'download_link' => 'https://pragmaappscstg.wpengine.com/wp-content/uploads/2025/02/FORM-SH-13.doc'
        ),
        array(
            'title' => 'Form SH-14',
            'description' => 'Cancellation or Variation of Nomination.',
            'download_link' => "https://pragmaappscstg.wpengine.com/wp-content/uploads/2025/02/FORM-SH-14.doc"
        ),
    );
    $form_list = array(
        array(
            'title' => 'Form 15G',
            'description' => 'Resident individual, HUF, trust, or other assessee (excluding companies or firms) under 60 years of age.'
        ),
        array(
            'title' => 'Form 15H',
            'description' => 'Resident individual aged 60 years or more (senior citizen).'
        ),
        array(
            'title' => 'Form 10F',
            'description' => 'To claim tax treaty benefits for income earned in India, a non-resident must provide details in Form 10F and a Tax Residency Certificate (TRC) as per Section 90(5) of the Income Tax Act, 1961.'
        ),
        array(
            'title' => 'Self Declaration Form',
            'description' => 'General self-declaration form.'
        ),
        array(
            'title' => 'Dec Under Rule 37BA',
            'description' => 'Declaration under Rule 37BA.'
        ),
        array(
            'title' => 'FORM ISR-3',
            'description' => 'Declaration form for opting out of nomination.'
        ),
    );
    $company_list = array(
        array(
            'name' => 'Faalcon Concepts Limited',
            'type' => 'SME-IPO',
            'Opening_date' => '04/19/2024',
            'Closing_date' => '04/23/2024'
        ),
        array(
            'name' => 'Amanaya Ventures Limited',
            'type' => 'SME-IPO',
            'Opening_date' => '02/28/2023',
            'Closing_date' => '02/28/2023'
        ),
        array(
            'name' => 'Wise Travel India Limited',
            'type' => 'SME-IPO',
            'Opening_date' => '02/12/2024',
            'Closing_date' => '02/12/2024'
        ),
    );
    // Start output buffering
    ob_start();
?>
    <div id="tab5" class="tabs" style="display:block; background-color: #212D45;">
        <?php echo show_recent_issue() ?>
    </div>
    <div id="tab2" class="tabs list-group py-4" style="display:none; width: 350px; background-color: #212D45;">
        <div class="d-flex flex-column px-4 text-white border-bottom">
            <h3 class="text-white">Services and Resources</h3>
        </div>
        <div class="tags-container px-4 mt-3">
            <p class=" p-0 text-sm text-justify text-white">Access key documents and resources, including application forms, allotment queries, prospectuses, public announcements, FAQs, and other shareholder-related services to streamline your investment process.</p>
        </div>
        <div class="d-flex justify-content-center d-none loading-tab2-resources">
            <div class="spinner-border" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        <?php
        $show_data = show_company_download_forms($company_selected);
        if (!empty($show_data['download'])) {
            echo $show_data['download'];
        }
        ?>
    </div>
    <div id="tab3" class="tabs list-group" style="display:none; background-color: #212D45;">
        <div class="" style="width:350px;">
            <div class="d-flex flex-column gap-2 py-4">
                <div class="px-3 border-bottom">
                    <h4 class="text-white">KYC Updation</h4>
                </div>
                <div class="d-flex flex-column gap-3 mt-3">
                    <?php foreach ($kyc_list as $list) : ?>
                        <div class="list-group px-3">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title d-flex justify-content-between m-0">
                                        <?php echo $list['title'] ?>
                                        <a href="<?= $list['download_link'] ?>" class="text-blue" target="_blank"><i class="fa-solid fa-download"></i></a>
                                    </h5>
                                    <p class="card-text my-1">
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
        </div>
    </div>
    <div id="tab4" class="tabs" style="display:none;  width: 350px; background-color: #212D45;">
        <div class="">
            <div class="d-flex flex-column gap-2 py-4">
                <div class="px-3 border-bottom">
                    <h4 class="text-white">Form List</h4>
                </div>
                <div class="d-flex flex-column gap-3 mt-3">
                    <?php foreach ($form_list as $list) : ?>
                        <div class="list-group px-3">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title d-flex justify-content-between m-0"><?php echo $list['title'] ?>
                                        <i class="fa-solid fa-download"></i>
                                    </h5>
                                    <p class="card-text my-1">
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
        </div>

    </div>
    <?php if (!(is_user_logged_in())) { ?>
        <div id="tab1" class="tabs list-group" style="display:none; background-color: #212D45;">
            <?php echo show_recent_issue() ?>
        </div>
    <?php
    } ?>

    <!-- <div id="tab6" class="tabs list-group shadow pt-4" style="display:none;">
    
    </div> -->
<?php
    return ob_get_clean();
}

function tax_forms_table()
{
    ob_start();
?>

    <table id="form1" class="tax-forms-table form-container">
        <tr class="table-head">
            <th>Form</th>
            <th>Form Details</th>
        </tr>
        <tr>
            <td>Form 15G</td>
            <td>Resident individual, HUF, trust, or other assessee (excluding companies or firms) under 60 years of age.</td>
        </tr>
        <tr>
            <td>Form 15H</td>
            <td>Resident individual aged 60 years or more (senior citizen).</td>
        </tr>
        <tr>
            <td>Form 10F</td>
            <td>To claim tax treaty benefits for income earned in India, a non-resident must provide details in Form 10F and a Tax Residency Certificate (TRC) as per Section 90(5) of the Income Tax Act, 1961.</td>
        </tr>
        <tr>
            <td>Self Declaration Form</td>
            <td>General self-declaration form.</td>
        </tr>
        <tr>
            <td>Dec Under Rule 37BA</td>
            <td>Declaration under Rule 37BA.</td>
        </tr>
        <tr>
            <td>FORM ISR-3</td>
            <td>Declaration form for opting out of nomination.</td>
        </tr>
    </table>
<?php
    return ob_get_clean();
}

add_shortcode('show_SEBI_downloads_table', function () {
    $data = [
        ["Format" => "SEBI Circulars", "Format Usage" => "SEBI_circular_031121.PDF", "Download URL" => "https://pragmaappscstg.wpengine.com/wp-content/uploads/2025/02/SEBI_circular_031121-1.pdf"],
        ["Format" => "SEBI Circulars", "Format Usage" => "SEBI_Circular_261121.pdf", "Download URL" => "https://pragmaappscstg.wpengine.com/wp-content/uploads/2025/02/SEBI_Circular_261121.pdf"],
        ["Format" => "SEBI Circulars", "Format Usage" => "SEBI_CIRCULAR_August 04, 2023.pdf", "Download URL" => "#"],
        ["Format" => "SEBI Circulars", "Format Usage" => "SEBI_Circular_For_Change of RTA with Draft Aggrement.pdf", "Download URL" => "https://pragmaappscstg.wpengine.com/wp-content/uploads/2025/02/SEBI_Circular_For_Change-of-RTA-with-Draft-Aggrement.pdf"],
        ["Format" => "SEBI Circulars", "Format Usage" => "SEBI_CIRCULAR_July 31, 2023.pdf", "Download URL" => "#"],
        ["Format" => "SEBI Circulars", "Format Usage" => "SEBI_KYC_CIRCULAR_DATED_160323.pdf", "Download URL" => "https://pragmaappscstg.wpengine.com/wp-content/uploads/2025/02/SEBI_KYC_CIRCULAR_DATED_160323.pdf"],
    ];
    $pods = pods('form_download')->find([
        'where' => 'form_type = "Circulars"',
        'limit' => -1
    ]);
    if ($pods->total() < 1) {
        return "  <div class='mx-md-5 mx-4 alert alert-danger mt-4' >
                            No Data Available
                        </div>";
    }
    ob_start(); ?>
    <div class="d-flex flex-wrap">
        <?php while ($pods->fetch()) : ?>
            <div class="col-12 col-md-4 d-flex p-2">
                <div class="d-flex flex-column rounded border-blue border-2 p-4 w-100">
                    <div class="d-flex justify-content-between text-blue">
                        <h5 class="mb-2"> <?= $pods->field('form_title'); ?></h5>
                        <a href="<?= $pods->field('form_url')['guid']; ?>" target="_blank">
                            <i class="fa-solid fa-download text-blue fs-4"></i>
                        </a>
                    </div>
                    <small class="text-blue text-sm">
                        <?= $pods->field('form_description'); ?>
                    </small>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

<?php
    return ob_get_clean();
});

add_shortcode('login_user_header', function () {
    if (!is_user_logged_in()) {
        return;
    }
    $current_user = wp_get_current_user();
    ob_start(); ?>
    <a href="<?= site_url('investor-request/#Investor_Service_Request') ?>" class="text-decoration-none d-flex align-items-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle text-white d-flex justify-content-center align-items-center h5 mb-0" style="width: 35px; height: 35px; background: #ffc03dcf;">
                <div class="rounded-circle text-white d-flex justify-content-center align-items-center h5 mb-0" style="width: 30px; height: 30px; background: #ffc03d;"> <?= strtoupper(substr($current_user->user_login, 0, 1)) ?></div>
            </div>
            <p class="text-white text-center mb-0">Welcome, <?= ucwords($current_user->user_login) ?></p>
        </div>
    </a>
    <script>
        jQuery(document).ready(async function($) {
            $(".ast-header-button-1").hide();
        });
    </script>
<?php
    return ob_get_clean();
});

add_shortcode('show_downloads_table', function () {
    $pods = pods('form_download')->find([
        'where' => 'form_type = "Downloads"',
        'limit' => -1
    ]);
    $data = [
        ["Format" => "Investor Charter", "Format Usage" => "Investor Charter for RTA.", "Download URL" => "https://www.beetalmail.com/forms/RTA_INVESTOR_CHARTER.pdf"],
        ["Format" => "Investor Complaint Summary", "Format Usage" => "Investor Complaint Summary as per SEBI Circular", "Download URL" => "https://www.beetalmail.com/forms/BEETAL_INVESTOR_COMLAINT_SUMMARY.PDF"],
        ["Format" => "SEBI Circulars", "Format Usage" => "Circular for KYC details like updation of Address Bank details PAN Nomination, Signature etc.", "Download URL" => "https://pragmaappscstg.wpengine.com/contact/#circulars"],
        ["Format" => "FORM ISR-1", "Format Usage" => "Format for Request for Registering PAN, KYC details for Changes/Updation etc.", "Download URL" => "https://www.beetalmail.com/forms/Form%20ISR-1.PDF"],
        ["Format" => "FORM ISR-2", "Format Usage" => "Updation/Confirmation of Signature of securities holder by the Banker", "Download URL" => "https://pragmaappscstg.wpengine.com/wp-content/uploads/2025/02/FORM-ISR-2-2.doc"],
        ["Format" => "FORM ISR-3", "Format Usage" => "Declaration Form for Opting-out of Nomination", "Download URL" => "https://pragmaappscstg.wpengine.com/wp-content/uploads/2025/02/FORM-ISR-3-2.doc"],
        ["Format" => "FORM ISR-4", "Format Usage" => "Request for issue of Duplicate Certificate", "Download URL" => "https://pragmaappscstg.wpengine.com/wp-content/uploads/2025/02/FORM-ISR-4.pdf"],
        ["Format" => "FORM SH-13", "Format Usage" => "Nomination Registration", "Download URL" => "https://pragmaappscstg.wpengine.com/wp-content/uploads/2025/02/FORM-SH-13.doc"],
        ["Format" => "FORM SH-14", "Format Usage" => "Cancellation or Variation of Nomination", "Download URL" => "https://pragmaappscstg.wpengine.com/wp-content/uploads/2025/02/FORM-SH-14.doc"],
        ["Format" => "SEBI Circular dated 26th November 2021", "Format Usage" => "Investor charter and disclosure of complaints", "Download URL" => "https://www.beetalmail.com/forms/SEBI_Circular_261121.pdf"],
        ["Format" => "Change of Name", "Format Usage" => "Format for change of Name", "Download URL" => "https://pragmaappscstg.wpengine.com/wp-content/uploads/2025/02/change_of_name-2.doc"],
        ["Format" => "Deletion of Name", "Format Usage" => "Name Deletion for Joint Holder in case of Death", "Download URL" => "https://pragmaappscstg.wpengine.com/wp-content/uploads/2025/02/deletion_of_name.doc"],
        ["Format" => "Transfer of Share", "Format Usage" => "Instruction for Transfer of Shares", "Download URL" => "https://pragmaappscstg.wpengine.com/wp-content/uploads/2025/02/FOLIO_CONSOLIDATION.doc"],
        ["Format" => "Transmission of Shares", "Format Usage" => "Transmission of Shares after the death of the Registered Share Holder", "Download URL" => "https://pragmaappscstg.wpengine.com/wp-content/uploads/2025/02/transmission_after_death.doc"],
        ["Format" => "Transposition of Shares", "Format Usage" => "Transpositioning of Names", "Download URL" => "https://pragmaappscstg.wpengine.com/wp-content/uploads/2025/02/Transposition-1.doc"],
        ["Format" => "Issue of Duplicate Share Certificates (For Holder)", "Format Usage" => "Format for obtaining the duplicate share certificate (For Holder)", "Download URL" => "https://pragmaappscstg.wpengine.com/wp-content/uploads/2025/02/duplicate_for_holder.doc"],
        ["Format" => "Issue of Duplicate Share Certificates (For Buyer)", "Format Usage" => "Format for obtaining the duplicate share certificate (For Buyer)", "Download URL" => "https://pragmaappscstg.wpengine.com/wp-content/uploads/2025/02/duplicate_for_buyer.doc"],
        ["Format" => "Demat of Share", "Format Usage" => "Procedure for Demat of Securities", "Download URL" => "https://pragmaappscstg.wpengine.com/wp-content/uploads/2025/02/ECS.doc"],
        ["Format" => "Indemnity Bond (For CAN / Refund Order/ Interest / Dividend Warrants)", "Format Usage" => "Format for obtaining Duplicate Refund Order/Interest Warrant/Dividend warrant", "Download URL" => "https://pragmaappscstg.wpengine.com/wp-content/uploads/2025/02/letter_duplicate_warrant.doc"],
        ["Format" => "Remat of Share", "Format Usage" => "Procedure for Remat of Securites", "Download URL" => "https://pragmaappscstg.wpengine.com/wp-content/uploads/2025/02/15G_FORM-1.doc"],
    ];
    if ($pods->total() < 1) {
        return "  <div class='mx-md-5 mx-4 alert alert-danger mt-4' >
                            No Data Available
                        </div>";
    }

    ob_start(); ?>
    <div class="d-flex flex-wrap">
        <?php while ($pods->fetch()) : ?>
            <div class="col-12 col-md-4 d-flex p-2">
                <div class="d-flex flex-column rounded border-blue border-2 p-4 w-100">
                    <div class="d-flex justify-content-between text-blue">
                        <h5 class="mb-2"> <?= $pods->field('form_title'); ?></h5>
                        <a href="<?= $pods->field('form_url')['guid']; ?>" target="_blank">
                            <i class="fa-solid fa-download text-blue fs-4"></i>
                        </a>
                    </div>
                    <small class="text-blue text-sm">
                        <?= $pods->field('form_description'); ?>
                    </small>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php
    return ob_get_clean();
});

add_shortcode('show_menu', function () {
    $menuList = array(
        array(
            'image_url' => 'https://pragmaappscstg.wpengine.com/wp-content/uploads/2024/11/images-2-2.jpg',
            'title' => 'IPO Allotment Status',
            'sub_title' => 'Click here to open form of acceptance from open offer.',
            'form' => 'form5',
            'tab' => 'tab5',
            'hash' => 'IPO_Allotment_Status',
            'icon' => 'file-earmark-text-fill'
        ),
        array(
            'image_url' => 'https://pragmaappscstg.wpengine.com/wp-content/uploads/2024/11/buyback-2.webp',
            'title' => 'Open/Buy Back/Rights Issue',
            'sub_title' => 'Click here to open offer handeled by as Registrar.',
            'form' => 'form2',
            'tab' => 'tab2',
            'hash' => 'Open_Buyback_right_issue',
            'icon' => 'cash-stack'
        ),
        array(
            'image_url' => 'https://pragmaappscstg.wpengine.com/wp-content/uploads/2024/11/Sekuritance-KYC-Compliance-600x300-1.jpg',
            'title' => 'KYC Compliance',
            'sub_title' => 'Click here to open KYC Compliances form.',
            'form' => 'form3',
            'tab' => 'tab3',
            'hash' => 'KYC_Compliance',
            'icon' => 'person-check-fill'
        ),
        array(
            'image_url' => 'https://pragmaappscstg.wpengine.com/wp-content/uploads/2024/11/TDS-Exemption-1.png',
            'title' => 'TDS Exemption',
            'sub_title' => 'Click here to open TDS Exemption form.',
            'form' => 'form4',
            'tab' => 'tab4',
            'hash' => 'TDS_Exemption',
            'icon' => 'file-earmark-excel-fill'
        ),
        array(
            'image_url' => 'https://pragmaappscstg.wpengine.com/wp-content/uploads/2024/11/pexels-photo-6771900-6771900.jpg',
            'title' => 'Investor Service Request',
            'sub_title' => 'Click here to open',
            'form' => 'form1',
            'tab' => 'tab1',
            'hash' => 'Investor_Service_Request',
            'icon' => 'person-fill-gear'
        ),
        // array(
        //     'image_url' => '',
        //     'title' => 'Logout',
        //     'sub_title' => 'Click here to logout',
        //     'form' => '',
        //     'tab' => '',
        //     'hash' => 'logout',
        //     'icon' => 'power'
        // ),
        // array(
        //     'image_url' => 'https://pragmaappscstg.wpengine.com/wp-content/uploads/2024/11/pexels-photo-17323635-17323635.jpg',
        //     'title' => 'Folio Information',
        //     'sub_title' => 'Click here to open',
        //     'form' => 'form6',
        //     'tab' => 'tab6'
        // ),
    );
    $tab = !empty($_GET['tab']) ? $_GET['tab'] : '';

    $redirect_url = site_url('/investor-request');
    ob_start(); ?>
    <div class="d-flex justify-content-between align-items-center py-4 border-bottom px-4">
        <a href="<?= site_url() ?>" class="d-flex align-items-center h4 text-white justify-content-center m-0 text-decoration-none"><i class="bi bi-arrow-left-circle me-4"></i>Go to BEETAL</a>
        <i id="menuToggleIcon" class="bi bi-chevron-down text-white fs-3 d-md-none" data-bs-toggle="collapse" data-bs-target="#menuCollapse" aria-expanded="false" aria-controls="menuCollapse"></i>
    </div>

    <!-- Collapsible content -->
    <div class="collapse d-md-block" id="menuCollapse">
        <div class="text-black p-0 m-0">
            <ul class="nav flex-column m-0">
                <?php foreach ($menuList as $item): ?>
                    <?php
                    $redirect_url = site_url('/investor-request?tab=dashboard');
                    ?>
                    <!-- <a href="<?= site_url('/investor-request#' . $item['hash'] . '') ?>" class="text-decoration-none"> -->
                    <li id="<?php echo $item['form'] . $item['tab'] ?>"
                        class="d-flex align-items-center justify-content-between border-bottom py-3 ps-3 menu-items <?php echo $item['form'] == 'form1' ? 'menu-active' : 'text-white' ?>"
                        style="cursor: pointer;"
                        onclick="changeTab('<?= $redirect_url ?>','<?= $item['hash'] ?>')">
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
                <?php if (is_user_logged_in()) : ?>
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
        </div>
    </div>

<?php
    return ob_get_clean();
});

add_shortcode('IPO_allotment_status_form', function () {
    $atts = array(
        'label' => 'Select an Option:',
        'options' => array(
            'Application Number' => 'Enter Application Number.',
            'PAN Number' => 'Enter PAN Number.',
            'DPID/Client ID' => 'Eg:-INXXXXXXXXXX,120XXXXXXXXX,XXXXXXX',
        )
    );
    ob_start();

    // Check if the form is submitted
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['company_details_form'])) {

        // Validate and sanitize input
        $company_name = sanitize_text_field($_POST['company_name']);
        $pan_number = sanitize_text_field($_POST['pan_number']);
        $application_number = sanitize_text_field($_POST['application_number']);
        $dpid_client_id = sanitize_text_field($_POST['dpid_client_id']);

        // Save data to Pods
        $pod = pods('ipo_allotment_status'); // Replace 'your_pod_name' with your Pod name

        $new_pod_id = $pod->add(array(
            'company_name' => $company_name,
            'pan_number' => $pan_number,
            'application_number' => $application_number,
            'dpid_client_id' => $dpid_client_id,
        ));

        if ($new_pod_id) {
            echo '<p>Company details saved successfully!</p>';
        } else {
            echo '<p>There was an error saving the details. Please try again.</p>';
        }
    }

?>

    <form method="post" class="ipo-allotment-form">
        <p class="full-field">
            <label for="company_name" class="required-label">Company Name</label>
            <select name="company_name" id="company_name" required>
                <option value="">----Select----</option>
                <option value="Faalcon Concepts LIMITED">Faalcon Concepts LIMITED</option>
                <option value="Wise Travel India LTD">Wise Travel India LTD</option>
                <option value="Amanaya Ventures Limited">Amanaya Ventures Limited</option>
            </select>
        </p>
        <div class="radio-button-group">
            <label for="radio"><?php $atts['label'] ?></label>
            <div class="radio-button-items">
                <?php
                foreach ($atts['options'] as $option => $value) {
                    echo '<div class="radio-button-item">';
                    if ($option == 'Application Number') {
                        echo '<input type="radio" name="radio_option" id="' . sanitize_title($option) . '" value="' . $value . '" checked />';
                    } else {
                        echo '<input type="radio" name="radio_option" id="' . sanitize_title($option) . '" value="' . $value . '"/>';
                    }
                    echo '<label for="' . sanitize_title($option) . '">' . $option . '</label>';
                    echo '</div>';
                }
                ?>
            </div>
            <input type="text" name="dynamic-value-field" id="dynamic-field" placeholder="Enter DP ID" />
        </div>
        <p class="submit">
            <input type="submit"></input>
        </p>
    </form>

<?php
    return ob_get_clean();
});

add_shortcode('show_ipo_issue_table', function () {
    ob_start();
?>
    <div id="form3" class="submission-table form-container">
        <?php echo tax_forms_table(); ?>
    </div>
<?php
    return ob_get_clean();
});

add_shortcode('show_latest_ipo_table', function () {
    ob_start();
?>

    <table class="tax-forms-table">
        <tr class="table-head">
            <th>Name</th>
            <th>Type</th>
            <th>Opening Date</th>
            <th>Closing Date</th>
        </tr>
        <tr>
            <td>Faalcon Concepts Limited</td>
            <td>SME-IPO</td>
            <td>19/04/2024</td>
            <td>23/04/2024</td>
        </tr>
        <tr>
            <td>Amanaya Ventures Limited</td>
            <td>SME-IPO</td>
            <td>24/02/2023</td>
            <td>28/02/2023</td>
        </tr>
        <tr>
            <td>Wise Travel India Limited</td>
            <td>SME-IPO</td>
            <td>12/02/2024</td>
            <td>14/02/2024</td>
        </tr>
    </table>
<?php
    return ob_get_clean();
});

add_shortcode('show_form_list', function () {
    if (!empty(get_current_user_id())) {
        $roles = get_userdata(get_current_user_id())->roles;
        if (!empty($roles) && $roles[0] == 'wpas_company_user') {
            redirect_company_user_shortcode();
        }
    }
    ob_start();
?>
    <div id="main-section" class="d-flex align-items-start flex-md-nowrap flex-wrap">
        <div class="flex-grow-1 h-100" id="left-section">
            <?php echo ipo_and_issue_handled_form() ?>
        </div>
        <div class="align-items-start d-flex h-100 text-white" id="right-section" style="background-color: #212D45;">
            <?php echo ipo_and_issues_list_function() ?>
        </div>
    </div>
<?php
    return ob_get_clean();
});

add_shortcode('show_recent_company_list', function () {

    // error_log(print_r($pods->data(), true));
    ob_start(); ?>
    <?= show_recent_company_list_fn(); ?>
<?php
    return ob_get_clean();
});
