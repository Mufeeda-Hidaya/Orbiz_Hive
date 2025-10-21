<?php include "common/header.php"; ?>
<!-- <?php if (session()->getFlashdata('error')): ?>
  <div class="alert alert-danger alert-fixed text-center">
    <?= session()->getFlashdata('error') ?>
  </div>
<?php endif; ?> -->
<style>
    #invoiceTable.dataTable tbody td {
        font-size: 14px;
        vertical-align: middle;
    }
    .status-badge {
    display: inline-block;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    padding: 0.375rem 0.5625rem;
    border-radius: 1px;
    min-width: 90px;
    text-align: center;
    color: #fff;
}
.status-paid {
    background-color: #28a745;
}
.status-unpaid {
    background-color: #dc3545;
}
.status-pending {
    background-color: #ffc107;
    color: #000;
}

</style>

<div class="form-control mb-3 right_container">
    <div class="alert d-none text-center position-fixed" role="alert"></div>

    <div class="row align-items-center mb-2">
        <div class="col-md-6">
            <h3 class="mb-0">Invoice List</h3>
        </div>
        <div class="col-md-6 text-end">
            <a href="<?= base_url('invoice/add') ?>" class="btn btn-secondary">Add New Invoice</a>
        </div>
    </div>
    <hr>

    <table id="invoiceTable" class="table table-bordered" style="width:100%">
        <thead>
            <tr>
                <th>Sl No</th>
                <th>Customer</th>
                <th>Address</th>
                <th>Subtotal</th>
                <th>Discount</th>
                <th>Total(KWD)</th>
                <th>Date</th>
                <th>Status</th>
                <th>Action</th>
                <th class="d-none">ID</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">Are You Sure You Want To Delete This Invoice?</div>
            <div class="modal-footer">
                <button type="button" id="confirm-delete-btn" class="btn btn-danger">Delete</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
</div>
<?php include "common/footer.php"; ?>

<script>
let table;
const alertBox = $('.alert');
$(document).ready(function () {
    $('#invoiceTable_filter input').on('input', function () {
        this.value = this.value.trimStart();
    });
     table = $('#invoiceTable').DataTable({
        ajax: {
            url: "<?= base_url('invoice/invoicelistajax') ?>",
            type: "POST",
            dataSrc: "data"
        },
        processing: true,
        serverSide: true,
        paging: true,
        searching: true,
        order: [[9, 'desc']], // Hidden ID column
        dom: "<'row mb-3'<'col-sm-6'l><'col-sm-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row mt-3'<'col-sm-5'i><'col-sm-7'p>>",
        columns: [
            { data: "slno" },
            {
                data: "customer_name",
                render: data => data ? data.replace(/\b\w/g, c => c.toUpperCase()) : ''
            },
            {
                data: "customer_address",
                 render: data => {
                        if (!data) return '';
                        let formatted = data.replace(/\b\w/g, c => c.toUpperCase());
                        return formatted.replace(/\n/g, "<br>");
                    }
            },
            {
    data: "subtotal",
    render: function(data, type, row) {
        return data ? parseFloat(data).toFixed(6) + ' KWD' : '0.000000 KWD';
    }
},

           {
    data: 'discount',
    render: function (data) {
        if (data === null || data === '' || parseFloat(data) === 0) {
            return '0.0000000 KWD';
        }
        return parseFloat(data).toFixed(6) + ' KWD';
    }
},

            {
                data: "total_amount",
                render: data => parseFloat(data).toFixed(6) + " KWD"
            },
            { data: 'invoice_date' },
            {
            data: 'status',
            render: function (data, type, row) {
                if (!data) return '';

                    let className = '';
                    let text = data.toUpperCase();

                    if (data.toLowerCase() === 'paid') className = 'status-badge status-paid';
                    else if (data.toLowerCase() === 'unpaid') className = 'status-badge status-unpaid';
                    else if (data.toLowerCase() === 'pending') className = 'status-badge status-pending';
                    else className = 'status-badge bg-warning';

                    return `<span class="${className}">${text}</span>`;
                }
            },
           {
                data: "invoice_id",
                render: function (id, type, row) {
                    const status = row.status.toLowerCase();
                    const isPaidOrPartial = status === 'paid' || status === 'partial paid';
                    const paymentMode = (row.payment_mode || '').toLowerCase();

                    const voucherLabel = paymentMode === 'cash' ? 'Receipt' : 'Voucher';
                    const voucherUrl = paymentMode === 'cash'
                        ? "<?= base_url('receiptvoucher/') ?>" + id
                        : "<?= base_url('paymentvoucher/') ?>" + id;

                    return `
                        <div class="d-flex gap-2">
                            <a href="<?= base_url('invoice/print/') ?>${id}" title="Print" style="color:green;">
                                <i class="bi bi-printer-fill"></i>
                            </a>

                           <a
                                href="${isPaidOrPartial ? '#' : '<?= base_url('invoice/edit/') ?>' + id}"
                                title="${isPaidOrPartial ? 'This invoice cannot be edited' : 'Edit Invoice'}"
                                style="color:${isPaidOrPartial ? 'gray' : 'rgb(13, 162, 199)'}; cursor: ${isPaidOrPartial ? 'not-allowed' : 'pointer'};"
                                ${isPaidOrPartial ? 'onclick="event.preventDefault(); showEditAlert();"' : ''}>
                                    <i class="bi bi-pencil-fill"></i>
                            </a>


                            <a href="javascript:void(0);" class="delete-invoice" data-id="${id}" title="Delete" style="color: #dc3545;">
                                <i class="bi bi-trash-fill"></i>
                            </a>
                        </div>
                    `;
                }
            },

            { data: "invoice_id", visible: false } 
        ],
        columnDefs: [
            { targets: 2, width: '250px' },
            { searchable: false, orderable: false, targets: [0,4,8] }
        ]
    });
    table.on('order.dt search.dt draw.dt', function () {
        table.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
            var pageInfo = table.page.info();
            cell.innerHTML = pageInfo.start + i + 1;
        });
    });
    let deleteId = null;
    const deleteModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
    // Delete icon clicked
    $(document).on('click', '.delete-invoice', function () {
        deleteId = $(this).data('id');
        deleteModal.show();
    });

    // Confirm delete
    $('#confirm-delete-btn').click(function () {
        if (!deleteId) return;

        $.post("<?= base_url('invoice/delete/') ?>" + deleteId, {
            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
        }, function () {
            showAlert('success', 'Invoice Deleted Successfully.');
            table.ajax.reload(null, false);
        }).fail(function () {
            showAlert('danger', 'Failed To Delete Invoice.');
        }).always(function () {
            deleteModal.hide();
            deleteId = null;
        });
    });

    function showAlert(type, message) {
        alertBox.removeClass().addClass(`alert alert-${type} text-center position-fixed`)
            .text(message).fadeIn();
        setTimeout(() => alertBox.fadeOut(), 2000);
    }
function showEditAlert() {
    const alertBox = document.querySelector('.alert');
    alertBox.textContent = 'Paid or Partial Paid invoice cannot be edited.';
    alertBox.classList.remove('d-none');

    setTimeout(() => {
        alertBox.classList.add('d-none');
    }, 3000);
}


});
</script>
