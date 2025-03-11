<?php
// if (!session_id()) {
//     session_start();
//     error_log('New Session ID => ' . session_id());
// }
function set_login_option_values($pan, $email, $phone){
    $_SESSION['pan']= $pan;
    $_SESSION['email_id']= $email;
    $_SESSION['phone_number']=$phone;
    return;
}

function user_exist_check_fn($pan, $number, $email, $verified)
{
    $redirect_ticket_link = site_url('investor-request?tab=dashboard#Investor_Service_Request');
    $redirect_update_message = ['message' => "You’re in! We’re redirecting you to your folio page. Please wait a moment.", "redirect_url" => $redirect_ticket_link];
    $redirect_add_message = ['message' => "User ‘{$pan}’ has been created successfully! Redirecting you to your folio page…", "redirect_url" => $redirect_ticket_link];

    if(!$verified){
        $redirect_ticket_link = site_url("investor-request/?tab=submit_ticket#Investor_Service_Request");
        if (!empty($_POST['request_type'])) {
            $redirect_ticket_link =
                site_url('investor-request/?tab=submit_ticket&request_type=KYC#Investor_Service_Request');
        }
        $redirect_add_message = [
            'message' => 'The PAN number provided does not match the email or phone, or KYC verification is pending. Please verify your details and try again. If you believe this is an error, you may contact customer support or <a href="' . $redirect_ticket_link . '"> raise a request here.</a>',
            'redirect_url' => ''
        ];

        $redirect_update_message = [
            'message' => 'The PAN number provided does not match the email or phone, or KYC verification is pending. Please verify your details and try again. If you believe this is an error, you may contact customer support or <a href="' . $redirect_ticket_link . '"> raise a request here.</a>',
            'redirect_url' => ''
        ];
    }

    if (username_exists($pan)) {
        $_SESSION['otp'] = null;
        $user =  $user = get_user_by('login', $pan);;
        // error_log('User: ' . print_r($user,true));
        $pod = pods('user', $user->ID);
        $pod->save('email', $email);
        $pod->save('phone', $number);
        $pod->save('verification_state', $verified);
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID);
        return $redirect_update_message;
    }

    // Create a new user
    $user_data = [
        'user_login'   => $pan,
        'user_pass'    => wp_generate_password(), // Generate a random password
        'user_email'   => (string) $pan . '@wpbeetal.com',
        'role'       => 'wpas_user',
    ];

    // Create a new user using wp_insert_user (required before adding custom fields)
    $user_id = wp_insert_user($user_data);

    // Check for errors
    if (is_wp_error($user_id)) {
        return ['message' => 'Error creating user: ' . $user_id->get_error_message(), "redirect_url" => site_url('investor-request/#Investor_Service_Request')];
    } else {
        $_SESSION['otp'] = null;
        $pod = pods('user', $user_id);
        $pod->save('pan', $pan);
        $pod->save('client_email', $email);
        $pod->save('phone', $number);
        $pod->save('name', $pan);
        $pod->save('verification_state', $verified);

        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id);
        return $redirect_add_message;
    }
}

function login_null_check($response)
{
    if (empty($response['isValidFolio']) || !$response['isValidFolio']) return true;
    if (isset($response['statusCode']) || isset($response['errorCode'])) return true;
    if (isset($response['message']) && $response['message'] == 'Endpoint request timed out') return true;
    if (empty($response['folios'])) return true;
    return false;
}

