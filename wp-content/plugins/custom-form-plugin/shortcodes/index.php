<?php
// if (!session_id()) {
//     session_start();
//     error_log('New Session ID => ' . session_id());
// }

function handle_fetch_download_pods_pagination_fn()
{
    $per_page = 10;

    // Get the current page number from the query parameter
    $current_page = isset($_POST['download']) ? intval($_POST['download']) : 1;
    $filter = isset($_POST['filter']) ? $_POST['filter'] : 'Downloads';

    // Calculate offset for pagination
    $offset = ($current_page - 1) * $per_page;
    // Define Pods query parameters
    $args = array(
        'where' => 'form_type = "' . $filter . '"',
        'limit' => $per_page,
        'offset' => $offset,
    );

    // Fetch Pods data
    $initial_pods = pods('form_download', [
        'where' => 'form_type = "' . $filter . '"',
        'limit' => -1
    ]);
    $pods = pods('form_download', $args);

    // Get total number of Pods
    $total_pods = $initial_pods->total();

    // Calculate total pages
    $total_pages = ceil($total_pods / $per_page);
    // error_log("Total pages: " . $total_pages . " Pods: " . $total_pods . " current Page: " . $current_page);
    $response_data = [];
    while ($pods->fetch()) {
        array_push($response_data, [
            'name' => $pods->field('name'),
            'form_description' => $pods->field('form_description'),
            'form_title' => $pods->field('form_title'),
            'form_type' => $pods->field('form_type'),
            'form_url' => $pods->field('form_url')['guid'],
        ]);
    }
    // error_log(print_r(reset($response_data),true));

    wp_send_json_success(["table_data" => $response_data, "total_pages" => $total_pages, "current_page" => $current_page]);
}

function show_recent_issue()
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
    ob_start();
?>
    <div class="pb-4 w-100">
        <div class="px-3 border-bottom">
            <p class="text-white text-center text-md-start px-1 py-4 m-0 h4 fw-bold">Recent Issues Handled</p>
        </div>
        <div class="px-3 mt-4">
            <div class="d-flex flex-wrap gap-3">
                <?php while ($pods->fetch()) : ?>
                    <?php error_log($pods->field('is_live')); ?>
                    <?php $redirect_url = $pods->field('offer_type') != 'IPO - SME' ?
                        site_url("open-buyback-right-issue/?company=" . $pods->field('company_code')) : site_url('ipo-allotment-form/?company=' . $pods->field('company_code'));
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

                    <!-- <div class="col-12 col-md-4 mb-4 mb-md-0 px-2 d-flex flex-wrap"> -->
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
                    <!-- </div> -->
                <?php endwhile; ?>
            </div>
        </div>
    </div>

<?php
    return ob_get_clean();
}

