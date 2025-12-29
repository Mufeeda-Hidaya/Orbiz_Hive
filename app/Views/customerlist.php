<?php include "common/header.php"; ?>

<style>
    #customersTable.dataTable tbody td {
        font-size: 14px;
        vertical-align: middle;
    }
</style>

<div class="form-control mb-3 right_container">
    <div class="alert d-none text-center position-fixed" role="alert"></div>

    <div class="row align-items-center mb-2">
        <div class="col-md-6">
            <h3 class="mb-0">Manage Customers</h3>
        </div>
        <div class="col-md-6 text-end">
            <button class="btn btn-secondary" id="addCustomerBtn">Add New Customer</button>
        </div>
    </div>
    <hr>
    <div class="table-responsive">
        <table class="table table-bordered" id="customersTable" style="width:100%">
            <thead>
                <tr>
                    <th class="d-none">ID</th>
                    <th>Sl No</th>
                    <th>Customer Name</th>
                    <th>Contact Person Name</th>
                    <th>Address</th>
                    <th>Phone Number</th>
                    <th style="width: 100px;">Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Modal: Add/Edit Customer -->
<div class="modal fade" id="customerModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="customerForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="customerModalLabel">Add Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="customer_id" id="customer_id">
                    <div class="mb-3">
                        <label>Contact Person Name</label>
                        <input type="text" name="contact_person_name" id="contact_person_name" class="form-control" required
                            autocomplete="off" style="text-transform: capitalize;">
                    </div>

                    <div class="mb-3">
                        <label>Customer Name</label>
                        <input type="text" name="name" id="name" class="form-control" required autocomplete="off"
                            style="text-transform: capitalize;">
                    </div>
                    <div class="mb-3">
                        <label>Address</label>
                        <textarea name="address" id="address" class="form-control" required autocomplete="off"
                            style="text-transform: capitalize;"></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Phone</label>
                        <input type="text" name="phone" id="phone" class="form-control" required autocomplete="off"
                            minlength="7" maxlength="15" pattern="^[0-9+\s]{7,15}$"
                            oninput="this.value = this.value.replace(/[^0-9+\s]/g, '')"
                            onkeypress="return /[0-9+\s]/.test(event.key)">
                    </div>
                    <!-- <label>Phone</label>
                    <input type="text" name="phone" id="phone" class="form-control" required minlength="7"
                        maxlength="25" pattern="^[0-9+\s]{7,25}$"
                        oninput="this.value = this.value.replace(/[^0-9+\s]/g, '')"
                        onkeypress="return /[0-9+\s]/.test(event.key)"> -->

                    <!-- <div class="mb-3">
                        <label>Max Discount</label>
                        <textarea name="discount" id="discount" class="form-control" required style="text-transform: capitalize;"></textarea>
                    </div> -->
                    <div class="modal-footer">
                        <button type="submit" id="saveCustomerBtn" class="btn btn-primary" disabled>Save</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">Are You Sure You Want To Delete This Customer?</div>
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
    let deleteId = null;
    const alertBox = $('.alert');
    // const customerModal = new bootstrap.Modal(document.getElementById('customerModal'));
    const deleteModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
    var customerModal = new bootstrap.Modal(document.getElementById('customerModal'));
    // let originalName = '', originalAddress = '', originalPhone = '';

    let originalName = '';
    let originalAddress = '';
    let originalPhone = '';

    $(document).ready(function () {
        // Load DataTable
        table = $('#customersTable').DataTable({
            ajax: {
                url: "<?= base_url('customer/fetch') ?>",
                type: "POST",
                dataSrc: "data"
            },
            processing: true,
            serverSide: true,
            order: [[0, 'desc']],
            columnDefs: [
                { targets: 0, visible: false },
                { targets: 1, orderable: false, width: "30px" },
                { targets: 2, width: "150px" },
                { targets: 3, width: "150px" },
                { targets: 4, width: "150px" },
                { targets: 5, orderable: false, width: "150px" },
                { targets: 6, orderable: false }
            ],
            dom: "<'row mb-3'<'col-sm-6'l><'col-sm-6'f>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row mt-3'<'col-sm-5'i><'col-sm-7'p>>",
            columns: [
                { data: "customer_id" },
                { data: "slno" },
                {
                    data: "name",
                    render: data => data ? data.replace(/\b\w/g, c => c.toUpperCase()) : ''
                },
                {
                    data: "contact_person_name",
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
                    data: "phone",
                    render: data => data ? data : ''
                },
                {
                    data: "customer_id",
                    render: data => `
                    <div class="d-flex gap-2">
                        <a href="javascript:void(0);" class="view-estimate" data-id="${data}" title="View Estimates" style="color:green;">
                            <i class="bi bi-eye-fill"></i>
                        </a>
                        <a href="javascript:void(0);" class="edit-customer" data-id="${data}" title="Edit" style="color:rgb(13, 162, 199);">
                            <i class="bi bi-pencil-fill"></i>
                        </a>
                        <a href="javascript:void(0);" class="delete-customer" data-id="${data}" title="Delete" style="color: #dc3545;">
                            <i class="bi bi-trash-fill"></i>
                        </a>
                    </div>`
                }
            ]

        });

        // Add Customer
        $('#addCustomerBtn').click(() => {
            $('#customerForm')[0].reset();
            $('#customer_id').val('');
            $('#customerModalLabel').text('Add Customer');
            $('#saveCustomerBtn').prop('disabled', false);
            customerModal.show();

            originalName = '';
            originalAddress = '';
            originalPhone = '';
            originalMaxDiscount = '';
        });

        // Edit Customer
        $(document).on('click', '.edit-customer', function () {
            const id = $(this).data('id');

            $.get("<?= base_url('customer/getCustomer/') ?>" + id, function (res) {
                if (res.status === 'success') {
                    const customer = res.customer;

                    $('#customer_id').val(customer.customer_id);
                    $('#name').val(customer.name);
                    $('#address').val(customer.address);
                    $('#phone').val(customer.phone);

                    // Store original values to detect changes
                    originalName = customer.name.trim();
                    originalAddress = customer.address.trim();
                    originalPhone = customer.phone ? customer.phone.trim() : '';

                    $('#customerModalLabel').text('Edit Customer');
                    $('#saveCustomerBtn').prop('disabled', true);
                    customerModal.show();
                } else {
                    showAlert('danger', res.message);
                }
            });
        });

        // Check if input values changed from original (only in Edit)
        $('#name, #address, #phone').on('input', function () {
            const isEdit = $('#customer_id').val() !== '';
            if (!isEdit) return;

            const currentName = $('#name').val().trim();
            const currentAddress = $('#address').val().trim();
            const currentPhone = $('#phone').val().trim();

            const hasChanged = currentName !== originalName || currentAddress !== originalAddress || currentPhone !== originalPhone;
            $('#saveCustomerBtn').prop('disabled', !hasChanged);
        });

        // Submit Form
        $('#customerForm').submit(function (e) {
            e.preventDefault();
            const $btn = $('#saveCustomerBtn');
            $btn.prop('disabled', true);

            // Capitalize before sending
            const name = $('#name').val().trim().replace(/\b\w/g, c => c.toUpperCase());
            const address = $('#address').val().trim().replace(/\b\w/g, c => c.toUpperCase());
            $('#name').val(name);
            $('#address').val(address);

            $.post("<?= base_url('customer/create') ?>", $(this).serialize(), function (res) {
                if (res.status === 'success') {
                    showAlert('success', res.message);
                    table.ajax.reload(null, false);
                    customerModal.hide();
                } else {
                    showAlert('danger', res.message);
                    $btn.prop('disabled', false);
                }
            }, 'json').fail(function () {
                showAlert('danger', 'Something went wrong!');
                $btn.prop('disabled', false);
            });
        });

        // Reset button on modal close
        $('#customerModal').on('hidden.bs.modal', function () {
            $('#saveCustomerBtn').prop('disabled', true);
            originalName = '';
            originalAddress = '';
        });

        // View Estimate
        $(document).on('click', '.view-estimate', function () {
            const customerId = $(this).data('id');
            window.location.href = "<?= base_url('estimate/customer/') ?>" + customerId;
        });

        // Delete
        $(document).on('click', '.delete-customer', function () {
            deleteId = $(this).data('id');
            deleteModal.show();
        });

        $('#confirm-delete-btn').click(function () {
            if (!deleteId) return;

            $.post("<?= base_url('customer/delete') ?>", {
                id: deleteId,
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            }, function (res) {
                if (res.status === 'success') {
                    showAlert('success', res.message);
                    table.ajax.reload(null, false);
                } else {
                    showAlert('danger', res.message);
                }
                deleteModal.hide();
                deleteId = null;
            }, 'json');
        });

        function showAlert(type, message) {
            alertBox.removeClass().addClass(`alert alert-${type} text-center position-fixed`)
                .text(message).fadeIn();
            setTimeout(() => alertBox.fadeOut(), 2000);
        }
    });
</script>