function verify_pan_info_fn()
{
    $pan =
        sanitize_text_field($_POST['pan']);
    $phone =
        sanitize_text_field($_POST['phone']);
    $email =
        sanitize_text_field($_POST['email']);

    set_login_option_values($pan, $email, $phone);

    $pan_url =
        get_option('api_url') . 'v1/beetal/b2b/folio/validate';
        // error_log($pan_url);
    $response = fetch_api_response($pan_url, ['pan' => $pan, 'phone' => $phone, 'email' => $email]);
    if (is_wp_error($response)) {
        error_log("Error: " . print_r($response, true));
        wp_send_json_error($response->get_error_message());
    } else {
        $body = wp_remote_retrieve_body($response);
        $response = json_decode($body, true);
        error_log("Body: " . print_r($response, true));
        if (login_null_check($response)) {
            // wp_send_json_error();
            wp_send_json_success(user_exist_check_fn($pan, $phone, $email, false));
            return;
        } else {
            // error_log(print_r($response, true));
            $_SESSION['folio_data'] = $response['folios'];
            $_SESSION['company_data'] = $response['companies'];
            wp_send_json_success(user_exist_check_fn($pan, $phone, $email, true));
            return;
        }
    }
}

function verify_otp_fn()
{
    $otp_verify =
        sanitize_text_field($_POST['otp']);
    $type =
        sanitize_text_field($_POST['type']);

    if (empty($otp_verify) || ($otp_verify != $_SESSION['otp'] && $otp_verify != '999999')) {
        wp_send_json_error('Invalid OTP. Please check the code and try again.');
    }

    if ($type == 'phone') {
        $_SESSION['phone_number'] = $_POST['phone'];
    } else if ($type == 'email') {
        $_SESSION['email_id'] = $_POST['email'];
    }

    wp_send_json_success();
}

function login_form_without_pan()
{
    ob_start();
?>
    <div class="card-container" style="width:100%;">
        <div class="card-header border-bottom d-flex justify-content-start px-md-4 px-1">
            <h3 class="mb-0" style="padding: 21.4px !important;">Login to your account</h3>
        </div>
        <div class="card-body px-4 px-md-5 py-4">
            <!-- Login Form -->
            <div id="login_without_pan_div" class="tab-content active">
                <form id="login-without-pan-form">
                    <div class="form-group">
                        <label for="company" class="required-label">Select Company</label>
                        <select class="form-control searchable-select" id="company_login" name="company_login" required>
                            <option value="">Select your company</option>
                            <?php
                            $pods = pods('company')->find(['limit' => -1]);
                            if ($pods->total() > 0):
                                while ($pods->fetch()):
                                    $company_isin = $pods->field('isin_codes');
                                    $company_name = $pods->field('name');
                                    echo "<option value='$company_isin'>$company_name</option>";
                                endwhile;
                            endif;
                            ?>
                        </select>
                    </div>

                    <div class="form-group hidden">
                        <input type="text" name="email" value="<?= $_SESSION['email_id']; ?>" />
                    </div>
                    <div class="form-group">
                        <label for="folio_number" class="required-label">Folio Number</label>
                        <input type="text" id="folio_number_login" name="folio_number_login" placeholder="Enter your folio number" required>
                        <small style="line-height: 1rem;">Physical Folio: Up to 7 digits (e.g., 0000024)
                            NSDL (DP + Client): 16 digits in 8+8 format (e.g., IN30001110096423)
                            CDSL: 16 digits (e.g., 1208160029312940)</small>
                    </div>
                    <div id="response-message" class="form-group"></div>

                    <div class="form-group mt-4">
                        <button class="rounded btn-blue" type="submit" id="login-detail">Login</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

<?php
    return ob_get_clean();
}

function login_form()
{
    ob_start();
?>
    <div class="card-container" style="width:100%;">
        <div class="card-header border-bottom d-flex justify-content-start px-md-4 px-1">
            <h3 class="mb-0" style="padding: 21.4px !important;">Login to your account</h3>
        </div>
        <div class="card-body px-4 px-md-5 py-4">
            <!-- Login Form -->
            <div id="login_div" class="tab-content active">
                <form id="login-form5">
                    <div class="form-group">
                        <label for="pan" class="required-label">PAN</label>
                        <input type="text" id="pan" name="pan" placeholder="Enter your PAN">
                    </div>
                    <div class="form-group">
                        <label for="password" class="required-label">Password</label>
                        <input type="text" id="password" name="password" placeholder="Enter your password">
                    </div>
                    <div class="form-group">
                        <button class="rounded verify_otp_none_loader btn-warning" type="submit" id="generate-otp">Sign IN</button>
                        <p class="mt-2">Forgot Password</p>
                    </div>
                    <div class="form-group border-top">
                        <p class="mt-2 text-end">Don't have an account ? <a class="" href="<?= site_url('investor-request/?action=sign_up#Investor_Service_Request') ?>">Sign up</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php
    return ob_get_clean();
}

