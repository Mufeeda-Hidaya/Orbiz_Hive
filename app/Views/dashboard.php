<?php include "common/header.php"; ?>
<style>
  table thead th {
    padding: 6px 12px !important;
    font-size: 13px !important;
    background-color: #a1263a !important; 
    color: #ffffff !important;
    vertical-align: middle;
    text-align: center;
  }
  table tbody td {
    font-size: 14px;
    padding: 8px 12px;
    vertical-align: middle;
  }
  table th, table td {
    border: 1px solid #dee2e6;
  }
</style>

<!-- partial -->
<div class="right_container">
  <div class="content-wrapper">
    <div class="row">
      <div class="col-md-12 grid-margin">
        <div class="d-flex justify-content-between flex-wrap">
          <div class="d-flex align-items-end flex-wrap">
            <div class="me-md-3 me-xl-5">
              <h2>Welcome Back</h2>
              <!-- <p class="mb-md-0">Alrai Printing Press</p> -->
            </div>
          </div>
          <div class="d-flex align-items-center flex-wrap">
            <i class="mdi mdi-home text-muted hover-cursor"></i>
            <span class="text-muted hover-cursor mx-1">/ Dashboard /</span>
            <a href="<?= base_url('expense/report') ?>" class="breadcrumb-link">Analytics</a>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body dashboard-tabs p-0">
            <div class="tab-content py-0 px-0 border-left-0 border-bottom-0 border-right-0">
              <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
                <div class="d-flex flex-wrap justify-content-xl-between">
                  <?php
                  use App\Models\Manageuser_Model;
                  $userModel = new Manageuser_Model();
                  $userCount = $userModel->getAllUserCount()->totuser ?? 0;
                  ?>
                  <div class="d-flex border-md-right flex-grow-1 align-items-center justify-content-left justify-content-md-center px-4 px-md-0 mx-1 mx-md-0 p-3 item bg-revenue text-white"
                    style="cursor: pointer;"
                    onclick="window.location.href='<?= base_url('companyledger') ?>'">
                  <div class="icon-box me-4">
                    <i class="mdi mdi-currency-usd"></i>
                  </div>
                  <div class="d-flex flex-column justify-content-around">
                    <small class="mb-3 text-white"><b>Total Income The Day</b></small>
                    <h5 id="dailyRevenue" class="me-2 mb-0 text-center">KWD 0.00</h5>
                  </div>
                  </div>

                  <div class="d-flex border-md-right flex-grow-1 align-items-center justify-content-left justify-content-md-center px-4 px-md-0 mx-1 mx-md-0 p-3 item bg-success text-white"
                      style="cursor: pointer;"
                      onclick="window.location.href='<?= base_url('expense/report') ?>'">
                      
                      <div class="icon-box me-4">
                        <i class="bi bi-cash"></i>
                      </div>
                      
                      <div class="d-flex flex-column justify-content-around">
                        <small class="mb-3 text-white"><b>Total Expenses Of The Day</b></small>
                        <h5 class="me-2 mb-0 text-center" id="dailyExpense">0.00</h5>
                      </div>
                  </div>
                  
                  <div class="d-flex border-md-right flex-grow-1 align-items-center justify-content-left justify-content-md-center px-4 px-md-0 mx-1 mx-md-0 p-3 item bg-info text-white"
                      style="cursor: pointer;"
                      onclick="window.location.href='<?= base_url('companyledger') ?>'">
                      
                      <div class="icon-box me-4">
                          <i class="mdi mdi-currency-usd"></i>
                      </div>
                      
                      <div class="d-flex flex-column justify-content-around">
                          <small class="mb-3 text-white"><b>Total Income Of The Month</b></small>
                          <h5 id="monthlyRevenue" class="me-2 mb-0 text-center">KWD 0.00</h5>
                      </div>
                  </div>

                  <div class="d-flex py-3 border-md-right flex-grow-1 align-items-center justify-content-left justify-content-md-center px-4 px-md-0 mx-1 mx-md-0 p-3 item bg-info text-white"
                      style="cursor: pointer;"
                      onclick="window.location.href='<?= base_url('expense/report') ?>'">
                      
                      <div class="icon-box me-4">
                        <i class="bi bi-cash"></i>
                      </div>
                      <div class="d-flex flex-column justify-content-around">
                        <small class="mb-3 text-white"><b>Total Expenses Of The Month</b></small>
                        <h5 class="me-2 mb-0 text-center" id="monthlyExpense">0.00</h5>
                      </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <p class="card-title">Recent Estimates</p>
            <div class="table-responsive">
              <table class="table">
                <thead>
                  <tr>
                    <th><strong>Sl No</strong></th>
                    <th><strong>Date</strong></th>
                    <th><strong>Customer Name</strong></th>
                    <th><strong>Customer Address</strong></th>
                    <th><strong>Subtotal</strong></th>
                    <th><strong>Gross Amount</strong></th>
                  </tr>
                </thead>
                <tbody id="recentEstimatesBody"></tbody>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</div>