function ipo_and_issue_handled_form()
{
    // $company_selected = !empty($_GET['company']) ? $_GET['company'] : '';
    // $show_data = show_company_download_forms($company_selected);
    // error_log(is_user_logged_in());
    ob_start();
?>
    <div id="IPO_Allotment_Status" class="form-container list-group pt-80 pb-80" style="display:none; background: white;">
        <?= do_shortcode('[show_ipo_allotment_form]'); ?>
    </div>
    <div id="Open_Buyback_right_issue" class="form-container list-group h-100 pt-80 pb-80" style="display:none; background: white;">
        <?= do_shortcode('[show_company_offers]'); ?>
    </div>
    <div id="KYC_Compliance" class="form-container list-group pt-80 pb-80" style="display:block; background: white;">
        <?= do_shortcode('[show_kyc_compliance]'); ?>
    </div>
    <div id="TDS_Exemption" class="form-container list-group pt-80 pb-80" style="display:none; background: white;">
        <?= do_shortcode('[show_SUBMISSION_OF_FORM]'); ?>
    </div>
    <div id="Investor_Service_Request" class="form-container list-group h-100 w-100 pt-80" style="display:none; background: white;">
        <?= do_shortcode('[show_investor_request_page]'); ?>
    </div>
    <div id="Investor_Forms" class="form-container list-group h-100 w-100 px-4 px-md-5 pt-80 pb-80" style="display:none; background: white;">
        <div class="border-bottom py-2 form-margin px-3">
            <h2 class="fw-bold m-0">Investor Forms</h2>
        </div>
        <?= do_shortcode('[show_downloads_table]'); ?>
    </div>
    <div id="SEBI_Circulars" class="form-container list-group h-100 w-100 px-4 px-md-5 pt-80 pb-80" style="display:none; background: white;">
        <div class="border-bottom py-2 form-margin px-3">
            <h2 class="fw-bold m-0">SEBI Circulars</h2>
        </div>
        <?= do_shortcode('[show_SEBI_downloads_table]'); ?>
    </div>
    <div id="loading-screen-1" class="d-flex w-100 h-100 bg-white px-md-5 p-4 hidden" style="z-index: 10500; margin-top: 80px;">
        <div class="d-flex flex-column placeholder-glow gap-2 w-100 p-3">
            <span class="p-3 col-6 placeholder rounded"></span>
            <hr class="m-0 form-margin" />
            <span class="p-2 col-12 placeholder rounded"></span>
            <span class="p-2 col-12 placeholder rounded"></span>
            <span class="p-2 col-2 placeholder rounded"></span>
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

function UserHasCompanyRole()
{
    if (!is_user_logged_in()) {
        return false;
    }

    $user = wp_get_current_user();
    $allowed_roles = ['wpas_company_user', 'wpas_company_admin'];

    foreach ($user->roles as $role) {
        if (in_array($role, $allowed_roles, true)) {
            return true;
        }
    }

    return false;
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
    $response_data = [];
    while ($pods->fetch()) {
        array_push($response_data, [
            'name' => $pods->field('name'),
            'form_description' => $pods->field('form_description'),
            'form_title' => $pods->field('form_title'),
            'form_type' => $pods->field('form_type'),
            'form_url' => $pods->field('form_url')['guid'],
        ]);
    }
    ob_start(); ?>
    <table class="tax-forms-table table-bordered table-striped rounded-top d-none d-md-table">
        <thead class="rounded-top">
            <tr>
                <th class="text-center col-1" style="width: 10px;">#</th>
                <th class="text-start col-3">File Name</th>
                <th class="text-start col-7">File Description</th>
                <th class="text-center col-1">Action</th>
            </tr>
        </thead>
        <tbody id="circular_table_data">
        </tbody>
    </table>
    <div class="d-md-none" id="circular_data_mobile"></div>
    <div id="circular_table_pagination" class="d-flex gap-4 justify-content-center flex-wrap">
    </div>
<?php
    return ob_get_clean();
});