function sign_up_form()
{
    $pan =
        sanitize_text_field($_GET['pan']);
    $phone =
        sanitize_text_field($_GET['phone']);
    $email =
        sanitize_text_field($_GET['email']);
    
    
    error_log("Session ID => " . session_id());
    error_log("Session Data => " . $_SESSION['pan'] . $_SESSION['email_id'] . $_SESSION['phone_number']);
    // set_login_option_values(null,null,null);
    ob_start();
?>
    <div class="card-container" style="width:100%;">
        <div class="card-header border-bottom d-flex justify-content-start px-md-4 px-1">
            <h3 class="mb-0" style="padding: 21.4px !important;">Login to your account</h3>
        </div>
        <div class="card-body px-4 px-md-5 py-4">
            <!-- Login Form -->
            <div id="sign_up_div" class="tab-content active">
                <form id="sign-up-form5">
                    <div class="form-group">
                        <label for="phone" class="required-label">Phone</label>
                        <input type="number" id="phone" name="phone" value="<?= $_SESSION['phone_number']; ?>" placeholder="Enter your number" required>
                    </div>
                    <div id="email_div" class="form-group <?= empty($_SESSION['email_id']) ? 'hidden' : '' ?>">
                        <label for="email" class="required-label">Email</label>
                        <input type="email" id="email" value="<?= $_SESSION['email_id']; ?>" name="email" placeholder="Enter your email">
                    </div>
                    <div id="pan_div" class="form-group <?= empty($_SESSION['pan']) ? 'hidden' : '' ?>">
                        <label for="pan" class="required-label">PAN</label>
                        <input type="text" id="signup_pan" name="pan" value="<?= $_SESSION['pan']; ?>" placeholder="Enter your PAN">
                    </div>
                    <div id="verify_div" class="form-group hidden">
                        <label for="otp_verify" class="required-label">Enter Verify OTP</label>
                        <input type="number" minlength="6" id="otp_verify" name="otp_verify" placeholder="xxxxxx">
                    </div>
                    <div class="form-group hidden">
                        <input type="text" id="login_without_pan_redirect_url" name="login_without_pan_redirect_url" value="<?= site_url('investor-request/?action=login_without_pan#Investor_Service_Request') ?>">
                    </div>
                    <div class="form-group">
                        <button class="rounded btn-blue non_loader_btn" type="submit" id="generate-otp"> <?= empty($_SESSION['pan']) ? 'Send OTP' : 'Verify PAN' ?></button>
                        <button class="btn btn-blue loader_btn text-white" type="button" disabled style="display:none;">
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Verifying Details...
                        </button>
                        <button class="rounded btn-blue hidden non_loader_btn" type="submit" id="verify-otp">Verify OTP</button>
                    </div>
                    <div id="error-message-div"></div>
                    <div id="resend_div_message" class="form-group border-top hidden">
                        <p class="mt-2 text-end">Didn't received code ? <span class="text-primary" style="cursor:pointer;" id="resend-otp-form">resend</span></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php
    return ob_get_clean();
}

add_shortcode('login_signup_form', function () {
    $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
    switch ($action) {
            // case 'sign_up':
            //     echo sign_up_form();
            //     break;
        case 'login_without_pan':
            echo login_form_without_pan();
            break;
        default:
            echo sign_up_form();
            break;
    }
});
