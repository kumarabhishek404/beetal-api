var downloadDate = [];
var report_dates = report_dates;
var uploadedFiles = [];
var preselectedValues = preselectedValues || '';
var preselectedSubServiceValues = preselectedSubServiceValues || '';
var reportId = '';
var reportTime = '';

document.addEventListener("DOMContentLoaded", function () {

  if (
    document.getElementById("distributionChart") &&
    Array.isArray(distribution_details.descriptions) &&
    distribution_details.descriptions.length > 0
  ) {
    if (Array.isArray(distribution_details?.descriptions)) {
      const colorCode = ["#6A92FE", "#3B48E3", "#9067FA"];
      const distributionCtx = document
        .getElementById("distributionChart")
        .getContext("2d");

      new Chart(distributionCtx, {
        type: "doughnut",
        data: {
          labels: distribution_details.descriptions,
          datasets: [
            {
              data: distribution_details?.equities,
              backgroundColor: colorCode,
            },
          ],
        },
        options: {
          responsive: true,
          plugins: {
            legend: { display: false },
          },
        },
      });

      if (document.getElementById("distribution-chart-label")) {
        let descriptionHTML = distribution_details.descriptions
          .map(
            (ele, idx) =>
              `<div class="d-flex">
          <div class="col-6 d-flex align-items-center"><span class="rounded-circle me-2" style="background: ${colorCode[idx]}; min-height: 10px; min-width: 10px;"></span>${ele}</div>
          <div class="col-6 text-end">${distribution_details.equities[idx]}%</div>
          </div>
          `
          )
          .join("");
        document.getElementById("distribution-chart-label").innerHTML =
          descriptionHTML;
      }
    }
  }

  if (
    document.getElementById("shareholdingChart") &&
    Array.isArray(shareholding_details.descriptions) &&
    shareholding_details.descriptions.length > 0
  ) {
    if (Array.isArray(shareholding_details?.descriptions)) {
      let shareholdingDescription = shareholding_details?.descriptions;
      const colorCode = [
        "#6A92FE",
        "#9067FA",
        "#93fe7f",
        "#6A92FE",
        "#9067FA",
        "#93fe7f",
        "#9067FA",
        "#ebeef1",
        "#3B48E3",
      ];
      let sortedArrayDescription = shareholdingDescription;
      if (shareholdingDescription.length > 6) {
        sortedArrayDescription = shareholdingDescription.slice(0, 6);
      }

      console.log(sortedArrayDescription);
      const shareholdingCtx = document
        .getElementById("shareholdingChart")
        .getContext("2d");

      new Chart(shareholdingCtx, {
        type: "doughnut",
        data: {
          labels: sortedArrayDescription,
          datasets: [
            {
              data: shareholding_details?.equities,
              backgroundColor: colorCode,
            },
          ],
        },
        options: {
          responsive: true,
          plugins: {
            legend: { display: false },
          },
        },
      });

      if (document.getElementById("shareholding-chart-label")) {
        let shareholdingHTML = sortedArrayDescription
          .map(
            (ele, idx) =>
              `<div class="d-flex gap-3 align-items-center">
        <div class="col-7 d-flex align-items-center" style="line-height: 1.2rem !important;"><span class="rounded-circle me-2" style="background: ${colorCode[idx]}; min-height: 10px; min-width: 10px;"></span>${ele}</div>
        <div class="col-5 text-end">${shareholding_details.equities[idx]}%</div>
        </div>
        `
          )
          .join("");
        document.getElementById("shareholding-chart-label").innerHTML =
          shareholdingHTML;
      }
    }
  }
  if (document.getElementById("view-reply")) {
    document
      .getElementById("view-reply")
      .addEventListener("click", function () {
        document.getElementById("reply-form").style.display = "block";
      });
    document
      .getElementById("cancel-reply")
      .addEventListener("click", function () {
        document.getElementById("reply-form").style.display = "none";
      });
  }

  if (document.getElementById("logout-user")) {
    document
      .getElementById("logout-user")
      .addEventListener("click", logoutUser);
  }

  const menuToggleIcon = document.getElementById("menuToggleIcon");
  const ticketMenu = document.getElementById("ticketMenu");
  if (menuToggleIcon && ticketMenu) {
    ticketMenu.addEventListener("shown.bs.collapse", function () {
      menuToggleIcon.classList.add("rotate-icon");
    });

    ticketMenu.addEventListener("hidden.bs.collapse", function () {
      menuToggleIcon.classList.remove("rotate-icon");
    });
  }
});

