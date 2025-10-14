<div class="container-fluid py-2">
    <div class="my-3"></div>
    <div class="row">
        <div class="col-12">
        <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
            <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center">
                <h5 class="text-white text-capitalize ps-3 mb-0">Add New Role and Permissions</h5>
                </div>
                    <!-- <h3 class="mb-0"><?= isset($role['role_id']) ? 'Edit Role' : 'Add New Role and Permissions' ?></h3> -->
                </div>
                <div id="messageBox" class="alert d-none text-center" role="alert"></div>
                <div class="card-body">
                    <form id="roleForm" action="<?= base_url('admin/manage_roles/save') ?>" method="POST">
                        <?= csrf_field() ?>
                        <input type="hidden" name="role_id" id="role_id" value="">
                        <div class="mb-3">
                            <label for="role_name">Role Name</label>
                            <input type="text" name="role_name" id="role_name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <input type="checkbox" id="select_all_permissions"> Select All<br>
                            <input type="checkbox" name="menus[]" value="Dashboard" class="permission-checkbox"> Dashboard<br>
                            <input type="checkbox" name="menus[]" value="Manage Role" class="permission-checkbox"> Manage Role<br>
                            <input type="checkbox" name="menus[]" value="Manage Admin User" class="permission-checkbox"> Manage Admin User<br>
                        </div>

                        <div class="col-12 d-flex justify-content-end gap-2">
                            <a href="<?= base_url('admin/manage_roles') ?>" class="btn btn-secondary">Discard</a>
                            <button type="submit" class="btn btn-primary enter-btn" id="saveBtn">Save Role</button>
                        </div>
                    </form>
                    <div id="messageBox" class="alert d-none"></div>

                </div>

            </div>
        </div>
    </div>
</div
