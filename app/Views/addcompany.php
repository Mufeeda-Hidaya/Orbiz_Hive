<?php include "common/header.php";?>
<div class="alert d-none text-center position-fixed" role="alert"></div>
    <div class="form-control right_container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="mb-0"><?= isset($selectedCompany['company_id']) ? 'Edit Company' : 'Add New Company' ?></h3>
            </div>
            <hr  class="d-none d-md-block">
            <div class="card-body p-3 px-md-4">
                <form id="company-form" enctype="multipart/form-data" method="post">
                    <div class="d-flex flex-wrap">
                        <div class="col-12 col-md-6 mb-3 px-2">
                            <label for="company_name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="company_name" id="company_name" class="form-control capitalize" maxlength="50"
                            value="<?= isset($selectedCompany['company_name']) ? esc($selectedCompany['company_name']) : '' ?>" />
                        </div>
                        
                        <div class="col-12 col-md-6 mb-3 px-2">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control"
                            value="<?= isset($selectedCompany['email']) ? esc($selectedCompany['email']) : '' ?>" />
                        </div>
                        <!-- address -->
                        <div class="col-12 col-md-6 mb-3 px-2">
                            <label for="address" class="form-label">Company Address</label>
                            <textarea name="address" id="address" class="form-control capitalize" maxlength="150"
                            style="resize: vertical;" rows="3"><?= isset($selectedCompany['address']) ? esc($selectedCompany['address']) : '' ?></textarea>
                        </div>

                        <?php
                            $addressVal = isset($selectedCompany['address']) ? trim($selectedCompany['address']) : '';
                            $billingVal = isset($selectedCompany['billing_address']) ? trim($selectedCompany['billing_address']) : '';
                            $isSame = $addressVal !== '' && $addressVal === $billingVal;
                        ?>

                        <div class="col-12 col-md-6 mb-3 px-2 position-relative">
                            <label for="billing_address" class="form-label d-flex flex-column-reverse flex-md-row  justify-content-md-between align-items-md-center">
                                <span>Billing Address</span>
                                <div class="form-check d-flex align-items-center end-0  ps-3 ps-md-0 pb-2 pb-md-0 pe-2 m-0 sameas">
                                    <input type="checkbox" class="form-check-input me-1" id="sameAddressCheck" <?= $isSame ? 'checked' : '' ?>>
                                    <label class="form-check-label small m-0" for="sameAddressCheck">Same as company address</label>
                                </div>
                            </label>
                            <textarea name="billing_address" id="billing_address" class="form-control capitalize" maxlength="150"
                                style="resize: vertical;" rows="3"><?= isset($selectedCompany['billing_address']) ? esc($selectedCompany['billing_address']) : '' ?></textarea>
                        </div>

                        <!-- address -->

                        <div class="col-12 col-md-6 mb-3 px-2">
                            <label for="phone" class="form-label">Phone <span class="text-danger">*</span></label>
                            <input type="text" name="phone" id="phone" class="form-control" maxlength="20"
                            pattern="^[\+0-9\s\-\(\)]{7,25}$"
                            title="Please enter a valid phone number (digits, +, -, spaces allowed, 7–20 characters)"
                            value="<?= isset($selectedCompany['phone']) ? esc($selectedCompany['phone']) : '' ?>" required>
                        </div>
                        <div class="col-12 col-md-6 mb-3 px-2">
                            <label for="tax_number" class="form-label">Tax Number</label>
                            <input type="text" name="tax_number" id="tax_number" class="form-control" maxlength="15"
                            value="<?= isset($selectedCompany['tax_number']) ? esc($selectedCompany['tax_number']) : '' ?>" />
                        </div>
                        <div class="col-12 col-md-6 mb-3 px-2">
                            <label class="form-label">Company Logo <span class="text-danger">*</span></label>
                           <?php if (!isset($selectedCompany['company_id'])): ?>
                                <input type="file" name="company_logo" id="company_logo" class="form-control" accept="image/*" />
                            <?php else: ?>

                                <div class="input-group loggo">
                                    <button type="button" class="btn btn-outline-secondary" id="btn-browse-file">Choose File</button>
                                    <input type="text" id="fake-file-name" class="form-control" readonly />
                                     <!-- value="<?= esc($selectedCompany['company_logo']) ?>"   -->
                                    <input type="file" name="company_logo" id="company_logo" class="d-none" accept="image/*" />
                                </div>
                           
                                <div class="mt-2">
                                    <strong>Current Logo Preview:</strong><br>
                                    <img id="logo-preview" src="<?= base_url('public/uploads/' . $selectedCompany['company_logo']) ?>" width="100" class="border p-1" />
                                </div>
                            <?php endif; ?>
                                <div>
                                    <input type="hidden" name="original_logo" id="original_logo"
                                    value="<?= isset($selectedCompany['company_logo']) ? esc($selectedCompany['company_logo']) : '' ?>" />
                                </div>
                        </div>

                        <input type="hidden" name="uid" id="uid"
                        value="<?= isset($selectedCompany['company_id']) ? esc($selectedCompany['company_id']) : '' ?>" />
                        <div class="col-12 p-3 d-flex justify-content-end gap-2" >
                            <a href="<?= base_url('companylist') ?>" class="btn btn-secondary">Discard</a>
                            <button type="button" class="btn btn-primary enter-btn"
                                <?= isset($selectedCompany['company_id']) ? 'disabled' : '' ?>
                                style="<?= isset($selectedCompany['company_id']) ? 'opacity: 0.6;' : '' ?>">
                                Save
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include "common/footer.php"; ?>
<script>
    $(document).ready(function () {
        $('#btn-browse-file').on('click', function () {
            $('#company_logo').click();
        });

        $('#company_logo').on('change', function () {
            const file = this.files[0];
            const fileName = file ? file.name : '';
            $('#fake-file-name').val(fileName);

            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    $('#logo-preview').attr('src', e.target.result);
                };
                reader.readAsDataURL(file);

                if (isEditMode) {
                    logoChanged = true; 
                    checkChanges();
                }
            }
        });

        const $saveBtn = $('.enter-btn');
        const $form = $('#company-form');
        const isEditMode = $('#uid').val().trim() !== '';
        let logoChanged = false;

        const initialValues = {
            name: $('#company_name').val(),
            address: $('#address').val(),
            billing: $('#billing_address').val(),
            tax: $('#tax_number').val(),
            email: $('#email').val(),
            phone: $('#phone').val()
        };

        if (isEditMode) {
            $saveBtn.prop('disabled', true).css('opacity', 0.6);
        }

        $form.on('input change', 'input, textarea', function () {
            checkChanges();
        });

        function checkChanges() {
            const currentValues = {
                name: $('#company_name').val(),
                address: $('#address').val(),
                billing: $('#billing_address').val(),
                tax: $('#tax_number').val(),
                email: $('#email').val(),
                phone: $('#phone').val()
            };

            let hasChanged = logoChanged;
            for (let key in currentValues) {
                if (currentValues[key] !== initialValues[key]) {
                    hasChanged = true;
                    break;
                }
            }

            if (isEditMode) {
                if (hasChanged) {
                    $saveBtn.prop('disabled', false).css('opacity', 1);
                } else {
                    $saveBtn.prop('disabled', true).css('opacity', 0.6);
                }
            }
        }

        function showMessage(message, type) {
            const alertBox = $('.alert');
            alertBox.removeClass('d-none alert-success alert-danger alert-warning');
            alertBox.addClass(`alert-${type}`);
            alertBox.html(message).fadeIn();

            setTimeout(() => {
                alertBox.fadeOut();
            }, 3000);
        }

        function containsLetters(str) {
            return /[a-zA-Z]/.test(str);
        }

        $saveBtn.on('click', function (e) {
            e.preventDefault();
            if ($saveBtn.prop('disabled')) return;

            $saveBtn.prop('disabled', true).css('opacity', 0.6);

            let name = $('#company_name').val().trim();
            let address = $('#address').val().trim();
            let tax = $('#tax_number').val().trim();
            let email = $('#email').val().trim();
            let phone = $('#phone').val().trim();
            let uid = $('#uid').val().trim();
            let fileInput = $('#company_logo')[0];
            let file = fileInput ? fileInput.files[0] : null;

            let errors = [];

            if (!name) errors.push('Company Name');
            if (!phone) errors.push('Phone Number');
            if (!file && !uid) errors.push('Company Logo');

            if (errors.length === 3) {
                showMessage('Please fill all mandatory fields.', 'danger');
                $saveBtn.prop('disabled', false).css('opacity', 1);
                return;
            }


            if (!name) {
                showMessage('Company Name is Required.', 'danger');
                $saveBtn.prop('disabled', false).css('opacity', 1);
                return;
            }


            if (!containsLetters(name)) {
                showMessage('Company Name Must Contain At Least One Letter.', 'danger');
                $saveBtn.prop('disabled', false).css('opacity', 1);
                return;
            }


            if (email) {
                let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    showMessage('Please Enter a Valid Email Address.', 'danger');
                    $saveBtn.prop('disabled', false).css('opacity', 1);
                    return;
                }
            }


            if (!phone) {
                showMessage('Please Enter a Valid Phone Number.', 'danger');
                $saveBtn.prop('disabled', false).css('opacity', 1);
                return;
            }

            let phoneRegex = /^[\+0-9\s\-\(\)]{7,25}$/;
            if (!phoneRegex.test(phone)) {
                showMessage('Please Enter a Valid Phone Number.', 'danger');
                $saveBtn.prop('disabled', false).css('opacity', 1);
                return;
            }


            if (!file && !uid) {
                showMessage('Please Upload a Company Logo (image file).', 'danger');
                $saveBtn.prop('disabled', false).css('opacity', 1);
                return;
            }

            if (file) {
                let fileType = file.type;
                let validImageTypes = ["image/jpeg", "image/png", "image/jpg", "image/gif"];
                if ($.inArray(fileType, validImageTypes) < 0) {
                    showMessage('Only image files (JPG, PNG, GIF) are Allowed For The Company Logo.', 'danger');
                    $saveBtn.prop('disabled', false).css('opacity', 1);
                    return;
                }
            }

            let formData = new FormData($form[0]);

            if (uid && (!file || file === undefined)) {
                formData.delete("company_logo");
            }

            $.ajax({
                url: '<?= base_url('managecompany/save') ?>',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'error') {
                        let msg = typeof response.message === 'object'
                            ? Object.values(response.message).join('<br>')
                            : response.message;
                        showMessage(msg, 'danger');
                        $saveBtn.prop('disabled', false).css('opacity', 1);
                    } else {
                        showMessage(response.message, 'success');
                        setTimeout(() => {
                            window.location.href = "<?= base_url('companylist') ?>";
                        }, 1500);
                    }
                },
                error: function (xhr) {
                    console.error('Server Error:', xhr.responseText);
                    showMessage('Something Went Wrong. Please Try Again.', 'danger');
                    $saveBtn.prop('disabled', false).css('opacity', 1);
                }
            });
        });
        $('#sameAddressCheck').on('change', function () {
            if ($(this).is(':checked')) {
                $('#billing_address').val($('#address').val());
                $('#billing_address').prop('readonly', true);
            } else {
                $('#billing_address').val('');
                $('#billing_address').prop('readonly', false);
            }
        });

        $('#address').on('input', function () {
            if ($('#sameAddressCheck').is(':checked')) {
                $('#billing_address').val($(this).val());
            }
        });

    });
</script>
