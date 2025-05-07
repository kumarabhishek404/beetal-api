var uploadedFiles = [];
var recaptchaKey = "6Lezg8wqAAAAAIze3YgWt7kkGfNhbklSHwRLpi0O";
const formList = [
  {
    title: "Form 15G",
    description:
      "Resident individual, HUF, trust, or other assessee (excluding companies or firms) under 60 years of age.",
    url: "https://beetal.in/wp-content/uploads/2025/04/form_15g.pdf",
  },
  {
    title: "Form 15H",
    description: "Resident individual aged 60 years or more (senior citizen).",
    url: "https://beetal.in/wp-content/uploads/2025/04/form_15H.pdf",
  },
  {
    title: "Form 10F",
    description:
      "To claim tax treaty benefits for income earned in India, a non-resident must provide details in Form 10F and a Tax Residency Certificate (TRC) as per Section 90(5) of the Income Tax Act, 1961.",
    url: "https://beetal.in/wp-content/uploads/2025/04/form_10F.pdf",
  },
  {
    title: "Self Declaration Form",
    description: "General self-declaration form.",
    url: "#",
  },
  {
    title: "Dec Under Rule 37BA",
    description: "Declaration under Rule 37BA.",
    url: "#",
  },
  {
    title: "FORM ISR-3",
    description: "Declaration form for opting out of nomination.",
    url: "https://beetal.in/wp-content/uploads/2025/03/FORM-ISR-3-2.doc",
  },
];

document.addEventListener("DOMContentLoaded", function () {
  const radioButtons = document.querySelectorAll(
    'input[name="form2_radio_option"]'
  );
  const radioButtonsForm3 = document.querySelectorAll(
    'input[name="ipo-allotment-radio-option"]'
  );
  const radioButtonsLoginForm = document.querySelectorAll(
    'input[name="login_radio_option"]'
  );
  const dynamicField = document.getElementById("form2-dynamic-field");
  const dynamicFieldForm3 = document.getElementById(
    "ipo-allotment-dynamic-field"
  );
  const dynamicFieldFormLogin = document.getElementById("dynamic-field-login");
  // console.log('dynamicField', dynamicField);
  radioButtons.forEach((radio) => {
    radio.addEventListener("change", function () {
      this.checked = true;
      // if (this.checked) {
      dynamicField.value = "";
      dynamicField.placeholder = this.value;
      // }
    });
  });
  radioButtonsForm3.forEach((radio) => {
    radio.addEventListener("change", function () {
      this.checked = true;
      // if (this.checked) {
      dynamicFieldForm3.value = "";
      dynamicFieldForm3.placeholder = this.value;
      // }
    });
  });
  radioButtonsLoginForm.forEach((radio) => {
    radio.addEventListener("change", function () {
      this.checked = true;
      // if (this.checked) {
      dynamicFieldFormLogin.placeholder = this.value;
      dynamicFieldFormLogin.value = "";
      // }
    });
  });

  const tabs = document.querySelectorAll(".tab");
  const contents = document.querySelectorAll(".tab-content");

  tabs.forEach((tab) => {
    tab.addEventListener("click", function () {
      tabs.forEach((t) => t.classList.remove("active"));
      contents.forEach((c) => c.classList.remove("active"));

      tab.classList.add("active");
      document
        .getElementById(tab.getAttribute("data-tab"))
        .classList.add("active");
    });
  });
  showDataOnURLChange();

  const menuToggleIcon = document.getElementById("menuToggleIcon");
  const menuCollapse = document.getElementById("menuCollapse");

  if (menuToggleIcon && menuCollapse) {
    // Add event listener for when the menu is shown
    menuCollapse.addEventListener("shown.bs.collapse", function () {
      menuToggleIcon.classList.add("rotate-icon");
    });

    // Add event listener for when the menu is hidden
    menuCollapse.addEventListener("hidden.bs.collapse", function () {
      menuToggleIcon.classList.remove("rotate-icon");
    });
  }

  let header = document.querySelector(".ast-below-header-wrap");
  let logo = document.querySelector(".ast-header-html-2");
  let originalPosition = header.offsetTop;
  let img = document.querySelector(".custom-logo-link");
  if (header) {
    window.addEventListener("scroll", function () {
      if (window.scrollY > originalPosition + 18) {
        img.querySelectorAll("img").forEach((image) => {
          image.src =
            "https://beetal.in/wp-content/uploads/2025/03/BEETAL-ORG-1.png";
          image.srcset =
            "https://beetal.in/wp-content/uploads/2025/03/BEETAL-ORG-1.png";
          // console.log(image);
        });

        header.classList.add("sticky-header");
        header.classList.add("visible");
        if (logo) {
          logo.style.display = "block";
        }
      } else {
        img.querySelectorAll("img").forEach((image) => {
          image.src =
            "https://beetal.in/wp-content/uploads/2025/03/cropped-white_text_trans.png";
          image.srcset =
            "https://beetal.in/wp-content/uploads/2025/03/cropped-white_text_trans.png";
          // console.log(image);
        });

        header.classList.remove("visible");
        header.classList.remove("sticky-header");
        if (logo) {
          logo.style.display = "none";
        }
      }
    });
  }

  if (document.getElementById("logout-user-header")) {
    document
      .getElementById("logout-user-header")
      .addEventListener("click", logoutUser);
  }
});

