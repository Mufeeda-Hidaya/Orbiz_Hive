<script>
    $(document).ready(function () {
    const $saveBtn = $('#saveBtn');
    const $roleForm = $('#roleForm');
    const $roleName = $('#role_name');

    let originalData = $roleForm.serialize();

    $saveBtn.prop('disabled', true).css({ opacity: 0.6, pointerEvents: 'none' });
    function checkFormChanges() {
        let currentData = $roleForm.serialize();
        if (currentData !== originalData) {
            $saveBtn.prop('disabled', false).css({ opacity: 1, pointerEvents: 'auto' });
        } else {
            $saveBtn.prop('disabled', true).css({ opacity: 0.6, pointerEvents: 'none' });
        }
    }
    $roleForm.on('input change', 'input, select, textarea', checkFormChanges);

    function resetFormState() {
        originalData = $roleForm.serialize();
        $saveBtn.prop('disabled', true).css({ opacity: 0.6, pointerEvents: 'none' });
    }
       const $selectAll = $("#select_all_permissions");
    const $permissions = $(".permission-checkbox");
    if ($permissions.length > 0 && $permissions.filter(":checked").length === $permissions.length) {
        $selectAll.prop("checked", true);
    }
    $selectAll.on("change", function () {
        $permissions.prop("checked", $(this).is(":checked"));
    });

    $permissions.on("change", function () {
        if ($permissions.filter(":checked").length === $permissions.length) {
            $selectAll.prop("checked", true);
        } else {
            $selectAll.prop("checked", false);
        }
    });
    $saveBtn.prop('disabled', true).css({ opacity: 0.6, pointerEvents: 'none' });
        function enableSaveButton() {
            $saveBtn.prop('disabled', false).css({ opacity: 1, pointerEvents: 'auto' });
        } 
        $roleName.on('input', enableSaveButton);
        $permissions.on('change', enableSaveButton);
        $('#roleForm').on('submit', function (e) {
            e.preventDefault();
            var form = $(this);
            var url = form.attr('action');
             $saveBtn.prop('disabled', true).css({ opacity: 0.6, pointerEvents: 'none' });
           $.post(url, $('#roleForm').serialize(), function(response) {
                $('#messageBox').removeClass('d-none alert-success alert-danger'); 

                if (response.status === 'success' || response.status == 1) {
                    $('#messageBox')
                        .addClass('alert-success')
                        .text(response.msg || response.message)
                        .show();

                        setTimeout(function () {
                            window.location.href = "<?php echo base_url('admin/manage_roles'); ?>";
                        }, 1500);
                         resetFormState();
                } else {
                    $('#messageBox')
                        .addClass('alert-danger')
                        .text(response.message || 'Something went wrong')
                        .show();
                        checkFormChanges();
                }

                setTimeout(function() {
                    $('#messageBox').fadeOut();
                }, 2000);
            }, 'json');
        });

});
    
// data table


$(document).ready(function () {
    var table = $('#rolesList').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "<?= base_url('admin/manage_roles/rolelistAjax') ?>",
            type: "POST",
            dataSrc: "data"
        },
        columns: [
            { data: "slno", className: "text-start" },
            { data: "role_name", className: "text-start" },
            { data: "status_switch", className: "text-start" },
            {
                data: "role_id",
                render: function (id) {
                    return `
                        <div class="text-start">
                            <a href="<?= base_url('admin/manage_roles/edit/') ?>${id}" title="Edit" style="margin-right:5px;">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <i class="bi bi-trash text-danger icon-delete" data-id="${id}" style="cursor:pointer;"></i>
                        </div>
                    `;
                }
            },
            { data: "role_id", visible: false }
        ],
        order: [[4, 'desc']],
        columnDefs: [
            { searchable: false, orderable: false, targets: [0, 3] }
        ],
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
    function showMessage(type, message) {
        $('#messageBox')
            .removeClass('d-none alert-success alert-danger')
            .addClass('alert-' + type)
            .text(message);

        setTimeout(() => {
            $('#messageBox').addClass('d-none').text('');
        }, 3000);
    }



    //delete roles

    $('#rolesList').on('click', '.icon-delete', function () {
    var roleId = $(this).data('id');
    var row = $(this).closest('tr');

    Swal.fire({
        title: 'Delete Confirmation',
        text: "Are you sure you want to delete this user?",
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
                url: "<?= base_url('admin/manage_roles/delete') ?>",
                type: "POST",
                dataType: "json",
                data: { role_id: roleId },
                success: function (res) {
                    if (res.success) {
                        showMessage('success', res.message);
                        table.ajax.reload(null, false); 
                    } else {
                        showMessage('danger', res.message);
                    }
                },
                error: function (xhr, status, error) {
                    showMessage('danger', 'Something went wrong: ' + error);
                }
            });
        }
    });
    });


//Add category

// var baseUrl = "<?= base_url() ?>";
// $('#rolesSubmit').click(function(e) {
//     e.preventDefault(); 
//     var url = baseUrl + "admin/roles/save"; 

//     $.post(url, $('#createroles').serialize(), function(response) {
//         if (response.status == 1) {
//             $('#messageBox')
//                 .removeClass('alert-danger')
//                 .addClass('alert-success')
//                 .text(response.msg || 'Roles Created Successfully!')
//                 .show();

//             setTimeout(function() {
//                 window.location.href = baseUrl + "admin/roles/"; 
//             }, 1500);
//         } else {
//             $('#messageBox')
//                 .removeClass('alert-success')
//                 .addClass('alert-danger')
//                 .text(response.message || 'Please Fill all the Data')
//                 .show();
//         }

//         setTimeout(function() {
//             $('#messageBox').empty().hide();
//         }, 2000);
//     }, 'json');
// });

//Active and Inactive status
var baseUrl = "<?= base_url('/') ?>"; // ensures trailing slash

$(document).on('click', '.status-toggle', function() {
    var badge = $(this);
    var roleId = badge.data('id');

    var currentStatus = badge.text().trim().toLowerCase() === 'active' ? 1 : 2;
    var newStatus = currentStatus === 1 ? 2 : 1;

    $.ajax({
        url: baseUrl + 'admin/manage_roles/status',
        type: 'POST',
        dataType: 'json',
        data: {
            role_id: roleId,   // must match controller
            status: newStatus  // must match controller
        },
        success: function(response) {
            if (response.success) {
                if (newStatus === 1) {
                    badge.removeClass('bg-gradient-secondary').addClass('bg-gradient-success').text('Active');
                } else {
                    badge.removeClass('bg-gradient-success').addClass('bg-gradient-secondary').text('Inactive');
                }

                $('#messageBox')
                    .removeClass('alert-danger d-none')
                    .addClass('alert-success')
                    .text(response.message)
                    .show();
            } else {
                $('#messageBox')
                    .removeClass('alert-success d-none')
                    .addClass('alert-danger')
                    .text(response.message)
                    .show();
            }

            setTimeout(() => { $('#messageBox').fadeOut(); }, 2000);
        },
        error: function(xhr, status, error) {
            console.log('AJAX Error:', xhr.responseText); // logs exact server error
            $('#messageBox')
                .removeClass('alert-success d-none')
                .addClass('alert-danger')
                .text('AJAX error: ' + error)
                .show();
        }
    });
});


});

</script>