function logoutUser() {
  showLoadingScreen();
  const urlParams = new URLSearchParams(window.location.search);
  const tab = urlParams.get("tab");
  let redirect_to = (tab == "company_information" || tab == "search_folio") ? 'company': 'investor';
  console.log("logged out", tab);
  jQuery.ajax({
    url: ajaxurl,
    type: "POST",
    data: {
      action: "my_custom_logout_action",
      redirect_to,
    },
    success: function (response) {
      window.location.href =
        response?.data?.redirect_url ||
        "https://pragmaappscstg.wpengine.com/investor";
      // window.location.reload();
    },
    error: function (response) {
      window.location.href =
        response?.data?.redirect_url ||
        "https://pragmaappscstg.wpengine.com/investor";
      // window.location.reload();
    },
  });
  return;
}

function changeFolioTab(tabId) {
  document.querySelectorAll(".folio-tabs").forEach((form) => {
    form.classList.remove("folio-active");
  });
  document.querySelectorAll(".company-tabs").forEach((form) => {
    form.classList.remove("folio-active");
  });
  document.querySelectorAll(".show-folio-info").forEach((form) => {
    form.style.display = "none";
  });

  if (tabId == "Download Reports" && report_dates && report_dates.length == 0) {
    fetchDownloadReports();
  }
  document.getElementById(tabId).classList.add("folio-active");
  document.getElementById(tabId).classList.remove("border-bottom");
  document.getElementById(`show-${tabId}`).style.display = "block";
}

function convertDate(dateString) {
  const dateObj = new Date(dateString);

  const year = dateObj.getFullYear();
  const month = String(dateObj.getMonth() + 1).padStart(2, "0");
  const day = String(dateObj.getDate()).padStart(2, "0");

  // Format the date in yyyy-mm-dd
  const formattedDate = `${year}-${month}-${day}`;

  return formattedDate;
}

function showToastSuccessMessage(
  message = "Oops! The server is currently unavailable. Please try again later. If the issue persists, please contact the administrator."
) {
  jQuery(document).ready(function ($) {
    // Create a temporary error message div with a close button
    $(".error-message").remove();
    $(".success-message").remove();
    var errorDiv = $(
      '<div class="success-message">' +
      message +
      '<button class="close-btn">✖</button></div>'
    );

    $("body").append(errorDiv);

    $(".close-btn").css({
      background: "none",
      border: "none",
      color: "#fff",
      "font-weight": "bold",
      cursor: "pointer",
      padding: "0",
      "font-size": "16px",
      "margin-left": "10px",
    });

    // Close the error message when the close button is clicked
    errorDiv.on("click", ".close-btn", function () {
      errorDiv.fadeOut("fast", function () {
        errorDiv.remove();
      });
    });
  });
}
function showToastErrorMessage(
  message = "Oops! The server is currently unavailable. Please try again later. If the issue persists, please contact the administrator."
) {
  jQuery(document).ready(function ($) {
    // Create a temporary error message div with a close button
    $(".error-message").remove();
    $(".success-message").remove();
    var errorDiv = $(
      '<div class="error-message">' +
      message +
      '<button class="close-btn">✖</button></div>'
    );

    $("body").append(errorDiv);

    $(".close-btn").css({
      background: "none",
      border: "none",
      color: "#fff",
      "font-weight": "bold",
      cursor: "pointer",
      padding: "0",
      "font-size": "16px",
      "margin-left": "10px",
    });

    // Close the error message when the close button is clicked
    errorDiv.on("click", ".close-btn", function () {
      errorDiv.fadeOut("fast", function () {
        errorDiv.remove();
      });
    });
  });
}

function showErrorMessage(
  message = "Oops! The server is currently unavailable. Please try again later. If the issue persists, please contact the administrator."
) {
  jQuery(document).ready(function ($) {
    // Create a temporary error message div with a close button
    $("#search-results").remove();
    var errorDiv = $('<div class="alert alert-danger">' + message + "</div>");
    if ($("#show-alert").length) {
      $("#show-alert").append(errorDiv);
    } else {
      $("#show-alert-2").append(errorDiv);
    }
  });
}

