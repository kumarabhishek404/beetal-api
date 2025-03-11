<?php

/**
 * Plugin Name: My CSV Plugin
 * Description: A plugin for uploading and processing CSV files in WordPress.
 * Version:     1.0
 * License:     GPLv2 or later
 * Text Domain: my-csv-plugin 
 * Description: Upload CSV file.
 * Version: 1.0
 * Author: Pragmaapps
 */

// Include necessary files
require_once plugin_dir_path(__FILE__) . 'includes/admin.php';

function custom_enqueue_scripts()
{
    wp_enqueue_style('custom-style', 'https://lsstaging15.wpenginepowered.com/wp-content/themes/enfold-child/lsq-elements/lsq-animation-testimonial/css/lsq-animation-testimonial-4.css?ver=1.2');
    wp_enqueue_script('custom-script', 'https://lsstaging15.wpenginepowered.com/wp-content/themes/enfold-child/lsq-elements/lsq-slick-slider/js/slick.min.js?ver=6.7.1', array('jquery'), null, true);
    wp_enqueue_script('custom-script2', 'https://lsstaging15.wpenginepowered.com/wp-content/themes/enfold-child/lsq-elements/lsq-animation-testimonial/js/lsq-animation-testimonial-2.js?ver=6.7.1', array('jquery'), null, true);
    wp_enqueue_script('slick-js', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js', array('jquery'), null, true);
    wp_enqueue_style('slick-css', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.css');
    wp_enqueue_style('slick-theme-css', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.css');
}
add_action('wp_enqueue_scripts', 'custom_enqueue_scripts');


wp_enqueue_style('upload-csv-css', plugin_dir_url(__FILE__) . 'assets/style.css');
wp_enqueue_script('upload-csv-script', plugin_dir_url(__FILE__) . 'assets/script.js', array('jquery'), '1.0.2', true);
wp_enqueue_style('csp-bootstrap', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css');
// Register the plugin's settings
add_action('admin_init', 'my_csv_plugin_settings_init');

add_action('admin_post_my_csv_plugin_upload', 'my_csv_plugin_handle_upload');
add_action('admin_post_nopriv_my_csv_plugin_upload', 'my_csv_plugin_handle_upload');

// Add the plugin's menu
add_action('admin_menu', 'my_csv_plugin_menu');


function my_csv_plugin_handle_upload()
{
    ob_start();
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    if (!empty($_FILES['my_csv_plugin_uploaded_file'])) {
        //  echo '<div id="loading-overlay"><div class="loading-spinner"></div></div>';
        // flush();
        $upload_dir = wp_upload_dir();
        $file_path = $upload_dir['path'] . '/' . basename($_FILES['my_csv_plugin_uploaded_file']['name']);

        if (move_uploaded_file($_FILES['my_csv_plugin_uploaded_file']['tmp_name'], $file_path)) {
            // Process the CSV file
            $csv_file = fopen($file_path, 'r');
            $data = array();
            $csv_row_data = array();
            $header = fgetcsv($csv_file); // Get header row
            $my_pod = pods('ipo_allotment_data');
            // $pod_items = $my_pod->find(['limit' => -1]);
            //     if($pod_items->total()>0){
            //     while ( $pod_items->fetch() ) {
            //         $my_pod->delete( $pod_items->field( 'id' ) );
            //     }
            // }
            $pods_header = [
                'company',
                'name',
                'application_number',
                'order_number',
                'client_id',
                'application_amount',
                'applied_shares',
                'alloted_shares',
                'amount_reference',
                'remarks',
                'pan_number'
            ];
            $sno = 0;
            while (($row = fgetcsv($csv_file)) !== FALSE) {
                foreach ($pods_header as $i => $key) {
                    $csv_row_data[$key] = (string) $row[$i];
                }
                $my_pod->add($csv_row_data);

                error_log("Count => " . print_r($csv_row_data, true));
            }
            fclose($csv_file);

            // echo '<script>document.getElementById("loading-overlay").style.display = "none";</script>';
            ob_end_clean();
            wp_redirect(admin_url('admin.php?page=upload-IPO-allotment-data&message=success'));
            exit;
            // echo '<div class="notice notice-success"><p>CSV file uploaded and data imported into Pods successfully!</p></div>';
        } else {
            ob_end_clean();
            wp_redirect(admin_url('admin.php?page=upload-IPO-allotment-data&message=error'));
            exit;
        }
    } else {
        ob_end_clean();
        wp_redirect(admin_url('admin.php?page=upload-IPO-allotment-data&message=error'));
        exit;
    }
}

add_shortcode('slide_text', function () {
    $atts = [
        "content" => [
            [
                'redirect' => site_url('/rta-services'),
                'title' => 'RTA Services'
            ],
            [
                'redirect' => site_url('/investor'),
                'title' => 'Investor Services'
            ]
        ]
    ];
    ob_start();
?>
    <div class="text-carousel">
        <?php foreach ($atts['content'] as $attachment) : ?>
            <a href="<?= $attachment['redirect'] ?>" class="d-flex align-items-center carousel-item text-decoration-none" style="color: #F7BB4F !important;">
                <h4 class="m-0 title-text-link" style="color: #F7BB4F !important;"><?= $attachment['title'] ?></h4>
                <i class="fa fa-arrow-circle-right fs-2 mx-3" aria-hidden="true"></i>
            </a>
        <?php endforeach; ?>
    </div>
<?php
    return ob_get_clean();
});

add_shortcode('home_show_company', function () {
    $atts['content'] = [
        [
            'attr' => [
                'attachment' => [
                    'https://pragmaappscstg.wpengine.com/wp-content/uploads/2025/01/kwality.webp',
                    'https://pragmaappscstg.wpengine.com/wp-content/uploads/2025/01/sbecsugar.png',
                    'https://pragmaappscstg.wpengine.com/wp-content/uploads/2025/01/marvel.png',
                    'https://pragmaappscstg.wpengine.com/wp-content/uploads/2025/01/apl-apollo-logo.png',
                    'https://pragmaappscstg.wpengine.com/wp-content/uploads/2025/01/logo-1-1.png',
                    'https://pragmaappscstg.wpengine.com/wp-content/uploads/2025/01/mayur_1-1.png'
                ]
            ],
        ],
        [
            'attr' => [
                'attachment' => [
                    'https://pragmaappscstg.wpengine.com/wp-content/uploads/2025/01/logo-1-1.png',
                    'https://pragmaappscstg.wpengine.com/wp-content/uploads/2025/01/mayur_1-1.png',
                    'https://pragmaappscstg.wpengine.com/wp-content/uploads/2025/01/marvel.png',
                    'https://pragmaappscstg.wpengine.com/wp-content/uploads/2025/01/apl-apollo-logo.png',
                    'https://pragmaappscstg.wpengine.com/wp-content/uploads/2025/01/kwality.webp',
                    'https://pragmaappscstg.wpengine.com/wp-content/uploads/2025/01/sbecsugar.png'
                ],
            ],
        ]
    ];

    ob_start();
?>
    <div class="animation-testimonial">
        <div class="animation-testimonial-inner">
            <div class="bg-shape" style="z-index: 0 !important;"></div>
            <div class="animation-testimonial-right-contents margin-left-div">
                <?php foreach ($atts['content'] as $animationSlider) {
                    $attachment = $animationSlider['attr']['attachment'];
                ?>
                    <div class="animation-testimonial-right-contents-rows">
                        <div class="animation-testimonial-c-position">
                            <div class="d-flex flex-column margin-left-div">
                                <h3 class="mb-0 text-center">Trust and Worth</h3>
                                <h2 class="text-center">400+ Clients</h2>
                            </div>
                            <div class="d-flex flex-wrap margin-left-div">
                                <?php foreach ($attachment as $attachment) : ?>
                                    <div class="col-4 p-4">
                                        <div class="d-flex justify-content-center align-items-center w-100 h-100">
                                            <img src="<?= $attachment ?>" alt="img" style="mix-blend-mode: multiply;" />
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
<?php
    return ob_get_clean();
});
