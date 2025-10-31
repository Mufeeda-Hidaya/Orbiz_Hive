<?php
$session = session();
$company_id = $session->get('company_id') ?? null; 
$user_id = $session->get('user_id') ?? null;       

if (!$company_id) {
    $company_id = 1; 
}
$company = [];
if ($company_id) {
    $companyModel = new \App\Models\Managecompany_Model();
    $company = $companyModel->find($company_id);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Orbizhive</title>
  <!-- plugins:css -->
  <link rel="stylesheet" href="<?php echo ASSET_PATH; ?>assets/vendors/mdi/css/materialdesignicons.min.css">
  <link rel="stylesheet" href="<?php echo ASSET_PATH; ?>assets/vendors/css/vendor.bundle.base.css">

  <link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css">
  <link rel="stylesheet" href="<?php echo ASSET_PATH; ?>assets/css/style.css">
  <link rel="stylesheet" href="<?php echo ASSET_PATH; ?>assets/css/custom.css">
  <!-- endinject -->
  <link rel="shortcut icon" href="<?php echo ASSET_PATH; ?>assets/images/logo-bx.png" />

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <!-- Already included in your file -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <!-- <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" ></script> -->
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Bootstrap 5 -->
  <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"> -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <!-- MDI Icons -->
  <link href="https://cdn.jsdelivr.net/npm/@mdi/font@6.5.95/css/materialdesignicons.min.css" rel="stylesheet">

</head>

<body>
  <nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
    <div class="navbar-brand-wrapper d-flex justify-content-center">
      <div class="navbar-brand-inner-wrapper d-flex justify-content-between align-items-center w-100">
        <a class="navbar-brand brand-logo" href="#">
          <img src="<?= ASSET_PATH; ?>assets/images/logo-new1.png"  alt="logo" /></a>
        </a>

        <a class="navbar-brand brand-logo-white" href="index.html"><img
            src="<?= ASSET_PATH; ?>assets/images/logo-bx.png" alt="logo" /></a>
        <a class="navbar-brand brand-logo-mini" href="index.html"><img
            src="<?= ASSET_PATH; ?>assets/images/logo-new.png" alt="logo" /></a>
        <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
        </button>
      </div>
    </div>
    <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
      
      <button id="menuToggle" class="btn icon-align d-xl-none">
        <i class="fas fa-bars"></i>
      </button>
    </div>
  </nav>
  <div class="container-fluid page-body-wrapper px-0" style=" padding-top: 70px; padding-bottom: 10px;">
    <?php
    $session = session();
    $allowedMenus = $session->get('allowed_menus') ?? [];
    $uri = service('uri');
    ?>
    <?php $currentPath = uri_string(); ?>
    <nav class="sidebar sidebar-offcanvas" id="sidebar">
      <ul class="nav">
        <?php if (in_array('dashboard', $allowedMenus)): ?>
          <li class="nav-item">
           <a class="nav-link <?= strpos($currentPath, 'dashboard') !== false ? 'active' : '' ?>"
            href="<?= base_url('dashboard') ?>">
            <i class="mdi mdi-home menu-icon"></i>
            <span class="menu-title">Dashboard</span>
          </a>

          </li>
        <?php endif; ?>
        <!-- <?php if (in_array('companylist', $allowedMenus)): ?>
          <li class="nav-item">
            <a class="nav-link <?= strpos($currentPath, 'company') !== false ? 'active' : '' ?>"
              href="<?= base_url('companylist') ?>">
              <i class="mdi mdi-view-headline menu-icon"></i>
              <span class="menu-title">Company Management</span>
            </a>

          </li>
        <?php endif; ?> -->
       
        <?php if (in_array('rolemanagement', $allowedMenus)): ?>
          <li class="nav-item">
            <a class="nav-link <?= strpos($currentPath, 'rolemanagement') !== false ? 'active' : '' ?>"
              href="<?= base_url('rolemanagement/rolelist') ?>">
              <i class="mdi mdi-chart-pie menu-icon"></i>
              <span class="menu-title">Role Management</span>
            </a>

          </li>
        <?php endif; ?>
         <?php if (in_array('adduserlist', $allowedMenus)): ?>
          <li class="nav-item">
            <a class="nav-link <?= strpos($currentPath, 'adduser') !== false || strpos($currentPath, 'manageuser') !== false ? 'active' : '' ?>"
              href="<?= base_url('adduserlist') ?>">
              <i class="mdi mdi-bi bi-person menu-icon"></i>
              <span class="menu-title">Manage User</span>
            </a>

          </li>
        <?php endif; ?>
        <?php if (in_array('customer', $allowedMenus)): ?>
          <li class="nav-item">
            <a class="nav-link <?= strpos($currentPath, 'customer') !== false ? 'active' : '' ?>"
              href="<?= base_url('customer/list') ?>">
              <i class="mdi mdi-account-multiple menu-icon"></i>
              <span class="menu-title">Customer List</span>
            </a>
          </li>
        <?php endif; ?>
       

          <?php if (in_array('supplier', $allowedMenus)): ?>
            <li class="nav-item">
              <a class="nav-link <?= (strpos($currentPath, 'supplier') !== false || strpos(uri_string(), 'enquiry') !== false) ? 'active' : '' ?>"
                href="<?= base_url('enquiry/list') ?>">
                  <i class="mdi mdi-file-outline menu-icon"></i>
                  <span class="menu-title">Enquiry List</span>
              </a>
            </li>
          <?php endif; ?>

        <!-- <?php if (in_array('enquirylist', $allowedMenus)): ?>
          <li class="nav-item">
           <a class="nav-link <?= strpos(uri_string(), 'enquiry') !== false ? 'active' : '' ?>"
              href="<?= base_url('enquirylist') ?>">
              <i class="mdi mdi-grid-large menu-icon"></i>
              <span class="menu-title">Enquiry</span>
            </a>
          </li>
        <?php endif; ?> -->
        <?php if (in_array('estimatelist', $allowedMenus)): ?>
          <li class="nav-item">
           <a class="nav-link <?= strpos(uri_string(), 'estimate') !== false || uri_string() == 'estimatelist' ? 'active' : '' ?>"
              href="<?= base_url('estimatelist') ?>">
              <i class="mdi mdi-grid-large menu-icon"></i>
              <span class="menu-title">Estimate Generation</span>
            </a>
          </li>
        <?php endif; ?>
        <?php if (in_array('invoices', $allowedMenus)): ?>
          <li class="nav-item">
            <a class="nav-link <?= strpos(uri_string(), 'orderlist') !== false ? 'active' : '' ?>"
              href="<?= base_url('orderlist') ?>">
              <i class="mdi mdi-receipt menu-icon"></i>
              <span class="menu-title">Job Order</span>
            </a>
          </li>
        <?php endif; ?>
        <!-- <?php if (in_array('customer', $allowedMenus)): ?>
          <li class="nav-item">
            <a class="nav-link <?= strpos($currentPath, 'cashlist') !== false ? 'active' : '' ?>" 
              href="<?= base_url('cashlist') ?>">
              <i class="mdi mdi-cash-multiple menu-icon"></i>
              <span class="menu-title">Transactions</span>
            </a>
          </li>
        <?php endif; ?> -->
        <!-- <?php if (in_array('expense', $allowedMenus)): ?>
          <li class="nav-item">
            <a class="nav-link <?= strpos($currentPath, 'expense') !== false ? 'active' : '' ?>"
              href="<?= base_url('expense') ?>">
              <i class="mdi mdi-square-outline menu-icon"></i>
              <span class="menu-title">Expenses</span>
            </a>

          </li>
        <?php endif; ?> -->

        <!-- <?php if (in_array('reports', $allowedMenus)): ?>
  <li class="nav-item">
    <a class="nav-link" data-bs-toggle="collapse" href="#auth" aria-expanded="false" aria-controls="auth">
      <i class="mdi mdi-clipboard menu-icon"></i>
      <span class="menu-title">Reports</span>
    </a>
    <div class="collapse" id="auth">
      <ul class="nav flex-column sub-menu">
        <li class="nav-item"><a class="nav-link" href="<?= base_url('expense/report') ?>">Expense Report</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= base_url('invoice/report') ?>">Sales Report</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= base_url('companyledger') ?>">Company Ledger</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= base_url('customerreport') ?>">Customer Report</a></li> 
      </ul>
    </div>
  </li>
<?php endif; ?> -->


        <li class="nav-item">
          <a href="#" id="logoutLink" class="nav-link">
            <i class="mdi mdi-logout menu-icon"></i>
            <span class="menu-title">Logout</span>
          </a>
        </li>
      </ul>
    </nav>
    <!-- Logout Confirmation Modal -->
    <div class="modal fade" id="logoutModel" tabindex="-1" role="dialog" aria-labelledby="logoutModelLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="logoutModelLabel">Confirmation</h5>
            <button type="button" class="close" id="closeModalBtn" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            Are You Sure You Want To Logout?
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" id="confirmlogout">Logout</button>
            <button type="button" class="btn btn-secondary" id="cancelLogoutBtn">Cancel</button>
          </div>
        </div>
      </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>