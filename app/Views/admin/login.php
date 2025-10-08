<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Admin Login</title>

  <!-- Material Dashboard + Bootstrap -->
  <link href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo base_url().ASSET_PATH; ?>admin/assets/css/bootstrap.min.css" />
  <link rel="stylesheet" href="<?php echo base_url().ASSET_PATH; ?>admin/assets/css/material-dashboard.css?v=3.2.0" />
  <link rel="icon" href="<?php echo base_url().ASSET_PATH; ?>admin/assets/img/logo.png" type="image/x-icon" />
</head>

<body class="">

  <main class="main-content mt-0">
    <section>
      <div class="page-header min-vh-100">
        <div class="container">
          <div class="row">
            <div class="col-6 d-lg-flex d-none h-100 my-auto pe-0 position-absolute top-0 start-0 text-center justify-content-center flex-column">
              <div class="position-relative bg-gradient-primary h-100 m-3 px-7 border-radius-lg d-flex flex-column justify-content-center" style="background-image: url('<?php echo base_url().ASSET_PATH; ?>admin/assets/img/illustrations/illustration-signup.jpg'); background-size: cover;">
              </div>
            </div>

            <div class="col-xl-4 col-lg-5 col-md-7 d-flex flex-column ms-auto me-auto ms-lg-auto me-lg-5">
              <div class="card card-plain mt-6">
                <div class="card-header text-center">
                  <img src="<?php echo base_url().ASSET_PATH; ?>admin/assets/img/logo.png" alt="Logo" style="width: 80px;">
                  <h4 class="font-weight-bolder mt-2">Admin Login</h4>
                  <p class="mb-0 text-muted">Enter your credentials to continue</p>
                </div>

                <div class="card-body">
                  <div id="errorDiv"></div>
                  <form id="loginForm" method="post">
                    <div class="input-group input-group-outline mb-3">
                      <label class="form-label">Email</label>
                      <input type="email" name="email" id="email" class="form-control" required>
                    </div>

                    <div class="input-group input-group-outline mb-3 position-relative">
                      <label class="form-label">Password</label>
                      <input type="password" name="password" id="password" class="form-control" required>
                      <span id="togglePassword" class="position-absolute end-0 top-50 translate-middle-y pe-3" style="cursor: pointer;">
                        <i class="fa fa-eye-slash"></i>
                      </span>
                    </div>

                    <div class="text-center">
                      <button type="submit" id="loginCheck" class="btn bg-gradient-primary w-100 my-4 mb-2">Login</button>
                    </div>
                  </form>
                </div>

              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- JS -->
  <script src="<?php echo base_url().ASSET_PATH; ?>admin/assets/js/core/jquery-3.7.1.min.js"></script>
  <script src="<?php echo base_url().ASSET_PATH; ?>admin/assets/js/core/bootstrap.min.js"></script>

  <script>
    $('#loginForm').on('submit', function(e) {
      e.preventDefault();

      let email = $('#email').val().trim();
      let password = $('#password').val().trim();

      if (email === '' || password === '') {
        showAlert('Please enter both Email and Password.', 'danger');
        return;
      }

      let $btn = $('#loginCheck');
      $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Logging in...');

      $.ajax({
        url: "<?php echo base_url('admin/login'); ?>",
        type: "POST",
        dataType: "json",
        data: $(this).serialize(),
        success: function(data) {
          if (data.success) {
            window.location.href = data.redirect;
          } else {
            showAlert(data.message, 'danger');
            $btn.prop('disabled', false).html('Login');
          }
        },
        error: function() {
          showAlert('Something went wrong. Please try again.', 'danger');
          $btn.prop('disabled', false).html('Login');
        }
      });
    });

    function showAlert(message, type = 'danger') {
      let $alertBox = $('#errorDiv');
      $alertBox
        .hide()
        .html('<div class="alert alert-' + type + ' text-white" role="alert">' + message + '</div>')
        .fadeIn();

      setTimeout(() => {
        $alertBox.fadeOut();
      }, 3000);
    }

    // Password toggle
    $(document).on('click', '#togglePassword', function() {
      const passwordField = $('#password');
      const icon = $(this).find('i');

      if (passwordField.attr('type') === 'password') {
        passwordField.attr('type', 'text');
        icon.removeClass('fa-eye-slash').addClass('fa-eye');
      } else {
        passwordField.attr('type', 'password');
        icon.removeClass('fa-eye').addClass('fa-eye-slash');
      }
    });
  </script>
</body>
</html>
