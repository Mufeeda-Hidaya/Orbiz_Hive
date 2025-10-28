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
            <h3 class="mb-0">Manage Enquiries</h3>
        </div>
        <div class="col-md-6 text-end">
            <a href="<?= base_url('add_enquiry') ?>" class="btn btn-secondary">Add New Enquiry</a>
        </div>
    </div>
    <hr>
<div class="table-responsive">
    <table class="table table-bordered" id="customersTable" style="width:100%">
        <thead>
            <tr>
                <th class="d-none">ID</th>
                <th>Sl No</th>
                <th>Name</th>
                <th>Address</th>
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
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">Are You Sure You Want To Delete This Enquiry?</div>
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
$(document).ready(function () {

    let deleteId = null;
    const alertBox = $('.alert');
    const deleteModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));

    let originalName = '';
    let originalAddress = '';

    //  Initialize DataTable
    const table = $('#customersTable').DataTable({
        ajax: {
            url: "<?= site_url('enquiry/fetch'); ?>",
            type: "POST",
            dataSrc: function (json) {
                return json.data || [];
            },
        },
        processing: true,
        serverSide: true,
        order: [[0, 'desc']],
        columns: [
            { data: "enquiry_id", visible: false },
            { data: "slno" ,orderable:"false"},
            { data: "name" },
            { data: "address" },
            {
                data: "enquiry_id",
                orderable: false,
                render: function (id, type, row) {
                    return `
                        <div class="d-flex align-items-center gap-3 justify-content-center">
                            <!-- Edit -->
                            <a href="<?= base_url('enquiry/edit/') ?>${id}" 
                            title="Edit Enquiry" 
                            style="color:rgb(13, 162, 199);">
                            <i class="bi bi-pencil-fill"></i>
                            </a>

                            <!-- Delete -->
                            <a href="javascript:void(0);" 
                            class="delete-supplier" 
                            data-id="${id}" 
                            title="Delete" 
                            style="color:#dc3545;">
                            <i class="bi bi-trash-fill"></i>
                            </a>

                            <!-- Convert to Estimate -->
                            ${row.is_converted == 0 ? `
                                <a href="<?= base_url('enquiry/convertToEstimate/') ?>${id}" 
                                title="Convert to Estimate" 
                                style="color:orange;">
                                <i class="bi bi-arrow-right-circle"></i>
                                </a>
                            ` : `
                                <a href="#" 
                                title="Already converted to Estimate" 
                                style="color:gray; cursor:not-allowed;" 
                                onclick="event.preventDefault(); showConvertedAlert();">
                                <i class="bi bi-arrow-right-circle"></i>
                                </a>
                            `}
                        </div>
                    `;
                }
            }


        ]
    });

    //  Delete Enquiry
    $(document).on('click', '.delete-supplier', function () {
        deleteId = $(this).data('id');
        deleteModal.show();
    });

    $('#confirm-delete-btn').click(function () {
        if (!deleteId) return;

        $.ajax({
            url: "<?= site_url('enquiry/delete'); ?>",
            type: "POST",
            dataType: "json",
            data: {
                id: deleteId,
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            },
            success: function (res) {
                if (res.status === 'success') {
                    showAlert('success', res.message);
                    table.ajax.reload(null, false);
                } else {
                    showAlert('danger', res.message);
                }
                deleteModal.hide();
                deleteId = null;
            },
            error: function (xhr) {
                showAlert('danger', 'Error: ' + xhr.statusText);
            }
        });
    });

    //  Alert Message
    function showAlert(type, message) {
        alertBox.removeClass()
            .addClass(`alert alert-${type} text-center position-fixed`)
            .text(message)
            .fadeIn();
        setTimeout(() => alertBox.fadeOut(), 2500);
    }

    // $('#customersTable').on('click', '.convert-estimate', function () {
    // const enquiryId = $(this).data('id');

    // if (!enquiryId) {
    //     alert('Invalid enquiry ID');
    //     return;
    // }

    // Optional: Confirm before converting
    
    
    
    
    $(document).on('click', '.convert-to-estimate', function() {
        const enquiryId = $(this).data('id');
        
        if (!confirm('Are you sure you want to convert this Enquiry to an Estimate?')) return;

        $.ajax({
            url: "<?= site_url('enquiry/markConverted') ?>",
            type: "POST",
            data: { enquiry_id: enquiryId },
            dataType: "json",
            success: function(res) {
                if (res.status === 'success') {
                    // Redirect to convert page
                    window.location.href = "<?= site_url('enquiry/convertToEstimate/') ?>" + enquiryId;
                } else {
                    alert(res.message || 'Failed to convert.');
                }
            },
            error: function() {
                alert('Server error occurred.');
            }
        });
    });


    


});
</script>





// <!-- <script>
    
// debugger;
// alert("hai");
// let table;
// let deleteId = null;
// const alertBox = $('.alert');
// const supplierModal = new bootstrap.Modal(document.getElementById('supplierModal'));
// const deleteModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));

// let originalName = '';
// let originalAddress = '';

