<?php

add_shortcode('load_ticket_page', function () {
$tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'dashboard';

        switch ($tab) {

            case 'information':
                echo '<div class="bg-white">';
                echo do_shortcode('[csp_ticket_information]');
                echo '</div>';
                break;

            case 'submit_ticket':
                echo '<div class="bg-white h-100 rounded-0 border-0 px-4 pt-4 px-md-5">';
                echo do_shortcode('[breadcrumbs dashboard_url="' . site_url('/investor-service-request') . '" dashboard_label="Dashboard" ticket_name="Add a request"]');
                echo '<h2 class="py-2 mb-0">Raise a Request</h2> <hr>';
                echo do_shortcode('[csp_ticket_form]');
                echo '</div>';
                break;

            case 'folio_information':
            // echo '<h2>Folio Information</h2> <hr>';
            // echo do_shortcode('[folio_summary]');
            echo do_shortcode('[profile_card]');

                break;

            case 'activities':
                echo '<div class="px-md-4 px-1 bg-white rounded py-4 border-0">';
                echo '<div class="d-flex justify-content-between align-items-center px-2">
                            <h2>My Requests</h2> 
                            <a class="nav-link bg-blue rounded px-3 py-2 d-flex align-items-center justify-content-center" href="?tab=submit_ticket" role="tab">
                        <i class="bi bi-plus-lg me-1" style=" font-weight: bolder;"></i>New Request
                    </a>
                        </div> <hr>';
                echo do_shortcode('[csp_ticket_list2]');
                echo '</div>';
                break;

            case 'search_folio':
                echo do_shortcode('[show_search_folio]');
                break;

            case 'company_information':
                // echo '<h2>Folio Information</h2> <hr>';
                // echo do_shortcode('[folio_summary]');
                echo do_shortcode('[company_profile]');
                break;

            case 'dashboard':
            default:
                echo '<div class="h-100 w-100 px-md-4 px-1 rounded-0 py-4 border-0">';
                echo '<div class="d-flex justify-content-between align-items-center px-4 px-md-2">
                            <h2>Dashboard</h2> 
                            <a class="nav-link bg-blue rounded px-3 py-2 d-flex align-items-center justify-content-center" href="?tab=submit_ticket#Investor_Service_Request" role="tab">
                        <i class="bi bi-plus-lg me-1" style=" font-weight: bolder;"></i>New Request
                    </a>
                        </div> <hr>';
                echo do_shortcode('[csp_ticket_list]');
                echo '</div>';
                break;
        }
});
