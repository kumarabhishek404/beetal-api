<?php

function custom_pods_admin_ticket()
{
    $tab = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'ticket';
    switch ($tab) {
        case 'edit':
            echo show_edit_ticket();
            break;
        default:
            echo show_admin_ticket();
            break;
    }
}

function edit_ticket_using_id_fn()
{
    $ticket_id = $_POST['id'];
    $params_data = [
        'service_request' => $_POST['service_request'],
        'subject' => $_POST['subject'],
        'sub_services' => $_POST['sub_services'],
        'status' => $_POST['status'],
        'modified' => $_POST['modified'],
        'post_content' => $_POST['ticket_description'],
        'assign_to' => $_POST['assign_to'],
    ];
    error_log(print_r($params_data, true));
    $ticket_pods = pods('ticket', $ticket_id);
    $pods_response = $ticket_pods->save($params_data, null, $ticket_id);
    wp_send_json_success($pods_response);
}

function delete_ticket_using_id_fn()
{
    $ticket =
        sanitize_text_field($_POST['id']);
    $ticket_pods = pods('ticket', $ticket);
    $ticket_pods->delete($ticket);
    wp_send_json_success(site_url('/wp-admin/admin.php?page=pods-manage-ticket'));
}

function show_admin_ticket()
{
    $pods = pods('ticket')->find([
        'limit' => -1,
        'orderby' => 'created DESC',
    ]);
    if ($pods->total() < 1) {
        echo "  <div class='mx-md-5 mx-4 alert alert-danger mt-4' >
                            No Data Available
                        </div>";
        return;
    }
    ob_start();
?>
    <div class="p-4 pe-5">
        <div class="d-flex justify-content-start">
            <h4>Manage Tickets</h4>
        </div>
        <div class="d-flex justify-content-end gap-1">
            <input type="search" placeholder="" />
            <button type="button">Search Tickets</button>
        </div>

        <table class="tax-forms-table w-100 mt-5 table-bordered table-striped d-none d-md-table">
            <thead class="">
                <tr>
                    <th class="text-center col-1">#</th>
                    <th class="text-start col-6">Subject</th>
                    <th class="text-start col-2">Posted On</th>
                    <th class="text-start col-1">Author</th>
                    <th class="text-start col-1">Status</th>
                    <th class="text-start col-1">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $sno = 1; ?>
                <?php while ($pods->fetch()) : ?>
                    <tr>
                        <td class="text-center col-1"><?= $sno++; ?></td>
                        <td class="text-start col-6">
                            <?= $pods->field('subject'); ?>
                        </td>
                        <td class="text-start col-2"><?= date('d M, Y', strtotime($pods->field('created'))); ?></td>
                        <?php
                        $status_badge = '';
                        switch ($pods->field('status')) {
                            case 'new':
                                $status_badge = '<span class="badge bg-primary">New</span>';
                                break;
                            case 'New':
                                $status_badge = '<span class="badge bg-primary">New</span>';
                                break;
                            case 'Open':
                                $status_badge = '<span class="badge bg-warning text-dark">In Progress</span>';
                                break;
                            case 'On Hold':
                                $status_badge = '<span class="badge bg-danger">On Hold</span>';
                                break;
                            case 'Closed':
                                $status_badge = '<span class="badge bg-success">Closed</span>';
                                break;
                            default:
                                $status_badge = '<span class="badge bg-secondary">' . $pods->field('status') . '</span>';
                                break;
                        }
                        ?>
                        <td class="text-start col-1">
                            <?php $roles = get_userdata($pods->field('post_author'))->roles; ?>
                            <?php $user_type = (!empty($roles) && $roles[0] == 'wpas_company_user') ? 'Company' : 'Investor' ?>
                            <?= $user_type; ?>
                        </td>
                        <td class=" col-1 text-start"><?= $status_badge; ?></td>
                        <td class="text-start col-1">
                            <div class="btn-group d-flex justify-content-between" role="group" aria-label="Actions">
                                <a class="text-decoration-none" href="?page=pods-manage-ticket&action=edit&ticket_id=<?= $pods->field('id'); ?>"><span class="d-flex badge badge-light"><i class="bi bi-pencil-fill p-1"></i> </span></a>
                                <span onclick="deleteTicketFn('<?= $pods->field('id'); ?>')" class="d-flex badge badge-danger"><i class="bi bi-trash-fill p-1"></i> </span>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

    </div>
<?php
    echo ob_get_clean();
}