// window.addEventListener("beforeunload", function () {
//   showLoadingScreen();
// });

window.addEventListener("hashchange", function () {
  showDataOnURLChange();
});

function showLoadingScreen() {
  const loadingScreen = document.createElement("div");
  loadingScreen.id = "loading-screen";
  loadingScreen.classList.add("loading");
  loadingScreen.innerHTML = '<div class="loader"></div>';

  document.body.appendChild(loadingScreen);
}

// Function to hide the loading screen
function hideLoadingScreen() {
  const loadingScreen = document.getElementById("loading-screen");
  if (loadingScreen) {
    loadingScreen.remove();
  }
}

// Show the loading screen before reload

// Hide the loading screen after the page has loaded

async function showDataOnURLChange() {
  const fragment = window.location.hash.substring(1); // Get the string after #
  if (fragment) {
    if (fragment === "IPO_Allotment_Status") {
      openForm("IPO_Allotment_Status", "tab5");
    } else if (fragment === "Open_Buyback_right_issue") {
      openForm("Open_Buyback_right_issue", "tab2");
    } else if (fragment === "TDS_Exemption") {
      openForm("TDS_Exemption", "tab4");
    } else if (fragment === "Investor_Service_Request") {
       const el = document.getElementById("Investor_Service_Request");
      const display = window.getComputedStyle(el).display;
      if (display == 'none') {
        await jQuery.ajax({
          url: ajaxurl, // WordPress AJAX URL provided by default
          type: "POST",
          data: {
            action: "is_user_login", // Action name defined in PHP
          },
          beforeSend: function () {
              document.getElementById("right-section-div").classList.add("hidden");
              document.getElementById("KYC_Compliance").style.display = "none";
            document.getElementById("loading-screen-1").classList.remove("hidden");
            document.getElementById("services").style.background =
              "linear-gradient(90deg, var(--ast-global-color-4) 50%, var(--ast-global-color-5) 50%)";
          },
          success: function (response) {
            if (response.success) {
              // console.log("In if condition user register");
              document
                .getElementById("right-section-div")
                .classList.add("hidden");
              document
                .getElementById("loading-screen-1")
                .classList.add("hidden");
              // console.log("loading deactive");
            } else {
              document
                .getElementById("right-section-div")
                .classList.remove("hidden");
                document.getElementById("services").style.background =
                  "var(--ast-global-color-4)";
            }
          },
        });
      }
      openForm("Investor_Service_Request", "tab1");
    } else if (fragment === "Investor_Forms") {
      openForm("Investor_Forms", "tab6");
    } else if (fragment === "SEBI_Circulars") {
      openForm("SEBI_Circulars", "tab7");
    } else {
      openForm("KYC_Compliance", "tab3");
    }
  } else {
      openForm("KYC_Compliance", "tab3");
  }
}

function getLastSegment(url) {
    let parts = url.split("/").filter((part) => part); // Remove empty parts
  return parts.pop(); // Get the last segment
}

function getHomeAddress(url) {
    return new URL(url).origin;
}

function replaceTabParameter(newCompanyName, tabName) {
  // Get the current URL
  const currentURL = window.location.href;
  document.getElementById("tab2_company_select_tab").value = newCompanyName;
  // const removeHash = currentURL.split("#")[0];
  // let urlParams = removeHash.split("?")[0];
  
  // let lastUrl = getLastSegment(urlParams);
  // console.log(lastUrl, urlParams);
  //   if (lastUrl != "open-buyback-right-issue") {
  //     window.location.href = `${getHomeAddress(
  //       currentURL
  //     )}/open-buyback-right-issue?company=${newCompanyName}`;
  //     return;
  //   }

  // // Extract the URL parameters
  // // const currentURL_split_hash = currentURL.split("#")[0];
  // // Construct the new URL
  // const newURL = `${urlParams.toString()}?company=${newCompanyName}`;
  // // Redirect to the new URL
  //   window.history.pushState({}, "", newURL);
  changeTab(currentURL, "Open_Buyback_right_issue");
    return;
}

