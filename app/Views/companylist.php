<?php include "common/header.php"; ?>
<style>
    #companiesTable.dataTable tbody td {
        font-size: 14px;
        vertical-align: middle;
    }
</style>


<div class="form-control mb-3 right_container">
    <div class="alert d-none text-center position-fixed" role="alert"></div>

    <div class="row align-items-center mb-2">
        <div class="col-md-6">
            <h3 class="mb-0">Manage Companies</h3>
        </div>
        <div class="col-md-6 text-end">
            <a href="<?= base_url('addcompany') ?>" class="btn btn-secondary">Add New Company</a>
        </div>
    </div>

    <hr>
    <div class="table-responsive">
        <table class="table table-bordered" id="companiesTable" style="width:100%">
            <thead>
                <tr>
                    <th class="d-none">ID</th>
                    <th>Sl No</th>
                    <th>Name</th>
                    <th>Address</th>
                    <th>Tax Number</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Logo</th>
                    <th style="width: 100px;">Action</th>

                </tr>
            </thead>
            <tbody></tbody>
        </table>
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
            <div class="modal-body">Are You Sure You Want To Delete This Company?</div>
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
    let table = '';
    let deleteId = null;
    const alertBox = $('.alert');

    $(document).ready(function () {
        const deleteModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));

        table = $('#companiesTable').DataTable({
            ajax: {
                url: "<?= base_url('managecompany/companylistjson') ?>",
                type: "POST",
                dataSrc: "data"
            },
            sort: true,
            searching: true,
            paging: true,
            processing: true,
            serverSide: true,
            order: [[0, 'desc']],
            columnDefs: [
                { targets: 1, orderable: false, width: "30px" },
                { targets: 2, width: "150px" },
                { targets: 3, width: "300px" },
                { targets: 5, orderable: false },
                { targets: 6, orderable: false },
                { targets: 7, orderable: false, width: "30px" },
                { targets: 8, orderable: false, width: "30px" }
            ],
            dom: "<'row mb-3'<'col-sm-6'l><'col-sm-6'f>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row mt-3'<'col-sm-5'i><'col-sm-7'p>>",

            columns: [
                { data: "company_id", visible: false },
                { data: "slno" },
                {
                    data: "company_name",
                    render: data => data ? data.replace(/\b\w/g, c => c.toUpperCase()) : ''
                },
                {
                    data: "address",
                     render: data => {
                        if (!data) return '';
                        let formatted = data.replace(/\b\w/g, c => c.toUpperCase());
                        return formatted.replace(/\n/g, "<br>");
                    }
                },
                {
                    data: "tax_number",
                    render: data => data && data.trim() !== "" ? data : '-N/A-'
                },
                {
                    data: "email",
                    render: data => data && data.trim() !== "" ? data : '-N/A-'
                },
                { data: "phone" },
                {
                    data: "company_logo",
                    className: "logo-col",
                    render: data => data ? `<img src="<?= base_url('public/uploads/') ?>${data}" width="30">` : ''
                },
                {
                    data: "company_id",
                    render: data => `
            <div class="d-flex gap-2">
                <a href="<?= base_url('addcompany/') ?>${data}" title="Edit" style="color:rgb(13, 162, 199); margin-right: 10px;">
                    <i class="bi bi-pencil-fill"></i>
                </a>
                <a href="javascript:void(0);" class="delete-company" data-id="${data}" title="Delete" style="color: #dc3545;">
                    <i class="bi bi-trash-fill"></i>
                </a>
            </div>`
                }
            ]

        });

        $(document).on('click', '.delete-company', function () {
            deleteId = $(this).data('id');
            deleteModal.show();
        });

        $('#confirm-delete-btn').click(function () {
            if (!deleteId) return;

            $.post("<?= base_url('managecompany/delete') ?>", {
                id: deleteId,
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            }, function (res) {
                if (res.status === 'success') {
                    alertBox.removeClass().addClass('alert alert-success text-center position-fixed')
                        .text(res.message).fadeIn();
                } else {
                    alertBox.removeClass().addClass('alert alert-danger text-center position-fixed')
                        .text(res.message).fadeIn();
                }
                setTimeout(() => alertBox.fadeOut(), 2000);
                table.ajax.reload(null, false);
            }).always(() => {
                deleteModal.hide();
                deleteId = null;
            });
        });
    });
</script>