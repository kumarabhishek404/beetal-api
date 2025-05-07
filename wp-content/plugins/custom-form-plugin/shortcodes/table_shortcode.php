<?php

function handle_fetch_client_pods_pagination_fn()
{
    $per_page = 20;

    // Get the current page number from the query parameter
    $current_page = isset($_POST['page']) ? intval($_POST['page']) : 1;

    // Calculate offset for pagination
    $offset = ($current_page - 1) * $per_page;

    // $where = 'allotment_status IS NULL OR allotment_status = "" OR allotment_status != "active"';

    // Define Pods query parameters
    $args = array(
        // 'where' => $where,
        'limit' => $per_page,
        'offset' => $offset,
    );

    // Fetch Pods data
    $initial_pods = pods('client_list', [
        // 'where' => $where,
        'limit' => -1
    ]);
    $pods = pods('client_list', $args);

    // Get total number of Pods
    $total_pods = $initial_pods->total();

    // Calculate total pages
    $total_pages = ceil($total_pods / $per_page);
    error_log("Data => " . print_r($pods->data(), true));
    // error_log("Total pages: " . $total_pages . " Pods: " . $total_pods . " current Page: " . $current_page);

    wp_send_json_success(["table_data" => $pods->data(), "total_pages" => $total_pages, "site_url" => site_url(), "current_page" => $current_page]);
}
function fetch_pods_pagination()
{
    $per_page = 10;


    // Get the current page number from the query parameter
    $current_page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $filter = isset($_POST['filter']) ? $_POST['filter'] : 'inactive';

    // Calculate offset for pagination
    $offset = ($current_page - 1) * $per_page;

    $where = 'allotment_status IS NULL OR allotment_status = "" OR allotment_status != "active"';

    if ($filter == 'active') {
        $where = 'company_status = "Active"';
    }

    error_log(print_r($where, true));
    // Define Pods query parameters
    $args = array(
        'where' => $where,
        'orderby' => 'opening_date DESC',
        'limit' => $per_page,
        'offset' => $offset,
    );

    // Fetch Pods data
    $initial_pods = pods('ipo_company', [
        'where' => $where,
        'limit' => -1
    ]);
    $pods = pods('ipo_company', $args);

    // Get total number of Pods
    $total_pods = $initial_pods->total();

    // Calculate total pages
    $total_pages = ceil($total_pods / $per_page);
    error_log("Total pages: " . $total_pages . " Pods: " . $total_pods . " current Page: " . $current_page);

    wp_send_json_success(["table_data" => $pods->data(), "total_pages" => $total_pages, "site_url" => site_url(), "current_page" => $current_page]);
}

add_shortcode(
    'ipo_companies_data_table',
    function ($atts) {

        $where = 'allotment_status IS NULL OR allotment_status = "" OR allotment_status != "active"';
        if (!empty($atts['filter']) && $atts['filter'] == 'active') {
            $where = 'company_status = "Active"';
        }
        // Fetch Pods data
        $initial_pods = pods('ipo_company', [
            'where' => $where,
            'limit' => -1
        ]);
        ob_start();
?>
    <?php if ($initial_pods->total() > 0) : ?>
        <div class="table-responsive">
            <table class="tax-forms-table table-bordered table-striped investor-data-table">
                <thead class="bg-warning p-2 text-white rounded-t">
                    <tr>
                        <th>Name of the Company</th>
                        <th style="white-space: nowrap;">Nature of Issue</th>
                        <th style="white-space: nowrap;">Opening Date</th>
                        <th style="white-space: nowrap;">Closing Date</th>
                    </tr>
                </thead>
                <tbody id="pods-data" data-value="<?= $atts['filter'] ?? 'inactive' ?>">

                </tbody>
            </table>
        </div>
        <!-- Pagination links -->
        <?php if ($initial_pods->total() > 10) : ?>
            <div id="pagination" class="d-flex gap-4 justify-content-center flex-wrap">
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="alert alert-danger">
            There is no data available at the moment. Please check back later.
        </div>
    <?php endif; ?>

<?php
        return ob_get_clean();
    }
);

add_shortcode(
    'client_list_table',
    function () {
        // $where = 'allotment_status IS NULL OR allotment_status = "" OR allotment_status != "active"';
        // Fetch Pods data
        $initial_pods = pods('client_list', [
            'limit' => -1
        ]);
        ob_start();
?>
    <?php if ($initial_pods->total() > 0) : ?>
        <div class="d-flex flex-wrap">
            <div id="client-pods-div1" class="col-12 col-md-6 px-4">
                <table class="tax-forms-table table-bordered table-striped investor-data-table">
                    <thead class="bg-warning p-2 text-white rounded-t">
                        <tr>
                            <th class="text-center" style="width: 10px;">#</th>
                            <th>Name Of Company</th>
                        </tr>
                    </thead>
                    <tbody id="client-pods-data">

                    </tbody>
                </table>
            </div>
            <div id="client-pods-div2" class="col-12 col-md-6 px-4">
                <table class="tax-forms-table table-bordered table-striped investor-data-table">
                    <thead class="bg-warning p-2 text-white rounded-t">
                        <tr>
                            <th class="text-center" style="width: 10px;">#</th>
                            <th>Name Of Company</th>
                        </tr>
                    </thead>
                    <tbody id="client-pods-data2">

                    </tbody>
                </table>
            </div>
        </div>
        <!-- Pagination links -->
        <?php if ($initial_pods->total() > 20) : ?>
            <div id="client-list-pagination" class="d-flex gap-4 justify-content-center flex-wrap">
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="alert alert-danger">
            There is no data available at the moment. Please check back later.
        </div>
    <?php endif; ?>

<?php
        return ob_get_clean();
    }
);

add_action('wp_ajax_fetch_pods_pagination', 'fetch_pods_pagination');
add_action('wp_ajax_nopriv_fetch_pods_pagination', 'fetch_pods_pagination');