function downloadReport(type) {
  showLoadingScreen();
  let selectedDate = convertDate(document.getElementById(type).value);

  if (downloadDate.length > 0 && !selectedDate) {
    let findId = downloadDate.findIndex(ele => ele.id === type);
    if (findId !== -1) {
      selectedDate = downloadDate[findId].date;
    }
  }
  if (!selectedDate) {
    showToastErrorMessage("Oops! Something went wrong. Please try again.");
    hideLoadingScreen();
    return;
  }
  // console.log(selectedDate, type);

  let download_array = report_dates.find((ele) => ele.report_type == type);
  if (!download_array) {
    showToastErrorMessage("Oops! Something went wrong. Please try again.");
    hideLoadingScreen();
    return;
  }
  let downloadId = download_array.report_dates.findIndex(
    (ele) => ele == selectedDate
  );
  if (downloadId < 0) {
    showToastErrorMessage("Oops! Something went wrong. Please try again.");
    hideLoadingScreen();
    return;
  }
  // console.log(selectedDate, date, selectedDate == date);
  jQuery.ajax({
    url: ajaxurl,
    type: "POST",
    data: {
      action: "download_report",
      id: download_array.report_ids[downloadId],
    },
    success: function (response) {
      // console.log(response);
      hideLoadingScreen()
      // console.log(response);
      if (response.success) {
        window.open(response.data, "_blank", "noopener, noreferrer");
      } else {
        showToastErrorMessage(response.data);
      }
    },
    error: function (error) {
      //   console.log(JSON.parse(error.responseText));
      showToastErrorMessage("Oops! Something went wrong. Please try again.");
      hideLoadingScreen();
    },
  });
}

function removeQueryParam(param, data) {
  let url = new URL(window.location.href);
  url.searchParams.delete(param);
  url.searchParams.delete("page_no");
  url.searchParams.delete("folio_no");
  url.searchParams.delete("pan");
  url.searchParams.delete("search_name");
  if (data.folio_no) {
    url.searchParams.set("folio_no", data.folio_no);
  }
  if (data.pan_no) {
    url.searchParams.set("pan", data.pan_no);
  }
  if (data.cmp_name) {
    url.searchParams.set("search_name", data.cmp_name);
  }
  window.location.href = url.toString();
}

function getDownloadDataHTML(data) {
  let output = '';
  output += data.map(
    (report, index) =>
      `<div class="card mb-3">
                                <div class="card-header d-flex flex-wrap flex-md-nowrap justify-content-between align-items-center bg-white">
                                <div class="d-flex align-items-center">
                                    <div class="d-flex align-items-md-center align-items-start" data-bs-toggle="collapse" data-bs-target="#collapse-${index}" aria-expanded="false">
                                        <i class="bi bi-filetype-xls text-dark fs-4 pt-2 pt-md-0"></i>
                                        <span class="p-2 d-flex flex-column" style="text-wrap: nowrap;">
                                           ${report["report_type"]}
                                            <small class="font-weight-light text-muted text-break text-wrap" style="font-weight: 500;">
                                                ${report["description"]}</small>
                                        </span>
                                    </div>
                                </div>
                                <div class="d-flex gap-md-4 align-items-center justify-content-between width-md-full mb-2 mb-md-0">
                                    <div class="d-flex align-items-center ms-4 gap-4">
                                        <div class="input-group bg-white" style="width: 160px;">
                                            <input type="text" id="${report["report_type"]}" class="datepicker-input bg-white form-control border-end-0" placeholder="Select a date">
                                            <span class="input-group-text bg-white">
                                                <i class="bi bi-calendar4-week"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <button id="show-${report["report_type"]}" onclick="downloadReport('${report["report_type"]}')" class="btn btn-sm me-2 p-1" disabled title="Download" style="background: #212d45; color: white;">
                                            <i class="bi bi-download fs-5"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>`
  );
  output = output.replaceAll("\n", "");
  return output.replaceAll(",", "");
}

function timestampToDate(timestamp) {
  // JavaScript timestamps are in milliseconds, while Unix timestamps are in seconds.
  // If your timestamp is in seconds, multiply it by 1000.
  const date = new Date(timestamp);

  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, "0"); // Months are 0-indexed
  const day = String(date.getDate()).padStart(2, "0");

  return `${year}-${month}-${day}`;
}