function openForm(formId, tabId) {
  console.log(formId, tabId);
  document
    .querySelectorAll(".form-container")
    .forEach((form) => (form.style.display = "none"));
  document.querySelectorAll(".menu-items").forEach((form) => {
    form.classList.remove("menu-active");
    form.classList.add("text-white");
  });
  document
    .querySelectorAll(".tabs")
    .forEach((form) => (form.style.display = "none"));
  if (document.getElementById(formId)) {
    document.getElementById(formId).style.display = "block";
  }
  if (document.getElementById(`${formId}${tabId}`)) {
    document.getElementById(`${formId}${tabId}`).classList.add("menu-active");
    document
      .getElementById(`${formId}${tabId}`)
      .classList.remove("text-white");
  }
  if (document.getElementById("loading-screen-1")) {
    document.getElementById("loading-screen-1").classList.add("hidden");
  }
  // if (document.getElementById(tabId)) {
  //     document.getElementById(tabId).style.display = "block";
  //     document
  //         .getElementById(`${formId}${tabId}`)
  //         .classList.add("menu-active");
  //     document
  //         .getElementById(`${formId}${tabId}`)
  //         .classList.remove("text-white");
  // } else {
  //     document
  //         .getElementById(`form5tab5`)
  //         .classList.add("menu-active");
  //     document.getElementById(`form5tab5`).classList.remove("text-white");
  // }
}

function scrollToServices(divID) {
  const target = document.getElementById(`${divID}`);
  const offset = 0; // pixels above the element
  const y = target.getBoundingClientRect().top + window.pageYOffset - offset;

  window.scrollTo({ top: y, behavior: "smooth" });
}

function changeTab(url, tab = "", isLogin = false) {
  if (tab == "KYC_Compliance") {
    openForm("KYC_Compliance", "tab3");
    document.getElementById("right-section-div").classList.remove("hidden");
    document.getElementById("services").style.background =
      "var(--ast-global-color-4)";
  } else if (tab == "IPO_Allotment_Status") {
    openForm("IPO_Allotment_Status", "tab5");
    document.getElementById("right-section-div").classList.remove("hidden");
    document.getElementById("services").style.background =
      "var(--ast-global-color-4)";
  } else if (tab == "Open_Buyback_right_issue") {
    openForm("Open_Buyback_right_issue", "tab2");
    document.getElementById("right-section-div").classList.remove("hidden");
    document.getElementById("services").style.background =
      "var(--ast-global-color-4)";
  } else if (tab == "TDS_Exemption") {
    openForm("TDS_Exemption", "tab4");
    document.getElementById("right-section-div").classList.remove("hidden");
    document.getElementById("services").style.background =
      "var(--ast-global-color-4)";
  } else if (tab == "Investor_Service_Request") {
    openForm("Investor_Service_Request", "tab1");
    // console.log("Is Login => ",isLogin);
    if (isLogin) {
      document.getElementById("right-section-div").classList.add("hidden");
      document.getElementById("services").style.background =
        "linear-gradient(90deg, var(--ast-global-color-4) 50%, var(--ast-global-color-5) 50%)";
    }
  } else if (tab == "Investor_Forms") {
    openForm("Investor_Forms", "tab6");
    document.getElementById("right-section-div").classList.remove("hidden");
    document.getElementById("services").style.background =
      "var(--ast-global-color-4)";
  } else if (tab == "SEBI_Circulars") {
    openForm("SEBI_Circulars", "tab7");
    document.getElementById("right-section-div").classList.remove("hidden");
    document.getElementById("services").style.background =
      "var(--ast-global-color-4)";
  }

  scrollToServices("services");
  if (document.getElementById("menuCollapse")) {
      document.getElementById("menuCollapse").classList.remove("show");
      document.getElementById("menuToggleIcon").classList.remove("rotate-icon");
  }
  // window.location.href = `${url}#${tab}`;
  location.hash = `${tab}`;
  const path = window.location.pathname + window.location.hash;
  history.replaceState(null, "", path);
}

