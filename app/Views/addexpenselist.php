    <?php include "common/header.php"; ?>
    <div class="form-control mb-3 right_container">
        <div class="alert d-none text-center position-fixed" role="alert"></div>
        <div class="row align-items-center">
            <div class="col-md-6">
                <h3 class="mb-0">Expense List</h3>
            </div>
            <div class="col-md-6 text-end">
                <a href="<?= base_url('addexpense') ?>" class="btn btn-secondary">Add New Expense</a>
            </div>
        </div>
        <hr>
    <div class="table-responsive">
        <table class="table table-bordered" id="expenseTable">
            <thead>
                <tr>
                    <th>Sl No</th>
                    <th>Date</th>
                    <th>Suppliers</th>
                    <th>Particular</th>
                    <th>Amount</th>
                    <th>Payment Mode</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
    </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">Are you sure you want to delete this company?</div>
                <div class="modal-footer">
                    <button type="button" id="confirm-delete-btn" class="btn btn-danger">Delete</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    <?php include "common/footer.php"; ?>

    <script>
    let table="";
    $(document).ready(function () {
        const alertBox = $('.alert');
        table = $('#expenseTable').DataTable({
            ajax: {
                url: "<?= base_url('expense/getExpensesAjax') ?>",
                type: "POST",
                dataSrc: "data"
            },
            sort:true,
            searching:true,
            paging:true,
            processing: true,
            serverSide: true,
            dom: "<'row mb-3'<'col-sm-6'l><'col-sm-6'f>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row mt-3'<'col-sm-5'i><'col-sm-7'p>>",
            columns: [
                {
                    data: "slno",
                    render: function (data) {
                        return data;
                    }
                },
                { 
                    data: "date" ,
                },
                {
                    data:"supplier_name",
                    render: data => data ? data.replace(/\b\w/g, c => c.toUpperCase()) : ''
                },
                {
                    data: "particular",
                    render: function (data) {
                        return data.replace(/\b\w/g, c => c.toUpperCase());
                    }
                },
                


                {
    data: "amount",
    render: function (data) {
        if (data === null || data === '') return '0.000000';
        let str = data.toString();
        // ensure at least 3 decimal places without rounding
        if (str.indexOf('.') === -1) {
            str += '.000000';
        } else {
            let parts = str.split('.');
            parts[1] = (parts[1] + '000000').slice(0, 6); 
            str = parts[0] + '.' + parts[1];
        }
        return str;
    }
},
                {
                    data: "payment_mode",
                    render: function (data) {
                        return data.replace(/\b\w/g, c => c.toUpperCase());
                    }
                },
                {
                    data: "id",
                    render: function (data) {
                        return `
                        <div class="d-flex align-items-center gap-3">
                            <a href="<?= base_url('addexpense/') ?>${data}" title="Edit" style="color:rgb(13, 162, 199); margin-right: 10px;">
                            <i class="bi bi-pencil-fill"></i>
                            </a>
                            <a href="javascript:void(0);" class="delete-btn" data-id="${data}" title="Delete"style="color: #dc3545;">
                            <i class="bi bi-trash-fill"></i>
                            </a>
                        </div>
                        `;
                    }
                }
            ],
           columnDefs: [
                { searchable: false, orderable: false, targets: [0,4,6,] }, 
                { targets: 3, width: '300px' }
            ],
            order: [[1, 'desc']], 
             language: {
        infoFiltered: "" 
    }
        });
        table.on('order.dt search.dt draw.dt', function () {
            table.column(0, { search: 'applied', order: 'applied' })
                .nodes()
                .each(function (cell, i) {
                    var pageInfo = table.page.info();
                    cell.innerHTML = pageInfo.start + i + 1;
                });
        });
    let expenseIdToDelete = null;

    $(document).on('click', '.delete-btn', function () {
        expenseIdToDelete = $(this).data('id');
        $('#confirmDeleteModal .modal-body').text('Are You Sure You Want To Delete This Expense?');
        $('#confirmDeleteModal').modal('show');
    });

    $('#confirm-delete-btn').on('click', function () {
        if (!expenseIdToDelete) return;

        $.ajax({
            url: "<?= base_url('expense/delete') ?>",
            type: "POST",
            data: { expense_id: expenseIdToDelete },
            dataType: "json",
            success: function (res) {
                $('#confirmDeleteModal').modal('hide');
                const alertBox = $('.alert');

                if (res.status === 'success') {
                    alertBox.removeClass('d-none alert-warning alert-danger')
                            .addClass('alert-success')
                            .text('Expense Deleted Successfully')
                            .fadeIn();

                    setTimeout(() => {
                        alertBox.fadeOut(() => {
                            alertBox.addClass('d-none').text('');
                        });
                    }, 2000);

                    $('#expenseTable').DataTable().ajax.reload(null, false);
                } else {
                    alertBox.removeClass('d-none alert-success alert-danger')
                            .addClass('alert-warning')
                            .text(res.message || 'Delete Failed.')
                            .fadeIn();

                    setTimeout(() => {
                        alertBox.fadeOut(() => {
                            alertBox.addClass('d-none').text('');
                        });
                    }, 3000);
                }
            },
            error: function () {
                $('#confirmDeleteModal').modal('hide');
                const alertBox = $('.alert');

                alertBox.removeClass('d-none alert-success alert-warning')
                        .addClass('alert-danger')
                        .text('Error Deleting Expense.')
                        .fadeIn();

                setTimeout(() => {
                    alertBox.fadeOut(() => {
                        alertBox.addClass('d-none').text('');
                    });
                }, 3000);
            }
        });
    });

    });
    </script>