function setDatepicker($, report_dates) {
  $(".datepicker-input").each(function () {
    let datepickerID = $(this).attr("id");
    // console.log("Data => ", report_dates, "id => ", datepickerID);
    let findData = report_dates.find((ele) => ele.report_type == datepickerID);
    if (findData) {
      $(this).datepicker({
        format: "dd M, yyyy",
        autoclose: true,
        todayHighlight: false,
        beforeShowDay: function (date) {
          var formattedDate =
            date.getFullYear() +
            "-" +
            ("0" + (date.getMonth() + 1)).slice(-2) +
            "-" +
            ("0" + date.getDate()).slice(-2);
          if (findData.report_dates.includes(formattedDate)) {
            return { enabled: true };
          }
          return { enabled: false, classes: "disabled-date" };
        }
      });
    }
  });
  $(document)
    .off("mousedown")
    .on("mousedown", ".disabled-date",async function (event) {
      event.stopPropagation();
      event.preventDefault();
      if (event.target.hasAttribute("data-date")) {
        let timestamp = event.target.getAttribute("data-date");
        reportTime = timestamp;
      }

      let activeDatepicker = $("input.datepicker-input:focus"); // Check for focused input
      if (!activeDatepicker.length) {
        activeDatepicker = $(".datepicker-input").filter(function () {
          return $(this).data("datepicker") !== undefined;
        });
      }
      let datepickerID = activeDatepicker.attr("id");
      // console.log("Clicked disabled date in datepicker ID:", datepickerID);
      reportId = datepickerID;
      showLoadingScreen();
      await $.ajax({
         url: ajaxurl, // Adjust path if needed
         type: "POST",
         data: { action: "get_date", report_date : reportTime },
         success: function (response) {
           $("#conform_request_details").append(`
            <p class="mb-1"><strong>Name: </strong>${reportId}</p>
            <p class="mb-1"><strong>Date: </strong>${response?.data}</p>
        `);
         }
      });
      hideLoadingScreen();
     
      $("#confirmModal").modal("show");
    });

  $(".datepicker-input").on("changeDate", function (value) {
    // console.log("datepicker => ", $(this).attr("id"), value.date);
    let divId = $(this)[0].id;
    if (downloadDate.length > 0) {
      let idx = downloadDate.findIndex((ele) => ele.id === divId);
      if (idx >= 0) {
        downloadDate[idx].date = convertDate(new Date(value.date));
      } else {
        downloadDate = [...downloadDate,
        { id: `${divId}`, date: convertDate(new Date(value.date)) },
        ];
      }
    } else {
      downloadDate = [
        { id: `${divId}`, date: convertDate(new Date(value.date)) },
      ];
    }
    $(`#show-${divId}`).prop("disabled", false);
  });

  $("#fetch-reports").on("click", async function (e) {
    e.preventDefault();
    fetchDownloadReports();
  });

  $("#confirmAction")
    .off("click")
    .on("click", async function (event) {
      event.preventDefault();
      console.log("Request");
      if (reportId) {
        jQuery.ajax({
          url: ajaxurl,
          type: "POST",
          data: {
            action: "request_report_generate",
            reportId,
            reportTime
          },
          success: function (response) {
            if (response.success) {
              showToastSuccessMessage(
                response?.data || "Data sent successfully"
              );
            } else {
              showToastErrorMessage(
                response?.data ||
                "Oops! The server is currently unavailable. Please try again in a little while."
              );
            }
          },
          error: function () {
            showToastErrorMessage(
              "Oops! The server is currently unavailable. Please try again in a little while."
            );
          },
        });
      }

      $("#confirmModal").modal("hide");
    });

}

function fetchDownloadReports() {
  jQuery(document).ready(function ($) {
    $(".alert-error").remove();
    showLoadingScreen();
    jQuery.ajax({
      url: ajaxurl,
      type: "POST",
      data: {
        action: "fetch_reports",
      },
      success: function (response) {
        if (Array.isArray(response.data) && response.data.length > 0) {
          // console.log("render html file in js", response);
          // console.log(
          //   "render html",
          //   getDownloadDataHTML(response.data)
          // );

          $(".show-download-reports").append(getDownloadDataHTML(response.data));
          setDatepicker($, response.data);
          report_dates = response.data;
        } else {
          $(".show-download-reports")
            .append(` <div class="alert alert-info mt-4" role="alert">
                        Oops! No reports found. Click here to <span id="fetch-reports" class="text-warning" style="cursor: pointer;">refresh </span> and see if new reports are available.
                    </div>`);
        }
        hideLoadingScreen();
      },
      error: function () {
        showErrorMessage(
          " Oops! The server is currently unavailable. Please try again in a little while."
        );
        $(".show-download-reports")
          .append(` <div class="alert alert-info mt-4" role="alert">
                        Oops! No reports found. Click here to <span id="fetch-reports" class="text-warning" style="cursor: pointer;">refresh </span> and see if new reports are available.
                    </div>`);
        hideLoadingScreen();
      },
    });
  });
}

