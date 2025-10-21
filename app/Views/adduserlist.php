
<?php include "common/header.php";?>
<div class="form-control mb-3 right_container">
    <div class="alert d-none text-center position-fixed" role="alert"></div>

    <div class="row align-items-center">
        <div class="col-12 col-md-6">
            <h3 class="mb-3 mb-md-0">User Directory</h3>
        </div>
        <div class="col-12 col-md-6 text-end">
            <a href="<?= base_url('adduser') ?>" class="btn btn-secondary  w-md-auto mt-2 mt-md-0">Add New User</a>
        </div>
    </div>

    <hr>
    <!-- Responsive table wrapper -->
    <div class="table-responsive">
        <table class="table " id="userTable">
            <thead class="table-light">
                <tr>
                    <th>Sl No</th>
                    <th>Name</th>
                    <th>Role Name</th>
                    <th>Email</th>
                    <th>Phone Number</th>
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
            <div class="modal-body">Are You Sure You Want To Delete This User?</div>
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
        table = $('#userTable').DataTable({
            ajax: {
                url: "<?= base_url('manageuser/userlistajax') ?>",
                type: "POST",
                dataSrc: "data"
            },
            sort: true,
            searching: true,
            paging: true,
            processing: true,
            serverSide: true,
            
            order: [[6, 'desc']],
            columnDefs: [
                { searchable: false, orderable: false, targets: [0, 4, 5] }
            ],
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
                    data: "name",
                    render: function (data) {
                        return data.replace(/\b\w/g, c => c.toUpperCase());
                    }
                },
                {
                    data: "role_name",
                    render: function (data, type, row) {
                        if (!data || typeof data !== 'string') return '';
                        return data.replace(/\b\w/g, c => c.toUpperCase());
                    }
                },
                { data: "email" },
                { data: "phonenumber" },
                {
                    data: "user_id",
                    render: function (data) {
                        return `
                    <div class="d-flex align-items-center gap-3">
                        <a href="<?= base_url('adduser/') ?>${data}" title="Edit" style="color:rgb(13, 162, 199); margin-right: 10px;">
                            <i class="bi bi-pencil-fill"></i>
                        </a>
                        <a href="javascript:void(0);" class="delete-btn" data-id="${data}" title="Delete" style="color: #dc3545;">
                            <i class="bi bi-trash-fill"></i>
                        </a>
                    </div>
                    `;
                    }
                },
                { data: "user_id", visible: false }
            ],
        });

        let userIdToDelete = null;

        $(document).on('click', '.delete-btn', function () {
            userIdToDelete = $(this).data('id');
            $('#confirmDeleteModal .modal-body').text('Are You Sure You Want To Delete This User?');
            $('#confirmDeleteModal').modal('show');
        });

        $('#confirm-delete-btn').on('click', function () {
            if (!userIdToDelete) return;

            $.ajax({
                url: "<?= base_url('manageuser/delete') ?>",
                type: "POST",
                data: { user_id: userIdToDelete },
                dataType: "json",
                success: function (res) {
                    $('#confirmDeleteModal').modal('hide');
                    const alertBox = $('.alert');

                    if (res.status === 'success') {
                        alertBox.removeClass('d-none alert-warning alert-danger')
                            .addClass('alert-success')
                            .text('User Deleted Successfully')
                            .fadeIn();

                        setTimeout(() => {
                            alertBox.fadeOut(() => {
                                alertBox.addClass('d-none').text('');
                            });
                        }, 2000);

                        $('#userTable').DataTable().ajax.reload(null, false);
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
                        .text('Error Deleting User.')
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