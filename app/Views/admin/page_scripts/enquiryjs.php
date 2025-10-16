<script>
// Enquiry DataTable 
$(document).ready(function() {
    var table = $('#orderTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "<?= base_url('admin/manage_enquiry/orderListAjax') ?>",
            type: "POST",
            dataSrc: "data"
        },
        columns: [
            { data: "slno", className: "text-start" },
            { data: "created_at", className: "text-start" },
            { data: "customer_name", className: "text-start" },
            {
                data: "enquiry_id",
                render: function(id) {
                    return `
                        <div class="text-start">
                            <a href="<?= base_url('admin/manage_enquiry/view_enquiry/') ?>${id}" 
                            title="View" style="color:rgba(37, 41, 43, 1);">
                                <i class="bi bi-eye-fill"></i>
                            </a>
                        </div>
                    `;
                }
            },
            { data: "enquiry_id", visible: false }
        ],
        order: [[4, 'desc']],
        columnDefs: [{ searchable: false, orderable: false, targets: [0, 3] }],
        language: { infoFiltered: "" },
        scrollX: false,
        autoWidth: false
    });
    table.on('order.dt search.dt draw.dt', function () {
        table.column(0, { search: 'applied', order: 'applied' })
            .nodes()
            .each(function (cell, i) {
                var pageInfo = table.page.info();
                cell.innerHTML = pageInfo.start + i + 1;
            });
    });
});

// Enquiry Details DataTable

$(document).ready(function() {
    var enquiryId = $('#enquiry_id').val();

    var table = $('#enquiryItemTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "<?= base_url('admin/manage_enquiry/orderDetailAjax/') ?>" + enquiryId,
            type: "POST",
            dataSrc: "data"
        },
        columns: [
            { data: "slno", className: "text-center" },
            { data: "product_name", className: "text-center" },
            { data: "product_desc", className: "text-start" },
            { data: "quantity", className: "text-center" },
            {
                data: "enquiry_id",
                render: function (id) {
                    return `
                        <div class="text-start">
                            <a href="<?= base_url('admin/view_enquiry/edit/') ?>${id}" title="Edit" style="margin-right:5px;">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <i class="bi bi-trash text-danger icon-delete" data-id="${id}" style="cursor:pointer;" title="Delete"></i>
                        </div>
                    `;
                }
            },
            { data: "enquiry_id", visible: false }
        ],
        order: [[5, 'asc']],
        columnDefs: [{ searchable: false, orderable: false, targets: [0, 4] }],
        language: { infoFiltered: "" },
        scrollX: false,
        autoWidth: false
    });
    table.on('order.dt search.dt draw.dt', function () {
        table.column(0, { search: 'applied', order: 'applied' })
            .nodes()
            .each(function (cell, i) {
                var pageInfo = table.page.info();
                cell.innerHTML = pageInfo.start + i + 1;
            });
    });
});
// Delete enquiry item

    $('#enquiryItemTable').on('click', '.icon-delete', function () {
        var enquiryId = $(this).data('id');
        var row = $(this).closest('tr');

        Swal.fire({
            title: 'Delete Confirmation',
            text: "Are You Sure You Want To Delete This Enquiry?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',       
            cancelButtonColor: '#3085d6',     
            confirmButtonText: 'Delete',      
            cancelButtonText: 'Cancel',       
            reverseButtons: true             
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "<?= base_url('admin/view_enquiry/delete') ?>",
                    type: "POST",
                    dataType: "json",
                    data: { enquiry_id: enquiryId },
                    success: function (res) {
                        if (res.success) {
                            var box = $('#messageBox');
                            box.removeClass('d-none alert-danger').addClass('alert-success');
                            box.text(res.message).fadeIn();
                            setTimeout(function () {
                                window.location.href = "<?= base_url('admin/manage_enquiry') ?>";
                            }, 2000);
                        } else {
                            var box = $('#messageBox');
                            box.removeClass('d-none alert-success').addClass('alert-danger');
                            box.text(res.message).fadeIn();
                        }
                    },
                    error: function (xhr, status, error) {
                        var box = $('#messageBox');
                        box.removeClass('d-none alert-success').addClass('alert-danger');
                        box.text('Something Went Wrong: ' + error).fadeIn();
                    }
                });
            }
        });
    });

    function showMessage(type, message) {
        $('#messageBox')
            .removeClass('d-none alert-success alert-danger')
            .addClass('alert-' + type)
            .text(message);

        setTimeout(() => {
            $('#messageBox').addClass('d-none').text('');
        }, 3000);
    }


</script>