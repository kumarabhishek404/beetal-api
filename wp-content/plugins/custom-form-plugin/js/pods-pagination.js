function formatDate(dateStr) {
  // Parse the input string into a Date object
  if (!dateStr) {
    return '--/--';
  }
  const date = new Date(dateStr);
  // Check if the date is valid
  if (isNaN(date.getTime())) {
    return "Invalid Date";
  }

  // Get individual date components
  const day = date.getDate();
  const monthNames = [
    "Jan",
    "Feb",
    "Mar",
    "Apr",
    "May",
    "Jun",
    "Jul",
    "Aug",
    "Sep",
    "Oct",
    "Nov",
    "Dec",
  ];
  const month = monthNames[date.getMonth()];
  const year = date.getFullYear();

  // Format the date as "DD MMM, YYYY"
  return `${day} ${month}, ${year}`;
}

jQuery(document).ready(function ($) {
  let pageNumber = 1;
  function createTableHTML(data, site_url) {
    let outputHTML = "";
    outputHTML += data
      .map(
        (ele) =>
          `<tr>
                        <td>${
                          ele?.company_status == "Active"
                            ? ` <a
                            class="text-decoration-none"
                            href="${site_url}/investor-request/?company=${ele.company_code}#Open_Buyback_right_issue"
                          >
                            ${ele.name}
                          </a>`
                            : ele.name
                        }</td>
                        <td>${ele.offer_type}</td>
                        <td>${formatDate(ele.opening_date)}</td>
                        <td>${formatDate(ele.closing_date)}</td>
                    </tr>`
      )
      .join(",");
    return outputHTML;
  }

  function placeholderHTML() {
    let outputHTML = "";
    outputHTML += Array(10)
      .fill(0)
      .map(
        (ele) => `<tr>
      <td>
        <div class="placeholder-glow">
          <span class="placeholder col-12"></span>
        </div>
      </td>
      <td>
        <div class="placeholder-glow">
          <span class="placeholder col-12"></span>
        </div>
      </td>
      <td>
        <div class="placeholder-glow">
          <span class="placeholder col-12"></span>
        </div>
      </td>
      <td>
        <div class="placeholder-glow">
          <span class="placeholder col-12"></span>
        </div>
      </td>
    </tr>`
      )
      .join(",");
    return {
      outputHTML,
      button_html: `
          <button class="disabled-button col-4 col-md-2 col-lg-1 text-white" style="min-width:100px;" disabled > Previous</button>
          <button class="disabled-button col-4 col-md-2 col-lg-1 bg-blue" style="min-width:100px;" disabled > Next</button>`,
    };
  }

  function loadPodsData(page) {
    let hash = window.location.hash.substring(1); // Get the fragment without #
    let filter = '';
    if (hash && hash == "Open_Buyback_right_issue") {
      filter = "active";
    }
    $.ajax({
      url: pods_ajax.ajaxurl,
      type: "POST",
      data: {
        action: "fetch_pods_pagination",
        page: page,
        filter,
      },
      beforeSend: function () {
        const htmlCode = placeholderHTML();
        $("#pods-data").html(htmlCode.outputHTML);
        $("#pagination").html(htmlCode.button_html);
      },
      success: function (response) {
        console.log(response);
        $("#pods-data").html(
          createTableHTML(response.data?.table_data, response.data?.site_url)
        );
        $("#pagination").html(
          `<button class="prev-page-btn col-4 col-md-2 col-xl-1 text-white ${
            pageNumber < 2 ? "disabled-button" : ""
          }" ${
            pageNumber < 2 ? "disabled" : ""
          } style="min-width: 100px;">Previous</button>
            <button class="next-page-btn col-4 col-md-2 col-xl-1 bg-blue ${
              pageNumber == response.data?.total_pages ? "disabled-button" : ""
            }" ${
            pageNumber == response.data?.total_pages ? "disabled" : ""
          } style="min-width: 100px;" > Next</button>`
        );
      },
    });
  }

  // Load first page on document ready
  // loadPodsData(1);

  // Handle pagination button click
  $(document).on("click", ".prev-page-btn", function () {
    if (pageNumber > 1) {
      loadPodsData(--pageNumber);
    }
  });
  $(document).on("click", ".next-page-btn", function () {
    loadPodsData(++pageNumber);
  });
});