// $(document).ready(function () {
    
//     alert("hai");
//     $('#customersTable').DataTable({
//         ajax: {
//             url: "<?= base_url('supplier/fetch') ?>",
//             type: "POST",
//             dataSrc: "data"
//         },
//         processing: true,
//         serverSide: true,
//         order: [[0, 'desc']],
//         columns: [
//             { data: "enquiry_id", visible: false },
//             { data: "slno" },
//             { data: "name" },
//             { data: "address" },
//             {
//                 data: "enquiry_id",
//                 render: data => `
//                     <div class="d-flex gap-2">
//                         <a href="javascript:void(0);" class="edit-supplier" data-id="${data}" title="Edit" style="color:rgb(13, 162, 199);">
//                             <i class="bi bi-pencil-fill"></i>
//                         </a>
//                         <a href="javascript:void(0);" class="delete-supplier" data-id="${data}" title="Delete" style="color: #dc3545;">
//                             <i class="bi bi-trash-fill"></i>
//                         </a>
//                     </div>`
//             }
//         ]
//     });
// });


//     // Add Supplier
//     // $('#addSupplierBtn').click(() => {
//     //     $('#supplierForm')[0].reset();
//     //     $('#supplier_id').val('');
//     //     $('#supplierModalLabel').text('Add Supplier');
//     //     $('#saveSupplierBtn').prop('disabled', false);
//     //     supplierModal.show();

//     //     originalName = '';
//     //     originalAddress = '';
//     //     originalMaxDiscount = '';
//     // });

//     // Edit Supplier
//     $(document).on('click', '.edit-supplier', function () {
//         const id = $(this).data('id');
//         $.get("<?= base_url('supplier/getSupplier/') ?>" + id, function (data) {
//             if (data.status !== 'error') {
//                 $('#supplier_id').val(data.supplier_id);
//                 $('#name').val(data.name);
//                 $('#address').val(data.address);
//                 $('#supplierModalLabel').text('Edit Customer');

//                 originalName = data.name.trim();
//                 originalAddress = data.address.trim();
                

//                 $('#saveSupplierBtn').prop('disabled', true);
//                 supplierModal.show();
//             } else {
//                 showAlert('danger', data.message);
//             }
//         });
//     });

//     // Check if input values changed from original (only in Edit)
//     $('#name, #address').on('input', function () {
//     const isEdit = $('#supplier_id').val() !== '';
//     if (!isEdit) return;

//     const currentName = $('#name').val().trim();
//     const currentAddress = $('#address').val().trim();
    

//     const hasChanged = currentName !== originalName || currentAddress !== originalAddress || currentMaxDiscount !== originalMaxDiscount;
//     $('#saveSupplierBtn').prop('disabled', !hasChanged);
// });


//     // Submit Form
//     $('#supplierForm').submit(function (e) {
//         e.preventDefault();
//         const $btn = $('#saveSupplierBtn');
//         $btn.prop('disabled', true);

//         // Capitalize before sending
//         const name = $('#name').val().trim().replace(/\b\w/g, c => c.toUpperCase());
//         const address = $('#address').val().trim().replace(/\b\w/g, c => c.toUpperCase());
//         $('#name').val(name);
//         $('#address').val(address);

//         $.post("<?= base_url('supplier/create') ?>", $(this).serialize(), function (res) {
//             if (res.status === 'success') {
//                 showAlert('success', res.message);
//                 table.ajax.reload(null, false);
//                 supplierModal.hide();
//             } else {
//                 showAlert('danger', res.message);
//                 $btn.prop('disabled', false);
//             }
//         }, 'json').fail(function () {
//             showAlert('danger', 'Something went wrong!');
//             $btn.prop('disabled', false);
//         });
//     });

//     // Reset button on modal close
//     $('#supplierModal').on('hidden.bs.modal', function () {
//         $('#saveSupplierBtn').prop('disabled', true);
//         originalName = '';
//         originalAddress = '';
//     });

//     // View Estimate
//     $(document).on('click', '.view-estimate', function () {
//         const supplier_id  = $(this).data('id');
//         window.location.href = "<?= base_url('estimate/supplier/') ?>" + supplier_id ;
//     });

//     // Delete
//     $(document).on('click', '.delete-supplier', function () {
//         deleteId = $(this).data('id');
//         deleteModal.show();
//     });

//     $('#confirm-delete-btn').click(function () {
//         if (!deleteId) return;

//         $.post("<?= base_url('supplier/delete') ?>", {
//             id: deleteId,
//             '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
//         }, function (res) {
//             if (res.status === 'success') {
//                 showAlert('success', res.message);
//                 table.ajax.reload(null, false);
//             } else {
//                 showAlert('danger', res.message);
//             }
//             deleteModal.hide();
//             deleteId = null;
//         }, 'json');
//     });

//     function showAlert(type, message) {
//         alertBox.removeClass().addClass(`alert alert-${type} text-center position-fixed`)
//             .text(message).fadeIn();
//         setTimeout(() => alertBox.fadeOut(), 2000);
//     }
// });
// 

