<script>
// User DataTable
$(document).ready(function () {
    var baseUrl = "<?= base_url() ?>";
    if ($('#userForm').data('edit') === true) {  
        $('#saveUserBtn').prop('disabled', true); 
    }
    $('#userForm input, #userForm select, #userForm textarea').on('input change', function () {
        $('#saveUserBtn').prop('disabled', false);
    });
    // ---  Manage admin user Save Button Click ---
   $(document).ready(function() {
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
        setTimeout(function() {
            messageBox.fadeOut();
        }, 2000);
    }
    var originalValues = {};
    $form.find("input, select, textarea").each(function() {
        if ($(this).is(':checkbox') || $(this).is(':radio')) {
            originalValues[this.name] = $(this).prop('checked');
        } else {
            originalValues[this.name] = $(this).val();
        }
    });
    function toggleSaveButton() {
        var changed = false;
        $form.find("input, select, textarea").each(function() {
            var name = this.name;
            var currentValue;

            if ($(this).is(':checkbox') || $(this).is(':radio')) {
                currentValue = $(this).prop('checked');
            } else {
                currentValue = $(this).val();
            }

            if (currentValue != originalValues[name]) {
                changed = true;
                return false; 
            }
        });
        $btn.prop('disabled', !changed);
    }
    $form.on('input change click', "input, select, textarea", toggleSaveButton);
    $btn.click(function(e) {
        e.preventDefault();
        var name = $("input[name='name']").val()?.trim();
        var email = $("input[name='email']").val()?.trim();
        var role = $("select[name='role_id']").val();
        var password = $("input[name='password']").val();
        var newPassword = $("input[name='new_password']").val();
        var confirmPassword = $("input[name='confirm_password']").val();
        if (!name || !email || !role) {
            showMessage('All Fields Are Required');
            return;
        }
        if (!isEdit) {
            if (!password || !confirmPassword) {
                showMessage('Password And Confirm Password Are Required');
                return;
            }
            if (password !== confirmPassword) {
                showMessage('Password And Confirm Password Do Not Match');
                return;
            }
            } else {
                if ((newPassword || confirmPassword) && newPassword !== confirmPassword) {
                    showMessage('New Password And Confirm Password Do Not Match');
                    return;
                }
            }
            $btn.prop('disabled', true).text('Saving...');
            $.post(baseUrl + "admin/save/user", $form.serialize(), function(response) {
                if (response.success) {
                    showMessage(response.message, 'success');
                    $form.find("input, select, textarea").each(function() {
                        if ($(this).is(':checkbox') || $(this).is(':radio')) {
                            originalValues[this.name] = $(this).prop('checked');
                        } else {
                            originalValues[this.name] = $(this).val();
                        }
                    });
                    toggleSaveButton(); 
                    if (response.redirect) {
                        setTimeout(() => window.location.href = response.redirect, 1500);
                    }
                } else {
                    showMessage(response.message);
                    $btn.prop('disabled', false).text('Save User');
                }
            }, 'json').fail(function() {
                showMessage('Server Error. Please try again.');
                $btn.prop('disabled', false).text('Save User');
            });
        });
    });
});

//  data table

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
            { data: "name", className: "text-start" },
            { data: "email", className: "text-start" },
            { data: "role_name", className: "text-start" },
            { data: "status_switch", className: "text-start" },
            {
                data: "user_id",
                render: function (id) {
                    return `
                        <div class="text-start">
                            <a href="<?= base_url('admin/add_user/edit/') ?>${id}" title="Edit" style="margin-right:5px;">
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
var baseUrl = "<?= base_url('/') ?>"; 

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
            user_id: user_Id,   
            status: newStatus  
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

