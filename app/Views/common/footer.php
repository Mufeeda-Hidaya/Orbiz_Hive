<footer class="footer">
  <div class="row">
    <div class="d-sm-flex justify-content-between align-items-center">
      <span class="text-muted ">Copyright © 2025 All rights reserved.</span>
      <span class="text-center">Powered by
        <a href="https://www.smartlounge.online/" target="_blank">Smartlounge.online</a>
      </span>
    </div>
    <div>
</footer>
</footer>
<!-- partial -->
</div>
<!-- main-panel ends -->
</div>
<!-- page-body-wrapper ends -->
</div>
<!-- container-scroller -->

<!-- plugins:js -->
<script src="assets/vendors/js/vendor.bundle.base.js"></script>
<!-- endinject -->
<!-- Plugin js for this page-->
<script src="assets/vendors/chart.js/chart.umd.js"></script>
<!-- End plugin js for this page-->
<!-- inject:js -->
<script src="assets/js/off-canvas.js"></script>
<script src="assets/js/hoverable-collapse.js"></script>
<script src="assets/js/template.js"></script>
<script src="assets/js/settings.js"></script>
<script src="assets/js/todolist.js"></script>
<!-- endinject -->
<!-- Custom js for this page-->
<script src="assets/js/dashboard.js"></script>
<script src="assets/js/proBanner.js"></script>
<script src="assets/js/jquery.cookie.js" type="text/javascript"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>
<!-- <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script> -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
  $(document).ready(function () {
    $('#logoutLink').on('click', function (e) {
      e.preventDefault();
      $('#logoutModel').modal('show');
    });

    $('#confirmlogout').on('click', function (e) {
      e.preventDefault();
      $('#logoutModel').modal('hide');
      window.location.href = "<?= base_url('logout') ?>";
    });

    $('#closeModalBtn,#cancelLogoutBtn').on('click', function () {



      $('#logoutModel').modal('hide');
    });
  });
  document.addEventListener('DOMContentLoaded', function () {
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.querySelector('.sidebar');

    menuToggle.addEventListener('click', function () {
      sidebar.classList.toggle('show');
    });
  });

</script>
</body>

</html>