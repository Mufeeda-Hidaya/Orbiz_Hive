<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Orbiz Admin Login</title>

  <!-- Fonts & Styles -->
  <link href="https://fonts.googleapis.com/css?family=Inter:400,500,600,700,900" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo base_url().ASSET_PATH; ?>admin/assets/css/bootstrap.min.css" />
  <link rel="stylesheet" href="<?php echo base_url().ASSET_PATH; ?>admin/assets/css/material-dashboard.css?v=3.2.0" />
  <link rel="stylesheet" href="<?php echo base_url().ASSET_PATH; ?>admin/assets/css/custom.css" /> 
  <!-- <link rel="icon" href="<?php echo base_url().ASSET_PATH; ?>admin/assets/img/logo.png" type="image/x-icon" /> -->

  <!-- Font Awesome 6 Free CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-pV5pC...==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>
  <div class="login-page">
    <!-- Left Side: Welcome Section (Dark) -->
    <div class="login-left">
      <div class="welcome-text">
       <img src="<?= base_url() . ASSET_PATH; ?>admin/assets/img/logo-ct-dark.webp" class="logo-img">
        <h1>Welcome to ORBIZ Admin</h1>
        <p>Easily manage all your data securely in one place.</p>
      </div>
    </div>

    <!-- Right Side: Login Form (Light) -->
    <div class="login-right">
      <div class="card login-card shadow w-75 p-4">
        <div class="card-header text-center border-0 bg-white header-center">
          <h4 class="mt-3 mb-0">Welcome Back !</h4>
          <p class="text-muted small">Administration Login</p>
        </div>
        <div id="errorDiv"></div>
        <div class="card-body">
          <form id="loginForm" method="post">
            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="email" name="email" id="email" class="form-control" placeholder="Enter Email" required style="padding-left: 10px;">
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">Password</label>
              <div class="input-group">
                <input type="password" name="password" id="password" class="form-control" placeholder="Password" required style="padding-left: 10px;">
                <span class="input-group-text position-absolute end-0 top-50 translate-middle-y" id="togglePassword" style=" margin-right:20px; cursor: pointer; z-index: 10;">
                  <i class="fas fa-eye-slash"></i>
                </span>
              </div>
            </div>

            <!-- Captcha -->
            <div class="g-recaptcha" data-sitekey="6Le-VXcrAAAAAFdEqJLtM5DxM6GoGl7cJdV6hknL"></div>
            <button type="submit" id="loginCheck" class="btn w-100 my-3" style="background-color: blue; color: white;">Login</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- JS -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>

  <script>
    // Login form submission
    $('#loginCheck').click(function(e) {
        e.preventDefault();

        let email = $('#email').val().trim();
        let password = $('#password').val().trim();
        let errorMessage = '';

        if (email === '' && password === '') {
            errorMessage = "Please Enter Email And password.";
        } else if (email === '') {
            errorMessage = "Please Enter Your Email.";
        } else if (password === '') {
            errorMessage = "Please Enter Your Password.";
        }
        if (errorMessage !== '') {
            showAlert(errorMessage, 'danger');
            return;
        }
        var response = grecaptcha.getResponse();
        if (response.length === 0) {
            showAlert("Please Complete The reCAPTCHA.", 'danger');
            return;
        }
        let $btn = $('#loginCheck');
        $btn.prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm me-2"></span> Authenticating, Please Wait…');

        var url = "<?php echo base_url('admin/login'); ?>";

         $.post(url, $('#loginForm').serialize(), function(data) {
            if (data.success) {
                window.location.href = data.redirect;
            } else {
                showAlert(data.message, 'danger');
                $btn.prop('disabled', false).html('Log in');
            }
        }, 'json').fail(function() {
            showAlert("Something Went Wrong. Please Try Again.", 'danger');
            $btn.prop('disabled', false).html('Log in');
        });

    });

    // Function to show alert messages

    function showAlert(message) {
    $('#errorDiv').text(message); 
    setTimeout(() => {
        $('#errorDiv').text(''); 
    }, 3000);
}

    //Password Show and hide
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
