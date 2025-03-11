jQuery(document).ready(async function ($) {

    function sendOTPfn(email) {
        console.log(`sendOTPfn: ${email}`);
        $.ajax({
            url: ajaxurl,
            method: "POST",
            data: {
                action: "send_otp",
                type: "email",
                recipient: email,
            },
            success: function (response) {
                // cfpShowSuccessMessage(response.data);
                $("#cfp-company-login-form").append(`
                    <div id="form-message" style="color: #218838; margin-top: 10px; margin-bottom: 10px;">
                        ${response.data}
                    </div>
                `);
                $(".company_login_send_otp_loader").hide();
                $(".company_resend_otp_btn").show();
                $("#show-company-login-form-otp").show();
            },
            error: function () {
                // cfpShowErrorMessage();
                $("#cfp-company-login-form").append(`
                    <div id="form-message" style="color: red; margin-top: 10px;">
                       Oops! The server is currently unavailable. Please try again later. If the issue persists, please contact the administrator.
                    </div>
                `);
                $(".company_login_send_otp_loader").hide();
                $(".company_login_btn").show();
            },
        });
    }

    $("#show-company-login-form-otp").on("submit", function (e) {
        e.preventDefault();
        $("#form-message").remove();
        let formData = $(this).serialize();
        let params = new URLSearchParams(formData);
        let otp = params.get("verify-otp-company-login");

        if (!otp) {
            jQuery("#show-company-login-form-otp").append(`
                        <div id="form-message" style="color: red; margin-top: 10px;">
                            Please enter the OTP.
                        </div>
                    `);
            return;
        } else if (otp.length != 6) {
            jQuery("#show-company-login-form-otp").append(`
                        <div id="form-message" style="color: red; margin-top: 10px;">
                            Please enter a valid OTP.
                        </div>
                    `);
            return;
        } else {
            jQuery(".company_verify_otp_none_loader").hide();
            jQuery(".company_verify_otp_loader").show();

            jQuery.ajax({
                url: ajaxurl,
                type: "POST",
                data: {
                    action: "company_user_login", // Action name defined in PHP
                    otp,
                },
                success: function (response) {
                    // console.log(response);
                    if (response.success) {
                        jQuery(".company_verify_otp_loader").hide();
                        jQuery(
                            ".company_redirect_verify_otp_loader"
                        ).show();

                        window.location.href =
                            response?.data?.redirect_url ||
                            "https://pragmaappscstg.wpengine.com/investor-service-request/?tab=company_information";
                    } else {
                        // cfpShowErrorMessage();
                        jQuery("#show-company-login-form-otp").append(`
                        <div id="form-message" style="color: red; margin-top: 10px;">
                            ${response.data.message}
                        </div>
                    `);
                        jQuery(".company_verify_otp_loader").hide();
                        jQuery(".company_verify_otp_none_loader").show();
                    }
                },
                error: function () {
                    // cfpShowErrorMessage();
                    jQuery("#show-company-login-form-otp").append(`
                        <div id="form-message" style="color:red; margin-top: 10px;">
                            Oops! The server is currently unavailable. Please try again in a little while.
                        </div>
                    `);
                    jQuery(".company_verify_otp_loader").hide();
                    jQuery(".company_verify_otp_none_loader").show();
                },
            });
        }
    });
     $("#submit-resend-request-button").on("click", function (e) {
       e.preventDefault(); // Prevent default form submission

       grecaptcha.ready(function () {
         grecaptcha
           .execute("6Lezg8wqAAAAAIze3YgWt7kkGfNhbklSHwRLpi0O", {
             action: "submit",
           })
           .then(function (token) {
             $("#recaptcha-token").val(token); // Set token in hidden field
             $("#cfp-company-login-form").submit(); // Now submit the form
           });
       });
     });
     $("#submit-request-button").on("click", function (e) {
       e.preventDefault(); // Prevent default form submission

       grecaptcha.ready(function () {
         grecaptcha
           .execute("6Lezg8wqAAAAAIze3YgWt7kkGfNhbklSHwRLpi0O", {
             action: "submit",
           })
           .then(function (token) {
             $("#recaptcha-token").val(token); // Set token in hidden field
             $("#cfp-company-login-form").submit(); // Now submit the form
           });
       });
     });
    $("#cfp-company-login-form").on("submit", function (e) {
        e.preventDefault();
       
        $("#form-message").remove();
        let formData = $(this).serialize();
        let params = new URLSearchParams(formData);
        let password = params.get("password");
        let username = params.get("username");
        let recaptcha = params.get("g-recaptcha-response");
        //  grecaptcha
        //    .execute("6Lezg8wqAAAAAIze3YgWt7kkGfNhbklSHwRLpi0O", {
        //      action: "login",
        //    })
        //    .then(function (token) {
        //      recaptcha =token; // Refresh token
        //      // $("#cfp-company-login-form")[0].submit(); // Now submit the form
        //    });

        if (!password || !username) {
            $("#cfp-company-login-form").append(`
                        <div id="form-message" style="color: red; margin-top: 10px;">
                            Please complete all the required fields before proceeding.
                        </div>
                    `);
            // alert('Please fill in all required fields.');
            return;
        }
        $(".company_login_loader").show();
        $(".company_login_btn").hide();
        $(".company_login_send_otp_loader").hide();
        $(".company_resend_otp_btn").hide();
        $("#show-company-login-form-otp").hide();
        $.ajax({
          url: ajaxurl, // WordPress AJAX URL provided by default
          type: "POST",
          data: {
            action: "company_login", // Action name defined in PHP
            password: password,
            username: username,
            "g-recaptcha-response": recaptcha,
            security: ajax_object.nonce,
          },
          success: function (response) {
            if (response.success) {
              $(".company_login_loader").hide();
              $(".company_login_send_otp_loader").show();
              sendOTPfn(response?.data?.email);
            } else {
              $("#cfp-company-login-form").append(`
                        <div id="form-message" style="color: red; margin-top: 10px;">
                            ${response.data}
                        </div>
                    `);
              $(".company_login_loader").hide();
              $(".company_login_btn").show();
            }
          },
          error: function () {
            $("#cfp-company-login-form").append(`
                        <div id="form-message" style="color: red; margin-top: 10px;">
                            Oops! The server is currently unavailable. Please try again later. If the issue persists, please contact the administrator.
                        </div>
                    `);
            $(".company_login_loader").hide();
            $(".company_login_btn").show();
          },
        });
    });
});