<?php include "common/header.php"; ?>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<div class="alert d-none text-center position-fixed" role="alert"></div>
<!DOCTYPE html>
<html>

<head>
    <title>ENQUIRY</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .enquiry-box {
            border: 1px solid #000;
            padding: 20px;
        }

        .enquiry-title {
            text-align: right;
            font-weight: bold;
            font-size: 24px;
        }

        .enquiry-details {
            text-align: right;
        }

        .table-bordered td,
        .table-bordered th {
            border: 1px solid #000 !important;
        }

        .totals td {
            text-align: right;
        }

        .remove-item-btn {
            cursor: pointer;
            color: red;
            font-weight: bold;
        }

        .select2-container--default .select2-selection--single {
            height: 38px;
            /* same as Bootstrap input */
            padding: 6px 12px;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
        }

        .select2-selection__rendered {
            line-height: 24px;
        }

        .select2-selection__arrow {
            height: 36px;
        }
    </style>
</head>
<body>
    <div class="mt-1 enquiry-box right_container">
    <div class="row mb-3">
        <div class="col-md-6">
            <h3><?= isset($enquiry['enquiry_id']) ? 'Edit Enquiry' : 'Enquiry Generation' ?></h3>
        </div>
    </div>

    <form id="enquiry-form">
        <input type="hidden" name="enquiry_id" id="enquiry_id" value="<?= isset($enquiry['enquiry_id']) ? $enquiry['enquiry_id'] : '' ?>">

        <div class="row">
            <div class="col-md-6">
                <label><strong>Customer</strong><span class="text-danger">*</span></label>
                <div class="input-group mb-2 d-flex">
                    <select name="customer_id" id="customer_id" class="form-control select2">
                        <option value="" disabled <?= !isset($enquiry['customer_id']) ? 'selected' : '' ?>>Select Customer</option>
                        <?php foreach ($customers ?? [] as $customer): ?>
                            <option value="<?= $customer['customer_id'] ?>" <?= (isset($enquiry['customer_id']) && $enquiry['customer_id'] == $customer['customer_id']) ? 'selected' : '' ?>>
                                <?= esc($customer['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="input-group-append">
                        <button type="button" class="btn btn-outline-primary" id="addCustomerBtn">+</button>
                    </div>
                </div>

                <label class="mt-3"><strong>Customer Address</strong><span class="text-danger">*</span></label>
                <textarea name="customer_address" id="customer_address" class="form-control" rows="3"><?= isset($enquiry['address']) ? trim($enquiry['address']) : '' ?></textarea>

                <div class="phone pt-3">
                    <label class="mt-md-0 mt-3"><strong>Contact Number</strong><span class="text-danger">*</span></label>
                    <input type="text" name="phone_number" id="phone_number" class="form-control"
                        value="<?= isset($enquiry['phone']) ? esc($enquiry['phone']) : '' ?>"
                        minlength="7" maxlength="25" pattern="^[\+0-9\s\-\(\)]{7,25}$" />
                </div>
            </div>

            <div class="col-md-6">
                <div class="enquiry-title">ENQUIRY</div>
                <div class="enquiry-details">
                    <p class="mb-1" id="enquiry-id-display">Enquiry No : <?= isset($enquiry['enquiry_no']) ? $enquiry['enquiry_no'] : '' ?></p>
                    <p>Date : <?= date('d-m-Y') ?></p>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered mt-4">
                <thead>
                    <tr>
                        <th>Description Of Goods</th>
                        <th>Quantity</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="item-container">
                    <?php if (!empty($items)): ?>
                        <?php foreach ($items as $item): ?>
                            <tr class="item-row">
                                <td><input type="text" name="description[]" class="form-control" value="<?= esc($item['description']) ?>"></td>
                                <td><input type="number" name="quantity[]" class="form-control quantity" value="<?= esc($item['quantity']) ?>"></td>
                                <td class="text-center">
                                    <span class="remove-item-btn"><i class="fas fa-trash text-danger"></i></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr class="item-row">
                            <td><input type="text" name="description[]" class="form-control" placeholder="Description"></td>
                            <td><input type="number" name="quantity[]" class="form-control" placeholder="Quantity"></td>
                            <td class="text-center">
                                <span class="remove-item-btn"><i class="fas fa-trash text-danger"></i></span>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <button type="button" class="btn btn-outline-secondary mb-3" id="add-item">Add More Item</button>

        <div class="text-right">
            <a href="<?= base_url('enquiry/list') ?>" class="btn btn-secondary">Discard</a>
            <button type="submit" id="generate-btn" class="btn btn-primary">Save</button>
        </div>
    </form>
</div>

    </div>

    <div class="modal fade" id="customerModal" tabindex="-1" role="dialog" aria-labelledby="customerModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="customerForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Customer</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="closeCustomerModalBtn">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Customer Name</label>
                            <input type="text" class="form-control" id="popup_name" required>
                        </div>
                        <div class="form-group">
                            <label>Customer Address</label>
                            <textarea class="form-control" id="popup_address" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Customer Phone</label>
                            <input type="text" class="form-control" id="popup_phone" required autocomplete="off" minlength="7" maxlength="15"
                             pattern="^[0-9+\s]{7,15}$" oninput="this.value = this.value.replace(/[^0-9+\s]/g, '')" onkeypress="return /[0-9+\s]/.test(event.key)">
                        </div>
                        <div class="alert alert-danger d-none" id="customerError"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" id="saveCustomerBtn">Save</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" id="cancelCustomerBtn">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php include "common/footer.php"; ?>
<script>
    
  $(document).ready(function() {
    const $saveBtn = $('#generate-btn'); 
    const $form = $('#enquiry-form');

    // Disable button initially
    $saveBtn.prop('disabled', true);

    // Store initial form data
    let initialFormData = $form.serialize();

    // Enable button only after changes
    $form.on('input change', 'input, select, textarea', function() {
        const currentFormData = $form.serialize();
        $saveBtn.prop('disabled', currentFormData === initialFormData);
    });

    // Remove any previous submit handlers to avoid duplicates
    $form.off('submit');

    // Submit form via AJAX
    $form.on('submit', function(e) {
        e.preventDefault(); // prevent normal form submit

        // Disable button while saving
        $saveBtn.prop('disabled', true).text('Saving...');

        const formData = new FormData(this);

        $.ajax({
            url: "<?= site_url('enquiry/saveEnquiry') ?>",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function(res) {
                if (res.status === 'success') {
                    $('.alert')
                        .removeClass('d-none alert-danger')
                        .addClass('alert-success')
                        .text(res.message)
                        .fadeIn();

                    // Update initial data to current form after save
                    initialFormData = $form.serialize();

                    setTimeout(() => {
                        window.location.href = "<?= site_url('enquiry/list') ?>";
                    }, 2000);
                } else {
                    $('.alert')
                        .removeClass('d-none alert-success')
                        .addClass('alert-danger')
                        .text(res.message)
                        .fadeIn()
                        .delay(3000)
                        .fadeOut();
                    $saveBtn.prop('disabled', false).text('Save');
                }
            },
            error: function() {
                $('.alert')
                    .removeClass('d-none alert-success')
                    .addClass('alert-danger')
                    .text('Server error occurred.')
                    .fadeIn()
                    .delay(3000)
                    .fadeOut();
                $saveBtn.prop('disabled', false).text('Save');
            },
            complete: function() {
                $saveBtn.prop('disabled', true).text('Save');
            }
        });
    });
});

// Prefill customer address and phone fetch 

$('#customer_id').on('change', function () {
    var customerId = $(this).val();

    if (!customerId) {
        $('#customer_address').val('');
        $('#phone_number').val('');
        return;
    }

    $.ajax({
        url: "<?= site_url('customer/get-address-phone') ?>",
        type: "POST",
        data: { customer_id: customerId },
        dataType: "json",
        success: function (res) {

            if (res.status === "success") {
                $('#customer_address').val(res.address);
                $('#phone_number').val(res.phone);
            } else {
                $('#customer_address').val('');
                $('#phone_number').val('');
            }
        },
        error: function () {
            $('#customer_address').val('');
            $('#phone_number').val('');
        }
    });
});



$('#add-item').click(function() {
    const newRow = `<tr class="item-row">
        <td><input type="text" name="description[]" class="form-control" placeholder="Description"></td>
        <td><input type="number" name="quantity[]" class="form-control quantity" placeholder="Quantity"></td>
        <td class="text-center">
            <span class="remove-item-btn" title="Remove"><i class="fas fa-trash text-danger"></i></span>
        </td>
    </tr>`;
    $('#item-container').append(newRow);
});

// Remove item row
$(document).on('click', '.remove-item-btn', function() {
    $(this).closest('tr').remove();
});

// Submit form via AJAX
$('#enquiry-form').submit(function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const $alert = $('.alert');
    const $saveBtn = $('#generate-btn');

    $.ajax({
        url: "<?= site_url('enquiry/saveEnquiry') ?>",
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        dataType: "json",
        beforeSend: function() {
            $saveBtn.prop('disabled', true).text('Saving...');
            $alert.addClass('d-none');
        },
        success: function(res) {
            if (res.status === 'success') {
                $alert.removeClass('d-none alert-danger').addClass('alert-success').text(res.message).fadeIn();
                setTimeout(() => { window.location.href = "<?= site_url('enquiry/list') ?>"; }, 2000);
            } else {
                $alert.removeClass('d-none alert-success').addClass('alert-danger').text(res.message).fadeIn().delay(3000).fadeOut();
            }
        },
        error: function(xhr) {
            let msg = 'Server error occurred';
            if(xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
            $alert.removeClass('d-none alert-success').addClass('alert-danger').text(msg).fadeIn().delay(3000).fadeOut();
        },
        complete: function() {
            $saveBtn.prop('disabled', false).text('Save');
        }
    });
});


    $(document).ready(function() {
        $('#customer_id').select2({
            placeholder: "Select Customer",
            width: 'calc(100% - 40px)',
            minimumResultsForSearch: 0
        });

        $('#popup_name').on('input', function() {
            let value = $(this).val();
            let capitalized = value.replace(/\b\w/g, function(char) {
                return char.toUpperCase();
            });
            $(this).val(capitalized);
        });

        $('#popup_address').on('input', function() {
            let value = $(this).val();
            let capitalized = value.replace(/\b\w/g, function(char) {
                return char.toUpperCase();
            });

            $(this).val(capitalized);
        });

        document.getElementById('phone_number').addEventListener('input', function() {
            let val = this.value;
            this.value = val.replace(/(?!^)\+/g, '').replace(/[^0-9\s\-\(\)\+]/g, '');
        });

        $(document).on('input', 'input[name="description[]"]', function() {
            let value = $(this).val();
            let capitalized = value.replace(/\b\w/g, function(char) {
                return char.toUpperCase();
            });
            $(this).val(capitalized);
        });

        $(document).on('input', '.price', function() {
            let input = this;
            let val = input.value;
            if (val === '' || val === '.') return;
            let match = val.match(/^(\d{0,8})(\.(\d{0,6})?)?/);
            if (match) {
                let newVal = (match[1] || '') + (match[2] || '');
                if (newVal !== val) {
                    input.value = newVal;
                    input.setSelectionRange(newVal.length, newVal.length);
                }
            } else {
                val = val.slice(0, -1);
                input.value = val;
                input.setSelectionRange(val.length, val.length);
            }
        });

        $('#addCustomerBtn').on('click', function() {
            $('#customerModal').modal('show');
        });
    });

    // cancelmodal
    
        $('#cancelCustomerBtn, #closeCustomerModalBtn').on('click', function() {
            $('#customerModal').modal('hide');
        });

        // $('#add-item').click(function() {
        //     const newRow = $(` 
        //         <tr class="item-row">
        //             <td><input type="text" name="description[]" class="form-control" placeholder="Description"></td>
        //             <td><input type="number" name="quantity[]" class="form-control quantity"></td>
        //             <td class="text-center">
        //                 <span class="remove-item-btn" title="Remove">
        //                     <i class="fas fa-trash text-danger"></i>
        //                 </span>
        //             </td>
        //         </tr>
        //     `);
        //     $('#item-container').append(newRow);
        //     newRow.find('input[name="description[]"]').focus();
        // });


//         $(document).on('click', '.remove-item-btn', function() {
//             $(this).closest('tr').remove();
//             calculateTotals();

//             const currentData = $('#estimate-form').serialize();
//             const hasChanged = currentData !== initialEstimateData;


//             $('#generate-btn').prop('disabled', !hasChanged);
//         });

//         $(document).on('input change', '.price, .quantity, #discount', calculateTotals);
//         calculateTotals();

        $('#cancelCustomerBtn, #closeCustomerModalBtn').on('click', function() {
            $('#customerModal').modal('hide');
        });

//         $('#customer_id').on('change', function() {
//             var customerId = $(this).val();
//             if (customerId === '') {
//                 $('#customer_address').val('');
//                 return;
//             }

//             $.ajax({
//                 url: '<?= site_url('
//                 customer / get-address ') ?>',
//                 type: 'POST',
//                 data: {
//                     customer_id: customerId
//                 },
//                 dataType: 'json',
//                 success: function(response) {
//                     if (response.status === 'success') {
//                         $('#customer_address').val(response.address);
//                     } else {
//                         $('#customer_address').val('');
//                     }
//                 },
//                 error: function() {
//                     $('#customer_address').val('');
//                 }
//             });
//         });


            // customer save 

        const saveCustomerBtn = $('#saveCustomerBtn');

        //  Disable button when modal opens
        $('#customerModal').on('show.bs.modal', function() {
            saveCustomerBtn.prop('disabled', true);
            $('#customerError').addClass('d-none');
        });

        //  Enable Save button only when required fields are filled
        $('#popup_name, #popup_address, #popup_phone').on('input', function() {
            let name = $('#popup_name').val().trim();
            let address = $('#popup_address').val().trim();
            let phone = $('#popup_phone').val().trim();

            if (name !== '' && address !== '' && phone !== '') {
                saveCustomerBtn.prop('disabled', false);
            } else {
                saveCustomerBtn.prop('disabled', true);
            }
        });


        //  Handle customer form submit
        $('#customerForm').submit(function(e) {
            e.preventDefault();

            let name = $('#popup_name').val().trim();
            let address = $('#popup_address').val().trim();
            // let max_discount = $('#max_discount').val().trim();
            let phone = $('#popup_phone').val().trim();

            name = name.replace(/\b\w/g, char => char.toUpperCase());
            address = address.replace(/(^\s*\w|[.!?]\s*\w)/g, char => char.toUpperCase());

            if (!name || !address) {
                $('#customerError').removeClass('d-none').text('Please Enter Valid Name And Address');
                return;
            }

            //  Disable button after first click to prevent double submission
            saveCustomerBtn.prop('disabled', true).text('Save');

            $.ajax({
                url: "<?= site_url('customer/create') ?>",
                type: "POST",
                data: {
                    name,
                    address,
                    phone,
                    // max_discount
                },
                dataType: "json",
                success: function(res) {
                    if (res.status === 'success') {
                        const newOption = new Option(res.customer.name, res.customer.customer_id, true, true);
                        $('#customer_id').append(newOption).trigger('change');
                        $('#popup_name').val('');
                        $('#popup_address').val('');
                        // $('#max_discount').val('');
                        $('#popup_phone').val(''); 
                        $('#customerModal').modal('hide');
                        $('.alert')
                            .removeClass('d-none alert-danger')
                            .addClass('alert-success')
                            .text('Customer Created Successfully.')
                            .fadeIn()
                            .delay(3000)
                            .fadeOut();
                    } else {
                        $('.alert')
                            .removeClass('d-none alert-success')
                            .addClass('alert-danger')
                            .text(res.message || 'Failed To Create Customer.')
                            .fadeIn()
                            .delay(3000)
                            .fadeOut();
                    }
                },
                error: function() {
                    $('.alert')
                        .removeClass('d-none alert-success')
                        .addClass('alert-danger')
                        .text('Server Error Occurred While Creating Customer.')
                        .fadeIn()
                        .delay(3000)
                        .fadeOut();
                },
                complete: function() {
                    //  Reset button after request is completed
                    saveCustomerBtn.prop('disabled', true).text('Save');
                }
            });
        });

//         let initialEstimateData = $('#estimate-form').serialize();
//         $('#generate-btn').prop('disabled', true);
//         $('#estimate-form').on('input change', 'input, select, textarea', function() {
//             const currentData = $('#estimate-form').serialize();
//             const hasChanged = currentData !== initialEstimateData;
//             $('#generate-btn').prop('disabled', !hasChanged);
//         });

//         function updateInitialFormState() {
//             initialEstimateData = $('#estimate-form').serialize();
//             $('#generate-btn').prop('disabled', true);
//         }


//         $('#estimate-form').submit(function(e) {
//             e.preventDefault();

//             const customerId = $('#customer_id').val();
//             const customerAddress = $('#customer_address').val().trim();
//             const customerName = $('#customer_id option:selected').text().trim();
//             const phoneNumber = $('#phone_number').val()?.trim();

//             if (!customerId) {
//                 showAlert('Please Select A Customer.', 'danger');
//                 return;
//             }

//             if (!customerAddress) {
//                 showAlert('Please Enter The Customer Address.', 'danger');
//                 return;
//             }
//             if (!phoneNumber) {
//                 showAlert('Please Enter The Customer Number.', 'danger');
//                 return;
//             }
//             let validItemExists = false;
//             $('.item-row').each(function() {
//                 const desc = $(this).find('input[name="description[]"]').val().trim();
//                 const price = parseFloat($(this).find('input[name="price[]"]').val()) || 0;
//                 const qty = parseFloat($(this).find('input[name="quantity[]"]').val()) || 0;

//                 if (desc && price > 0 && qty > 0) {
//                     validItemExists = true;
//                     return false;
//                 }
//             });

//             if (!validItemExists) {
//                 showAlert('Please Enter At Least One Valid Item With Description, Price, and Quantity.', 'danger');
//                 return;
//             }
//             $('.item-row').each(function() {
//                 const desc = $(this).find('input[name="description[]"]').val().trim();
//                 const price = parseFloat($(this).find('input[name="price[]"]').val()) || 0;
//                 const qty = parseFloat($(this).find('input[name="quantity[]"]').val()) || 0;

//                 if (!desc && price === 0 && qty === 0) {
//                     $(this).remove();
//                 }
//             });

//             $('#generate-btn').prop('disabled', true).text('Generating...');

//             const formData = new FormData(this);
//             formData.append('customer_name', customerName);

//             $('#item-container tr.item-row').each(function(index) {
//                 formData.append('item_order[]', index + 1); // Save order starting from 1
//             });

//             $.ajax({
//                 url: "<?= site_url('estimate/save') ?>",
//                 type: "POST",
//                 data: formData,
//                 processData: false,
//                 contentType: false,
//                 dataType: "json",
//                 success: function(res) {
//                     if (res.status === 'success') {
//                         showAlert(res.message, 'success');

//                         updateInitialFormState();

//                         setTimeout(function() {
//                             window.location.href = "<?= site_url('estimate/generateEstimate/') ?>" + res.estimate_id;
//                         }, 1500);
//                     } else if (res.status === 'nochange') {
//                         showAlert(res.message, 'warning');
//                         $('#generate-btn').prop('disabled', true).text('Generate Estimate');
//                     } else {
//                         showAlert(res.message || 'Failed To Save Estimate.', 'danger');
//                         $('#generate-btn').prop('disabled', false).text('Generate Estimate');
//                     }
//                 },

//                 error: function() {
//                     showAlert('Something Went Wrong While Saving The Estimate.', 'danger');
//                     $('#generate-btn').prop('disabled', false).text('Generate Estimate');
//                 }
//             });

//         });

//         $('#discount').on('input', function() {
//             var max = parseFloat($(this).attr('max'));
//             var val = parseFloat($(this).val());
//             if (val > max) {
//                 alert('Cannot exceed maximum discount set for this customer.');
//                 $(this).val(max);
//             }
//         });

//         let maxCustomerDiscount = 0;
        
//         $('#customer_id').on('change', function() {
//             let customerId = $(this).val();

//             if (customerId) {
//                 // Fetch customer address
//                 $.post("<?= site_url('customer/get_address') ?>", {
//                     customer_id: customerId
//                 }, function(res) {
//                     if (res.status === 'success') {
//                         $('#customer_address').val(res.address);
//                     } else {
//                         $('#customer_address').val('');
//                     }
//                 }, 'json');

//                 // Fetch customer-specific discount
//                 $.ajax({
//                     url: '<?= base_url("customer/get_discount") ?>/' + customerId,
//                     type: 'GET',
//                     dataType: 'json',
//                     success: function(res) {
//                         if (res.discount !== undefined) {
//                             maxCustomerDiscount = parseFloat(res.discount) || 0;
//                             // Set the discount input to the fetched value
//                             $('#discount').val(maxCustomerDiscount.toFixed(6));
//                         } else {
//                             // If customer has no discount, reset to 0
//                             maxCustomerDiscount = 0;
//                             $('#discount').val('0.000000');
//                         }
//                         // Recalculate totals immediately after setting the discount
//                         calculateTotals();
//                     }
//                 });
//             } else {
//                 // If no customer is selected, reset everything
//                 maxCustomerDiscount = 0;
//                 $('#discount').val('0.000000');
//                 $('#customer_address').val('');
//                 calculateTotals();
//             }
//         });

//         // Tooltip setup
//         $('#discount').tooltip({
//             trigger: 'manual',
//             placement: 'top'
//         });

//         // Restrict discount to max but allow lower values
//         $('#discount').on('input', function() {
//             let val = parseFloat($(this).val()) || 0;

//             if (maxCustomerDiscount === 0) {
//                 // Customer has no discount
//                 $(this).val(0);
//                 $(this).attr('data-bs-original-title', 'No discount is set for the selected customer').tooltip('show');
//                 setTimeout(() => $(this).tooltip('hide'), 2000);
//             } else if (val > maxCustomerDiscount) {
//                 // Exceeds max discount
//                 $(this).val(maxCustomerDiscount);
//                 $(this).attr('data-bs-original-title', 'Cannot exceed max discount for this customer').tooltip('show');
//                 setTimeout(() => $(this).tooltip('hide'), 2000);
//             } else {
//                 $(this).tooltip('hide');
//             }
//         });


//         // When editing existing invoice: just fetch max discount, do not overwrite field
//         let existingCustomerId = $('#customer_id').val();
//         if (existingCustomerId) {
//             $.ajax({
//                 url: '<?= base_url("customer/get_discount") ?>/' + existingCustomerId,
//                 type: 'GET',
//                 dataType: 'json',
//                 success: function(res) {
//                     maxCustomerDiscount = parseFloat(res.discount) || 0;
//                     //  Do not auto-fill #discount here to preserve user's entered value
//                 }
//             });
//         }

//         function showAlert(message, type = 'success') {
//             $('.alert')
//                 .removeClass('d-none alert-success alert-danger alert-warning')
//                 .addClass('alert-' + type)
//                 .text(message)
//                 .fadeIn()
//                 .delay(3000)
//                 .fadeOut();
//         }
//     });

// $(window).on('keydown', function(e) {
//     if (e.ctrlKey && e.key === 'Enter') {
//         e.preventDefault();
//         $('#generate-btn').trigger('click');
//     }

//     if (e.ctrlKey && e.key.toLowerCase() === 'f') {
//         e.preventDefault();
//         $('#add-item').trigger('click');
//     }
// }); 
</script>