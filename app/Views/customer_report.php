<?php include "common/header.php"; ?>
<style>
#customer_id + .select2-container .select2-selection__arrow {
    height: 48px;
}
#customer_id + .select2-container .select2-selection--single {
    height: 48px;
    line-height: 48px;
    padding: 0 12px;
    font-size: 14px;
}
table.dataTable td:first-child, table.dataTable th:first-child {
    width: 44.4px !important;
    text-align: left;
}
</style>

<div class="form-control mb-4 right_container">
    <h3 style="padding: 10px;">Customer Report</h3>
    <div class="alert d-none text-center position-fixed" role="alert"></div>

    <div class="row mb-3">
        <div class="col-md-6">
            <select id="customer_id" class="form-control">
                <option value="">-- Select Customer --</option>
                <?php foreach($customers as $cust): ?>
                    <option value="<?= $cust['customer_id'] ?>"><?= esc($cust['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6 d-flex align-items-end">
            <button id="viewInvoices" class="btn btn-primary me-2" style="height: 48px;">View Invoices</button>
            <button id="viewEstimates" class="btn btn-success" style="height: 48px;">View Estimates</button>
        </div>
    </div>

    <!-- Table Container (hidden by default) -->
    <div id="reportContainer" class="table-responsive mt-4 d-none">
        <table id="customerReportTable" class="table table-bordered table-sm" style="width:100%">
            <thead>
                <tr>
                    <th>SL No</th>
                    <th>No</th>
                    <th>Customer</th>
                    <th>Subtotal</th>
                    <th>Discount</th>
                    <th>Total</th>
                    <th>Paid</th>
                    <th>Balance</th>
                    <th>Date</th>
                    <th class="d-none">ID</th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
        <tr>
            <th colspan="5" style="text-align:right">Total:</th>
            <th id="totalTotal">0 KWD</th>
            <th id="totalPaid">0 KWD</th>
            <th id="totalBalance">0 KWD</th>
            <th colspan="2"></th>
        </tr>
    </tfoot>
        </table>
    </div>
</div>
                </div>
<?php include "common/footer.php"; ?>

<script>
let reportTable;

// Custom global search (ignore spaces)
$.fn.dataTable.ext.search.push(function(settings, data, dataIndex){
    if(settings.nTable.id !== 'customerReportTable') return true;
    let searchInput = $('#customerReportTable_filter input').val() || '';
    let searchValue = searchInput.toLowerCase().replace(/\s/g,'');
    let customer = (data[2] || '').toLowerCase().replace(/\s/g,'');
    return customer.includes(searchValue);
});

// Load report function
function loadReport(type){
    let customer_id = $("#customer_id").val();
    if(!customer_id){
        $(".alert").removeClass("d-none alert-success")
                   .addClass("alert-danger")
                   .text("Please select a customer.");
        return;
    }

    // Show the table
    $("#reportContainer").removeClass("d-none");

    // Destroy previous instance if exists
    if(reportTable){
        reportTable.destroy();
        $("#customerReportTable tbody").empty();
    }

    // Initialize DataTable
    reportTable = $('#customerReportTable').DataTable({
        ajax: {
            url: "<?= base_url('customerreport/getReport') ?>",
            type: "POST",
            data: { customer_id, type },
            dataSrc: ''
        },
        columns: [
    { data: null, orderable: false },          // SL No → no sort icon
    { data: 'no', orderable: false },          // No → no sort icon
    { data: 'customer' },                       // Customer → sortable
    { 
    data: 'subtotal', 
    render: d => parseFloat(d).toFixed(6) + ' KWD' 
    },// Subtotal → sortable
    { data: 'discount', render: d => d == 0 ? '0.000000' : d + ' KWD', orderable: false }, // Discount → no sort icon
   { data: 'total', render: d => parseFloat(d).toFixed(6) + ' KWD' },
{ data: 'paid', render: d => d !== null ? parseFloat(d).toFixed(6) + ' KWD' : '-' },
{ data: 'balance', render: d => d !== null ? parseFloat(d).toFixed(6) + ' KWD' : '-' },
 // Balance → sortable
    { 
        data: 'date',
        orderable: false,
        render: function(d){
            if(!d) return '';
            const date = new Date(d);
            const day = String(date.getDate()).padStart(2,'0');
            const month = String(date.getMonth()+1).padStart(2,'0');
            const year = date.getFullYear();
            return `${day}-${month}-${year}`;
        }
    },     // Date → no sort icon
    { data: 'id', visible: false }            // Hidden ID
],
footerCallback: function ( row, data, start, end, display ) {
        let api = this.api();

        // Helper to parse number
        let parseKWD = i => typeof i === 'string' ? parseFloat(i.replace(/[^0-9.-]+/g,"")) : (i || 0);

        // Total over all pages
        let totalTotal = api.column(5).data().reduce((a, b) => a + parseKWD(b), 0);
        let totalPaid = api.column(6).data().reduce((a, b) => a + parseKWD(b), 0);
        let totalBalance = api.column(7).data().reduce((a, b) => a + parseKWD(b), 0);

        // Update footer
        $(api.column(5).footer()).html(totalTotal.toFixed(6) + ' KWD');
        $(api.column(6).footer()).html(totalPaid.toFixed(6) + ' KWD');
        $(api.column(7).footer()).html(totalBalance.toFixed(6) + ' KWD');
    },

        order: [[1,'desc']],
        columnDefs: [
        { targets: 1, visible: false }  // hide the 2nd column
    ],
        paging: true,
        searching: true,
        initComplete: function(){
            reportTable.draw();

             if(type === 'estimate'){
            reportTable.column(6).visible(false); // Paid
            reportTable.column(7).visible(false); // Balance
        }
        }
    });

    // Serial numbers
    reportTable.on('order.dt search.dt draw.dt', function(){
        reportTable.column(0, { search:'applied', order:'applied'}).nodes().each((cell,i)=>{
            cell.innerHTML = i+1;
        });
    });

    // Custom search listener
    $('#customerReportTable_filter input').off().on('keyup', function(){
        reportTable.draw();
    });
}

// DOM Ready
$(function(){
    // Select2 init
    $('#customer_id').select2({
        placeholder: "-- Select Customer --",
        allowClear: true,
        width: '100%'
    });

    // Attach button click events
    $("#viewInvoices").on("click", function(){ loadReport('invoice'); });
    $("#viewEstimates").on("click", function(){ loadReport('estimate'); });
});
$('#viewEstimates').on('click', function() {
    if(reportTable){
        reportTable.column(3).visible(false); // Subtotal
        reportTable.column(5).visible(false); // Total(KWD)
        reportTable.column(6).visible(false); // Paid
        reportTable.column(7).visible(false); // Balance
    }
});


</script>
