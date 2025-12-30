<?php include "common/header.php"; ?>
<style>
    /* Apply only to #estimateTable to avoid conflict */
    #estimateTable.dataTable thead th {
        padding: 10px 10px !important;
        font-size: 14px !important;
        vertical-align: middle !important;
        white-space: nowrap !important;
    }

    #estimateTable.dataTable tbody td {
        font-size: 15px;
        vertical-align: middle;
        white-space: nowrap;
    }
</style>


<div class="form-control mb-3 right_container">
    <div class="alert d-none text-center position-fixed" role="alert"></div>
    <div class="row align-items-center">
        <div class="col-md-6">
            <h3 class="mb-0">Estimate List</h3>
        </div>
        <!-- <div class="col-md-6 text-end">
            <a href="<?= base_url('add_estimate') ?>" class="btn btn-secondary">Add New Estimate</a>
        </div> -->
    </div>
    <hr>
    <div class="table-responsive">
        <table class="table table-bordered" id="estimateTable" style="width:100%">
            <thead>
                <tr>
                    <th>Sl No</th>
                    <th>Customer</th>
                    <th>Address</th>
                    <th>Subtotal</th>
                    <th>Discount</th>
                    <th>Total (AED)</th>
                    <th>Date</th>
                    <th>Action</th>
                    <th class="d-none">ID</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
</div>

<!-- Confirm Delete Modal -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeleteModalLabel">Confirmation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are You Sure You Want To Delete This Estimate?
            </div>
            <div class="modal-footer">
                <button type="button" id="confirm-delete-btn" class="btn btn-danger">Delete</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<?php include "common/footer.php"; ?>

<script>
    let table = "";
    $(document).ready(function () {
        const alertBox = $('.alert');
        $('#estimateTable_filter input').on('input', function () {
            this.value = this.value.trimStart();
        });
        table = $('#estimateTable').DataTable({
            ajax: {
                url: "<?= base_url('estimate/estimatelistajax') ?>",
                type: "POST",
                dataSrc: "data"
            },
            processing: true,
            serverSide: true,
            paging: true,
            searching: true,
            order: [[8, 'desc']],
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
                        // Convert first letter of each word to uppercase
                        let formatted = data.replace(/\b\w/g, c => c.toUpperCase());
                        // Replace newline characters with <br>
                        return formatted.replace(/\n/g, "<br>");
                    }
                },

                {
                    data: "subtotal",
                    render: data => parseFloat(data).toFixed(4) + " AED"
                },
                {
                    data: 'discount',
                    render: function (data) {
                        if (data === null || data === '' || parseFloat(data) === 0) {
                            return '0.0000 AED';
                        }
                        return parseFloat(data).toFixed(4) + 'AED';
                    }
                },

                {
                    data: "total_amount",
                    render: data => parseFloat(data).toFixed(4) + "AED"
                },
                {
                    data: "date",
                    render: function (data) {
                        if (!data) return "-";
                        const d = new Date(data);
                        const day = String(d.getDate()).padStart(2, '0');
                        const month = String(d.getMonth() + 1).padStart(2, '0');
                        const year = d.getFullYear();
                        return `${day}-${month}-${year}`;
                    }
                },
                {
                    data: "estimate_id",
                    render: function (id, type, row) {
                        return `
                        <div class="d-flex align-items-center gap-3">
                            <a href="<?= base_url('estimate/generateEstimate/') ?>${id}" title="Print" style="color:green;">
                                <i class="bi bi-printer-fill"></i>
                          ${row.is_converted == 0 ? `
                            <a 
                                href="<?= base_url('estimate/edit/') ?>${id}" 
                                title="Edit Estimate" 
                                style="color:rgb(13, 162, 199); cursor:pointer;">
                                <i class="bi bi-pencil-fill"></i>
                            </a>
                        ` : `
                            <a 
                                href="#" 
                                title="This Estimate Has Already Been Converted To An Invoice And Cannot Be Edited" 
                                style="color:gray; cursor:not-allowed;" 
                                onclick="event.preventDefault(); showEstimateEditAlert();">
                                <i class="bi bi-pencil-fill"></i>
                            </a>
                        `}

                            
                            <a href="javascript:void(0);" class="delete-estimate" data-id="${id}" title="Delete" style="color: #dc3545;">
                                <i class="bi bi-trash-fill"></i>
                            </a>
                            ${row.is_converted == 0 ? `
                            <a href="<?= base_url('invoice/convertFromEstimate/') ?>${id}" title="Convert Invoice" style="color:orange;">
                                <i class="bi bi-arrow-right-circle"></i>
                            </a>
                        ` : ''}
                        </div>
                    `;
                    }
                },
                { data: "estimate_id", visible: false } // index 8
            ],
            columnDefs: [
                { targets: 2, width: '350px' },
                { searchable: false, orderable: false, targets: [0, 7] } // DO NOT block column 8!
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

        $(document).on('click', '.delete-estimate', function () {
            deleteId = $(this).data('id');
            deleteModal.show();
        });

        $('#confirm-delete-btn').on('click', function () {
            if (!deleteId) return;

            $.ajax({
                url: "<?= base_url('estimate/delete') ?>",
                type: "POST",
                data: {
                    estimate_id: deleteId,
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                },
                dataType: "json",
                success: function (res) {
                    if (res.status === 'success') {
                        alertBox.removeClass().addClass('alert alert-success text-center position-fixed').text('Estimate Deleted Successfully.').fadeIn();
                        setTimeout(() => alertBox.fadeOut(), 2000);
                        table.ajax.reload(null, false);
                    } else {
                        alertBox.removeClass().addClass('alert alert-warning text-center position-fixed').text(res.message || 'Delete Failed.').fadeIn();
                        setTimeout(() => alertBox.fadeOut(), 3000);
                    }
                },
                error: function () {
                    alertBox.removeClass().addClass('alert alert-danger text-center position-fixed').text('Error Occurred While Deleting Estimate.').fadeIn();
                    setTimeout(() => alertBox.fadeOut(), 3000);
                }
            });

            deleteModal.hide();
            deleteId = null;
        });
    });
</script>