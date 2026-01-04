<!DOCTYPE html>
<html lang="en">
<style>
  .btn-primary:not(.btn-light) {
    background: #686868 !important;
    border: none;
    color: white;
  }

  .auth .auth-form-light {
    color: #181010 !important;
  }

  .auth .auth-form-light select {
    color: #181010 !important;
    appearance: auto;
    -webkit-appearance: auto;
    -moz-appearance: auto;
  }

  .form-control {
    border: 1px solid #b9b7b7ff !important;
  }
</style>

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Orbizhive</title>
  <!-- plugins:css -->
  <link rel="stylesheet" href="<?php echo ASSET_PATH; ?>assets/vendors/mdi/css/materialdesignicons.min.css">
  <link rel="stylesheet" href="<?php echo ASSET_PATH; ?>assets/vendors/css/vendor.bundle.base.css">
  <link rel="stylesheet" href="<?php echo ASSET_PATH; ?>assets/css/style.css">
  <!-- endinject -->
  <link rel="shortcut icon" href="<?php echo ASSET_PATH; ?>assets/images/logo-bx.png" />
</head>

<body>
  <div class="container-scroller">
    <div class="container-fluid page-body-wrapper full-page-wrapper">
      <div class="content-wrapper d-flex align-items-center auth px-0">
        <div class="row w-100 mx-0">
          <div class="col-lg-4 mx-auto">
            <div class="auth-form-light text-left py-5 px-4 px-sm-5">
              <div style="text-align: center;">
                <img src="<?php echo ASSET_PATH; ?>assets/images/logo-new1.png" 
                    alt="logo"
                    style="width: 167px; height: 63px; margin-bottom:30px;">
              </div>

              <h4>Hello! let's get started</h4>
              <h6 class="font-weight-light">Sign in to continue.</h6>
              <form class="pt-3" id="login-form">
                <div class="alert alert-danger" role="alert" id="loginalert" style="display: none;">
                </div>
                <input type="hidden" name="login_mode" 
                  value="<?= !empty($isAdminLogin) && $isAdminLogin ? 'admin_with_company' : 'normal' ?>">
                <div class="form-group">
                  <input type="text" name="email" class="form-control form-control-lg" placeholder="example@gmail.com">
                </div>
                <div class="form-group position-relative">
                  <input type="password" name="password" id="password" class="form-control form-control-lg"
                    placeholder="Password">
                  <i class="mdi mdi-eye-off position-absolute" id="togglePassword"
                    style="top: 50%; right: 15px; transform: translateY(-50%); cursor: pointer;"></i>
                </div>
               

                <div class="mt-3 d-grid gap-2">
                  <button type="button"
                    class="enter-btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn">SIGN IN</button>
                </div>
               <?php if (empty($isAdminLogin) || !$isAdminLogin): ?>
                <div class="my-2 d-flex justify-content-between align-items-center">
                  <a href="<?= base_url('admin/login') ?>" class="auth-link text-black">Login As Admin</a>
                </div>
              <?php endif; ?>


              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="<?php echo ASSET_PATH; ?>assets/vendors/js/vendor.bundle.base.js"></script>
  <script src="<?php echo ASSET_PATH; ?>assets/js/off-canvas.js"></script>
  <script src="<?php echo ASSET_PATH; ?>assets/js/hoverable-collapse.js"></script>
  <script src="<?php echo ASSET_PATH; ?>assets/js/template.js"></script>
  <script src="<?php echo ASSET_PATH; ?>assets/js/settings.js"></script>
  <script src="<?php echo ASSET_PATH; ?>assets/js/todolist.js"></script>
</body>
<script>
  $(document).ready(function () {

    $('#togglePassword').click(function () {
      const passwordField = $('#password');
      const type = passwordField.attr('type') === 'password' ? 'text' : 'password';
      passwordField.attr('type', type);
      $(this).toggleClass('mdi-eye-off mdi-eye');
    });

    $('.enter-btn').click(function () {
      const body = $('#login-form').serialize();
      const url = '<?= base_url("admin/login/authenticate") ?>';

      $.post(url, body, function (response) {
        if (response.status === 1) {
          $('#loginalert').removeClass('alert-danger').addClass('alert-success');
          $('#loginalert').html('Login Successful').fadeIn();

          setTimeout(function () {
            $('#loginalert').fadeOut();
            window.location.href = "<?= base_url('dashboard') ?>";
          }, 2500);
        } else {
          $('#loginalert').removeClass('alert-success').addClass('alert-danger');
          const errorMessage = response.message || 'Invalid Credentials';
          $('#loginalert').html(errorMessage).fadeIn();

          setTimeout(function () {
            $('#loginalert').fadeOut();
          }, 2500);
        }
      }, 'json');
    });

  });
</script>

</html>