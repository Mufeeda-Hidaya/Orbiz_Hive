<?php
$session     = session();
$roleId      = $session->get('role_id');
$menus       = $session->get('role_menu') ?? [];
$roleName    = $session->get('role_name') ?? '';
$uri         = service('uri');
$currentPath = uri_string();

// Define all available menus
$allMenus = [
    'Dashboard' => [
        'url'   => 'dashboard',
        'icon'  => 'bi bi-house-door',
        'match' => ['dashboard']
    ],
    'Manage Roles' => [
        'url'   => 'manage_roles',
        'icon'  => 'fas fa-th-list',
        'match' => ['manage_roles', 'add_role']
    ],
    'Manage Admin Users' => [
        'url'   => 'manage_user',
        'icon'  => 'bi bi-people',
        'match' => ['manage_user', 'adduser']
    ],
    'Manage Enquiries' => [
        'url'   => 'manage_enquiry',
        'icon'  => 'bi bi-question-circle',
        'match' => ['manage_enquiry']
    ],
    'Estimate Generation' => [
        'url'   => 'manage_estimate',
        'icon'  => 'bi bi-file-earmark',
        'match' => ['manage_estimate', 'add_estimate']
    ],
];
?>

<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-radius-lg fixed-start ms-2 bg-white my-2" id="sidenav-main">
    <div class="sidenav-header">
        <a class="navbar-brand px-4 py-3 m-0" href="<?= base_url('admin/dashboard') ?>">
            <img src="<?= base_url() . ASSET_PATH; ?>admin/assets/img/logo-ct-dark.png" class="navbar-brand-img" width="100" alt="main_logo">
        </a>
    </div>
    <hr class="horizontal dark mt-0 mb-2">

    <div class="collapse navbar-collapse w-auto" id="sidenav-collapse-main">
        <ul class="navbar-nav">

            <!-- DASHBOARD always visible -->
            <!-- <li class="nav-item <?= ($uri->getSegment(2) == 'dashboard') ? 'active' : '' ?>">
                <a class="nav-link text-dark" href="<?= base_url('admin/dashboard') ?>">
                    <i class="bi bi-house-door"></i>
                    <span class="nav-link-text ms-1">Dashboard</span>
                </a>
            </li> -->

            <!-- <li class="nav-section">
                <h6 class="text-section text-uppercase text-xs font-weight-bolder">Menus</h6>
            </li> -->

            <?php foreach ($allMenus as $menuName => $data): ?>
                <?php
                    // Show all for admin, only assigned for others
                    if ($roleId != 1 && !in_array($menuName, $menus)) continue;

                    // Highlight active
                    $isActive = false;
                    foreach ($data['match'] as $keyword) {
                        if (strpos($currentPath, $keyword) !== false) {
                            $isActive = true;
                            break;
                        }
                    }
                ?>
                <li class="nav-item <?= $isActive ? 'active bg-light rounded-3' : '' ?>">
                    <a class="nav-link text-dark <?= $isActive ? 'active' : '' ?>" href="<?= base_url('admin/' . $data['url']) ?>">
                        <i class="<?= $data['icon'] ?>"></i>
                        <span class="nav-link-text ms-1"><?= esc($menuName) ?></span>
                    </a>
                </li>
            <?php endforeach; ?>

            <li class="nav-item mt-0">
                <a class="nav-link text-dark" href="#" onclick="confirmLogout(event)">
                    <i class="bi bi-box-arrow-right me-2"></i>
                    <span class="nav-link-text ms-1">Logout</span>
                </a>
            </li>

        </ul>
    </div>
</aside>


   <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
  <!-- SweetAlert2 -->
<!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> -->

<script>
function confirmLogout(event) {
    event.preventDefault();

    Swal.fire({
        title: 'Logout Confirmation',
        text: "Are you sure you want to logout?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Logout',    
        cancelButtonText: 'Cancel',     
        reverseButtons: true            
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "<?= base_url('admin/logout'); ?>";
        }
    });
}
</script>
