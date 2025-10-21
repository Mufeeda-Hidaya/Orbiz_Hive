<?php include "common/header.php"; ?>
<style>
    .input-group-text {
        color: #000 !important;
    }
    .badge.bg-success {
    background-color: #28a745 !important; /* Bootstrap green */
    color: #fff !important; /* white text for contrast */
    width: 80px;
}
.badge.bg-danger{
    width: 80px;
}


    #salesTable th,
    #salesTable td {
        vertical-align: middle;
        padding: 16px 30px;
        font-size: 14px;
        text-align: left !important;
    }

    #salesTable th:nth-child(1),
    #salesTable td:nth-child(1) {
        width: 10%;
    }

    #salesTable th:nth-child(2),
    #salesTable td:nth-child(2) {
        width: 15%;
    }

    #salesTable th:nth-child(3),
    #salesTable td:nth-child(3) {
        width: 30%;
    }

    #salesTable th:nth-child(4),
    #salesTable td:nth-child(4) {
        width: 20%;
    }

    #salesTable th:nth-child(5),
    #salesTable td:nth-child(5) {
        width: 15%;
    }

    .filter_item {
        width: 25%;
    }

    .alert {
        z-index: 1050;
    }
</style>

<div class="form-control mb-3 right_container">
    <div class="alert d-none text-center position-fixed" role="alert"></div>

    <h3 class="mb-3">Sales Report</h3>

    <div class="d-flex gap-1 mb-3">
        <div class="filter_item input-group">
            <div class="input-group-prepend">
                <span class="input-group-text from-date">From</span>
            </div>
            <input type="date" id="fromDate" class="form-control">
        </div>

        <div class="filter_item input-group">
            <div class="input-group-prepend">
                <span class="input-group-text from-date">To</span>
            </div>
            <input type="date" id="toDate" class="form-control">
        </div>

        <div class="filter_item input-group">
            <div class="input-group-prepend">
                <span class="input-group-text from-date">Customer</span>
            </div>
            <select id="customerId" class="form-control">
                <option value="">All Customers</option>
                <?php foreach ($customers as $cust): ?>
                    <option value="<?= $cust['customer_id'] ?>"><?= esc($cust['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <button id="filterBtn" class="btn btn-primary">Apply Filter</button>
        <button id="resetBtn" class="btn btn-secondary">Reset</button>
    </div>

    <table class="table table-bordered" id="salesTable">
        <thead>
            <tr>
                <th><strong>Sl No</strong></th>
                <th><strong>Date</strong></th>
                <th><strong>Customer Name</strong></th>
                <th><strong>Invoice Amount</strong></th>
                <th><strong>Status</strong></th>
            </tr>
        </thead>
        <tbody></tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="text-end">Total:</th>
                <th id="totalAmount">0.000000 KWD</th>
                <th></th>
            </tr>
        </tfoot>
    </table>
</div>
</div>
<?php include "common/footer.php"; ?>

<script>
    function formatDate(dateStr) {
        const dateObj = new Date(dateStr);
        if (isNaN(dateObj.getTime())) return dateStr;
        const day = String(dateObj.getDate()).padStart(2, '0');
        const month = String(dateObj.getMonth() + 1).padStart(2, '0');
        const year = dateObj.getFullYear();
        return `${day}-${month}-${year}`;
    }

    $(document).ready(function () {
        const alertBox = $('.alert');
        let filterApplied = false;

        function showAlert(message) {
            alertBox.removeClass('d-none alert-success alert-danger')
                .addClass('alert alert-danger')
                .text(message)
                .fadeIn();
            setTimeout(() => alertBox.fadeOut(), 2000);
        }

        function loadSales() {
            $.ajax({
                url: "<?= base_url('invoice/getSalesReportAjax') ?>",
                type: "POST",
                data: {
                    fromDate: $('#fromDate').val(),
                    toDate: $('#toDate').val(),
                    customerId: $('#customerId').val()
                },
                dataType: "json",
                success: function (data) {
                    let rows = '';
                    let grandTotal = 0;

                    if (data.invoices.length > 0) {
                        data.invoices.forEach((item, index) => {
                            const totalAmount = parseFloat(item.total_amount);
                            grandTotal += totalAmount;

                            const statusMap = {
                                'paid': {label: 'Paid', class: 'bg-success'},
                                'unpaid': {label: 'Unpaid', class: 'bg-danger'},
                                'partial paid': {label: 'Partial Paid', class: 'bg-warning text-white'}
                            };
                            const st = statusMap[item.status] || {label: 'Unknown', class: 'bg-secondary'};

                            rows += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${formatDate(item.invoice_date)}</td>
                                    <td>${item.customer_name}</td>
                                    <td>${totalAmount.toFixed(6)} KWD</td>
                                    <td><span class="badge ${st.class}">${st.label}</span></td>
                                </tr>`;
                        });
                    } else {
                        rows = `<tr><td colspan="5" class="text-center">No records found.</td></tr>`;
                    }

                    $('#salesTable tbody').html(rows);
                    $('#totalAmount').text(`${grandTotal.toFixed(6)} KWD`);
                },
                error: function () {
                    $('#salesTable tbody').html('<tr><td colspan="5" class="text-center text-danger">Error loading data.</td></tr>');
                    $('#totalAmount').text('0.000000 KWD');
                }
            });
        }

        $('#filterBtn').click(function () {
            const from = $('#fromDate').val();
            const to = $('#toDate').val();

            // Validate date range
            if ((from && !to) || (!from && to)) {
                showAlert('Please select both From and To dates.');
                return;
            }

            filterApplied = true;
            loadSales();
        });

        $('#resetBtn').click(function () {
            $('#fromDate').val('');
            $('#toDate').val('');
            $('#customerId').val('');
            filterApplied = false;
            loadSales();
        });

        loadSales();
    });
</script>
