<div class="container-fluid py-2">
  <div class="my-3"></div>
  <div class="row">
    <div class="col-12">
      <div class="card my-4">
        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
          <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center">
            <h5 class="text-white mb-0 ms-3">
              <?= isset($role['role_id']) ? 'Edit Role And Permissions' : 'Add New Role And Permissions' ?>
            </h5>
          </div>
        </div>

        <div class="card-body">
          <div id="messageBox" class="alert d-none text-center" role="alert"></div>
          <form id="roleForm" action="<?= base_url('admin/manage_roles/store') ?>" class="p-3">
            <input type="hidden" name="role_id" id="role_id"
                   value="<?= isset($role['role_id']) ? esc($role['role_id']) : '' ?>">

            <div class="mb-3">
              <label for="role_name" class="form-label fw-bold">
                Role Name <span class="text-danger">*</span>
              </label>
              <input type="text" name="role_name" id="role_name" class="form-control capitalize cursor-padding" value="<?= isset($role['role_name']) ? esc($role['role_name']) : '' ?>" autocomplete="off" required>
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
                  <?php
                  $menus = ['Dashboard', 'Manage Role', 'Manage Admin User', 'Manage Enquiries'];
                  foreach ($menus as $menu):
                      $menuKey = ucwords(str_replace('_', ' ', $menu));
                      $isChecked = (isset($access[$menuKey]) && $access[$menuKey] == 1) ? 'checked' : '';
                  ?>
                    <div class="col-md-4">
                      <div class="form-check mb-2">
                        <input class="form-check-input permission-checkbox" type="checkbox"
                               name="menus[]" value="<?= $menuKey ?>" id="<?= $menuKey ?>" <?= $isChecked ?>>
                        <label class="form-check-label" for="<?= $menuKey ?>">
                          <?= $menu ?>
                        </label>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>

                <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                  <a href="<?= base_url('admin/manage_roles') ?>" class="btn btn-secondary">Discard</a>
                  <button type="submit" class="btn btn-primary enter-btn" id="saveBtn">Save Role</button>
                </div>
              </div>
            </div>
          </form>
        </div> 
      </div> 
    </div> 
  </div> 
</div> 
