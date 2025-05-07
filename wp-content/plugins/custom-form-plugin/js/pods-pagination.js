function formatDate(dateStr) {
  // console.log("Date => ",dateStr);
  // Parse the input string into a Date object
  if (!dateStr) {
    return '--/--';
  }
  const date = new Date(dateStr);
  // Check if the date is valid
  if (isNaN(date.getTime())) {
    return "";
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
  let urlParams = new URLSearchParams(window.location.search);
  let pageNumber = urlParams.get("page") ? parseInt(urlParams.get("page")) : 1;
  let total_pages = 5;
  let entry_page = false;

  function generatePagination(
    current,
    total,
    divId,
    btnID = "page-btn"
  ) {
    // console.log("Generating Pagination with : ",current,total,divId,btnID);
    let pagination = `<button class="${btnID} pagination-btn col bg-eee" ${
      current == 1 ? "disabled" : ""
    } data-page="${
      current - 1
    }"><i class="fa fa-chevron-left ${btnID}" data-page="${
      current - 1
    } aria-hidden="true"></i></button>`;

    let startPage = Math.max(1, current - 2);
    let endPage = Math.min(total, startPage + 4);

    if (startPage > 1) {
      pagination += `<button class="${btnID} pagination-btn bg-eee" data-page="1">1</button>`;
      if (startPage > 2) {
        pagination += `<button class="${btnID} pagination-btn
          bg-eee
        " data-page="0">...</button>`;
      }
    }

    for (let i = startPage; i <= endPage; i++) {
      pagination += `<button class="${btnID} pagination-btn ${
        i === current ? "bg-blue" : "bg-eee"
      }" data-page="${i}">${i}</button>`;
    }

    if (endPage < total) {
      if (endPage < total - 1) {
        pagination += `<button class="${btnID} pagination-btn
          bg-eee
        " data-page="0">...</button>`;
      }
      pagination += `<button class="${btnID} pagination-btn bg-eee" data-page="${total}">${total}</button>`;
    }

    pagination += `<button class="${btnID} pagination-btn col bg-eee" ${
      current == total ? "disabled" : ""
    } data-page="${
      current + 1
    }"><i class="fa fa-chevron-right ${btnID}" aria-hidden="true" data-page="${
      current + 1
    }"></i></button>`;
    $(`#${divId}`).html(pagination);
  }

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
                            href="${site_url}/open-buyback-right-issue/?company=${
                                ele.company_code
                              }"
                          >
                            ${ele.name.toUpperCase()}
                          </a>`
                            : ele.name.toUpperCase()
                        }</td>
                        <td>${ele.offer_type}</td>
                        <td style="white-space: nowrap;">${
                          ele.opening_date == "0000-00-00"
                            ? ""
                            : formatDate(ele.opening_date)
                        }</td>
                        <td style="white-space: nowrap;">${
                          ele.closing_date == "0000-00-00"
                            ? ""
                            : formatDate(ele.closing_date)
                        }</td>
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
    
     let pagination = "";
     for (let i = 1; i <= total_pages; i++) {
       pagination += `<button class="disabled-button col bg-eee" data-page="${i}" style="min-width: 50px; max-width: 50px;">${i}</button> `;
     }
    
    return {
      outputHTML,
      button_html: pagination
    };
  }

  function loadPodsData(page) {
    let filter = $("#pods-data").attr("data-value") || "";
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
        // $("#pagination").html(htmlCode.button_html);
      },
      success: function (response) {
        console.log(response);
        $("#pods-data").html(
          createTableHTML(response.data?.table_data, response.data?.site_url)
        );
        let pagination = "";
        for (let i = 1; i <= response.data.total_pages; i++) {
          pagination += `<button class="page-btn col ${
            response.data.current_page === i ? "bg-blue text-white" : "bg-eee"
          }" data-page="${i}" style="min-width: 50px; max-width: 50px;">${i}</button> `;
        }
        // $("#pagination").html(
        //  pagination
        // );
          generatePagination(
            response?.data?.current_page,
            response?.data?.total_pages,
            "pagination"
          );
        if (entry_page) {
          history.pushState(null, "", `?page=${response.data.current_page}`);
        }
        entry_page = true;
      },
    });
  }

  // Handle pagination button click
  $(document).on("click", ".page-btn", function (e) {
    pageNumber = e.target.getAttribute("data-page");
      if (pageNumber && pageNumber > 0) {
        loadPodsData(pageNumber);
      }
  });

  function createClientTableHTML(data, site_url, current_page) {
    let outputHTML = "";
    let outputHTML2 = "";
    // let data1 = data.slice(0, 10);
    data
      .map(
        (ele, index) => {
          if (index < 10) {
            outputHTML += `<tr>
                        <td class="text-center" style="width: 10px;">${++index + (current_page - 1) * 20}</td>
                        style="max-height: 80px;"/></td>
                        <td>${ele.name.toUpperCase()}</td>
                    </tr>`
          } else {
            outputHTML2 += `<tr>
                        <td class="text-center" style="width: 10px;">${++index + (current_page - 1) * 20
              }</td>
                        style="max-height: 80px;"/></td>
                        <td>${ele.name.toUpperCase()}</td>
                    </tr>`;
          }
        }
      );
    return {table1: outputHTML, table2: outputHTML2};
  }

  function clientPlaceholderHTML() {
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
    </tr>`
      )
      .join(",");

    let pagination = "";
    for (let i = 1; i <= total_pages; i++) {
      pagination += `<button class="disabled-button col bg-eee" data-page="${i}" style="min-width: 50px; max-width: 50px;">${i}</button> `;
    }

    return {
      outputHTML,
      button_html: pagination,
    };
  }

  function loadClientPodsData(page) {
    $.ajax({
      url: pods_ajax.ajaxurl,
      type: "POST",
      data: {
        action: "fetch_client_pods_pagination",
        page: page,
      },
      beforeSend: function () {
        const htmlCode = clientPlaceholderHTML();
        $("#client-pods-data").html(htmlCode.outputHTML);
        $("#client-pods-data2").html(htmlCode.outputHTML);
      },
      success: function (response) {
        console.log(response);
        let htmlData = createClientTableHTML(
          response.data?.table_data,
          response.data?.site_url,
          response.data.current_page
        );
        if (response.data?.table_data.length < 11) {
          $("#client-pods-div1").removeClass('col-md-6');
          $("#client-pods-div2").addClass('hidden');
          $("#client-pods-data").html(htmlData.table1);
        } else {
          $("#client-pods-div2").removeClass("hidden");
          $("#client-pods-div1").addClass("col-md-6");
          $("#client-pods-data").html(
            htmlData.table1
          );
          $("#client-pods-data2").html(
            htmlData.table2
          );
        }
        generatePagination(
          response?.data?.current_page,
          response?.data?.total_pages,
          "client-list-pagination",
          "client-page-btn"
        );
        if (entry_page) {
          history.pushState(null, "", `?page=${response.data.current_page}`);
        }
        entry_page = true;
      },
    });
  }

  // Handle pagination button click
  $(document).on("click", ".client-page-btn", function (e) {
    pageNumber = e.target.getAttribute("data-page");
    if (pageNumber && pageNumber > 0) {
      loadClientPodsData(pageNumber);
    }
  });
  // $(document).on("click", ".next-page-btn", function () {
  //   loadPodsData(++pageNumber);
  // });
  if ($("#pods-data").length) {
    loadPodsData(pageNumber);
  };
  if ($("#client-pods-data").length) {
    loadClientPodsData(pageNumber);
  };
});
