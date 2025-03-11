<?php

function my_csv_plugin_menu()
{
    add_menu_page(
        'Upload IPO Allotment Data', // Page title
        'Upload IPO Allotment Data', // Menu title
        'read', // Capability
        'upload-IPO-allotment-data', // Menu slug
        'my_csv_plugin_options_page', // Callback function
        'dashicons-media-text', // Icon (optional)
        50                 // Position (optional)
    );
}

function my_csv_plugin_options_page()
{
    $message = isset($_GET['message']) ? $_GET['message'] : '';
    if ($message === 'success'): ?>
        <div class="notice notice-success is-dismissible">
            <p><strong>CSV file uploaded and data imported into IPO Allotment data successfully!. To view the data <a href="<?= admin_url('admin.php?page=pods-manage-ipo_allotment_data'); ?>">click here</a></strong></p>
        </div>
    <?php elseif ($message === 'error'): ?>
        <div class="notice notice-error is-dismissible">
            <p><strong>A file is required for this action. Please choose a file to upload.</strong></p>
        </div>
    <?php endif;
    ?>
    <div class="wrap">
        <div id="loading-overlay" style="display:none;">
            <div class="loading-spinner"></div>
        </div>
        <h1 style="margin-bottom: 40px;">Upload IPO Allotment Data</h1>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
            <?php wp_nonce_field('my_csv_plugin_upload_action', 'my_csv_plugin_nonce'); ?>
            <input type="hidden" name="action" value="my_csv_plugin_upload" required>
            <div class="form-group">
                <label for="csv_file">Select CSV File:</label>
                <input type="file" id="csv_file" name="my_csv_plugin_uploaded_file" class="form-control p-2 mt-2">
            </div>
            <button type="submit" class="btn btn-primary mt-4">Upload and Import</button>
        </form>
    </div>

    <?php
    if (isset($_POST['submit']) && check_admin_referer('my_csv_plugin_upload_action', 'my_csv_plugin_nonce')) {
        my_csv_plugin_handle_upload();
    }
    ?>
<?php
}

function my_csv_plugin_settings_init()
{
    // register_setting('my-csv-plugin-group', 'my_csv_plugin_uploaded_file');

    // add_settings_section(
    //     'my_csv_plugin_section_id',
    //     'CSV File Upload',
    //     '__return_empty_string', // No callback for this section
    //     'my-csv-plugin'
    // );

    // add_settings_field(
    //     'my_csv_plugin_file_id',
    //     'Upload CSV File:',
    //     'my_csv_plugin_file_field_callback',
    //     'my-csv-plugin',
    //     'my_csv_plugin_section_id'
    // );
}