function fetchLoginDataFromURL(params) {
  // console.log("Fetching login data for ", params);
  return new Promise((resolve) => {
    jQuery.ajax({
      url: ajaxurl,
      method: "POST",
      data: { ...params },
      success: function (response) {
        console.log(response);
        if (response.success) {
          resolve({ status: true });
        } else {
          resolve({ status: false, message: response?.data?.message });
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

function cfpShowSuccessMessage(message) {
  jQuery(document).ready(function ($) {
    // Create a temporary error message div
    var errorDiv = $('<div class="success-message">' + message + "</div>");
    $("body").append(errorDiv);

    // Show the error message for 1 second
    setTimeout(function () {
      errorDiv.fadeOut("fast", function () {
        errorDiv.remove();
      });
    }, 8000);
  });
}

function cfpShowErrorMessage(
  message = "Oops! The server is currently unavailable. Please try again later. If the issue persists, please contact the administrator."
) {
  jQuery(document).ready(function ($) {
    // Create a temporary error message div
    var errorDiv = $('<div class="error-message">' + message + "</div>");
    $("body").append(errorDiv);

    // Show the error message for 1 second
    setTimeout(function () {
      errorDiv.fadeOut("fast", function () {
        errorDiv.remove();
      });
    }, 8000);
  });
}

function redirectUser(url) {
  const fragment = window.location.hash.substring(1);
  if (fragment === "Investor_Service_Request") {
    window.location.href = url;
  }
}

function offer_html(data) {
  data = data[0];
  return `
      <div id="show_allotment_result" class="form-margin">
        <h3 class="mb-4">Rights Entitlement</h3>
        <div class="card border-0 shadow-none shadow-chart">
            <div class="card-body">
                <div class="d-flex justify-content-between flex-wrap">
                    <div class="d-flex h5 gap-2"><i class="bi bi-person-fill text-blue"></i>${
                      data?.name || "N/A"
                    }</div>
                </div>
                <div class="d-flex justify-content-between flex-column gap-2 mt-2">
                    <div class="d-flex flex-column flex-xxl-row">
                     <div class="d-flex col-12 col-xxl-6 mb-2 mb-xxl-0">
                            <div class="col-6 text-nowrap">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>Shares Held
                            </div>
                            <div class="col-6 text-end pe-xxl-4">
                                ${Math.trunc(data?.share_alt || 0)}
                            </div>
                        </div>
                        <div class="d-flex col-12 col-xxl-6 mb-xxl-0">
                            <div class="col-6 text-nowrap">
                                <i class="bi bi-award-fill text-${
                                  Math.trunc(data?.share_alt || 0) !=
                                  Math.trunc(data?.amount || 0)
                                    ? "warning"
                                    : "success"
                                } me-2"></i>Shares Entitled
                            </div>
                            <div class="col-6 text-end">
                                ${Math.trunc(data?.amount || 0)}
                            </div>
                        </div>
                        </div>
                    <div class="d-flex flex-column flex-xxl-row">
                        <div class="d-flex col-12 col-xxl-6 mb-2 mb-xxl-0">
                            <div class="col-6 text-nowrap">
                                <i class="bi bi-file-earmark-text-fill text-blue me-2"></i>Application No.
                            </div>
                            <div class="col-6 text-end pe-xxl-4">
                                ${data?.appl_no || "N/A"}
                            </div>
                        </div>
                        <div class="d-flex col-12 col-xxl-6">
                            <div class="col-6 text-nowrap">
                                <i class="bi bi-person-lines-fill text-blue me-2"></i>DP/Client ID/Folio
                            </div>
                            <div class="col-6 text-end">
                                ${data?.dp_cl || "N/A"}
                            </div>
                        </div>
                    </div>
                     <div class="col-12">
                            <p class="h6 my-2 pb-2 border-bottom fw-bold">Remark</p>
                            <p class="mb-1 fs-6">${data?.remark}</p>
                        </div>
                </div>
            </div>
        </div>
    </div>`;
}

function ipo_allotment_html(data) {
  data = data[0];
  console.log(data);
  return `
      <div id="show_allotment_result" class="my-3">
        <h3 class="mb-3">Allotment Status</h3>
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between flex-wrap">
                    <div class="d-flex h5 gap-2"><i class="bi bi-person-fill text-blue"></i>${
                      data?.name || "N/A"
                    }</div>
                </div>
                <div class="d-flex flex-column justify-content-between gap-2">
                   <div class="d-flex flex-column flex-xxl-row">
                     <div class="d-flex col-12 col-xxl-6 mb-2 mb-xxl-0">
                            <div class="col-6 text-nowrap">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>Shares Held
                            </div>
                            <div class="col-6 text-end">
                                ${Math.trunc(data?.share_alt || 0)}
                            </div>
                        </div>
                        <div class="d-flex col-12 col-xxl-6 mb-xxl-0">
                            <div class="col-6 text-nowrap ps-xxl-4">
                                <i class="bi bi-award-fill text-${
                                  Math.trunc(data?.share_alt || 0) !=
                                  Math.trunc(data?.share_appl || 0)
                                    ? "warning"
                                    : "success"
                                } me-2"></i>Shares Entitled
                            </div>
                            <div class="col-6 text-end">
                                ${Math.trunc(data?.share_appl || 0)}
                            </div>
                        </div>
                      </div>
                      <div class="d-flex flex-column flex-xxl-row">
                     <div class="d-flex col-12 col-xxl-6 mb-2 mb-xxl-0">
                            <div class="col-6 text-nowrap">
                                <i class="bi bi-file-earmark-text-fill text-blue me-2"></i>Application No.
                            </div>
                            <div class="col-6 text-end">
                                ${data?.appl_no || "N/A"}
                            </div>
                        </div>
                        <div class="d-flex col-12 col-xxl-6 mb-xxl-0">
                            <div class="col-6 text-nowrap ps-xxl-4">
                                 <i class="bi bi-person-vcard-fill text-blue me-2"></i>PAN
                            </div>
                            <div class="col-6 text-end">
                                ${data?.acc_pan1 || "N/A"}
                            </div>
                        </div>
                      </div>
                      <div class="d-flex flex-column flex-xxl-row">
                     <div class="d-flex col-12 col-xxl-6 mb-2 mb-xxl-0">
                            <div class="col-5 text-nowrap">
                                <i class="bi bi-person-lines-fill text-blue me-2"></i>DP/Client ID
                            </div>
                            <div class="col-7 text-end">
                                ${data?.dp_cl || "N/A"}
                            </div>
                        </div>
                      </div>
                    
                </div>
            </div>
        </div>
    </div>`;
}

function sendAjaxRequestToDisplayResources(company, $) {
  // console.log(company);
  if (!company || !$) {
    $("#download-company-forms-data").html("");
    return;
  }
  $("#company-data").remove();

  $(".loading-tab2-resources").removeClass("hidden");
  $("#show_allotment_result").html(``);
  $("#error-form2-result").html("");
  $("#form2-dynamic-field").val("");
  $.ajax({
    url: ajaxurl, // WordPress AJAX URL provided by default
    type: "POST",
    data: {
      action: "tab2_download_resources", // Action name defined in PHP
      company_name: company,
    },
    beforeSend: function () {
      $("#download-company-forms-data").html(`
        <div class="d-flex flex-column placeholder-glow gap-2">
          <span class="p-3 col-6 placeholder rounded"></span>
          <hr class="m-0" />
          <span class="p-2 col-12 placeholder rounded"></span>
          <span class="p-2 col-12 placeholder rounded"></span>
          <span class="p-2 col-2 placeholder rounded"></span>
        </div>
                 <div class="d-flex mt-3 justify-content-center w-100 card rounded loading-tab2-resources">
                    <p class="d-flex flex-wrap placeholder-glow px-4 justify-content-between py-3 m-0 gap-2">
                        <span class="placeholder rounded col-6 p-2"></span>
                        <span class="placeholder rounded col-1 text-end p-2"></span>
                        <span class="placeholder rounded col-12 p-2"></span>
                    </p>
                </div>`);
      
      $("#form2-company-name").html(`
                 <div class="d-flex justify-content-center w-100 rounded">
                    <div class="d-flex align-items-center p-3 w-100 rounded">
        <div class="placeholder-glow me-3">
        <span class="placeholder rounded p-5"></span>
        </div>
        
        <div class="flex-grow-1 placeholder-glow">
            <h5 class="fw-bold mb-1 placeholder rounded p-2 w-100"></h5>
            <p class="mb-1 placeholder rounded p-2 w-50"></p>
            <div class="d-flex justify-content-between">
                <p class="me-4 mb-0 placeholder rounded p-2 col-3"></p>
                <p class="mb-0 placeholder rounded p-2 col-3"></p>
            </div>
        </div>
    </div>
                </div>`);
    },
    success: function (response) {
      if (response.success) {
        console.log(response.data);
        $("#download-company-forms-data").html(response.data.download);
        $("#form2-company-name").html(response.data.company);
        $("#company-select-div").hide();
      }
    },
    error: function () {
      $("#download-company-forms-data").html(
        `It looks like the downloadable form isn’t available right now. Need help? Reach out to our support team!`
      );
    },
  });
}

jQuery(document).ready(async function ($) {
  jQuery("#login-form").validate({
    rules: {
      email_phone: {
        required: true,
        email: true,
      },
      folio_number: {
        required: true,
      },
      company_login: {
        required: true,
      },
    },
    messages: {
      email_phone: {
        required: "Please enter your email address",
        email: "Please enter a valid email address or phone number",
      },
      folio_number: "Please fill the folio number",
      company_login: "Select company name",
    },
  });

  jQuery("#login-form").on("submit", async function (e) {
    e.preventDefault();
    jQuery("#form-message").remove();

    let formData = jQuery(this).serialize();
    let params = new URLSearchParams(formData);
    let email_phone = params.get("dynamic-value-field");
    let radio_selected = params.get("login_radio_option");
    let company_login = params.get("company_login");
    let folio_number = params.get("folio_number_login");

    if (!company_login && !email_phone && !folio_number) {
      jQuery("#login-form").append(`
                    <div id="form-message" style="color: red; margin-top: 10px;">
                        Please fill in the following fields: Company, Email/Phone number & Folio number.
                    </div>
                `);
      return;
    }
    if (!company_login) {
      jQuery("#login-form").append(`
                    <div id="form-message" style="color: red; margin-top: 10px;">
                        Please select a company
                    </div>
                `);
      return;
    }
    if (params.has("dynamic-value-field")) {
      if (!email_phone) {
        jQuery("#login-form").append(`
                    <div id="form-message" style="color: red; margin-top: 10px;">
                        Please enter a valid email address or phone number
                    </div>
                `);
        return;
      }
      if (radio_selected === "Enter your email") {
        let parts = email_phone.split("@");
        if (parts.length !== 2) {
          jQuery("#login-form").append(`
                    <div id="form-message" style="color: red; margin-top: 10px;">
                        Please enter a valid email
                    </div>
                `);
          return false;
        }

        let localPart = parts[0];
        let domainPart = parts[1];

        if (!localPart || /\s/.test(localPart)) {
          jQuery("#login-form").append(`
                    <div id="form-message" style="color: red; margin-top: 10px;">
                        Please enter a valid email
                    </div>
                `);
          return false;
        }
        if (!domainPart || domainPart.indexOf(".") === -1) {
          jQuery("#login-form").append(`
                    <div id="form-message" style="color: red; margin-top: 10px;">
                        Please enter a valid email
                    </div>
                `);
          return false;
        }
        let domainParts = domainPart.split(".");

        for (let i = 0; i < domainParts.length; i++) {
          if (!domainParts[i]) {
            jQuery("#login-form").append(`
                    <div id="form-message" style="color: red; margin-top: 10px;">
                        Please enter a valid email
                    </div>
                `);
            return false;
          }
        }
      } else if (radio_selected === "Enter your phone number") {
        if (email_phone.length !== 10) {
          jQuery("#login-form").append(`
                    <div id="form-message" style="color: red; margin-top: 10px;">
                        Please enter a valid phone number
                    </div>
                `);
          return false;
        }
      }
    } else {
      jQuery("#login-form").append(`
                    <div id="form-message" style="color: red; margin-top: 10px;">
                        Please enter a valid email address or phone number
                    </div>
                `);
      return;
    }

    if (!folio_number) {
      // alert('Please fill in all required fields.');
      jQuery("#login-form").append(`
                    <div id="form-message" style="color: red; margin-top: 10px;">
                        Please enter a valid folio number
                    </div>
                `);
      return;
    }
    // console.log("processing...");

    jQuery(".login_loader").show();
    jQuery(".login_none_loader").hide();
    jQuery(".login_resend_loader").hide();
    jQuery(".sending_otp_login_loader").hide();
    // console.log(ajaxurl);
    const data = {
      action: "verify_folio_info",
      folio_number,
      company_login,
      email: radio_selected === "Enter your email" ? email_phone : null,
      phone: radio_selected === "Enter your email" ? null : email_phone,
    };
    await fetchLoginDataFromURL(data).then(async (fetchResult) => {
      if (fetchResult?.status) {
        //send OTP notification
        jQuery(".login_loader").hide();
        jQuery(".login_none_loader").hide();
        jQuery(".login_resend_loader").hide();
        jQuery(".sending_otp_login_loader").show();
        jQuery.ajax({
          url: ajaxurl,
          method: "POST",
          data: {
            action: "send_otp",
            type: radio_selected === "Enter your email" ? "email" : "phone",
            recipient: email_phone,
          },
          success: function (response) {
            // cfpShowSuccessMessage(response.data);
            jQuery("#login-form").append(`
                    <div id="form-message" style="color: #218838; margin-top: 10px; margin-bottom: 10px;">
                        ${response.data}
                    </div>
                `);
            jQuery("#show-login-form-otp").show();
            jQuery(".login_loader").hide();
            jQuery(".login_none_loader").hide();
            jQuery(".login_resend_loader").show();
            jQuery(".sending_otp_login_loader").hide();
          },
          error: function () {
            // cfpShowErrorMessage();
            jQuery("#login-form").append(`
                    <div id="form-message" style="color: red; margin-top: 10px;">
                       Oops! The server is currently unavailable. Please try again later. If the issue persists, please contact the administrator.
                    </div>
                `);
            jQuery("#show-login-form-otp").hide();
            jQuery(".login_loader").hide();
            jQuery(".login_none_loader").show();
            jQuery(".login_resend_loader").hide();
            jQuery(".sending_otp_login_loader").hide();
          },
        });
      } else {
        // cfpShowErrorMessage(fetchResult?.message);
        jQuery("#login-form").append(`
                    <div id="form-message" style="color: red; margin-top: 10px;">
                        ${
                          fetchResult?.message ||
                          "Oops! The server is currently unavailable. Please try again later. If the issue persists, please contact the administrator."
                        }
                    </div>
                `);
        jQuery(".login_loader").hide();
        jQuery(".login_none_loader").show();
        jQuery(".login_resend_loader").hide();
        jQuery(".sending_otp_login_loader").hide();
      }
    });
  });

  jQuery("#show-login-form-otp").validate({
    rules: {
      verify_otp: {
        required: true,
      },
    },
    messages: {
      verify_otp: "Please fill the OTP",
    },
  });

  jQuery("#show-login-form-otp").on("submit", async function (e) {
    e.preventDefault();
    jQuery("#form-message").remove();

    let formData = jQuery(this).serialize();
    let params = new URLSearchParams(formData);
    let otp = params.get("verify-otp-login");
    let email_phone = jQuery("#dynamic-field-login").val();
    let radio_selected = jQuery(
      "input[name='login_radio_option']:checked"
    ).val();
    let company_login = jQuery("#company_login").val();
    let folio_number = jQuery("#folio_number_login").val();
    // console.log({ email_phone, radio_selected, company_login, folio_number });

    if (!otp) {
      jQuery("#show-login-form-otp").append(`
                        <div id="form-message" style="color: red; margin-top: 10px;">
                            Please enter the OTP.
                        </div>
                    `);
      return;
    } else if (otp.length != 6) {
      jQuery("#show-login-form-otp").append(`
                        <div id="form-message" style="color: red; margin-top: 10px;">
                            Please enter a valid OTP.
                        </div>
                    `);
      return;
    } else {
      jQuery(".verify_otp_none_loader").hide();
      jQuery(".redirect_verify_otp_loader").hide();
      jQuery(".verify_otp_loader").show();

      jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
          action: "custom_user_login", // Action name defined in PHP
          email: radio_selected === "Enter your email" ? email_phone : null,
          phone: radio_selected === "Enter your email" ? null : email_phone,
          company_login: company_login,
          folio_number: folio_number,
          otp,
        },
        success: function (response) {
          // console.log(response);
          if (response.success) {
            jQuery(".redirect_verify_otp_loader").show();
            jQuery(".verify_otp_loader").hide();
            // cfpShowSuccessMessage(response.data.message);
            jQuery("#show-login-form-otp").append(`
                        <div id="form-message" style="color: #218838; margin-top: 10px;">
                            ${response.data.message}
                        </div>
                    `);
            window.location.href =
              response?.data?.redirect_url ||
              "https://pragmaappscstg.wpengine.com/client-services";
            // new Promise(resolve => {
            //     setTimeout(() => {
            //         resolve(
            //             window.location.href = response?.data?.redirect_url || "https://pragmaappscstg.wpengine.com/client-services"
            //             //response.data.redirect_url
            //         );
            //     }, 1000);
            // });
          } else {
            // cfpShowErrorMessage();
            jQuery("#show-login-form-otp").append(`
                        <div id="form-message" style="color: red; margin-top: 10px;">
                            ${response.data.message}
                        </div>
                    `);
            jQuery(".login_loader").hide();
            jQuery(".login_none_loader").hide();
            jQuery(".login_resend_loader").show();
            jQuery(".sending_otp_login_loader").hide();
            jQuery(".verify_otp_none_loader").show();
            jQuery(".verify_otp_loader").hide();
          }
        },
        error: function () {
          // cfpShowErrorMessage();
          jQuery("#show-login-form-otp").append(`
                        <div id="form-message" style="color:red; margin-top: 10px;">
                            Oops! The server is currently unavailable. Please try again in a little while.
                        </div>
                    `);
          jQuery(".login_none_loader").hide();
          jQuery(".login_loader").hide();
          jQuery(".login_resend_loader").hide();
          jQuery(".sending_otp_login_loader").show();
          jQuery(".verify_otp_none_loader").show();
          jQuery(".verify_otp_loader").hide();
          jQuery("#show-login-form-otp")
            .text("An error occurred while processing your request.")
            .css("color", "red");
        },
      });
    }
  });

  jQuery("#signup-form").on("submit", function (e) {
    e.preventDefault();
    // console.log("Form submiitteddd");
    let formData = jQuery(this).serialize();
    let params = new URLSearchParams(formData);
    let phone = params.get("signup_phone");
    let email = params.get("signup_email");
    let pan = params.get("pan");
    let password = params.get("signup_password");
    let username = params.get("signup_username");

    if (params.has("signup_email")) {
      if (!email) {
        return;
      }
      let parts = email.split("@");
      if (parts.length !== 2) {
        return false;
      }

      let localPart = parts[0];
      let domainPart = parts[1];

      if (!localPart || /\s/.test(localPart)) {
        return false;
      }
      if (!domainPart || domainPart.indexOf(".") === -1) {
        return false;
      }
      let domainParts = domainPart.split(".");

      for (let i = 0; i < domainParts.length; i++) {
        if (!domainParts[i]) {
          return false;
        }
      }
    }
    if (params.has("signup_phone")) {
      if (!phone || phone.length != 10) {
        return false;
      }
    }

    if (!password || !username || !phone || !pan) {
      // alert('Please fill in all required fields.');
      return;
    }
    jQuery(".signup_loader").show();
    jQuery(".signup_none_loader").hide();
    jQuery.ajax({
      url: ajaxurl, // WordPress AJAX URL provided by default
      type: "POST",
      data: {
        action: "register_new_user", // Action name defined in PHP
        email: email,
        password: password,
        phone: phone,
        pan: pan,
        username: username,
      },
      success: function (response) {
        if (response.success) {
          // console.log("In if condition user register");
          jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            data: {
              action: "custom_user_login", // Action name defined in PHP
              email: email,
              password: password,
            },
            success: function (response) {
              console.log(response);
              if (response.success) {
                // jQuery('.signup_loader').hide()
                jQuery(".signup_loader").hide();
                jQuery(".signup_none_loader").show();
                window.location.href =
                  "https://pragmaappscstg.wpengine.com/client-services"; // Replace with your desired page
              } else {
                jQuery("#signup-form").append(`
                                    <div id="form-message" style="color: red; margin-top: 10px;">
                                        ${response.data.message}
                                    </div>
                                `);
                jQuery(".signup_loader").hide();
                jQuery(".signup_none_loader").show();
              }
            },
            error: function () {
              jQuery(".signup_loader").hide();
              jQuery(".signup_none_loader").show();
              // jQuery('#login-message').text('An error occurred while processing your request.').css('color', 'red');
            },
          });
        } else {
          jQuery("#signup-form").append(`
                        <div id="form-message" style="color: red; margin-top: 10px;">
                            ${response.data.message}
                        </div>
                    `);
          jQuery(".signup_loader").hide();
          jQuery(".signup_none_loader").show();
        }
      },
      error: function () {
        jQuery(".signup_loader").hide();
        jQuery(".signup_none_loader").show();
        // jQuery('#login-message').text('An error occurred while processing your request.').css('color', 'red');
      },
    });
  });

  $("#tab2_company_select_tab").on("change", function () {
    // Get the selected tab value
    var selectedTab = $(this).val();
    sendAjaxRequestToDisplayResources(selectedTab, $);
    replaceTabParameter(selectedTab, "Open_Buyback_right_issue");
  });

  $(".change-company-div").on("click", function () {
    // Get the selected tab value
    var selectedTab = $(this).data("company");
    console.log(selectedTab);
    if (selectedTab) {
      sendAjaxRequestToDisplayResources(selectedTab, $);
      replaceTabParameter(selectedTab, "Open_Buyback_right_issue");
      return;
    }
    return;
  });

  $("#select_exemption_form_type").on("change", function (e) {
    let extractedList = formList.find((ele) => ele.title == e.target.value);
    console.log(extractedList);
    if (extractedList) {
      $("#display-download-form").html(`
                <a class="text-decoration-none text-blue fw-medium" target="_blank" href="${extractedList.url}" alt="${extractedList.title}" download >
                    Download ${extractedList.title}  <i class="fa-solid fa-download ms-1"></i>
                </a>
            `);
    } else {
      $("#display-download-form").html(`
            `);
    }
  });

  if ($('body').hasClass('page-template-show-custom-support')) { // Replace 42 with your actual page ID
    window.addEventListener("beforeunload", function () {
      showLoadingScreen();
    });
  }

  // $("#copy_of_form_10f_submitted_at").on("change", function (e) {
  //     $(".download-files-list").remove();
  //     let files = e.target.files;
  //     let outputHTML = `<div class="d-flex align-items-center gap-3 flex-wrap download-files-list">
  //                         <div class="flex-nowrap d-none d-md-flex"> Uploaded files: </div>
  //                         <div class="d-md-none d-flex col-12 flex-nowrap"> Uploaded files: </div>`;

  //     // Append selected files to the list
  //     $.each(files, function (index, file) {
  //         uploadedFiles.push(file);
  //     });
  //     outputHTML += `<div class="d-flex flex-row flex-wrap">`;
  //     uploadedFiles.forEach(function (file, fileId) {
  //         outputHTML += `
  //             <span class="badge badge-primary m-1 p-2 border-blue file-badge bg-white text-blue d-flex justify-content-between align-items-center text-wrap" style="font-size:12px;" data-id="${fileId}">
  //                 ${file.name}
  //                 <i class="bi bi-x-circle text-danger remove-upload-file ms-2 fs-6" data-id="${fileId}"></i>
  //             </span>
  //         `;
  //     });

  //     outputHTML += `</div>`;
  //     outputHTML += `</div>`;
  //     $(".show-uploaded-form2-list").append(outputHTML);

  //     // Clear input to allow selecting the same file again
  //     $("#copy_of_form_10f_submitted_at").val("");
  // });

  $(document).on("click", ".remove-upload-file", function () {
    if (uploadedFiles.length == 1) {
      uploadedFiles = [];
      $(".download-files-list").remove();
    }
    let fileId = $(this).data("id");
    uploadedFiles = uploadedFiles.filter((_, index) => index !== fileId);
    $(this).closest(".file-badge").remove();
    console.log("Uploading files...", uploadedFiles);
  });

  $("#clear-form4-tds").on("click", () => {
    $("#tds-exemption-form")[0].reset();
    $("html, body").animate({ scrollTop: 0 }, "slow");
  });
});
