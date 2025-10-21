<?php include "common/header.php"; ?>
<style>
      .input-group-text {
    color: #000 !important;
}
    #plainExpenseTable th,
    #plainExpenseTable td {
        vertical-align: middle;
        padding: 16px 16px;
        font-size: 14px;
    }

    #plainExpenseTable th:nth-child(1),
    {
        width: 10%;
        text-align: center;
    }

    #plainExpenseTable td:nth-child(1) {
        text-align: left !important;
    }

    #plainExpenseTable th:nth-child(2),
     {
        width: 15%;
         text-align: center !important;
    }
    #plainExpenseTable td:nth-child(2){
        text-align: left !important;
    }
    #plainExpenseTable th:nth-child(3),
    {
        width: 15% !important;
        text-align: center !important;
    }
    #plainExpenseTable td:nth-child(3){
        text-align: left !important;
    }
     #plainExpenseTable th:nth-child(4),
     {
        width: 15% !important;
        text-align: center !important;
    }
    #plainExpenseTable td:nth-child(4){
         text-align: left !important;
    }
    #plainExpenseTable th:nth-child(5),
    {
        width: 15% !important;
        text-align: center !important;
    }
    #plainExpenseTable td:nth-child(5){
        text-align: left !important;
    }
    #plainExpenseTable th:nth-child(6),
    {
        width: 15% !important;
        text-align: center !important;
    }
    #plainExpenseTable td:nth-child(6) {
         text-align: left !important;
    }
    #plainExpenseTable th:nth-child(7),
    {
        width: 15% !important;
        text-align: center !important;
    }
    #plainExpenseTable td:nth-child(7){
        text-align: left !important;
    }

.bg-success {
    background-color: #28a745 !important;
    color: #ffffff !important;
}
    .filter_item {
        width: 50%;
    }
</style>

<div class="form-control mb-3 right_container">
    <div class="alert d-none text-center position-fixed" role="alert"></div>

    <h3 class="mb-3">Company Ledger</h3>

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

        <select id="filterMonth" class="filter_item form-control">
            <option value="">Filter by Month</option>
            <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?= $m ?>"><?= date('F', mktime(0, 0, 0, $m, 10)) ?></option>
            <?php endfor; ?>
        </select>
        <select id="filterYear" class="filter_item form-control">
            <option value="">Filter by Year</option>
            <?php
            $currentYear = date('Y');
            $endYear = $currentYear + 5;
            for ($y = $endYear; $y >= 2000; $y--): ?>
                <option value="<?= $y ?>"><?= $y ?></option>
            <?php endfor; ?>
        </select>
        <button id="filterBtn" class="btn btn-primary">Apply Filter</button>
        <button id="resetBtn" class="btn btn-secondary">Reset</button>
    </div>

    <table class="table table-bordered" id="plainExpenseTable">
        <thead>
            <tr>
                <th><strong>SI No</strong></th>
                <!-- <th><strong>Invoice ID</strong></th> -->
                <th><strong>Date</strong></th>
                <th><strong>Customer</strong></th>
                <th><strong>Total Amount</strong></th>
                <th><strong>Paid Amount</strong></th>
                <th><strong>Balance Amount</strong></th>
                <th><strong>Payment Mode</strong></th>
                <th><strong>Status</strong></th>
            </tr>
        </thead>
        <tbody></tbody>
        <tfoot>
    <tr>
        <th colspan="3" class="text-end">Total:</th>
        <th id="totalAmount">₹0.000000</th>
        <th id="totalPaid">₹0.000000</th>
        <th id="totalBalance">₹0.000000</th>
        <th colspan="2"></th>
    </tr>
</tfoot>

    </table>
</div>
</div>
<?php include "common/footer.php"; ?>
<script>
// function formatDate(dateStr) {
//     const dateObj = new Date(dateStr);
//     const day = String(dateObj.getDate()).padStart(2, '0');
//     const month = String(dateObj.getMonth() + 1).padStart(2, '0');
//     const year = dateObj.getFullYear();
//     return `${day}-${month}-${year}`;
// }
function formatDate(dateStr) {
    if (!dateStr) return '';
    const parts = dateStr.split('-'); // ['2025','10','03']
    const year = parts[0];
    const month = parts[1];
    const day = parts[2];
    return `${day}-${month}-${year}`; // DD-MM-YYYY
}

