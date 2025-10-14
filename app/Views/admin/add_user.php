<div class="container-fluid py-2">
  <div class="row">
    <div class="col-12">
      <div class="card my-4">
        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
          <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center">
            <h4 class="text-white mb-0 ms-3">
              <?= isset($userData['user_id']) ? 'Edit User' : 'Add New User' ?>
            </h4>
          </div>
        </div>

        <div class="card-body px-4 py-4">
          <div id="messageBox" class="alert d-none text-center" role="alert"></div>

          <form id="userForm" method="post" data-edit="true">
            <?php if (isset($userData['user_id'])): ?>
              <input type="hidden" name="user_id" value="<?= esc($userData['user_id']) ?>">
            <?php endif; ?>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Name<span class="text-danger">*</span></label>
                <input type="text" name="user_name" class="form-control cursor-padding" value="<?= isset($userData['name']) ? esc($userData['name']) : '' ?>" autocomplete="off" required>
              </div>

              <div class="col-md-6 mb-3">
                <label class="form-label">Email<span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control cursor-padding" value="<?= isset($userData['email']) ? esc($userData['email']) : '' ?>" autocomplete="off" required>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">User Role <span class="text-danger">*</span></label>
                <select name="role_id" id="role_id" class="form-control cursor-padding" required>
                  <option value="">Select Role</option>
                  <?php if (isset($roles) && !empty($roles)): ?>
                    <?php foreach ($roles as $role): ?>
                      <option value="<?= $role->role_id ?>" <?= isset($userData['role_id']) && $userData['role_id'] == $role->role_id ? 'selected' : '' ?>>
                        <?= ucfirst($role->role_name) ?>
                      </option>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <option value="">No roles available</option>
                  <?php endif; ?>
                </select>
              </div>

              <div class="col-md-6 mb-3">
                <label class="form-label">Phone Number</label>
                <input type="text" name="phone" class="form-control cursor-padding" maxlength="20" value="<?= isset($userData['phone']) ? esc($userData['phone']) : '' ?>" oninput="this.value = this.value.replace(/[^0-9 +]/g, '')" autocomplete="off" required>
              </div>
            </div>

            <?php if (!isset($userData['user_id'])): ?>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Password <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <input type="password" id="new_password" name="new_password" class="form-control cursor-padding">
                    <span class="input-group-text eye-icon"><i class="bi bi-eye-slash toggle-password"></i></span>
                  </div>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control cursor-padding">
                    <span class="input-group-text eye-icon"><i class="bi bi-eye-slash toggle-password"></i></span>
                  </div>
                </div>
              </div>
            <?php endif; ?>

            <div class="col-12 d-flex justify-content-end gap-2 mt-4">
              <a href="<?= base_url('admin/manage_user') ?>" class="btn btn-secondary">Cancel</a>
              <button type="submit" id="saveUserBtn" class="btn btn-primary">Save User</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>




