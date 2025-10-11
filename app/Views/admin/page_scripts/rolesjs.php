<script>
$(document).ready(function() {
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
            { data: "role_Name", className: "text-start" },
            { data: "status_switch", className: "text-start" },
            { 
                data: "role_Id",
                render: function(id, type, row) {
                    return `
                        <div class="text-start">
                            <a href="<?= base_url('admin/roles/edit/') ?>${id}" title="Edit" style="margin-right:5px;">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <i class="bi bi-trash text-danger icon-clickable" onclick="confirmDelete(${id})"></i>
                        </div>
                    `;
                }
            },
            { data: "role_Id", visible: false }
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
// var baseUrl = "<?= base_url() ?>";

// $(document).on('click', '.status-toggle', function() {
//     var badge = $(this);
//     var roleId = badge.data('id');
//     var currentStatus = badge.text() === 'Active' ? 1 : 2;
//     var newStatus = currentStatus === 1 ? 2 : 1;

//     $.ajax({
//         url: baseUrl + 'admin/roles/status',
//         type: 'POST',
//         dataType: 'json',
//         data: {
//             role_Id: roleId,
//             role_Status: newStatus
//         },
//         success: function(response) {
//             if (response.success) {
//                 // Update badge instantly
//                 if (newStatus === 1) {
//                     badge.removeClass('bg-gradient-secondary').addClass('bg-gradient-success').text('Active');
//                 } else {
//                     badge.removeClass('bg-gradient-success').addClass('bg-gradient-secondary').text('Inactive');
//                 }

//                 $('#messageBox')
//                     .removeClass('alert-danger')
//                     .addClass('alert-success')
//                     .text(response.message)
//                     .show();
//             } else {
//                 $('#messageBox')
//                     .removeClass('alert-success')
//                     .addClass('alert-danger')
//                     .text(response.message)
//                     .show();
//             }

//             setTimeout(() => { $('#messageBox').fadeOut(); }, 2000);
//         },
//         error: function(xhr, status, error) {
//             $('#messageBox')
//                 .removeClass('alert-success')
//                 .addClass('alert-danger')
//                 .text('AJAX error: ' + error)
//                 .show();
//         }
//     });
// });

//Delete
// function confirmDelete(roleId) {
//     Swal.fire({
//         title: 'Are you sure?',
//         text: 'You want to delete this Role?',
//         icon: 'warning',
//         showCancelButton: true,
//         confirmButtonText: 'Delete',
//         cancelButtonText: 'Cancel',
//     }).then((result) => {
//         if (result.isConfirmed) {
//             $.ajax({
//                 url: "<?php echo base_url('admin/roles/delete/'); ?>" + roleId,
//                 type: "POST",
//                 success: function(response) {
//                     Swal.fire('Deleted!', 'Roles has been deleted.', 'success')
//                         .then(() => {
//                             location.reload(); 
//                         });
//                 },
//                 error: function(xhr, status, error) {
//                     Swal.fire('Error!', 'Something went wrong.', 'error');
//                 }
//             });
//         }
//     });
// }

</script>