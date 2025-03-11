function showLoadingScreen() {
  const loadingScreen = document.createElement("div");
  loadingScreen.id = "loading-screen";
  loadingScreen.classList.add("loading");
  loadingScreen.innerHTML = '<div class="loader"></div>';

  document.body.appendChild(loadingScreen);
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

// Function to hide the loading screen
function hideLoadingScreen() {
  const loadingScreen = document.getElementById("loading-screen");
  if (loadingScreen) {
    loadingScreen.remove();
  }
}

function deleteTicketFn(id) {
  showLoadingScreen();
  console.log("deleteTicketFn: ", id);
  jQuery.ajax({
    url: ajaxurl,
    type: "POST",
    data: {
      action: "delete_ticket_using_id",
      id,
    },
    success: function (response) {
      window.location.href = response.data || " https://pragmaappscstg.wpengine.com/wp-admin/admin.php?page=pods-manage-ticket";
      hideLoadingScreen();
    },
    error: function () {
      wp_die(__("Unable to delete ticket, try again later"));
      hideLoadingScreen();
    },
  });
}

if (document.getElementById("view-reply")) {
  document.getElementById("view-reply").addEventListener("click", function () {
    document.getElementById("reply-form").style.display = "block";
  });
  document
    .getElementById("cancel-reply")
    .addEventListener("click", function () {
      document.getElementById("reply-form").style.display = "none";
    });
}

function convertDate(dateString) {
  const dateObj = new Date(dateString);
  let date =  dateObj.toLocaleString("en-US", {
    day: "2-digit",
    month: "short",
    hour: "2-digit",
    minute: "2-digit",
    hour12: true,
  });
  // console.log(date);  
  return date;
}

function getReplyHTML(response, current_user) {
  console.log(response);
  let outputHTML = '';
  outputHTML += response
    .map(
      (response) =>
        `<div class="card mb-3">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <div class="rounded-circle ${
                                          response.user_id == current_user
                                            ? "bg-info"
                                            : "bg-secondary"
                                        } text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            ${
                                              response.user_name
                                                ? response.user_name
                                                    .slice(0, 1)
                                                    .toUpperCase()
                                                : "N"
                                            }
                                        </div>
                                    </div>
                                    <div class="w-100">
                                        <div class="d-flex justify-content-between mb-0">
                                            <h6 class="mb-0">${
                                              response.user_name
                                            }</h6>
                                            <small class="text-muted float-end">${
                                              convertDate(response.created)
                                            }</small>
                                        </div>
                                        <p class="mt-0 mb-0">${
                                          response.reply_content
                                        }</p>
                                    </div>
                                </div>
                            </div>
                        </div>`
    )
    .join("");
  return outputHTML;
}

jQuery(document).ready(async function ($) {
  $("#edit-ticket-form").on("submit", async function (e) {
    e.preventDefault();
    $(".notice").remove();
    showLoadingScreen();
    let receivedFormData = $(this).serialize();
    let params = new URLSearchParams(receivedFormData);
    console.log("ticket",params.get("id"));
    let formData = new FormData();

    formData.append("action", "edit_ticket_using_id");
    formData.append("id", params.get("id"));
    formData.append("service_request", params.get("service_request"));
    formData.append("subject", params.get("subject"));
    formData.append("sub_services", params.get("sub_services"));
    formData.append("status", params.get("status"));
    formData.append("modified", params.get("modified"));
    formData.append("ticket_description", params.get("ticket_description"));
    formData.append("assign_to", params.get("assign_to"));
    // console.log(formData);

    $.ajax({
      url: ajaxurl,
      type: "POST",
      data: formData,
      contentType: false,
      processData: false,
      success: function (response) {
        console.log(response);
        $(".message-div")
          .append(`<div class="notice notice-success is-dismissible m-0">
                <p><strong>Pods Data Saved Successfully.</strong></p>
            </div>`);
        hideLoadingScreen();
      },
      error: function () {
        wp_die(__("Unable to save ticket details, try again later"));
        hideLoadingScreen();
      },
    });
  });
  
  $("#save-reply-form").on("submit", async function (e) {
    e.preventDefault();
    $(".alert").remove();
    showLoadingScreen();

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
          // window.location.reload();
          console.log(response.data);
          showToastSuccessMessage(
            `Request successfully saved with id ${response?.data?.message}`
          );
          $(".ticket-reply").empty();
          $(".ticket-reply").append(
            getReplyHTML(response.data.replies, response.data.current_user)
          );
          hideLoadingScreen();
        } else {
          showToastErrorMessage(response?.data);
          hideLoadingScreen();
        }
      },
      error: function () {
        showToastErrorMessage();
        hideLoadingScreen();
      },
    });
  });

  $("#csp_ticket_edit_fn").on("click", function (e) {
    $("#edit-ticket-form").submit();
  });
});
