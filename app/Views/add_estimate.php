<?php include "common/header.php"; ?>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<div class="alert d-none text-center position-fixed" role=alert></div>
<!DOCTYPE html>
<html>

<head>
    <title>Estimate</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .estimate-box {
            border: 1px solid #000;
            padding: 20px;
        }

        .estimate-title {
            text-align: right;
            font-weight: bold;
            font-size: 24px;
        }

        .estimate-details {
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
    <div class="mt-1 estimate-box right_container">
        <div class="row mb-3">
            <div class="col-md-6">
                <h3><?= isset($estimate['estimate_id']) ? 'Edit Estimate' : 'Estimate Generation' ?></h3>
            </div>
        </div>
        <form id="estimate-form">
             <input type="hidden" name="estimate_id" id="estimate_id" value="<?= isset($estimate['estimate_id']) ? $estimate['estimate_id'] : '' ?>">
            <div class="row">
                <div class="col-md-6">
                    <label><strong> Customer</strong><span class="text-danger">*</span></label>
                    <div class="input-group mb-2 d-flex">
                        <select name="customer_id" id="customer_id" class="form-control select2">
                            <option value="" disabled <?= !isset($estimate['customer_id']) ? 'selected' : '' ?>>Select Customer</option>
                            <?php foreach ($customers ?? []  as $customer): ?>
                                <option value="<?= $customer['customer_id'] ?>"
                                    <?= (isset($estimate['customer_id']) && $estimate['customer_id'] == $customer['customer_id']) ? 'selected' : '' ?>>
                                    <?= esc($customer['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-primary" id="addCustomerBtn">+</button>
                        </div>
                    </div>
                    <label class="mt-3"><strong>Customer Address</strong><span class="text-danger">*</span></label>
                    <textarea name="customer_address" id="customer_address" class="form-control" rows="3"><?= isset($estimate['customer_address']) ? trim($estimate['customer_address']) : '' ?></textarea>
                    <div class="phone pt-3">
                        <label class="mt-md-0 mt-3"><strong>Contact Number</strong><span class="text-danger">*</span></label>
                        <input type="text" name="phone_number" id="phone_number" class="form-control"
                        value="<?= isset($estimate['phone_number']) ? esc($estimate['phone_number']) : '' ?>"
                        minlength="7" maxlength="25" pattern="^[\+0-9\s\-\(\)]{7,25}$" />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="estimate-title">ESTIMATE</div>
                    <div class="estimate-details">
                        <p class="mb-1" id="estimate-id-display">Estimate No :
                            <?= isset($estimate['estimate_no']) ? $estimate['estimate_no'] : '' ?></p>
                        <p>Date : <?= date('d-m-Y') ?></p>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered mt-4">
                    <thead>
                        <tr>
                            <th>Description Of Goods</th>
                            <th>Market Price</th>
                            <th>Selling Price</th>
                            <th>Difference (%)</th> 
                            <th>Quantity</th>
                            <th>Amount (AED)</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="item-container">
                        <?php if (!empty($items)): ?>
                            <?php foreach ($items as $index => $item): ?>
                                <tr class="item-row">
                                    <td>
                                        <input type="text" name="description[]" class="form-control"
                                            value="<?= esc($item['description'] ?? '') ?>">
                                    </td>

                                    <!--  Use market_price instead of price -->
                                    <td>
                                        <input type="number" name="market_price[]" class="form-control market-price"
                                            step="0.0001" min="0"
                                            value="<?= esc($item['market_price'] ?? 0) ?>">
                                    </td>

                                    <td>
                                        <input type="number" name="selling_price[]" class="form-control selling-price"
                                            step="0.0001" min="0"
                                            value="<?= esc($item['selling_price'] ?? ($item['market_price'] ?? 0)) ?>">
                                    </td>

                                    <td><input type="text" name="difference[]" class="form-control difference" readonly></td>

                                    <td>
                                        <input type="number" name="quantity[]" class="form-control quantity"
                                            value="<?= esc($item['quantity'] ?? 1) ?>">
                                    </td>

                                    <td>
                                        <input type="number" name="total[]" class="form-control total"
                                            step="0.0001" value="<?= esc($item['total'] ?? 0) ?>" readonly>
                                    </td>

                                    <td class="text-center">
                                        <span class="remove-item-btn" title="Remove">
                                            <i class="fas fa-trash text-danger"></i>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- If no items, show one empty row -->
                            <tr class="item-row">
                                <td><input type="text" name="description[]" class="form-control" placeholder="Description"></td>
                                <td><input type="number" name="market_price[]" class="form-control market-price" step="0.0001" min="0"></td>
                                <td><input type="number" name="selling_price[]" class="form-control selling-price" step="0.0001" min="0"></td>
                                <td><input type="text" name="difference[]" class="form-control difference" readonly></td>
                                <td><input type="number" name="quantity[]" class="form-control quantity"></td>
                                <td><input type="number" name="total[]" class="form-control total" step="0.0001" readonly></td>
                                <td class="text-center">
                                    <span class="remove-item-btn" title="Remove"><i class="fas fa-trash text-danger"></i></span>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <button type="button" class="btn btn-outline-secondary mb-34" id="add-item">Add More Item</button>
            <table class="table totals">
                <tr>
                    <td><strong>Sub Total:</strong></td>
                    <td><span id="sub_total_display">0.0000</span> AED</td>
                </tr>
                <tr>
                    <td><strong>Discount:</strong></td>
                    <td>
                        <input type="number" name="discount" id="discount" class="form-control col-7 d-inline"
                            value="<?= isset($estimate['discount']) ? number_format($estimate['discount'], 4, '.', '') : '0.0000' ?>"
                            step="0.0001" min="0">
                        AED
                    </td>
                </tr>
                <tr>
                    <td><strong>Total:</strong></td>
                    <td><strong><span id="total_display">0.0000</span> AED</strong></td>
                </tr>
            </table>
            <input type="hidden" id="estimate_id" value="<?= $estimate['estimate_id'] ?? '' ?>">

            <div class="text-right">
                <a href="<?= base_url('estimatelist') ?>" class="btn btn-secondary">Discard</a>
                <button type="submit" id="generate-btn" class="btn btn-primary">Generate Estimate</button>
            </div>
        </form>
    </div>
    </div>

    <div class="modal fade" id="customerModal" tabindex="-1" role="dialog" aria-labelledby="customerModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="customerForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Customer</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                            id="closeCustomerModalBtn"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Customer Name</label>
                            <input type="text" class="form-control" id="popup_name" required>
                            <!-- <textarea class="form-control" id="popup_address" rows="3" required></textarea> -->

                        </div>
                        <div class="form-group">
                            <label>Customer Address</label>
                            <!-- <input type="text" name="description[]" class="form-control description" required> -->
                            <textarea class="form-control" id="popup_address" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Customer Phone</label>
                            <input type="text" class="form-control" id="popup_phone" required autocomplete="off" minlength="7" maxlength="15"
                             pattern="^[0-9+\s]{7,15}$" oninput="this.value = this.value.replace(/[^0-9+\s]/g, '')" onkeypress="return /[0-9+\s]/.test(event.key)">
                        </div>
                        <div class="alert alert-danger d-none" id="customerError"></div>
                        <div class="mb-3">
                            <label>Maximum Discount (KWD)</label>
                            <input type="number" name="max_discount" id="max_discount" class="form-control" min="0" step="0.000001" placeholder="Enter maximum discount amount">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" id="saveCustomerBtn">Save</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"
                            id="cancelCustomerBtn">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php include "common/footer.php"; ?>
<script>
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


        function calculateTotals() {
            let subtotal = 0;

            $('.item-row').each(function() {
                let qty = parseFloat($(this).find('.quantity').val()) || 0;
                let sPrice = parseFloat($(this).find('.selling-price').val()) || 0;
                let total = sPrice * qty;
                $(this).find('.total').val(total.toFixed(4));
                subtotal += total;
            });

            $('#sub_total_display').text(subtotal.toFixed(4));

            let discount = parseFloat($('#discount').val()) || 0;
            let effectiveDiscount = Math.min(discount, subtotal);

            let finalTotal = subtotal - effectiveDiscount;
            if (finalTotal < 0) finalTotal = 0;

            $('#total_display').text(finalTotal.toFixed(4));
        }

        $(document).on('input change', '.selling-price, .quantity, #discount', calculateTotals);
        calculateTotals();


        $('#add-item').click(function() {
            const newRow = $(`
                <tr class="item-row">
                    <td><input type="text" name="description[]" class="form-control" placeholder="Description"></td>
                    <td><input type="number" name="market_price[]" class="form-control market-price" step="0.0001" min="0"></td>
                    <td><input type="number" name="selling_price[]" class="form-control selling-price" step="0.0001" min="0"></td>
                    <td><input type="text" name="difference[]" class="form-control difference" readonly></td> 
                    <td><input type="number" name="quantity[]" class="form-control quantity" step="0.0001" min="0"></td>
                    <td><input type="number" name="total[]" class="form-control total" step="0.0001" readonly></td>
                    <td class="text-center">
                        <span class="remove-item-btn" title="Remove">
                            <i class="fas fa-trash text-danger"></i>
                        </span>
                    </td>
                </tr>
            `);
            $('#item-container').append(newRow);
            newRow.find('input[name="description[]"]').focus();
            newRow.find('.market-price, .selling-price').trigger('input')
        });

        // Calculate difference between Market Price and Selling Price 
        $(document).on('input', '.market-price, .selling-price', function() {
            const row = $(this).closest('tr');
            const market = parseFloat(row.find('.market-price').val()) || 0;
            const selling = parseFloat(row.find('.selling-price').val()) || 0;

            if (market > 0) {
                const diff = ((selling - market) / market) * 100;
                row.find('.difference').val(diff.toFixed(2) + '%')
                    .css('color', 'black');
            } else {
                row.find('.difference').val('0.00%').css('color', 'black');
            }
        });

        //  Prefill difference for existing rows
        $('.item-row').each(function() {
            const row = $(this);
            const market = parseFloat(row.find('.market-price').val()) || 0;
            const selling = parseFloat(row.find('.selling-price').val()) || 0;

            if (market > 0) {
                const diff = ((selling - market) / market) * 100;
                row.find('.difference').val(diff.toFixed(2) + '%').css('color', 'black');
            } else {
                row.find('.difference').val('0.00%').css('color', 'black');
            }
        });



        $(document).on('click', '.remove-item-btn', function() {
            $(this).closest('tr').remove();
            calculateTotals();

            const currentData = $('#estimate-form').serialize();
            const hasChanged = currentData !== initialEstimateData;


            $('#generate-btn').prop('disabled', !hasChanged);
        });

        $(document).on('input change', '.price, .quantity, #discount', calculateTotals);
        calculateTotals();

        $('#cancelCustomerBtn, #closeCustomerModalBtn').on('click', function() {
            $('#customerModal').modal('hide');
        });

        $('#customer_id').on('change', function() {
            var customerId = $(this).val();
            if (customerId === '') {
                $('#customer_address').val('');
                return;
            }

            $.ajax({
                url: '<?= site_url('
                customer / get-address ') ?>',
                type: 'POST',
                data: {
                    customer_id: customerId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#customer_address').val(response.address);
                    } else {
                        $('#customer_address').val('');
                    }
                },
                error: function() {
                    $('#customer_address').val('');
                }
            });
        });

        const saveCustomerBtn = $('#saveCustomerBtn');

        // Disable button when modal opens
        $('#customerModal').on('show.bs.modal', function() {
            saveCustomerBtn.prop('disabled', true);
            $('#customerError').addClass('d-none');
        });

        // Enable Save button only when required fields are filled
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
            let max_discount = $('#max_discount').val().trim();
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
                    max_discount
                },
                dataType: "json",
                success: function(res) {
                    if (res.status === 'success') {
                        const newOption = new Option(res.customer.name, res.customer.customer_id, true, true);
                        $('#customer_id').append(newOption).trigger('change');
                        $('#popup_name').val('');
                        $('#popup_address').val('');
                        $('#max_discount').val('');
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
                    // Reset button after request is completed
                    saveCustomerBtn.prop('disabled', true).text('Save');
                }
            });
        });

        let initialEstimateData = $('#estimate-form').serialize();
        $('#generate-btn').prop('disabled', true);
        $('#estimate-form').on('input change', 'input, select, textarea', function() {
            const currentData = $('#estimate-form').serialize();
            const hasChanged = currentData !== initialEstimateData;
            $('#generate-btn').prop('disabled', !hasChanged);
        });

        function updateInitialFormState() {
            initialEstimateData = $('#estimate-form').serialize();
            $('#generate-btn').prop('disabled', true);
        }


        $('#estimate-form').submit(function(e) {
            e.preventDefault();

            const customerId = $('#customer_id').val();
            const customerAddress = $('#customer_address').val().trim();
            const customerName = $('#customer_id option:selected').text().trim();
            const phoneNumber = $('#phone_number').val()?.trim();

            if (!customerId) {
                showAlert('Please Select A Customer.', 'danger');
                return;
            }

            if (!customerAddress) {
                showAlert('Please Enter The Customer Address.', 'danger');
                return;
            }
            if (!phoneNumber) {
                showAlert('Please Enter The Customer Number.', 'danger');
                return;
            }
            let validItemExists = false;
            $('.item-row').each(function() {
                const desc = $(this).find('input[name="description[]"]').val().trim();
                const sPrice = parseFloat($(this).find('input[name="selling_price[]"]').val()) || 0;
                const qty = parseFloat($(this).find('input[name="quantity[]"]').val()) || 0;

                if (desc && sPrice > 0 && qty > 0) {
                    validItemExists = true;
                    return false; 
                }
            });

            if (!validItemExists) {
                showAlert('Please Enter At Least One Valid Item With Description, Price, and Quantity.', 'danger');
                return;
            }

            // Remove completely empty rows before submission
            $('.item-row').each(function() {
                const desc = $(this).find('input[name="description[]"]').val().trim();
                const sPrice = parseFloat($(this).find('input[name="selling_price[]"]').val()) || 0;
                const qty = parseFloat($(this).find('input[name="quantity[]"]').val()) || 0;

                if (!desc && sPrice === 0 && qty === 0) {
                    $(this).remove();
                }
            });


            $('#generate-btn').prop('disabled', true).text('Generating...');

            const formData = new FormData(this);
            formData.append('customer_name', customerName);

            $('#item-container tr.item-row').each(function(index) {
                formData.append('item_order[]', index + 1); // Save order starting from 1
            });

            $.ajax({
                url: "<?= site_url('estimate/save') ?>",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                dataType: "json",
                success: function(res) {
                    if (res.status === 'success') {
                        showAlert(res.message, 'success');

                        updateInitialFormState();

                        setTimeout(function() {
                            window.location.href = "<?= site_url('estimate/generateEstimate/') ?>" + res.estimate_id;
                        }, 1500);
                    } else if (res.status === 'nochange') {
                        showAlert(res.message, 'warning');
                        $('#generate-btn').prop('disabled', true).text('Generate Estimate');
                    } else {
                        showAlert(res.message || 'Failed To Save Estimate.', 'danger');
                        $('#generate-btn').prop('disabled', false).text('Generate Estimate');
                    }
                },

                error: function() {
                    showAlert('Something Went Wrong While Saving The Estimate.', 'danger');
                    $('#generate-btn').prop('disabled', false).text('Generate Estimate');
                }
            });

        });

        $('#discount').on('input', function() {
            var max = parseFloat($(this).attr('max'));
            var val = parseFloat($(this).val());
            if (val > max) {
                alert('Cannot exceed maximum discount set for this customer.');
                $(this).val(max);
            }
        });

        let maxCustomerDiscount = 0;
        
        $('#customer_id').on('change', function() {
            let customerId = $(this).val();

            if (customerId) {
                // Fetch customer address
                $.post("<?= site_url('customer/get_address') ?>", {
                    customer_id: customerId
                }, function(res) {
                    if (res.status === 'success') {
                        $('#customer_address').val(res.address);
                    } else {
                        $('#customer_address').val('');
                    }
                }, 'json');

                // Fetch customer-specific discount
                $.ajax({
                    url: '<?= base_url("customer/get_discount") ?>/' + customerId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(res) {
                        if (res.discount !== undefined) {
                            maxCustomerDiscount = parseFloat(res.discount) || 0;
                            // Set the discount input to the fetched value
                            $('#discount').val(maxCustomerDiscount.toFixed(6));
                        } else {
                            // If customer has no discount, reset to 0
                            maxCustomerDiscount = 0;
                            $('#discount').val('0.000000');
                        }
                        // Recalculate totals immediately after setting the discount
                        calculateTotals();
                    }
                });
            } else {
                // If no customer is selected, reset everything
                maxCustomerDiscount = 0;
                $('#discount').val('0.000000');
                $('#customer_address').val('');
                calculateTotals();
            }
        });

        // Tooltip setup
        $('#discount').tooltip({
            trigger: 'manual',
            placement: 'top'
        });

        // Restrict discount to max but allow lower values
        $('#discount').on('input', function() {
            let val = parseFloat($(this).val()) || 0;

            if (maxCustomerDiscount === 0) {
                // Customer has no discount
                $(this).val(0);
                $(this).attr('data-bs-original-title', 'No discount is set for the selected customer').tooltip('show');
                setTimeout(() => $(this).tooltip('hide'), 2000);
            } else if (val > maxCustomerDiscount) {
                // Exceeds max discount
                $(this).val(maxCustomerDiscount);
                $(this).attr('data-bs-original-title', 'Cannot exceed max discount for this customer').tooltip('show');
                setTimeout(() => $(this).tooltip('hide'), 2000);
            } else {
                $(this).tooltip('hide');
            }
        });


        // When editing existing invoice: just fetch max discount, do not overwrite field
        let existingCustomerId = $('#customer_id').val();
        if (existingCustomerId) {
            $.ajax({
                url: '<?= base_url("customer/get_discount") ?>/' + existingCustomerId,
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    maxCustomerDiscount = parseFloat(res.discount) || 0;
                    //  Do not auto-fill #discount here to preserve user's entered value
                }
            });
        }

        function showAlert(message, type = 'success') {
            $('.alert')
                .removeClass('d-none alert-success alert-danger alert-warning')
                .addClass('alert-' + type)
                .text(message)
                .fadeIn()
                .delay(3000)
                .fadeOut();
        }
    });

$(window).on('keydown', function(e) {
    if (e.ctrlKey && e.key === 'Enter') {
        e.preventDefault();
        $('#generate-btn').trigger('click');
    }

    if (e.ctrlKey && e.key.toLowerCase() === 'f') {
        e.preventDefault();
        $('#add-item').trigger('click');
    }
}); 
</script>