function show_edit_ticket()
{
    $id = isset($_GET['ticket_id']) ? sanitize_text_field($_GET['ticket_id']) : 0;

    $pods = pods('ticket', $id);
    error_log('Edit Tickets: ' . print_r($pods->row(), true));
    if (empty($pods->row())) {
        echo "<div class='mx-md-5 mx-4 alert alert-danger mt-4' >
                            No Data Available
                        </div>";
        return;
    }
    ob_start();
?>
    <div class="p-4 pe-5">
        <div class="d-flex justify-content-start">
            <h4>Edit Ticket</h4>
        </div>
        <div class="message-div"></div>
        <div class="d-flex mt-4">
            <form id="edit-ticket-form" class="col-9">
                <input type="hidden" name="action" value="edit_ticket_using_id">
                <input type="hidden" name="id" value="<?= $pods->field('id'); ?>">
                <div class="card-body border p-4 rounded">

                    <div class="mb-3 col-12">
                        <label for="name" class="form-label required-label">Name</label>
                        <input type="text" name="name" class="form-control" id="name" value="<?= $pods->field('name'); ?>" />
                    </div>
                    <div class="mb-3 col-12">
                        <label for="subject" class="form-label required-label">Subject</label>
                        <input type="text" name="subject" class="form-control" id="subject" value="<?= str_replace('\\', '', $pods->field('subject')) ?>" />
                    </div>
                    <div class="mb-3 col-12">
                        <label for="service_request" class="form-label required-label">Service Request Type</label>
                        <input type="text" class="form-control" name="service_request" id="service_request" value="<?= $pods->field('service_request'); ?>" />
                    </div>
                    <?php if (!empty($pods->field('sub_services')) && $pods->field('sub_services') != 'null') : ?>
                        <div class="mb-3 col-12">
                            <label for="sub_services" class="form-label required-label">Sub Service Type</label>
                            <input type="text" class="form-control" name="sub_services" id="sub_services" value="<?= $pods->field('sub_services'); ?>" />
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="ticket_description" class="form-label required-label">Description</label>
                        <textarea name="ticket_description" class="form-control" rows="5" required><?= $pods->field('post_content'); ?></textarea>
                    </div>

                    <div class="d-flex">
                        <div class="mb-3 col-12 col-md-6">
                            <div class="d-flex mx-1 flex-column">
                                <label for="folio_number" class="form-label required-label">Folio Number</label>
                                <input type="text" class="form-control" name="folio_number" id="folio_number" value="<?= $pods->field('folio_number'); ?>" />
                            </div>
                        </div>
                        <div class="mb-3 col-12 col-md-6">
                            <div class="d-flex mx-1 flex-column">

                                <label for="company" class="form-label required-label">Company</label>
                                <input type="text" class="form-control" name="company" id="company" value="<?= $pods->field('company'); ?>" />
                            </div>
                        </div>
                    </div>
                    <div class="d-flex">
                        <div class="mb-3 col-12 col-md-6">
                            <div class="d-flex mx-1 flex-column">

                                <label for="email" class="form-label required-label">Email</label>
                                <input type="email" class="form-control" name="email" id="email" value="<?= $pods->field('email'); ?>" />
                            </div>
                        </div>
                        <div class="mb-3 col-12 col-md-6">
                            <div class="d-flex mx-1 flex-column">

                                <label for="phone" class="form-label required-label">Phone</label>
                                <input type="number" class="form-control" name="phone" id="phone" value="<?= $pods->field('phone'); ?>" />
                            </div>
                        </div>
                    </div>
                    <div class="d-flex">
                        <div class="mb-3 col-12 col-md-6">
                            <div class="d-flex mx-1 flex-column">
                                <label for="assign_to" class="form-label required-label">Assign To</label>
                                <select name="assign_to" class="form-select form-control" required>
                                    <option value="">Select</option>
                                    <?php $user_pods = pods('user')->find(['limit' => -1]);
                                    // error_log("Users: " . print_r($user_pods->data(), true));
                                    while ($user_pods->fetch()) : ?>
                                        <?php $selected = $user_pods->field('ID') == $pods->field('assign_to') ? 'selected' : '' ?>
                                        <option value="<?= $user_pods->field('ID') ?>" <?= $selected ?>><?= $user_pods->field('display_name') ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3 col-12 col-md-6">
                            <div class="d-flex mx-1 flex-column">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" class="form-select form-control" required>
                                    <option value="new" <?= $pods->field('status') == 'New' ? 'selected' : '' ?>>New</option>
                                    <option value="open" <?= $pods->field('status') == 'open' ? 'selected' : '' ?>>Open</option>
                                    <option value="hold" <?= $pods->field('status') == 'hold' ? 'selected' : '' ?>>On Hold</option>
                                    <option value="closed" <?= $pods->field('status') == 'closed' ? 'selected' : '' ?>>Closed</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <input type="text" name="modified" class="form-control hidden" style="display: none;" value="<?= date('Y-m-d H:i:s') ?>">
                    <!-- <button type="submit" name="csp_submit_ticket" class="btn btn-warning p-2 mt-1">Save</button> -->
                </div>
            </form>
            <div class="col-3 ms-3 d-flex flex-column">
                <div class="d-flex flex-column border rounded " style="height:fit-content;">
                    <div class="card-header border-bottom px-3 py-2">Manage</div>
                    <div class="card-body px-3 py-3">
                        <div class="d-flex gap-2">
                            <p class=""><i class="bi bi-calendar3"></i> Created On:</p>
                            <p class="h6"><?= date('d M, Y', strtotime($pods->field('created'))); ?></p>
                        </div>
                        <div class="d-flex gap-2">
                            <p class="mb-0"><i class="bi bi-calendar3"></i> Last Modified:</p>
                            <p class="h6 mb-0"><?= date('d M, Y', strtotime($pods->field('modified'))); ?></p>
                        </div>
                    </div>
                    <div class="card-footer px-3 py-2 d-flex border-top justify-content-between align-items-center">
                        <p onclick="deleteTicketFn('<?= $pods->field('ID'); ?>')" class="text-danger text-decoration-underline mb-0 h6 fw-normal">Delete</p>
                        <button type="submit" id="csp_ticket_edit_fn" class="btn btn-blue py-1 px-3">Update Ticket</button>
                    </div>
                </div>
                <div class="d-flex flex-column border rounded mt-3" style="height:fit-content;">
                    <div class="d-flex card-header border-bottom px-3 py-2">
                        <!-- <i class="bi bi-paperclip me-2"></i> -->
                        Attachments (<?= count(decode_string($pods->field('files'))) ?>)
                    </div>
                    <!-- <hr class="mb-1 mt-2"> -->
                    <div class="px-3" style="max-height: 646.5px; overflow: auto;">
                        <?=
                        csp_show_edit_attachments($pods->field('files'), true) ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-4 ticket-reply">
            <?= csp_ticket_reply_form($pods->field('files')); ?>
        </div>
    </div>
<?php
    echo ob_get_clean();
}