$(document).ready(function () {
    let filterApplied = false;

    function showAlert(type, message) {
        const alertBox = $('.alert');
        alertBox.removeClass('d-none alert-success alert-danger')
                .addClass(`alert alert-${type}`)
                .text(message)
                .fadeIn();
        setTimeout(() => alertBox.fadeOut(), 2000);
    }

    function loadPaidInvoices() {
        const from = $('#fromDate').val();
        const to = $('#toDate').val();
        const month = $('#filterMonth').val();
const year = $('#filterYear').val();

// Check if only one of month/year is selected
if ((month && !year) || (!month && year)) {
    showAlert('danger', 'Select Both Month And Year.');
    return;
}


        if ((from && !to) || (!from && to)) {
            showAlert('danger', 'Please Select Both From and To dates For Report.');
            return;
        }

        $.ajax({
            url: "<?= base_url('companyledger/getPaidInvoices') ?>",
            method: "POST",
            data: { from, to, month, year },
            dataType: "json",
            success: function (response) {
                if (response.status !== 'success') {
                    showAlert('danger', response.message);
                    return;
                }

                const res = response.data;
                let rows = '';
                let totalAmountSum = 0;
                let totalPaidSum = 0;
                let totalBalanceSum = 0;

                if (res.length > 0) {
                    res.forEach((invoice, index) => {
                        const totalAmount = parseFloat(invoice.total_amount) || 0;
                        const paidAmount = parseFloat(invoice.paid_amount) || 0;
                        const balanceAmount = totalAmount - paidAmount;

                        totalAmountSum += totalAmount;
                        totalPaidSum += paidAmount;
                        totalBalanceSum += balanceAmount;

                        // Payment mode formatting
                        const paymentMode = invoice.payment_mode 
                            ? invoice.payment_mode
                                .replace('_', ' ')
                                .toLowerCase()
                                .replace(/\b\w/g, c => c.toUpperCase()) 
                            : '-';

                        // Status badge
                        const statusBadge = invoice.status === 'paid'
                            ? '<span class="badge bg-success w-100">Paid</span>'
                            : (invoice.status === 'partial paid'
                                ? '<span class="badge bg-warning text-white w-100">Partial Paid</span>'
                                : '<span class="badge bg-secondary w-100">Unknown</span>');

                        rows += `
                            <tr>
                                <td class="text-center">${index + 1}</td>
                                <td>${formatDate(invoice.invoice_date)}</td>
                                <td>${invoice.customer_name}</td>
                                <td class="text-end">₹${totalAmount.toFixed(6)}</td>
                                <td class="text-end">₹${paidAmount.toFixed(6)}</td>
                                <td class="text-end">₹${balanceAmount.toFixed(6)}</td>
                                <td class="text-center">${paymentMode}</td>
                                <td class="text-center">${statusBadge}</td>
                            </tr>
                        `;
                    });
                } else {
                    rows = `<tr><td colspan="9" class="text-center">No paid or partial paid invoices found.</td></tr>`;
                }

                $('#plainExpenseTable tbody').html(rows);

                // Update totals in footer
                $('#totalAmount').text('₹' + totalAmountSum.toFixed(6));
                $('#totalPaid').text('₹' + totalPaidSum.toFixed(6));
                $('#totalBalance').text('₹' + totalBalanceSum.toFixed(6));
            }
        });
    }

    $('#fromDate, #toDate').on('change', function () {
        const dateVal = $(this).val();
        if (dateVal) {
            const dateObj = new Date(dateVal);
            const month = dateObj.getMonth() + 1;
            const year = dateObj.getFullYear();
            $('#filterMonth').val(month);
            $('#filterYear').val(year);
        }
    });

    $('#filterMonth, #filterYear').on('change', function () {
        if (filterApplied) {
            $('#fromDate').val('');
            $('#toDate').val('');
        }
    });

    $('#filterBtn').click(function () {
        filterApplied = true;
        loadPaidInvoices();
    });

    $('#resetBtn').click(function () {
        $('#fromDate, #toDate, #filterMonth, #filterYear').val('');
        filterApplied = false;
        loadPaidInvoices();
    });

    loadPaidInvoices();
});
</script>


<!-- <td>${invoice.invoice_id}</td> -->