add_shortcode('show_SEBI_downloads_table_2', function () {
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
    $user_information = pods('user', get_current_user_id());
    $email = $user_information->field('client_email');
    if (empty($email)) {
        $email = $current_user->user_email;
    }
    if (UserHasCompanyRole()) {
        $email = $user_information->field('display_name');
    }
    // error_log(print_r($current_user, true));
    ob_start(); ?>
    <!-- <div class="d-flex align-items-center gap-2">
        <a href="<?= site_url('investor-request') ?>" class="text-decoration-none d-flex align-items-center gap-2">
            <div class="rounded-circle text-white d-flex justify-content-center align-items-center h5 mb-0" style="width: 35px; height: 35px; background: #ffc03dcf;">
                <div class="rounded-circle text-white d-flex justify-content-center align-items-center h5 mb-0" style="width: 30px; height: 30px; background: #ffc03d;"> <?= strtoupper(substr($email ?? $current_user->user_login, 0, 1)) ?></div>
            </div>
            <p class="text-white text-center mb-0"><?= ucfirst($email) ?? ucfirst($current_user->user_login) ?></p>
        </a>
        <i class="bi bi-chevron-down text-white"></i>
    </div> -->
    <!-- <div class="btn-group">
        <button class="d-flex align-items-center gap-2 dropdown-toggle" id="dropdownMenu2" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <div class="rounded-circle text-white d-flex justify-content-center align-items-center h5 mb-0" style="width: 35px; height: 35px; background: #ffc03dcf;">
                <div class="rounded-circle text-white d-flex justify-content-center align-items-center h5 mb-0" style="width: 30px; height: 30px; background: #ffc03d;"> <?= strtoupper(substr($email ?? $current_user->user_login, 0, 1)) ?></div>
            </div>
            <p class="text-white text-center mb-0"><?= ucfirst($email) ?? ucfirst($current_user->user_login) ?></p>
            <i class="bi bi-chevron-down text-white"></i>
        </button>
        <div class="dropdown-menu" aria-labelledby="dropdownMenu2">
            <div class=""> <i class="bi bi-chevron-down text-white"></i> Logout</div>
        </div>
    </div> -->
    <div class="dropdown">
        <div class="d-flex align-items-center gap-2 text-white dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
            <div class="rounded-circle text-white d-flex justify-content-center align-items-center h5 mb-0" style="width: 35px; height: 35px; background: #ffc03dcf;">
                <div class="rounded-circle text-white d-flex justify-content-center align-items-center h5 mb-0" style="width: 30px; height: 30px; background: #ffc03d;"> <?= strtoupper(substr($email ?? $current_user->user_login, 0, 1)) ?></div>
            </div>
            <p class="text-white text-center mb-0"><?= ucfirst($email) ?? ucfirst($current_user->user_login) ?></p>
        </div>
        <ul class="dropdown-menu mt-1 py-1 p-0 <?= UserHasCompanyRole() ? '' : 'width_webkit' ?>" aria-labelledby="dropdownMenuButton">
            <li><a class="dropdown-item text-blue py-3" href="<?= UserHasCompanyRole() ? site_url('client-services/?tab=client_information') : site_url('investor-services/#Investor_Service_Request') ?>">
                    <i class="bi bi-<?= UserHasCompanyRole() ? 'buildings' : 'person' ?> me-2"></i>
                    <?= UserHasCompanyRole() ? 'Client Information' : 'Investor Information' ?>
                </a></li>
            <li id="logout-user-header" class="border-top"><a class="dropdown-item text-blue py-3" href="#"> <i class="bi bi-box-arrow-right me-2"></i>
                    Logout</a></li>
        </ul>
    </div>
    <script>
        jQuery(document).ready(async function($) {
            $(".ast-header-button-1").hide();
            $(".client-login-header-css").hide();
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
    <table class="tax-forms-table table-bordered table-striped rounded-top d-none d-md-table">
        <thead class="rounded-top">
            <tr>
                <th class="text-center col-1" style="width: 10px;">#</th>
                <th class="text-start col-3">File Name</th>
                <th class="text-start col-7">File Description</th>
                <th class="text-center col-1">Action</th>
            </tr>
        </thead>
        <tbody id="download_table_data">

        </tbody>
    </table>
    <div class="d-md-none" id="download_data_mobile"></div>
    <div id="download_table_pagination" class="d-flex gap-4 justify-content-center flex-wrap">
    </div>
<?php
    return ob_get_clean();
});
add_shortcode('show_downloads_table_2', function () {
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
            'image_url' => 'https://pragmaappscstg.wpengine.com/wp-content/uploads/2024/11/Sekuritance-KYC-Compliance-600x300-1.jpg',
            'title' => 'KYC Compliance',
            'sub_title' => 'Click here to open KYC Compliances form.',
            'form' => 'KYC_Compliance',
            'tab' => 'tab3',
            'hash' => 'KYC_Compliance',
            'icon' => 'person-check-fill'
        ),
        array(
            'image_url' => 'https://pragmaappscstg.wpengine.com/wp-content/uploads/2024/11/images-2-2.jpg',
            'title' => 'IPO Allotment Status',
            'sub_title' => 'Click here to open form of acceptance from open offer.',
            'form' => 'IPO_Allotment_Status',
            'tab' => 'tab5',
            'hash' => 'IPO_Allotment_Status',
            'icon' => 'file-earmark-text-fill'
        ),
        array(
            'image_url' => 'https://pragmaappscstg.wpengine.com/wp-content/uploads/2024/11/buyback-2.webp',
            'title' => 'Open/Buyback Offer',
            'sub_title' => 'Click here to open offer handeled by as Registrar.',
            'form' => 'Open_Buyback_right_issue',
            'tab' => 'tab2',
            'hash' => 'Open_Buyback_right_issue',
            'icon' => 'cash-stack'
        ),
        array(
            'image_url' => 'https://pragmaappscstg.wpengine.com/wp-content/uploads/2024/11/TDS-Exemption-1.png',
            'title' => 'TDS Exemption',
            'sub_title' => 'Click here to open TDS Exemption form.',
            'form' => 'TDS_Exemption',
            'tab' => 'tab4',
            'hash' => 'TDS_Exemption',
            'icon' => 'file-earmark-excel-fill'
        ),
        array(
            'image_url' => 'https://pragmaappscstg.wpengine.com/wp-content/uploads/2024/11/pexels-photo-6771900-6771900.jpg',
            'title' => 'Investor Request',
            'sub_title' => 'Click here to open',
            'form' => 'Investor_Service_Request',
            'tab' => 'tab1',
            'hash' => 'Investor_Service_Request',
            'icon' => 'person-fill-gear'
        ),
        array(
            'image_url' => '',
            'title' => 'Investor Forms',
            'sub_title' => 'Click here to open',
            'form' => 'Investor_Forms',
            'tab' => 'tab6',
            'hash' => 'Investor_Forms',
            'icon' => 'cloud-arrow-down-fill'
        ),
        array(
            'image_url' => '',
            'title' => 'SEBI Circulars',
            'sub_title' => 'Click here to open',
            'form' => 'SEBI_Circulars',
            'tab' => 'tab7',
            'hash' => 'SEBI_Circulars',
            'icon' => 'circle-fill'
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

    $redirect_url = site_url('/investor-services');
    ob_start(); ?>
    <div class="d-flex d-md-none justify-content-between align-items-center p-4 border-bottom menu-items">
        <a href="<?= $_SERVER['HTTP_REFERER'] ?? site_url() ?>" class="d-flex align-items-center h4 text-white justify-content-center m-0 text-decoration-none"><i class="bi bi-back mx-2 fs-2 text-black"></i>
            <p class="h5 m-0">Back</p>
        </a>
        <i id="menuToggleIcon" class="bi bi-chevron-down text-dark fs-3" data-bs-toggle="collapse" data-bs-target="#menuCollapse" aria-expanded="false" aria-controls="menuCollapse"></i>
    </div>

    <!-- Collapsible content -->
    <div class="collapse d-md-block pt-80 pb-80" id="menuCollapse">
        <div class="text-black p-0 m-0">
            <ul class="nav flex-column m-0">
                <?php
                $sno = 1;
                foreach ($menuList as $item): ?>
                    <?php
                    $redirect_url = site_url('/investor-services?tab=dashboard');
                    ?>
                    <!-- <a href="<?= site_url('/investor-request#' . $item['hash'] . '') ?>" class="text-decoration-none"> -->
                    <li id="<?php echo $item['form'] . $item['tab'] ?>"
                        class="d-flex align-items-center justify-content-between py-3 px-3 menu-items mycustomClass <?= $sno == 1 ? 'first-child-left' : '' ?> <?php echo $item['form'] == 'Investor_Service_Request' ? 'menu-active' : 'text-white' ?>"
                        style="cursor: pointer;"
                        onclick="changeTab('<?= $redirect_url ?>','<?= $item['hash'] ?>','<?= is_user_logged_in() ?>')">
                        <span class="d-flex align-items-center justify-space-between gap-2" style="font-weight: 500;">
                            <!-- <img src="<?php echo $item['image_url'] ?>" class="me-2 border-y-1" style="width:40px; height:40px; border-radius: 100%; min-width:40px;" /> -->
                            <span class="text-white rounded-circle d-flex justify-content-center align-items-center bg-theme-blue" style="min-height: 50px; min-width: 50px; width: 50px; height: 50px; margin-right: 10px;">
                                <i class="bi bi-<?= $item['icon'] ?> fs-2"></i>
                            </span>
                            <p class="m-0 h5"><?php echo esc_html($item['title']); ?></p>
                        </span>
                    </li>
                    <!-- </a> -->
                <?php
                    $sno++;
                endforeach; ?>
                <?php if (is_user_logged_in()) : ?>
                    <li
                        class="d-flex align-items-end justify-content-between mycustomClass py-3 px-3 menu-items text-white"
                        style="cursor: pointer;"
                        onclick="logoutUser()">
                        <span class="d-flex align-items-center justify-space-between gap-2 h5" style="font-weight: 500;">
                            <span class="text-white rounded-circle d-flex justify-content-center align-items-center bg-theme-blue" style="min-height: 50px; min-width: 50px; width: 50px; height: 50px; margin-right: 10px;">
                                <i class="bi bi-power ?> fs-2"></i>
                            </span>
                            <p class="m-0 h5">Logout</p>
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
        if (!empty($roles) && $roles[0] == 'wpas_company_user' && $roles[0] == 'wpas_company_admin') {
            redirect_company_user_shortcode();
        }
    }
    ob_start();
?>
    <div id="main-section" class="d-flex align-items-start flex-md-nowrap flex-wrap w-100 h-100 bg-white">
        <div class="flex-grow-1 h-100 w-100 height-adjust-md">
            <?php echo ipo_and_issue_handled_form() ?>
        </div>
        <div class="align-items-start d-flex h-100 text-white pt-80" id="right-section-div" style="background-color: #F5F5F5;">
            <?= show_active_company_list() ?>
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
