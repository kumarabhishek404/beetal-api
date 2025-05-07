function fetchRequestAJAX(params) {
    // console.log("Fetching login data for ", params);
    return new Promise((resolve) => {
        jQuery.ajax({
            url: ajaxurl,
            method: "POST",
            data: params,
            contentType: false,
            processData: false,
            success: function (response) {
                // console.log(response);
                if (response.success) {
                    resolve({ status: true, data: response.data });
                } else {
                    resolve({ status: false, data: response?.data });
                }
            },
            error: function () {
                resolve({
                    status: false,
                    message: "Something went wrong, try again later...",
                });
            },
        });
    });
}
var verify_phone = 0;
var verify_email = "";

jQuery(document).ready(async function ($) {
    hideLoadingScreen();
    $("#sign-up-form5").submit(async function (e) {
        e.preventDefault();
        $("#form-message").remove();
        let receivedFormData = $(this).serialize();
        let params = new URLSearchParams(receivedFormData);
        let formData = new FormData();
        let otp_verify = params.get('otp_verify');
        let phone = params.get("phone") || $('#phone').val();
        let email = params.get("email") || $("#email").val();
        let pan = params.get("pan");

        if ((otp_verify && otp_verify.length != 6)) {
            jQuery("#error-message-div").html(`
                    <div id="form-message" style="color: red; margin-top: 10px; margin-bottom: 10px;">
                        ${otp_error_message}
                    </div>
                `);
            return;
        }
        if (phone.length !== 10) {
            jQuery("#error-message-div").html(`
                    <div id="form-message" style="color: red; margin-top: 10px; margin-bottom: 10px;">
                        ${phone_error_message}
                    </div>
                `);
            return;
        }

        // var emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

        // console.log(emailRegex.test(email));

        if ($("#phone").prop("disabled") && !email) {
            jQuery("#error-message-div").html(`
                    <div id="form-message" style="color: red; margin-top: 10px; margin-bottom: 10px;">
                        ${email_error_message}
                    </div>
                `);
            return;
        }
        if (
            $("#phone").prop("disabled") &&
            $("#email").prop("disabled") &&
            (!pan || pan.length != 10)
        ) {
            jQuery("#error-message-div").html(`
                    <div id="form-message" style="color: red; margin-top: 10px; margin-bottom: 10px;">
                        ${pan_error_message}
                    </div>
                `);
            return;
        }

        // console.log(pan, email, phone);

        $(".loader_btn").show();
        $(".non_loader_btn").hide();
        if (pan && phone && email) {
            let urlParams = new URLSearchParams(window.location.search);
            let request_type = urlParams.get("request_type") ? urlParams.get("request_type").replace("/", "") : '';

            await grecaptcha.ready(async function () {
                await grecaptcha
                    .execute(recaptchaKey, {
                        action: "submit",
                    })
                    .then(async function (recaptcha) {
                        formData.append("action", "verify_pan_info");
                        formData.append("pan", pan);
                        formData.append("request_type", request_type);
                        formData.append("phone", phone);
                        formData.append("email", email);
                        formData.append("recaptcha", recaptcha);
                        let response = await fetchRequestAJAX(formData);
                        if (response.status) {
                            if (response.data?.redirect_url) {
                                jQuery("#error-message-div").html(`
                                    <div id="form-message" class="alert alert-success">
                                        <i class="bi bi-check-circle text-success me-1"></i>
                                        ${response.data?.message}
                                    </div>
                                `);
                                        window.location.href = response.data?.redirect_url;
                                            } else {
                                                jQuery("#error-message-div").html(`
                                    <div id="form-message" class="alert alert-danger">
                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                        ${response.data?.message}
                                    </div>
                                `);
                            }
                             $(".loader_btn").hide();
                             $(".non_loader_btn").show();
                             return;
                        } else {
                            jQuery("#error-message-div").html(`
                    <div id="form-message" class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        ${response.data?.message || response?.data}
                    </div>
                `);
                            // hideLoadingScreen();
                            $(".loader_btn").hide();
                            $(".non_loader_btn").show();
                            // $("#verify-otp").removeClass("hidden");
                            // $("#verify-otp").show();
                            return;
                        }
                    });
            });
            return;
        } else if (phone && email) {
            formData.append("recipient", email);
            formData.append("type", "email");
        } else if (phone && phone.length == 10) {
            formData.append("recipient", phone);
            formData.append("type", "phone");
        } else {
            jQuery("#error-message-div").html(`
                    <div id="form-message" style="color: red; margin-top: 10px; margin-bottom: 10px;">
                        Required field missing. Please complete all fields.
                    </div>
                `);
            $(".loader_btn").hide();
            $("#generate-otp").show();
            return;
        }
        // console.log(verify_phone, verify_email);
        // showLoadingScreen();
        if (otp_verify) {
            formData.append("action", "verify_otp");
            formData.append("otp", otp_verify);
            // formData.append("recaptcha", recaptcha);
            jQuery.ajax({
                url: ajaxurl,
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    $("#otp_verify").val("");
                    if (response.success) {
                        if (formData.get("type") == "phone") {
                            $("#phone").val(verify_phone);
                            $("#phone").prop("disabled", true);
                            $("#email_div").removeClass("hidden");
                            $("#phone-verified").removeClass("hidden");
                            $("#email").attr("required", "required");
                            $("#loading-text").text("Sending OTP...");
                        } else if (formData.get("type") == "email") {
                            $("#email").val(verify_email);
                            $("#email").prop("disabled", true);
                            $("#pan_div").removeClass("hidden");
                            $("#email-verified").removeClass("hidden");
                            $("#signup_pan").attr("required", "required");
                            $("#generate-otp").text("Verify PAN");
                        }
                        $("#otp_verify").removeAttr("required");
                        $("#resend_div_message").addClass("hidden");
                        $("#verify_div").addClass("hidden");
                        $("#generate-otp").removeClass("hidden");
                        $("#verify-otp").addClass("hidden");
                        $("#generate-otp").show();
                    } else {
                        jQuery("#error-message-div").html(`
                    <div id="form-message" style="color: red; margin-top: 10px; margin-bottom: 10px;">
                        ${response.data}
                    </div>
                `);
                        $("#verify-otp").removeClass("hidden");
                        $("#verify-otp").show();
                    }
                    $(".loader_btn").hide();

                },
                error: function () {
                    jQuery("#error-message-div").html(`
                    <div id="form-message" style="color: red; margin-top: 10px; margin-bottom: 10px;">
                        Oops! The server is currently unavailable. Please try again later. If the issue persists, please contact the administrator.
                    </div>`);
                    $(".loader_btn").hide();
                    $("#verify-otp").removeClass("hidden");
                    $("#verify-otp").show();
                },
            });
        } else {
            grecaptcha.ready(function () {
                grecaptcha
                    .execute(recaptchaKey, {
                        action: "submit",
                    })
                    .then(function (recaptcha) {
                        formData.append("action", "send_otp");
                        formData.append("recaptcha", recaptcha);
                        formData.append("recaptcha_verification", true);
                        formData.append("login_by", "investor service");
                        jQuery.ajax({
                            url: ajaxurl,
                            type: "POST",
                            data: formData,
                            contentType: false,
                            processData: false,
                            beforeSend: function () {
                                $("#loading-text").text("Sending OTP...");
                            },
                            success: function (response) {
                                if (response.success) {
                                    $("#resend_div_message").removeClass("hidden");
                                    $("#generate-otp").addClass("hidden");
                                    $("#verify-otp").show();
                                    $("#verify-otp").removeClass("hidden");
                                    $("#verify_div").removeClass("hidden");
                                    $("#verify_div").show();
                                    $("#otp_verify").attr("required", "required");
                                    $("#loading-text").text("Verifying...");
                                    verify_phone = phone;
                                    verify_email = email;
                                    jQuery("#error-message-div").html(`
                    <div id="form-message" style="color: #218838; margin-top: 10px; margin-bottom: 10px;">
                        ${response.data}
                    </div>
                `);
                                } else {
                                    jQuery("#error-message-div").html(`
                    <div id="form-message" style="color: red; margin-top: 10px; margin-bottom: 10px;">
                        ${response.data}
                    </div>
                `);
                                }
                                $(".loader_btn").hide();
                                $("#generate-otp").show();
                            },
                            error: function () {
                                jQuery("#error-message-div").html(`
                    <div id="form-message" style="color: red; margin-top: 10px; margin-bottom: 10px;">
                        Oops! The server is currently unavailable. Please try again later. If the issue persists, please contact the administrator.
                    </div>`);
                                $(".loader_btn").hide();
                                $("#generate-otp").show();
                                // hideLoadingScreen();
                            },
                        });
                    });
            });
        }
    });

    $("#login-without-pan-form").submit(async function (e) {
        e.preventDefault();
        $("#form-message").remove();
        let receivedFormData = $(this).serialize();
        let params = new URLSearchParams(receivedFormData);
        if (!params.get("company_login") || !params.get("folio_number_login")) {
            $("#response-message").append(`
                    <div id="form-message" style="color: red;">
                        Please fill all the required details.
                    </div>
                `);
            return;
        }
        showLoadingScreen();
        let formData = new FormData();
        formData.append("action", 'verify_folio_info');
        formData.append("company_login", "company_login");
        formData.append("email", "email");
        formData.append("folio_number", "folio_number_login");
        let response = await fetchRequestAJAX(formData);
        if (response.status) {
            // window.location.href = response.data;
        } else {
            // window.location.href = params.get("login_without_pan_redirect_url");
            // return;
        }
    });

    $("#resend-otp-form").click(function (e) {
        e.preventDefault(); // Prevent default form submission
        $("#sign-up-form5").submit();
    });
});
