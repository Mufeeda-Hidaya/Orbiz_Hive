<script>
// User DataTable
$(document).ready(function () {
    var table = $('#userTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "<?= base_url('admin/manage_user/userListAjax') ?>",
            type: "POST",
            dataSrc: "data"
        },
        columns: [
            { data: "slno", className: "text-start" },
            { data: "user_name", className: "text-start" },
            { data: "email", className: "text-start" },
            { data: "role_name", className: "text-start" },
            { data: "status_switch", className: "text-start" },
            {
                data: "user_id",
                render: function (id) {
                    return `
                        <div class="text-start">
                            <a href="<?= base_url('admin/manage_user/edit/') ?>${id}" title="Edit" style="margin-right:5px;">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <i class="bi bi-trash text-danger icon-delete" data-id="${id}" style="cursor:pointer;"></i>
                        </div>
                    `;
                }
            },
            { data: "user_id", visible: false }
        ],
        order: [[6, 'desc']],
        columnDefs: [{ searchable: false, orderable: false, targets: [0, 5] }],
        scrollX: false,
        autoWidth: false
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

    // Delete user

    $('#userTable').on('click', '.icon-delete', function () {
    var userId = $(this).data('id');
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
                url: "<?= base_url('admin/manage_user/delete') ?>",
                type: "POST",
                dataType: "json",
                data: { user_id: userId },
                success: function (res) {
                    if (res.success) {
                        showMessage('success', res.message);
                        table.row(row).remove().draw(false);
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



    // Toggle user status
    // $('#userTable').on('change', '.toggle-status', function () {
    //     let userId = $(this).data('id');
    //     let newStatus = $(this).is(':checked') ? 1 : 2;
    
    //     $.ajax({
    //         url: "<?= base_url('admin/manage_user/toggleStatus') ?>",
    //         type: "POST",
    //         data: {
    //             user_id: userId,
    //             status: newStatus,
    //             "<?= csrf_token() ?>": "<?= csrf_hash() ?>"
    //         },
    //         dataType: "json",
    //             success: function(response) {
    //                 let $msg = $('#messageBox');
    //                 $msg.removeClass('d-none alert-success alert-danger');
    
    //                 if (response.status === 'success') {
    //                     $msg.addClass('alert-success').text(response.message).show();
    //                     setTimeout(function() {
    //                         $msg.fadeOut();
    //                         table.ajax.reload(null, false);
    //                     }, 1500);
    //                 } else {
    //                     $msg.addClass('alert-danger').text(response.message || 'Failed To Update Status').show();
    //                     setTimeout(function() {
    //                         $msg.fadeOut();
    //                     }, 2000);
    //                 }
    //             },
    //         error: function(xhr, status, error) {
    //             console.error(xhr.responseText);
    //             let $msg = $('#messageBox');
    //             $msg.removeClass('d-none alert-success').addClass('alert-danger')
    //                 .text('Error Updating Status').show();
    //             setTimeout(function() { $msg.fadeOut(); }, 2000);
    //         }
    //     });
    // });
 var baseUrl = "<?= base_url('/') ?>"; // ensures trailing slash

$(document).on('click', '.status-toggle', function() {
    var badge = $(this);
    var user_Id = badge.data('id');

    var currentStatus = badge.text().trim().toLowerCase() === 'active' ? 1 : 2;
    var newStatus = currentStatus === 1 ? 2 : 1;

    $.ajax({
        url: baseUrl + 'admin/manage_user/status',
        type: 'POST',
        dataType: 'json',
        data: {
            user_id: user_Id,   // must match controller
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
 

    // Save user

    $(document).ready(function () {
        var baseUrl = "<?= base_url() ?>";
        var $form = $('#userForm');
        var $btn = $('#saveUserBtn');
        var isEdit = $("input[name='user_id']").length > 0;

        if (isEdit) $btn.prop('disabled', true);

        function showMessage(message, type = 'danger') {
            var messageBox = $('#messageBox');
            messageBox.removeClass('d-none alert-success alert-danger')
                    .addClass(type === 'success' ? 'alert-success' : 'alert-danger')
                    .text(message)
                    .show();
            setTimeout(function () { messageBox.fadeOut(); }, 3000);
        }

        $btn.click(function(e){
            e.preventDefault();

            var userName = $("input[name='name']").val()?.trim();
            var email = $("input[name='email']").val()?.trim();
            var role = $("select[name='role_id']").val()?.trim();
            var phone = $("input[name='phone']").val()?.trim();
            var newPassword = $("input[name='new_password']").val();
            var confirmPassword = $("input[name='confirm_password']").val();

            // Required field validation
            if (!userName || !email) {
                showMessage('All Fields Are Required');
                return;
            }

            // Password validation for create
            if (!isEdit && (!newPassword || !confirmPassword)) {
                showMessage('Password And Confirm Password Are Required');
                return;
            }

            // Password match validation
            if ((newPassword || confirmPassword) && newPassword !== confirmPassword) {
                showMessage('Password And Confirm Password Do Not Match');
                return;
            }

            $btn.prop('disabled', true).text('Saving...');

            $.post(baseUrl + "admin/save/user", $form.serialize(), function(response){
                if (response.success) {
                    showMessage(response.message, 'success');
                    if (response.redirect) {
                        setTimeout(() => window.location.href = response.redirect, 1500);
                    }
                } else {
                    showMessage(response.message);
                    $btn.prop('disabled', false).text('Save User');
                }
            }, 'json').fail(function(){
                showMessage('Server Error. Please try again.');
                $btn.prop('disabled', false).text('Save User');
            });
        });
    });
    


// Manage admin user password toggle

    


    // Toggle password visibility
$('.toggle-password').click(function() {
    var input = $(this).closest('.input-group').find('input');
     if (input.attr('type') === 'password') {
        input.attr('type', 'text');
        $(this).removeClass('bi-eye-slash').addClass('bi-eye');
    } else {
        input.attr('type', 'password');
        $(this).removeClass('bi-eye').addClass('bi-eye-slash');
    }
});

});

</script>

