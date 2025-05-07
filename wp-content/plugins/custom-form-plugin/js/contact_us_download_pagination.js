jQuery(document).ready(function ($) {
  let urlParams = new URLSearchParams(window.location.search);
  let download = urlParams.get("page") ? parseInt(urlParams.get("page")) : 1;
  let table = urlParams.get("table") ? urlParams.get("table") : "";
  let total_pages = 5;
  let entry_page = true;

  function generatePagination(
    current,
    total,
    divId,
    btnID = "download-page-btn"
  ) {
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

  function downloadCardsCreateHTML(data, page = 1) {
    let outputHTML = "";
    outputHTML += data
      .map(
        (ele, idx) =>
          `<div class="d-flex d-md-none flex-wrap">
            <div class="col-12 col-md-4 d-flex p-2">
                <div class="d-flex flex-column rounded border-blue border-2 p-4 w-100">
                    <div class="d-flex justify-content-between text-blue">
                        <h5 class="mb-2">${ele.form_title}</h5>
                        <a href="${ele?.form_url || "#"}" target="_blank">
                            <i class="fa-solid fa-download text-blue fs-4"></i>
                        </a>
                    </div>
                    <small class="text-blue text-sm">
                    ${ele.form_description}
                    </small>
                </div>
            </div>
    </div>`
      )
      .join("");
    return outputHTML;
  }

  function downloadTableCreateTableHTML(data, page = 1) {
    let outputHTML = "";
    outputHTML += data
      .map(
        (ele, idx) =>
          `  <tr>
                    <td class="text-center col-1" style="width: 10px;">${
                      ++idx + (page - 1) * 10
                    }</td>
                    <td class="text-start col-3">
                        <span class="d-flex align-items-start">
                            ${ele.form_title}
                        </span>
                    </td>
                    <td class="text-start col-7">${ele.form_description}</td>
                    <td class="text-center col-1 border-start-0 d-flex justify-content-center w-100">
                        <a class="d-flex btn-blue align-items-center rounded" style="width: fit-content;" href="${
                          ele?.form_url || "#"
                        }" target="_blank">
                            <i class="fa-solid fa-download text-white" style="color: #06102A;"></i>
                        </a>
                    </td>
                </tr>`
      )
      .join("");
    return outputHTML;
  }

  function downloadCardsPlaceholderHTML() {
    let outputHTML = Array(10)
      .fill(0)
      .map(
        () => `
        <div class="col-12 col-md-4 d-flex p-2">
          <div class="d-flex flex-column rounded border-blue border-2 p-4 w-100">
            <div class="d-flex justify-content-between text-blue">
              <h5 class="mb-2 placeholder-glow w-75">
                <span class="placeholder col-12"></span>
              </h5>
              <div class="placeholder-glow">
                <span class="placeholder rounded-circle" style="width: 24px; height: 24px; display: inline-block;"></span>
              </div>
            </div>
            <div class="placeholder-glow">
              <span class="placeholder col-10 mb-1"></span>
              <span class="placeholder col-8"></span>
            </div>
          </div>
        </div>
      `
      )
      .join("");
    let pagination = "";
    for (let i = 1; i <= total_pages; i++) {
      pagination += `<button class="disabled-button col bg-eee" data-page="${i}" style="min-width: 50px; max-width: 50px;">${i}</button> `;
    }

    return {
      outputHTML,
      button_html: pagination,
    };
  }

  function downloadTablePlaceholderHTML() {
    let outputHTML = "";
    outputHTML += Array(10)
      .fill(0)
      .map(
        (ele) => `<tr>
      <td class="col-1" style="width:10px;">
        <div class="placeholder-glow">
          <span class="placeholder col-12"></span>
        </div>
      </td>
      <td class="col-3">
        <div class="placeholder-glow">
          <span class="placeholder col-12"></span>
        </div>
      </td>
      <td class="col-7">
        <div class="placeholder-glow">
          <span class="placeholder col-12"></span>
        </div>
      </td>
      <td class="col-1">
        <div class="placeholder-glow">
          <span class="placeholder col-12"></span>
        </div>
      </td>
    </tr>`
      )
      .join("");

    let pagination = "";
    for (let i = 1; i <= total_pages; i++) {
      pagination += `<button class="disabled-button col bg-eee" data-page="${i}" style="min-width: 50px; max-width: 50px;">${i}</button> `;
    }

    return {
      outputHTML,
      button_html: pagination,
    };
  }

  function loadCircularPodsData(page) {
    $.ajax({
      url: pods_ajax.ajaxurl,
      type: "POST",
      data: {
        action: "fetch_download_pods_pagination",
        download: page,
        filter: "Circulars",
      },
      beforeSend: function () {
        const htmlCode = downloadTablePlaceholderHTML();
        $("#circular_table_data").html(htmlCode.outputHTML);
        const htmlTemp = downloadCardsPlaceholderHTML();
        $("#circular_data_mobile").html(htmlTemp.outputHTML);
        // $("#circular_table_pagination").html(htmlCode.button_html);
      },
      success: function (response) {
        console.log(response);
        $("#circular_table_data").html(
          downloadTableCreateTableHTML(
            response.data?.table_data,
            response?.data?.current_page
          )
        );
        $("#circular_data_mobile").html(
          downloadCardsCreateHTML(
            response.data?.table_data,
            response?.data?.current_page
          )
        );

        if (response?.data?.total_pages > 1) {
          generatePagination(
            response?.data?.current_page,
            response?.data?.total_pages,
            "circular_table_pagination",
            "circular-page-btn"
          );
        }

        if (!entry_page) {
          history.pushState(
            null,
            "",
            `?table=circulars&page=${response.data.current_page}`
          );
        }
      },
    });
  }

  async function loadDownloadPodsData(page) {
    $.ajax({
      url: pods_ajax.ajaxurl,
      type: "POST",
      data: {
        action: "fetch_download_pods_pagination",
        download: page,
        filter: "Downloads",
      },
      beforeSend: function () {
        const htmlCode = downloadTablePlaceholderHTML();
        $("#download_table_data").html(htmlCode.outputHTML);
        const htmlTemp = downloadCardsPlaceholderHTML();
        $("#download_data_mobile").html(htmlTemp.outputHTML);
        // $("#download_table_pagination").html(htmlCode.button_html);
      },
      success: async function (response) {
        console.log(response);
        $("#download_table_data").html(
          downloadTableCreateTableHTML(
            response.data?.table_data,
            response?.data?.current_page
          )
        );

        $("#download_data_mobile").html(
          downloadCardsCreateHTML(
            response.data?.table_data,
            response?.data?.current_page
          )
        );
        if (response?.data?.total_pages > 1) {
          generatePagination(
            response?.data?.current_page,
            response?.data?.total_pages,
            "download_table_pagination"
          );
        }
        if (!entry_page) {
          history.pushState(
            null,
            "",
            `?table=download&page=${response.data.current_page}`
          );
        }
      },
    });
  }

  $(document).on("click", ".circular-page-btn", function (e) {
    entry_page = false;
    download = e.target.getAttribute("data-page");
    if (download > 0) {
      loadCircularPodsData(download);
    }
  });
  $(document).on("click", ".download-page-btn", function (e) {
    entry_page = false;
    download = e.target.getAttribute("data-page");
    //   console.log(download);
    if (download > 0) {
      loadDownloadPodsData(download);
    }
  });
  if (document.getElementById("download_table_data")) {
    loadDownloadPodsData(table == "download" ? download : 1);
  }
  if (document.getElementById("circular_table_data")) {
    loadCircularPodsData(table == "circulars" ? download : 1);
  }
});
