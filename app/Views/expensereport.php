<?php include "common/header.php"; ?>
<style>
    .input-group-text {
    color: #000 !important;
}
    #plainExpenseTable th,
    #plainExpenseTable td {
        vertical-align: middle;
        padding: 16px 30px;
        font-size: 14px;
    }

    #plainExpenseTable th:nth-child(1),
    #plainExpenseTable td:nth-child(1) {
        width: 10%;
        text-align: left;
    }

    #plainExpenseTable th:nth-child(2),
    #plainExpenseTable td:nth-child(2) {
        width: 15%;
    }

    #plainExpenseTable th:nth-child(4),
    #plainExpenseTable td:nth-child(4) {
        width: 20% !important;
        text-align: left;
    }

    #plainExpenseTable th:nth-child(5),
    #plainExpenseTable td:nth-child(5) {
        width: 20% !important;
    }

    .filter_item {
        width: 50%;
    }
   table#plainExpenseTable tbody tr td {
    text-align: left !important;
}

</style>

<div class="form-control mb-3 right_container">
    <div class="alert d-none text-center position-fixed" role="alert"></div>

    <h3 class="mb-3">Expense Report</h3>

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
                <th><strong>Date</strong></th>
                <th><strong>Particular</strong></th>
                <th><strong>Payment Mode</strong></th>
                <th><strong>Amount</strong></th> 
            </tr>
        </thead>
        <tbody></tbody>
        <tfoot>
            <tr>
                <th colspan="4" class="text-end">Total:</th>
                <th id="totalAmount">₹0.000</th>
            </tr>
        </tfoot>
    </table>
</div>
</div>
<?php include "common/footer.php"; ?>

<script>
    function capitalizeFirst(str) {
        return (typeof str === 'string' && str.length > 0)
            ? str.charAt(0).toUpperCase() + str.slice(1)
            : '';
    }
    function formatDate(dateStr) {
        const dateObj = new Date(dateStr);
        if (isNaN(dateObj.getTime())) return dateStr; // Fallback for invalid date

        const day = String(dateObj.getDate()).padStart(2, '0');
        const month = String(dateObj.getMonth() + 1).padStart(2, '0');
        const year = dateObj.getFullYear();
        return `${day}-${month}-${year}`;
    }

    $(document).ready(function () {
        let filterApplied = false;

      
        $.ajax({
            url: "<?= base_url('expense/getExpenseReportAjax') ?>",
            type: "POST",
            dataType: "json",
            success: function (data) {
                const years = new Set();

                data.forEach(item => {
                    const year = new Date(item.date).getFullYear();
                    years.add(year);
                });

                if (years.size > 0) {
                    const minYear = Math.min(...years);
                    const maxYear = new Date().getFullYear() + 1;
                    let options = '<option value="">Filter by Year</option>';
                    for (let y = maxYear; y >= minYear; y--) {
                        options += `<option value="${y}">${y}</option>`;
                    }
                    $('#filterYear').html(options);
                }
            }
        });

        function loadExpenses() {
            $.ajax({
                url: "<?= base_url('expense/getExpenseReportAjax') ?>",
                type: "POST",
                data: {
                    fromDate: $('#fromDate').val(),
                    toDate: $('#toDate').val(),
                    month: $('#filterMonth').val(),
                    year: $('#filterYear').val()
                },
                dataType: "json",
                success: function (data) {
                    let rows = '';
                    let total = 0;

                    if (data.length > 0) {
                        data.forEach((item, index) => {
                            total += parseFloat(item.amount);
                            rows += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${formatDate(item.date)}</td>
                                    <td>${capitalizeFirst(item.particular)}</td>
                                    <td>${capitalizeFirst(item.payment_mode)}</td>
                                    <td>₹${parseFloat(item.amount).toFixed(6)}</td>
                                </tr>
                            `;
                        });
                    } else {
                        rows = `<tr><td colspan="5" class="text-center">No records found.</td></tr>`;
                    }

                    $('#plainExpenseTable tbody').html(rows);
                    $('#totalAmount').text('₹' + total.toFixed(6));
                }
            });
        }

        $('#filterMonth, #filterYear').on('change', function () {
            if (filterApplied) {
                $('#fromDate').val('');
                $('#toDate').val('');
            }
        });

        $('#fromDate, #toDate').on('change', function () {
            const dateValue = $(this).val();
            if (dateValue) {
                const dateObj = new Date(dateValue);
                const month = dateObj.getMonth() + 1;
                const year = dateObj.getFullYear();
                $('#filterMonth').val(month);
                $('#filterYear').val(year);
            }
        });

        $('#filterBtn').click(function () {
            const month = $('#filterMonth').val();
            const year = $('#filterYear').val();
            const from = $('#fromDate').val();
            const to = $('#toDate').val();

            const alertBox = $('.alert');
            alertBox.removeClass('alert-danger alert-success').addClass('d-none');

            if (month && !year) {
                alertBox
                    .removeClass('d-none')
                    .addClass('alert alert-danger')
                    .text('Select Both Month And Year.')
                    .fadeIn();
                setTimeout(() => alertBox.fadeOut(), 2000);
                return;
            }

            if ((from && !to) || (!from && to)) {
                alertBox
                    .removeClass('d-none')
                    .addClass('alert alert-danger')
                    .text('Please Select Both From and To dates For Report.')
                    .fadeIn();
                setTimeout(() => alertBox.fadeOut(), 2000);
                return;
            }

            filterApplied = true;
            loadExpenses();
        });

        $('#resetBtn').click(function () {
            $('#fromDate').val('');
            $('#toDate').val('');
            $('#filterMonth').val('');
            $('#filterYear').val('');
            filterApplied = false;
            loadExpenses();
        });

        loadExpenses();
    });
</script>


