<?php
// Ticket Info

function decode_string($string)
{
    $decoded_string = html_entity_decode($string);
    return json_decode($decoded_string, true);
}
function csp_show_edit_attachments($attachments, $admin)
{
    $edit = isset($_GET['action']) ? $_GET['action'] : '';
    $images = decode_string($attachments);
    // error_log(print_r($images, true));

    ob_start();
    $fallback_image = 'https://pragmaappscstg.wpengine.com/wp-content/uploads/2025/02/istockphoto-1209490615-612x612-1.jpg';
?>
    <div class="row">
        <?php if (empty($images) && $admin) : ?>
            <div class="col-12 pb-3">
                <div class="card">
                    <div class="position-relative">
                        <img
                            src="<?= esc_url($fallback_image) ?>"
                            class="card-img-top" alt="Not Found"
                            style="object-fit: contain; height: 170px; width: 100%;">
                        <div class="position-absolute bottom-0 start-0 end-0 text-center bg-dark text-white py-1" style="opacity: 0.8;">
                            No Attachment found
                        </div>
                    </div>
                </div>
            </div>
        <?php elseif (!empty($images) && $admin) : ?>
            <?php foreach ($images as $attachment_url): ?>
                <?php
                // Extract image name from URL
                $attachment_name = pathinfo($attachment_url, PATHINFO_FILENAME);
                ?>
                <div class="col-12 pb-3">
                    <div class="card">
                        <div class="position-relative">
                            <!-- Image -->
                            <a href="<?= esc_url($attachment_url) ?>" download>
                                <img
                                    src="<?= esc_url($attachment_url) ?>"
                                    onerror="this.onerror=null; this.src='<?= $fallback_image ?>';"
                                    class="card-img-top" alt="<?= esc_attr($attachment_name) ?>"
                                    style="object-fit: contain; height: 150px; width: 100%;">
                                <!-- Image name overlay -->
                                <div class="position-absolute bottom-0 start-0 end-0 text-center bg-dark text-white py-1" style="opacity: 0.8;">
                                    <?= esc_html($attachment_name) ?>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <?php foreach ($images as $attachment_url): ?>
                <?php
                // Extract image name from URL
                $attachment_name = pathinfo($attachment_url, PATHINFO_FILENAME);
                ?>
                <div class="col-md-3">
                    <div class="card <?= $edit == 'edit' ? 'p-0' : '' ?>">
                        <div class="position-relative">
                            <!-- Image -->
                            <a href="<?= esc_url($attachment_url) ?>" download>
                                <img
                                    src="<?= esc_url($attachment_url) ?>"
                                    onerror="this.onerror=null; this.src='<?= $fallback_image ?>';"
                                    class="card-img-top" alt="<?= esc_attr($attachment_name) ?>"
                                    style="object-fit: contain; height: 150px; width: 100%;">
                                <!-- Image name overlay -->
                                <div class="position-absolute bottom-0 start-0 end-0 text-center bg-dark text-white py-1" style="opacity: 0.8;">
                                    <?= esc_html($attachment_name) ?>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
<?php
    return ob_get_clean();
}
function csp_show_attachments($attachments)
{
    $edit = isset($_GET['action']) ? $_GET['action'] : '';
    $images = decode_string($attachments);
    // error_log(print_r($images, true));

    ob_start();
    $fallback_image = 'https://pragmaappscstg.wpengine.com/wp-content/uploads/2025/02/istockphoto-1209490615-612x612-1.jpg';
?>
    <div class="d-flex flex-wrap mb-3">
        <?php foreach ($images as $attachment_url): ?>
            <?php
            // Extract image name from URL
            $attachment_name = pathinfo($attachment_url, PATHINFO_FILENAME);
            ?>
            <div class="pe-2 pe-md-4">
                <div class="card <?= $edit == 'edit' ? 'p-0' : '' ?>" style="height: 70px; width: 100px;">
                    <div class="position-relative">
                        <!-- Image -->
                        <a href="<?= esc_url($attachment_url) ?>" download>
                            <img
                                src="<?= esc_url($attachment_url) ?>"
                                onerror="this.onerror=null; this.src='<?= $fallback_image ?>';"
                                class="card-img-top" alt="<?= esc_attr($attachment_name) ?>"
                                style="object-fit: contain; height: 70px; width: 100%;">
                            <!-- Image name overlay -->
                            <!-- <div class="position-absolute bottom-0 start-0 end-0 text-center bg-dark text-white py-1" style="opacity: 0.8;">
                                <?= esc_html($attachment_name) ?>
                            </div> -->
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php
    return ob_get_clean();
}
// Holder HTML
function holder_html($name, $pan, $aadhar, $uid, $holder)
{
    error_log("");
    ob_start();
?>
    <div class="border-top <?= $holder != 1 ? 'border-start' : '' ?> remove-border-start d-flex flex-column p-3 col-md-4 col-12">
        <div class="border-bottom holder-border mb-md-2">
            <p class="h5"><?= $holder ?>. <?= $name ?></p>
        </div>
        <?php if (!empty($name)) : ?>
            <div class="d-flex justify-content-between flex-wrap mb-md-1">
                <!-- <div class="d-flex align-items-center gap-3 gap-md-2">
                    <i class="bi bi-person-fill fs-4 text-warning"></i>
                    <p class="<?= strlen($name) > 15 ? 'h6' : 'h5' ?> m-0 holder-name-font"><?= $name ?? 'N/A'; ?></p>
                </div> -->
                <div class="d-flex align-items-center gap-3 gap-md-2 ms-1">
                    <i class="bi bi-credit-card text-info" data-toggle="tooltip" data-placement="top" title="PAN"></i>
                    <p class="m-0 text-break text-wrap"><?= !empty($pan) ? $pan : 'N/A'; ?></p>
                </div>
                <div class="d-flex align-items-center gap-3 gap-md-2 ms-1">
                    <i class="bi bi-person-vcard text-info" data-toggle="tooltip" data-placement="top" title="Aadhaar Number"></i>
                    <p class="m-0 text-break text-wrap"><?= !empty($aadhar) ? esc_html($aadhar) : 'N/A'; ?></p>
                </div>
            </div>
            <div class="d-flex justify-content-between flex-wrap">
                <div class="d-flex align-items-center gap-3 gap-md-2 ms-1">
                    <i class="bi bi-person-lines-fill text-info" data-toggle="tooltip" data-placement="top" title="UUID"></i>
                    <p class="m-0 text-break text-wrap"><?= !empty($uid) ? esc_html($uid) : 'N/A'; ?></p>
                </div>

            </div>
        <?php else : ?>
            <div class="d-flex flex-md-column">
                <div class="d-flex align-items-center mb-1 gap-3 gap-md-2  col-7 col-md-12">
                    <i class="bi bi-person-fill fs-4 text-warning"></i>
                    <p class="h5 m-0">N/A</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php
    return ob_get_clean();
}
// Certificate HTML
function certificate_html($certificate_data)
{
    ob_start();
?>
    <div class="d-md-flex d-none flex-column table-ticket-list">
        <?php if (!empty($certificate_data)) : ?>
            <div class='d-flex px-2 py-2 shadow-md align-items-center rounded-top mt-2' style='background: #212d45; color: white;'>
                <div class='col-1 d-flex justify-content-center' style='border-right: 1px solid #f8f9fa;'>S No.</div>
                <div class='col-3 d-flex justify-content-center' style='border-right: 1px solid #f8f9fa;'>Certificate Info</div>
                <div class='col-3 d-md-flex justify-content-center d-none' style='border-right: 1px solid #f8f9fa;'>Distinctive Number</div>
                <div class='col-2 d-flex justify-content-center' style='border-right: 1px solid #f8f9fa;'>Shares</div>
                <div class='col-3 d-flex justify-content-center'>Active</div>
                <!-- <div class='col-3 col-md-2 d-flex justify-content-center'>Status</div> -->
            </div>
            <?php foreach ($certificate_data as $index => $certificate) : ?>
                <?php $backgroundColor = $index % 2 == 0 ? "#f8f9fa" : "#e0e0e0" ?>
                <div class='d-flex px-2 py-2 shadow-md align-items-center' style='background: <?= $backgroundColor ?> ";'>
                    <div class='col-1 d-flex justify-content-center'><?= ++$index ?></div>
                    <div class='col-3 d-flex justify-content-center'><?= $certificate['cert'] ?></div>
                    <div class='col-3 d-md-flex d-none justify-content-center'>
                        <p class="m-0"><?= $certificate['start_dist'] ?></p> - <p class="m-0"><?= $certificate['end_dist'] ?></p>
                    </div>
                    <div class='col-2 d-flex justify-content-center'><?= $certificate['shares'] ?></div>
                    <div class='col-3 d-flex justify-content-center'>
                        <?php if ($certificate['inactive']) : ?>
                            <span class="badge bg-danger text-end ms-3 fit-content">
                                <i class="bi bi-bookmark-x-fill"></i> Inactive
                            </span>
                        <?php else : ?>
                            <span class="badge bg-success text-end ms-3 fit-content">
                                <i class="bi bi-bookmark-check-fill"></i> Active
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <div class="alert alert-info mt-2" role="alert">
                No Certificate Found!
            </div>
        <?php endif ?>
    </div>
    <?php if (!empty($certificate_data)) : ?>
        <?php foreach ($certificate_data as $index => $row) : ?>
            <div class="card flex-fill shadow-md border-0 rounded d-md-none f-flex mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-journal-check text-warning"></i>
                            <p class="h5 m-0">Certificate <?= ++$index; ?></p>
                        </div>
                        <div>
                            <?php if ($row['inactive']) : ?>
                                <span class="badge bg-danger text-end ms-3 fit-content">
                                    <i class="bi bi-patch-exclamation-fill"></i> Inactive
                                </span>
                            <?php else : ?>
                                <span class="badge bg-success text-end ms-3 fit-content">
                                    <i class="bi bi-patch-check-fill"></i> Active
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-file-earmark-text text-info" data-toggle="tooltip" data-placement="top" title="Certificate Number"></i>
                            <p class="m-0 text-break text-wrap"><?= !empty($row['cert']) ? esc_html($row['cert']) : 'N/A'; ?></p>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-graph-up text-info" data-toggle="tooltip" data-placement="top" title="Total no. of shares"></i>
                            <p class="m-0 text-break text-wrap"><?= !empty($row['shares']) ? esc_html($row['shares']) : 'N/A'; ?></p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-1 gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-plus-slash-minus text-info" data-toggle="tooltip" data-placement="top" title="Distinctive Number"></i>
                            <p class="m-0 text-break text-wrap">
                                <?= !empty($row['start_dist']) ? esc_html($row['start_dist']) : 'N/A'; ?> - <?= !empty($row['end_dist']) ? esc_html($row['end_dist']) : 'N/A'; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else : ?>
        <div class="alert alert-info mt-2 d-md-none" role="alert">
            No Certificate Found!
        </div>
    <?php endif; ?>
<?php
    return ob_get_clean();
}

// Error HTML
function error_html()
{
    return '<div class="alert alert-info m-5" role="alert">
                    Oops! The server is currently unavailable. Please try <span id="reloading-window" class="text-warning" style="cursor:pointer;">reloading</span> the page. If the issue persists, please contact the administrator.
                </div>';
}