function openRepliesDiv() {
  document.getElementById("replies-div").classList.remove('hidden');
}

jQuery(document).ready(function ($) {

  $('[data-toggle="tooltip"]').tooltip();
  setDatepicker($, report_dates);

  function addRequestForm(form) {
    $("#form-message").remove();
    $(".alert").remove();

    let receivedFormData = jQuery(form).serialize();
    let params = new URLSearchParams(receivedFormData);

    let formData = new FormData();
    // Append files
    uploadedFiles.forEach((file) => {
      formData.append("files[]", file);
    });

    formData.append("action", "csp_submit_ticket");
    formData.append("edit", params.get("edit"));
    formData.append("folio_company", params.get("folio_company"));
    formData.append("folio_number", params.get("folio_number"));
    formData.append("service_request", $("#request-type-select-box").val());
    formData.append("sub_services", $("#request-sub-type-select-box").val());
    formData.append("status", params.get("status"));
    formData.append("modified", params.get("modified"));
    formData.append("ticket_description", params.get("ticket_description"));
    formData.append("subject", params.get("subject"));

    $(".submit_ticket_none_loader").hide();
    $(".create_ticket_loader").show();

    jQuery.ajax({
      url: ajaxurl,
      type: "POST",
      data: formData,
      contentType: false,
      processData: false,
      success: function (response) {
        if (response.success && response?.data?.redirect_url) {
          jQuery("#error-message-div").append(`
                  <div id="form-message" class="alert alert-success">
                      <i class="bi bi-check-circle me-2"></i>
                      ${response.data.message}
                  </div>
              `);
          $(window).scrollTop(0);
          window.location.href = response.data.redirect_url;
        } else {
          jQuery("#error-message-div").append(`
                    <div id="form-message" class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        ${response.data}
                    </div>
                `);
          $(window).scrollTop(0);
          $(".submit_ticket_none_loader").show();
          $(".create_ticket_loader").hide();
        }
      },
      error: function () {
        showToastErrorMessage(
          "An error occurred while submitting your ticket. If the issue persists, contact support."
        );
        $(".submit_ticket_none_loader").show();
        $(".create_ticket_loader").hide();
      },
    });
  }

  $("#add-request-form").validate({
    rules: {
      subject: {
        required: true,
      },
      folio_company: {
        required: true,
      },
      folio_number: {
        required: true,
      },
      "service_request[]": {
        required: true,
      },
      "sub_services[]": {
        required: true,
      },
      ticket_description: {
        required: true,
      },
    },
    messages: {
      subject: {
        required: "Please enter a subject.",
        // minlength: "Subject must be at least 5 characters.",
        // maxlength: "Subject cannot exceed 255 characters.",
      },
      folio_company: {
        required: "Please select a company.",
      },
      folio_number: {
        required: "Please enter your folio number.",
        pattern: "Enter a valid folio number format.",
      },
      "service_request[]": {
        required: "Please select at least one service request type.",
      },
      "sub_services[]": {
        required: "Please select at least one sub service request type.",
      },
      ticket_description: {
        required: "Please provide a description.",
      },
    },
    errorElement: "span",
    errorClass: "text-danger",
    errorPlacement: function (error, element) {
      if (element.is("select")) {
        error.insertAfter(element.next(".select2-container"));
      } else {
        error.insertAfter(element);
      }
    },
    submitHandler: function (form, e) {
      e.preventDefault();
      addRequestForm(form);
    },
  });


  $("#fileInput").on("change", function (e) {
    $(".download-files-list").remove();
    let files = e.target.files;
    let outputHTML = `<div class="d-flex align-items-center gap-3 flex-wrap download-files-list">
    <div class="flex-nowrap d-none d-md-flex"> Uploaded files: </div>
    <div class="d-md-none d-flex col-12 flex-nowrap"> Uploaded files: </div>`;

    // Append selected files to the list
    $.each(files, function (index, file) {
      let fileId = uploadedFiles.length; // Assign unique ID
      uploadedFiles.push(file);
    });
    outputHTML += `<div class="d-flex flex-row flex-wrap">`
    uploadedFiles.forEach(function (file, fileId) {
      outputHTML += `
                <span class="badge badge-primary m-1 p-2 border-blue file-badge bg-white text-blue d-flex justify-content-between align-items-center text-wrap" style="font-size:12px;" data-id="${fileId}">
                    ${file.name} 
                    <i class="bi bi-x-circle text-danger remove-file ms-2 fs-6" data-id="${fileId}"></i>
                </span>
            `;
    })

    outputHTML += `</div>`;
    outputHTML += `</div>`;
    $(".show-uploaded-list").append(outputHTML);

    // Clear input to allow selecting the same file again
    $("#fileInput").val("");
  });

  $(document).on("click", ".remove-file", function () {
    if (uploadedFiles.length == 1) {
      uploadedFiles = [];
      $(".download-files-list").remove();
    }
    let fileId = $(this).data("id");
    uploadedFiles = uploadedFiles.filter((_, index) => index !== fileId);
    $(this).closest(".file-badge").remove();
    console.log("Uploading files...", uploadedFiles);
  });

  $("#folio-search-form").on("submit", async function (e) {
    e.preventDefault();
    $(".alert").remove();
    showLoadingScreen();
    let formData = jQuery(this).serialize();
    let params = new URLSearchParams(formData);
    // console.log("Params", formData);
    let folio_no = params.get("folio_no");
    let pan_no = params.get("pan_no");
    let cmp_name = params.get("cmp_name");

    if (!folio_no && !pan_no && !cmp_name) {
      showErrorMessage(
        "Please fill in atleast one of the parameters before submitting and try again."
      );
      hideLoadingScreen();
      return;
    } else {
      jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
          action: "folio_search",
          folio: folio_no,
          pan: pan_no,
          name: cmp_name,
        },
        success: function (response) {
          if (response.success && response?.data) {
            console.log(response.data);
            if (response.data) {
              removeQueryParam("folio", { folio_no, pan_no, cmp_name });
            } else {
              showErrorMessage(
                "Oops! No results were found. Please adjust your search criteria."
              );
              hideLoadingScreen();
            }
          } else {
            showErrorMessage(
              response?.data ||
              "Oops! No results were found. Please adjust your search criteria."
            );
            hideLoadingScreen();
          }
        },
        error: function () {
          showErrorMessage(
            " Oops! The server is currently unavailable. Please try again in a little while."
          );
          hideLoadingScreen();
        },
      });
    }
  });

  $("#reloading-window").on("click", async function (e) {
    $("#loading-screen").show();
    window.location.reload();
  });

  $("#request-type-select-box").on("change", function () {
    // Get the selected tab value
    let subValueArray = [];
    if ($("#request-sub-type-select-box")) {
      subValueArray = $("#request-sub-type-select-box").val();
    }

    $("#dynamic-select").remove();
    $("#dynamic-list").remove();
    $("#fileList").remove();
    var selectedTab = $(this).val();
    // console.log(requestTypeArray);
    if (selectedTab) {
      let selectedArray = requestTypeArray.filter(
        (ele) => selectedTab.indexOf(ele.request_type) >= 0
      );

      if (selectedArray.length > 0) {
        let outputHTML = "";
        let subServiceArray = selectedArray.reduce((acc, curr) => {
          let subArray = curr.sub_type.map(ele => ele.type);
          return [...acc, ...subArray];
        }, []);
        if (Array.isArray(subServiceArray) && subServiceArray.length > 0) {
          outputHTML = `<div id="dynamic-select" class="mb-3 col-12">
                <label for="sub_services" class="form-label required-label">Sub Services</label>
                <select id="request-sub-type-select-box" data-placeholder="----Select----" name="sub_services[]" class="form-select" multiple required>
                    ${subServiceArray
              .map((ele) => `<option value='${ele}'>${ele}</option>`)
              .join("")}
                </select>
            </div>`;
        }

        // console.log(outputHTML);
        $(".show-request-type").append(outputHTML);
        $("#request-sub-type-select-box").select2();
        $("#request-sub-type-select-box").on("change", function () {
          $("#dynamic-list").remove();
          var selectedTab = $(this).val();
          if (selectedTab) {
            let docsOutputHTML = "";
            let subTypeArray = requestTypeArray.reduce((acc, curr) => [...acc, ...curr.sub_type], []);
            let selectedSubArray = subTypeArray.filter(ele => selectedTab.indexOf(ele.type) >= 0);
            let uploadFormsArray = selectedSubArray.reduce(
              (acc, curr) =>
                [...acc, ...curr.upload_forms.filter((f) =>
                  acc.findIndex((ele) => ele.name === f.name) < 0
                )]
              ,
              []
            );

            preselectedSubServiceValues += `,${selectedTab}`;
  
            if (
              Array.isArray(uploadFormsArray) &&
              uploadFormsArray.length > 0
            ) {
              docsOutputHTML = `<div id="dynamic-list" class="h6 d-flex flex-wrap gap-2 align-items-center">
                        <div class="d-none d-md-flex" >Document List: </div>
                        <div class="d-flex d-md-none col-12" >Document List: </div>
                        <div class="d-flex flex-wrap form-control">
                          ${uploadFormsArray
                            .map(
                              (ele) =>
                                `<div class="col-12 col-md-4 p-2 d-flex">
                              <div class="badge p-2 d-flex w-100 flex-column gap-2 border-blue text-blue bg-white">
                                  <div class="d-flex justify-content-between">
                                      <p class="text-blue m-0" style="font-size: medium; text-wrap: auto; text-align: left;">${ele.name}</p>
                                      <a href = "${ele.url}" class="text-decoration-none" target="_blank" download>
                                      <i class="fa-solid fa-download fs-6" style="color:#212D45;"></i></a>
                                  </div>
                                  <div class="d-flex text-wrap text-left"  style="font-size: small; font-weight: 400; text-align: left;">
                                    ${ele.description}
                                  </div>
                              </div>
                              </div>`
                            )
                            .join("")}
                            </div>
                          </div>`;
              $(".display-documents").append(docsOutputHTML);
            }
          }
        });
        if (subValueArray) {
          $("#request-sub-type-select-box")
            .val(subValueArray)
            .trigger("change");
        }
      }
    }
  });

  $("#save-reply-form").on("submit", async function (e) {
    e.preventDefault();
    $(".alert").remove();
    $(".submit-reply-btn").hide();
    $(".save-reply-btn").show();

    let receivedFormData = jQuery(this).serialize();
    let params = new URLSearchParams(receivedFormData);
    let formData = new FormData();

    formData.append("action", "submit_ticket_reply");
    formData.append("reply_content", params.get("reply_content"));
    formData.append("ticket_id", params.get("ticket_id"));

    jQuery.ajax({
      url: ajaxurl,
      type: "POST",
      data: formData,
      contentType: false,
      processData: false,
      success: function (response) {
        if (response.success && response?.data) {
          window.location.reload();
          showToastSuccessMessage(
            `Request successfully saved with id ${response?.data?.message}`
          );
        } else {
          showToastErrorMessage(response?.data);
          $(".submit-reply-btn").show();
          $(".save-reply-btn").hide();
        }
      },
      error: function () {
        showToastErrorMessage();
        $(".submit-reply-btn").show();
        $(".save-reply-btn").hide();
      },
    });
  });

  $("#request-type-select-box").select2();

  if (preselectedValues) {
    $("#request-type-select-box").val(preselectedValues.split(',')).trigger("change");
  }
  if (preselectedSubServiceValues) {
    $("#request-sub-type-select-box")
      .val(preselectedSubServiceValues.split(','))
      .trigger("change");
  }
});

// window.addEventListener('hashchange', function () {
//     showDataOnURLChange();
// });

// function showDataOnURLChange() {
//     const hash = window.location.hash.substring(1);
//     if (!hash) {
//         window.location.href = "http://localhost/Beetal-wordpress/dummy/#my-ticket";-
//     }
//     document.querySelectorAll('.menu-items').forEach(form => form.classList.remove("menu-active"));
//     document.querySelectorAll('.right-section').forEach(form => form.classList.add("hidden"));
//     console.log(hash);
//     document.getElementById(`${hash}`).classList.add('menu-active');
//     document.getElementById(`csp-${hash}`).classList.remove('hidden');

// }