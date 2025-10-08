<script>

$(document).ready(function () {
    var baseUrl = "<?= base_url() ?>";
    var csrfToken = "<?= csrf_token() ?>";
    var csrfHash = "<?= csrf_hash() ?>";

    $('#rolesList').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: baseUrl + "admin/roles/List",
            type: "POST",
            data: function (d) {
                d[csrfToken] = csrfHash;
            }
        },
        columns: [
            {
                data: null,
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                },
                orderable: false,
                searchable: false
            },
            { data: 'role_Name' },

            { data: 'status_switch' },
            { data: 'actions' }
        ],
        columnDefs: [
            {
                targets: [2,3], 
                orderable: false,
                searchable: false
            },
            {
                targets: 2, 
                render: function (data, type, row) {
                    return data;
                }
            }
        ]
    });
});


//Add category

var baseUrl = "<?= base_url() ?>";
$('#rolesSubmit').click(function(e) {
    e.preventDefault(); 
    var url = baseUrl + "admin/roles/save"; 

    $.post(url, $('#createroles').serialize(), function(response) {
        if (response.status == 1) {
            $('#messageBox')
                .removeClass('alert-danger')
                .addClass('alert-success')
                .text(response.msg || 'Roles Created Successfully!')
                .show();

            setTimeout(function() {
                window.location.href = baseUrl + "admin/roles/"; 
            }, 1500);
        } else {
            $('#messageBox')
                .removeClass('alert-success')
                .addClass('alert-danger')
                .text(response.message || 'Please Fill all the Data')
                .show();
        }

        setTimeout(function() {
            $('#messageBox').empty().hide();
        }, 2000);
    }, 'json');
});

//Active and Inactive status
var baseUrl = "<?= base_url() ?>";

$(document).on('change', '.checkactive', function() {
    let catId = $(this).attr('id').split('-')[1]; // e.g., id="check-3" → prId=3
    let status = $(this).is(':checked') ? 1 : 2;

    $.ajax({
        url: baseUrl + 'admin/roles/status', // Make sure route maps to controller
        type: 'POST',
        dataType: 'json',
        data: {
            cat_Id: catId,
            cat_Status: status
        },
        success: function(response) {
            if (response.success) {
                $('#messageBox')
                    .removeClass('alert-danger')
                    .addClass('alert-success')
                    .text(response.message)
                    .show();
            } else {
                $('#messageBox')
                    .removeClass('alert-success')
                    .addClass('alert-danger')
                    .text(response.message)
                    .show();
            }

            setTimeout(() => {
                $('#messageBox').fadeOut();
            }, 2000);
        },
        error: function(xhr, status, error) {
            $('#messageBox')
                .removeClass('alert-success')
                .addClass('alert-danger')
                .text('AJAX error: ' + error)
                .show();
        }
    });
});
//Delete
function confirmDelete(catId) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'You want to delete this Role?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel',
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "<?php echo base_url('admin/roles/delete/'); ?>" + catId,
                type: "POST",
                success: function(response) {
                    Swal.fire('Deleted!', 'Roles has been deleted.', 'success')
                        .then(() => {
                            location.reload(); 
                        });
                },
                error: function(xhr, status, error) {
                    Swal.fire('Error!', 'Something went wrong.', 'error');
                }
            });
        }
    });
}

</script>