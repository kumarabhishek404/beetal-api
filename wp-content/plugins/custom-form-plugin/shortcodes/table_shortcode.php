<?php

function fetch_pods_pagination()
{
    $per_page = 10;

    // Get the current page number from the query parameter
    $current_page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $filter = isset($_POST['filter']) ? intval($_POST['filter']) : '';

    // Calculate offset for pagination
    $offset = ($current_page - 1) * $per_page;

    $where = 'allotment_status IS NULL OR allotment_status = "" OR allotment_status != "active"';

    if($filter == 'active'){
        $where = 'company_status = "Active"';
    }
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

    wp_send_json_success(["table_data" => $pods->data(), "total_pages" => $total_pages, "site_url" => site_url()]);
}

add_shortcode(
    'ipo_companies_data_table',
    function ($atts) {
        error_log("Error: " . print_r($atts,true));
        
        $per_page = 10;

        // Get the current page number from the query parameter
        $current_page = isset($_POST['page']) ? intval($_POST['page']) : 1;

        // Calculate offset for pagination
        $offset = ($current_page - 1) * $per_page;

        // Define Pods query parameters
        $where = 'allotment_status IS NULL OR allotment_status = "" OR allotment_status != "active"';
        if($atts['filter'] == 'active'){
            $where = 'company_status = "Active"';
        }
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
        // error_log("Total pages: " . $total_pages . " Pods: " . $total_pods . " current Page: " . $current_page);
        ob_start();
?>
    <?php if ($pods->total() > 0) : ?>
        <table class="tax-forms-table table-bordered table-striped investor-data-table">
            <thead class="bg-warning p-2 text-white rounded-t">
                <tr>
                    <th>Name of the Company</th>
                    <th>Nature of Issue</th>
                    <th>Opening Date</th>
                    <th>Closing Date</th>
                </tr>
            </thead>
            <tbody id="pods-data">
                <?php while ($pods->fetch()) : ?>
                    <tr>
                        <td>
                            <?php if (!empty($pods->field('company_status')) && $pods->field('company_status') == 'Active') : ?>
                                <a class="text-decoration-none" href="<?= site_url('investor-request/?company=' . $pods->field('company_code') . '#Open_Buyback_right_issue') ?>"><?= $pods->field('name') ?></a>
                            <?php else: ?>
                                <?= $pods->field('name') ?>
                            <?php endif; ?>
                        </td>
                        <td><?= $pods->field('offer_type') ?></td>
                        <td><?= !empty($pods->field('opening_date')) ? date("j M, Y", strtotime($pods->field('opening_date'))) : '--/--' ?></td>
                        <td><?= !empty($pods->field('closing_date')) ? date("j M, Y", strtotime($pods->field('closing_date'))) : '--/--'  ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <!-- Pagination links -->
        <?php if ($total_pages > 1) : ?>
            <div id="pagination" class="d-flex gap-4 justify-content-end">
                <button class="prev-page-btn col-4 col-md-2 col-xl-1 text-white disabled-button" style="min-width: 100px;" <?= $current_page == 1 ? 'disabled' : '' ?>>Previous</button>
                <button class="next-page-btn col-4 col-md-2 col-xl-1 bg-blue" style="min-width: 100px;" <?= $current_page == $total_pages ? 'disabled' : '' ?>> Next</button>
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
