jQuery(document).ready(function ($) {
    $("#tds-exemption-form").validate({
      rules: {
        tds_company_name: {
          required: true,
        },
        folio_number: {
          required: true,
          minlength: 5,
        },
        pan_number: {
          required: true,
        //   pattern: /^[A-Z]{5}[0-9]{4}[A-Z]{1}$/,
        },
        mobile_number: {
          required: true,
          digits: true,
          minlength: 10,
          maxlength: 10,
        },
        email_id: {
          required: true,
          email: true,
        },
        isin: {
          required: true,
        },
      },
      messages: {
        tds_company_name: "Please select your company.",
        folio_number: {
          required: "Folio Number is required.",
          minlength: "Folio Number must be at least 5 characters.",
        },
        pan_number: {
          required: "PAN Number is required.",
        //   pattern: "Enter a valid PAN number (e.g., ABCDE1234F).",
        },
        mobile_number: {
          required: "Mobile number is required.",
          digits: "Only digits are allowed.",
          minlength: "Mobile number must be 10 digits.",
          maxlength: "Mobile number must be 10 digits.",
        },
        email_id: {
          required: "Email ID is required.",
          email: "Enter a valid email address.",
        },
        isin: "ISIN is required.",
      },
        submitHandler: function (form,e) {
            e.preventDefault();

            let receivedForm = jQuery(form).serialize();
            let params = new URLSearchParams(receivedForm);

            $(".alert").remove();
            $(".form4_submit_loader").show();
            $(".form4_non_loader").hide();

            let formData = new FormData();
            // Append files
            uploadedFiles.forEach((file) => {
              formData.append("files[]", file);
            });

            formData.append("action", "cfp_submit_tds_form");
            formData.append("tds_company_name", params.get("tds_company_name"));
            formData.append("financial_year", params.get("financial_year"));
            formData.append(
              "select_exemption_form_type",
              params.get("select_exemption_form_type")
            );
            formData.append("folio_number", params.get("folio_number"));
            formData.append("pan_number", params.get("pan_number"));
            formData.append("mobile_number", params.get("mobile_number"));
            formData.append("email_id", params.get("email_id"));
            formData.append("isin", params.get("isin"));

            jQuery.ajax({
              url: ajaxurl,
              type: "POST",
              data: formData,
              contentType: false,
              processData: false,
              success: function (response) {
                if (response.success) {
                  $("#tds-exemption-form")[0].reset();
                  jQuery("#form4-response-div").append(`
                    <div id="form-message" class="alert alert-success">
                        <i class="bi bi-check-circle me-2"></i>
                        ${response.data || "Form Successfully submitted"} 
                    </div>
                `);
                  $(window).scrollTop(0);
                } else {
                  jQuery("#form4-response-div").append(`
                    <div id="form-message" class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        ${
                          response?.data ||
                          "unable to save the data at this moment! try again later"
                        }
                    </div>
                `);
                  $(window).scrollTop(0);
                }
                $(".form4_submit_loader").hide();
                $(".form4_non_loader").show();
              },
              error: function () {
                jQuery("#form4-response-div").append(`
                    <div id="form-message" class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        An error occurred while submitting your ticket. If the issue persists, contact support.
                    </div>
                `);
                $(window).scrollTop(0);
                $(".form4_submit_loader").hide();
                $(".form4_non_loader").show();
              },
            });
      },
    });

});