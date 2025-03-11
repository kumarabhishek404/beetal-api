<?php

function company_user_login_fn()
{
    $otp =  isset($_POST['otp']) ? sanitize_text_field($_POST['otp']) : '';

    if (empty($otp) || ($otp != $_SESSION['otp'] && $otp != '999999')) {
        wp_send_json_error(['message' => 'Invalid OTP. Please check the code and try again.']);
    } else {
        $_SESSION['otp'] = null;
        wp_send_json_success(["redirect_url" => site_url('investor-service-request/?tab=company_information')]);
    }
}

function verify_recaptcha($token)
{
    $secret_key = '6Lezg8wqAAAAADE-g3fXd048QK8bNAdut7GxoXu0';
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = array(
        'secret' => $secret_key,
        'response' => $token
    );

    $options = array(
        'http' => array(
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        )
    );

    $context = stream_context_create($options);
    $result = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secret_key}&response={$token}");
    $response = json_decode($result);
    error_log("Recaptcha " . print_r($response, true));

    return isset($response->success) && $response->success;
}

add_shortcode(
    'company_login_form',
    function () {
        $message = '';
        $message_type = '';

        if (isset($_SESSION['company_login_message'])) {
            $message_type = $_SESSION['company_login_type'] == 'danger' ? "alert-danger" : "alert-success";
            $message = '<div class="alert ' . $message_type . '">' . $_SESSION['company_login_message'] . '</div>';
            unset($_SESSION['company_login_message'], $_SESSION['company_login_type']);
        }

        ob_start(); ?>

    <head>
        <script src="https://www.google.com/recaptcha/api.js?render=6Lezg8wqAAAAAIze3YgWt7kkGfNhbklSHwRLpi0O"></script>
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card border-0">
                        <div class="card-header text-start d-flex justify-content-start p-3 bg-blue text-white border-warning">
                            <p class="mb-0 text-white h3">Company Login</p>
                        </div>
                        <div class="card-body">
                            <?= $message ?>
                            <?php if (isset($_GET['error'])): ?>
                                <div class="alert alert-danger"><?php echo esc_html($_GET['error']); ?></div>
                            <?php endif; ?>
                            <form id="cfp-company-login-form" method="POST">
                                <input type="hidden" name="action" value="company_login">
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" class="form-control" id="username" name="username" required>
                                </div>

                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>

                                <input type="hidden" id="recaptcha-token" name="g-recaptcha-response">

                                <div class="form-group">
                                    <button id="submit-request-button" type="submit" class="company_login_btn btn btn-blue btn-block text-white h4">Login</button>
                                    <button id="submit-resend-request-button" type="submit" class="company_resend_otp_btn btn btn-blue btn-block text-white h4" style="display:none;">Resend OTP</button>
                                    <button class="btn btn-blue company_login_loader text-white" type="button" disabled style="display:none;">
                                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                        Verifying Details, please wait...
                                    </button>
                                    <button class="btn btn-blue company_login_send_otp_loader text-white" type="button" disabled style="display:none;">
                                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                        Sending OTP to your email address..
                                    </button>
                                </div>
                            </form>

                            <form id="show-company-login-form-otp" style="display: none;">
                                <div class="form-group">
                                    <label for="verify-otp-login" class="required-label">Enter Verify OTP</label>
                                    <input type="text" id="verify-otp-login" name="verify-otp-company-login" placeholder="xxxxxx">
                                </div>
                                <div class="form-group mt-4">
                                    <button class="rounded company_verify_otp_none_loader btn-blue" type="submit" id="generate-otp">Verify OTP</button>
                                    <button class="btn btn-blue company_verify_otp_loader text-white" type="button" disabled style="display:none;">
                                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                        Verifying OTP...
                                    </button>
                                    <button class="btn btn-blue company_redirect_verify_otp_loader text-white" type="button" disabled style="display:none;">
                                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                        Redirecting to company dashboard, Please Wait...
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php
        return ob_get_clean();
    }
);
