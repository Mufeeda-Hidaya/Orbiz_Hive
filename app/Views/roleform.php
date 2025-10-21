<?php include "common/header.php"; ?>
<div class="alert d-none text-center position-fixed" role="alert"></div>
<div class="form-control right_container">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="mb-0"><?= isset($role['role_id']) ? 'Edit Role' : 'Add New Role and Permissions' ?></h3>
        </div>
        <div class="card-body">
            <form id="roleForm" action="<?= base_url('rolemanagement/store') ?>" method="post" class="p-3">
                <input type="hidden" name="role_id" id="role_id" value="<?= isset($role['role_id']) ? esc($role['role_id']) : '' ?>">
                <div class="mb-3">
                    <label for="role_name" class="form-label">Role Name <span class="text-danger">*</span></label>
                    <input type="text" name="role_name" id="role_name" class="form-control capitalize"
                        value="<?= isset($role['role_name']) ? esc($role['role_name']) : '' ?>" required>
                </div>

                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0">Permissions</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="select_all_permissions">
                            <label class="form-check-label fw-bold" for="select_all_permissions">Select All</label>
                        </div>
                        <div class="row">
                            <?php foreach ($menus as $menuKey => $menuLabel): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input permission-checkbox" type="checkbox"
                                            name="access[<?= $menuKey ?>]" value="1" id="perm_<?= $menuKey ?>"
                                            <?= isset($access[$menuKey]) && $access[$menuKey] == 1 ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="perm_<?= $menuKey ?>">
                                            <?= esc($menuLabel) ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="col-12 d-flex justify-content-end gap-2">
                    <a href="<?= base_url('rolemanagement/rolelist') ?>" class="btn btn-secondary">Discard</a>
                    <button type="submit" class="btn btn-primary enter-btn" id="saveBtn">Save Role</button>
                </div>
            </form>
        </div>
    </div>
</div>
                            </div>
<?php include "common/footer.php"; ?>


<script>
$(document).ready(function () {
    const $saveBtn = $('.enter-btn');
    const $form = $('#roleForm');
    const originalData = {
        roleName: $('#role_name').val().trim(),
        checkboxes: {}
    };

    $('input.form-check-input').each(function () {
        originalData.checkboxes[$(this).attr('id')] = $(this).prop('checked');
    });

    const isEdit = $('#role_id').val().trim() !== '';

    if (isEdit) {
        $saveBtn.prop('disabled', true).css({ opacity: 0.6, pointerEvents: 'none' });
    }

    $('#select_all_permissions').on('change', function () {
        $('.permission-checkbox').prop('checked', this.checked).trigger('change');
    });

    $('.permission-checkbox').on('change', function () {
        const allChecked = $('.permission-checkbox').length === $('.permission-checkbox:checked').length;
        $('#select_all_permissions').prop('checked', allChecked);
    });

    const total = $('.permission-checkbox').length;
    const checked = $('.permission-checkbox:checked').length;
    $('#select_all_permissions').prop('checked', total === checked);

    function checkIfChanged() {
        const currentName = $('#role_name').val().trim();
        let changed = currentName !== originalData.roleName;

        $('input.permission-checkbox').each(function () {
            const id = $(this).attr('id');
            if ($(this).prop('checked') !== originalData.checkboxes[id]) {
                changed = true;
            }
        });

        if (isEdit) {
            if (changed) {
                $saveBtn.prop('disabled', false).css({ opacity: 1, pointerEvents: 'auto' });
            } else {
                $saveBtn.prop('disabled', true).css({ opacity: 0.6, pointerEvents: 'none' });
            }
        }
    }

    $('#role_name').on('input', checkIfChanged);
    $('.permission-checkbox').on('change', checkIfChanged);

    $saveBtn.on('click', function (e) {
        e.preventDefault();

        let roleName = $('#role_name').val().trim();
        let role_id = $('#role_id').val().trim();

        if (!roleName.match(/[a-zA-Z]/)) {
            showMessage('Role name must contain at least one letter.', 'danger');
            return;
        }

        $saveBtn.prop('disabled', true).css({ opacity: 0.6, pointerEvents: 'none' });

        const form = $form[0];
        const formData = new FormData(form);

        $.ajax({
            url: role_id ? '<?= base_url('rolemanagement/update') ?>/' + role_id : '<?= base_url('rolemanagement/store') ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                if (response.status === 'error') {
                    showMessage(response.message, 'danger');
                    $saveBtn.prop('disabled', false).css({ opacity: 1, pointerEvents: 'auto' });
                } else {
                    showMessage(response.message, 'success');
                    setTimeout(() => {
                        $('.alert').fadeOut();
                        window.location.href = "<?= base_url('rolemanagement/rolelist') ?>";
                    }, 2000);
                }
            },
            error: function () {
                showMessage('Something Went wrong. Please Try Again.', 'danger');
                $saveBtn.prop('disabled', false).css({ opacity: 1, pointerEvents: 'auto' });
            }
        });
    });

    function showMessage(msg, type) {
        $('.alert')
            .removeClass('d-none alert-danger alert-success alert-warning')
            .addClass('alert-' + type)
            .html(msg)
            .fadeIn();

        setTimeout(function () {
            $('.alert').fadeOut();
        }, 3000);
    }
});
</script>

