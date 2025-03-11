<?php

/**
 * Template Name: Support Tickets
 */
get_header();

if (empty(get_current_user_id())) {
    redirect_if_not_logged_in();
}
$user_information = pods('user', get_current_user_id());
if (empty($user_information->field('isin'))) {
    redirect_if_not_logged_in();
}
// $company_logo = $user_information->field('company_logo')['guid'];
$current_user = wp_get_current_user();
$menu_company_name = '';
if (reset($current_user->roles) == "wpas_company_user") {
    $pods = pods('company')->find(['limit' => -1]);
    if ($pods->total() > 0) {
        while ($pods->fetch()) {
            if ($pods->field('isin_codes') == $user_information->field('isin')) {
                $menu_company_name = $pods->field('name');
            }
        }
    }
}

$tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'dashboard';
?>
<!-- loading screen -->
<div id="loading-screen" class="position-fixed top-0 start-0 w-100 h-100 bg-white" style="z-index: 10500; display: none">
    <div class="d-flex justify-content-center align-items-center w-100 h-100">
        <div class="spinner-border text-blue" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
</div>
<!-- main content -->
<div class="w-100">
    <div class="d-flex flex-column">
        <?= do_shortcode('[bg_image_div]'); ?>

        <div class="d-flex flex-wrap flex-md-nowrap">
            <!-- Left Menu Tabs -->
            <div class="col-md-2 pb-md-4 border-bottom request-menu" style="background: #212d45;">
                <div class="d-flex d-md-none justify-content-between justify-content-md-center align-items-center py-4 px-4">
                    <?php if (!empty($menu_company_name) && strlen((string) $menu_company_name) > 15) : ?>
                        <h6 class="d-flex align-items-center d-md-none text-white justify-content-center text-break m-0"><?= empty($menu_company_name) ? 'Request Menu' : $menu_company_name ?></h6>
                    <?php else : ?>
                        <h4 class="d-flex align-items-center d-md-none text-white justify-content-center text-break m-0"><?= empty($menu_company_name) ? 'Request Menu' : $menu_company_name ?></h4>
                    <?php endif; ?>
                    <i id="menuToggleIcon" class="bi bi-chevron-down text-white fs-3 d-md-none" data-bs-toggle="collapse" data-bs-target="#ticketMenu" aria-expanded="false" aria-controls="ticketMenu"></i>
                </div>


                <!-- Collapsible content -->
                <div class="collapse d-md-block" id="ticketMenu">
                    <div class="nav flex-column nav-pills" id="ticket-tabs" role="tablist" aria-orientation="vertical">
                        <!-- <h5 class="text-white text-center pb-4 border-bottom border-secondary">Welcome, <?= ucfirst($current_user->user_login) ?></h5> -->
                        <?php if (!in_array('wpas_company_user', $current_user->roles, true)) : ?>
                            <a class="nav-link d-flex align-items-center ps-4 <?php echo ($tab !== 'folio_information' && $tab !== 'logout'  && $tab !== 'company_information' && $tab !== 'search_folio') ? 'active' : 'inactive-menu-tab'; ?>" href="?tab=dashboard" role="tab">
                                <i class="bi bi-card-list"></i> Dashboard
                            </a>
                            <!-- <?php if (!empty(get_current_user_id())) : ?>
                                <a class="nav-link d-flex align-items-center ps-4 <?php echo $tab === 'folio_information' ? 'active' : 'inactive-menu-tab'; ?>" href="?tab=folio_information" role="tab">
                                    <i class="bi bi-info-circle"></i> Folio Information
                                </a>
                            <?php endif; ?> -->
                        <?php endif ?>
                        <?php if (in_array('wpas_company_user', $current_user->roles, true) && !empty(get_current_user_id())) : ?>
                            <a class="nav-link d-flex align-items-center ps-4 <?php echo $tab === 'company_information' ? 'active' : 'inactive-menu-tab'; ?>" href="?tab=company_information" role="tab">
                                <i class="bi bi-buildings"></i> Company Information
                            </a>
                            <a class="nav-link d-flex align-items-center ps-4 <?php echo $tab === 'search_folio' ? 'active' : 'inactive-menu-tab'; ?>" href="?tab=search_folio" role="tab">
                                <i class="bi bi-search"></i> Search Folio
                            </a>
                        <?php endif ?>
                        <a id="logout-user" class="nav-link d-flex align-items-center ps-4 <?php echo $tab === 'logout' ? 'active' : 'inactive-menu-tab'; ?>" href="#" role="tab">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="col-md-10 h-100" style="background: #dee2e6; overflow:auto;">
                <?= do_shortcode('[load_ticket_page]') ?>
            </div>
        </div>
    </div>
</div>

<?php
get_footer();