<?php include "common/footer.php"; ?>
<script>
  function fetchTodayExpense() {
    $.ajax({
      url: "<?= base_url('dashboard/getTodayExpenseTotal') ?>",
      type: "POST",
      dataType: "json",
      success: function (res) {
        $('#dailyExpense').text('KWD ' + parseFloat(res.total).toFixed(6));
      },
      error: function () {
        $('#dailyExpense').text('KWD 0.0000000');
      }
    });
  }

  function fetchMonthlyExpense() {
    $.ajax({
      url: "<?= base_url('dashboard/getMonthlyExpenseTotal') ?>",
      type: "POST",
      dataType: "json",
      success: function (res) {
        $('#monthlyExpense').text('KWD ' + parseFloat(res.total).toFixed(6));
      },
      error: function () {
        $('#monthlyExpense').text('KWD 0.000000');
      }
    });
  }

  function fetchTodayRevenue() {
    $.ajax({
      url: "<?= base_url('dashboard/getTodayRevenueTotal') ?>",
      type: "GET",
      dataType: "json",
      success: function (res) {
        $('#dailyRevenue').text('KWD ' + parseFloat(res.total).toFixed(6));
      },
      error: function () {
        $('#dailyRevenue').text('KWD 0.000000');
      }
    });
  }

  function fetchMonthlyRevenue() {
    $.ajax({
      url: "<?= base_url('dashboard/getMonthlyRevenueTotal') ?>",
      type: "GET",
      dataType: "json",
      success: function (res) {
        $('#monthlyRevenue').text('KWD ' + parseFloat(res.total).toFixed(6));
      },
      error: function () {
        $('#monthlyRevenue').text('KWD 0.000000');
      }
    });
  }

  function loadRecentEstimates() {
    $.ajax({
      url: "<?= base_url('estimate/recentEstimates') ?>",
      method: "GET",
      dataType: "json",
      success: function (data) {
        let rows = '';
        if (data.length > 0) {
          data.forEach((est, index) => {
            rows += `
              <tr>
                <td>${index + 1}</td>
                <td>${formatDate(est.date)}</td>
                <td>${capitalizeWords(est.customer_name ?? '-')}</td>
                <td>${capitalizeWords(est.customer_address ?? '-')}</td>
                <td>${parseFloat(est.sub_total || 0).toFixed(6)} KWD</td>
                <td>${parseFloat(est.total_amount || 0).toFixed(6)} KWD</td>
              </tr>
            `;
          });
        } else {
          rows = '<tr><td colspan="6" class="text-center">No recent estimates found.</td></tr>';
        }
        $('#recentEstimatesBody').html(rows);
      },
      error: function () {
        $('#recentEstimatesBody').html('<tr><td colspan="6" class="text-center text-danger">Error loading estimates.</td></tr>');
      }
    });
  }

  function capitalizeWords(str) {
    return str.toLowerCase().replace(/\b\w/g, char => char.toUpperCase());
  }

  function formatDate(dateStr) {
    const date = new Date(dateStr);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    return `${day}-${month}-${year}`;
  }

  $(document).ready(function () {
    // Initial load
    fetchTodayExpense();
    fetchMonthlyExpense();
    fetchTodayRevenue();
    fetchMonthlyRevenue();
    loadRecentEstimates();

    // Auto-refresh every 10 seconds
    setInterval(function () {
      fetchTodayExpense();
      fetchMonthlyExpense();
      fetchTodayRevenue();
      fetchMonthlyRevenue();
      loadRecentEstimates();
    }, 10000);
  });
</script>

