
function AJAXCallbacksFn(formData) {
    return new Promise(resolve => {
        jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                // console.log(response);
                resolve(response);
            },
            error: function () {
                return resolve({ 'success': false, 'message': ajax_error_message });
            },
        });
    });
}

jQuery(document).ready(async function ($) {
    jQuery("#form2-fetch").on("submit", async function (e) {
        e.preventDefault();
        jQuery("#form-message").remove();
        jQuery("#show_offer_result").remove();
        // console.log("Form submiitteddd");
        let formData = jQuery(this).serialize();
        let params = new URLSearchParams(formData);
        let sendFormData = new FormData();
        let company_isin = params.get("offer_company");
        let login_option = params.get("form2_radio_option");
        let option_value = params.get("form2-dynamic-value-field");
        let option_value_otp = params.get("form2_radio_option_otp");
        let verify_value = '';
        let otp_verify = params.get("otp_verify");

        if (option_value_otp === 'number') {
            verify_value = params.get("dynamic-field-form2-phone-otp");
        } else {
            verify_value = params.get("dynamic-field-form2-otp");
        }
        console.log(option_value, option_value_otp);

        if (otp_verify) {
            if (otp_verify.length != 6) {
                jQuery("#form2-fetch").append(`
                    <div id="form-message" style="color: red; margin-top: 10px;">
                       ${otp_error_message}
                    </div>
                `);
                return;
            }
            if (!company_isin || !login_option || !option_value) {
                jQuery("#form2-fetch").append(`
                        <div id="form-message" style="color: red; margin-top: 10px;">
                            ${required_fields_error_message}
                        </div>
                    `);
                return;
            }
            jQuery(".fetch_ipo_submit_loader").show();
            jQuery(".ipo_submit_btn").hide();
            sendFormData.append("action", "verify_otp");
            sendFormData.append("otp", otp_verify);
            let responseFn = await AJAXCallbacksFn(sendFormData);
            if (responseFn.success) {
                $("#form2_otp_verify").val('');
                $("#verify_form2_div").addClass("hidden");
                jQuery.ajax({
                    url: ajaxurl,
                    type: "POST",
                    data: {
                        action: "fetch_ipo_allotment_details",
                        company_isin,
                        login_option,
                        option_value,
                    },
                    success: function (response) {
                        if (response.success) {
                            console.log("Data => ", response);
                            // jQuery("#allotment_status_result").show();
                            jQuery("#form2-result").append(
                              offer_html(response.data)
                            );
                            jQuery("#form2-fetch").hide();
                            jQuery('[data-toggle="tooltip"]').tooltip();
                        } else {
                            jQuery("#form2-fetch").append(`
                        <div id="form-message" class="alert alert-danger mt-4" >
                            ${response.data}
                        </div>
                    `);
                            jQuery(".fetch_ipo_submit_loader").hide();
                            jQuery(".ipo_submit_btn").show();
                        }
                    },
                    error: function () {
                        jQuery("#form2-fetch").append(`
                        <div id="form-message" class="alert alert-danger mt-4" >
                            ${not_found_error_message}
                        </div>
                    `);
                        jQuery(".fetch_ipo_submit_loader").hide();
                        jQuery(".ipo_submit_btn").show();
                    },
                });
            } else {
                jQuery("#error-message-div").append(`
                    <div id="form-message" style="color: red; margin-top: 10px; margin-bottom: 10px;">
                       ${responseFn?.data?.message || ajax_error_message}
                    </div>`);
                jQuery(".fetch_ipo_submit_loader").hide();
                jQuery(".ipo_submit_btn").show();
                return;
            }
        } else {
            if (!verify_value || !option_value_otp) {
                jQuery("#form2-fetch").append(`
                    <div id="form-message" style="color: red; margin-top: 10px;">
                        Please enter your email address or phone number
                    </div>
                `);
                return;
            }
            jQuery(".fetch_ipo_submit_loader").show();
            jQuery(".ipo_submit_btn").hide();
            sendFormData.append("action", "send_otp");
            if (option_value_otp == 'email') {
                sendFormData.append("type", "email");
            } else {
                sendFormData.append("type", "phone");
            }
            sendFormData.append("recipient", verify_value);
            let responseFn = await AJAXCallbacksFn(sendFormData);
            if (responseFn.success) {
                $("#form2_otp_verify").removeClass("hidden");
                jQuery(".fetch_ipo_submit_loader").hide();
                jQuery(".ipo_submit_btn").show();
                jQuery("#verify_form2_div").removeClass('hidden');
                jQuery("#company-select-div").addClass("hidden");
                return;
            } else {
                jQuery("#error-message-div").append(`
                    <div id="form-message" style="color: red; margin-top: 10px; margin-bottom: 10px;">
                        ${responseFn?.data?.message || ajax_error_message}
                    </div>`);
                jQuery(".fetch_ipo_submit_loader").hide();
                jQuery(".ipo_submit_btn").show();
                return;
            }
        }
    });

    jQuery("#ipo-allotment-form").on("submit", function (e) {
        e.preventDefault();
        jQuery("#form-message").remove();
        jQuery("#show_allotment_result").remove();
        // console.log("Form submiitteddd");
        let formData = jQuery(this).serialize();
        let params = new URLSearchParams(formData);
        let company_isin = params.get("ipo-allotment-company_name");
        let login_option = params.get("ipo-allotment-radio-option");
        let option_value = params.get("ipo-allotment-dynamic-field");
        console.log(company_isin);

        console.log(company_isin, login_option, option_value);
        if (!company_isin || !login_option || !option_value) {
            jQuery("#ipo-allotment-form").append(`
                        <div id="form-message" class="mx-md-5 mx-4" style="color: red; margin-top: 10px;">
                            Fill all required fields
                        </div>
                    `);
            return;
        }
        jQuery(".fetch_ipo_submit_loader").show();
        jQuery(".ipo_submit_btn").hide();
        jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            data: {
                action: "fetch_ipo_allotment_details",
                company_isin,
                login_option,
                option_value,
            },
            success: function (response) {
                if (response.success) {
                    console.log("Data => ", response);
                    // jQuery("#allotment_status_result").show();
                    jQuery("#ipo-allotment-form").append(ipo_allotment_html(response.data));
                    jQuery('[data-toggle="tooltip"]').tooltip();
                } else {
                    jQuery("#ipo-allotment-form").append(`
                        <div id="form-message" class="mx-md-5 mx-4 alert alert-danger mt-4" >
                            ${response.data}
                        </div>
                    `);
                }
                jQuery(".fetch_ipo_submit_loader").hide();
                jQuery(".ipo_submit_btn").show();
            },
            error: function () {
                jQuery("#ipo-allotment-form").append(`
                        <div id="form-message" class="mx-md-5 mx-4 alert alert-danger mt-4" >
                            No Data Available
                        </div>
                    `);
                jQuery(".fetch_ipo_submit_loader").hide();
                jQuery(".ipo_submit_btn").show();
            },
        });
    });

    $("input[type='radio'][name='form2_radio_option_otp']").change(function () {
      let selectedValue = $(
        "input[name='form2_radio_option_otp']:checked"
      ).val();
        console.log(selectedValue);
        if (selectedValue === "number") {
            $("#dynamic-field-form2-phone-otp").removeClass("hidden");
            $("#dynamic-field-form2-email-otp").addClass("hidden");
        } else {
            $("#dynamic-field-form2-email-otp").removeClass("hidden");
            $("#dynamic-field-form2-phone-otp").addClass("hidden");
        }
    });
});