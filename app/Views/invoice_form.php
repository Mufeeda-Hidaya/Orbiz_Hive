<?php include "common/header.php"; ?>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<div class="alert d-none text-center position-fixed" role="alert"></div>


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

        .location {
            text-transform: capitalize;
        }

        .estimate-details {
            text-align: right;
            position: absolute;
            right: 15px;
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

        textarea[readonly] {
            background-color: #fff !important;
            border-color: #ced4da;
            /* Optional: match normal border */
            box-shadow: none !important;
            /* Remove blue focus glow */
            color: #212529;
            /* Default text color */
        }
    </style>
</head>

<div class="mt-1 estimate-box right_container">
    <div class="row mb-3">
        <div class="col-md-6">
            <h3><?= isset($invoice['invoice_id']) ? 'Edit Invoice' : 'Create Invoice' ?></h3>
        </div>

        <div class="col-md-6 text-end">
            <div class="estimate-title">INVOICE</div>
            <div class="estimate-details">
                <p class="mb-1">Invoice No:
                    <?= isset($invoice['invoice_no']) ? $invoice['invoice_no'] : '' ?>
                </p>
                <p>Date: <?= date('d-m-Y') ?></p>
            </div>
        </div>
    </div>

    <form id="invoice-form">
        <div class="row">
            <div class="col-md-6">
                <label><strong>Customer Name</strong><span class="text-danger">*</span></label>
                <div class="input-group mb-2 d-flex">
                    <select name="customer_id" id="customer_id" class="form-control select2">
                        <option value="" disabled <?= !isset($invoice['customer_id']) ? 'selected' : '' ?>>Select
                            Customer</option>
                        <?php foreach ($customers as $customer): ?>
                            <option value="<?= $customer['customer_id'] ?>" <?= (isset($invoice['customer_id']) && $invoice['customer_id'] == $customer['customer_id']) ? 'selected' : '' ?>>
                                <?= esc($customer['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="input-group-append">
                        <button type="button" class="btn btn-outline-primary" id="addCustomerBtn">+</button>
                    </div>
                </div>
            </div>
            <div class="row">
                <!-- Billing Address -->
                <div class="col-md-6">
                    <label for="customer_address" class="form-label">
                        <strong>Customer Address</strong> <span class="text-danger">*</span>
                    </label>
                    <textarea name="customer_address" id="customer_address" class="form-control capitalize"
                        maxlength="150" style="resize: vertical;"
                        rows="3"><?= isset($invoice['customer_address']) ? trim($invoice['customer_address']) : '' ?></textarea>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label class="mt-3"><strong>LPO No</strong></label>
                    <input type="text" name="lpo_no" id="lpo_no" class="form-control"
                        value="<?= isset($invoice['lpo_no']) ? esc($invoice['lpo_no']) : '' ?>">
                </div>
                <div class="col-md-6">
                    <label class="mt-3"><strong>Phone Number</strong><span class="text-danger">*</span></label>
                    <input type="text" name="phone_number" id="phone_number" class="form-control"
                        value=" <?= esc($invoice['phone_number'] ?? '') ?>" minlength="7" maxlength="15"
                        pattern="^[\+0-9\s\-\(\)]{7,25}$"
                        title="Phone number must be 7 to 15 digits and can start with +" />
                </div>
            </div>
        </div>

        <table class="table table-bordered mt-4">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Unit Price</th>
                    <th>Quantity</th>
                    <th>Amount</th>
                    <th>Location</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="item-container">
                <?php if (!empty($invoiceformitems)): ?>
                    <?php foreach ($invoiceformitems as $index => $item): ?>
                        <tr class="item-row">
                            <td><input type="text" name="description[]" class="form-control"
                                    value="<?= esc($item['description'] ?? $item['item_name'] ?? '') ?>"></td>
                            <td><input type="number" class="form-control price" step="0.000001" min="0" inputmode="decimal"
                                    name="price[]" value="<?= $item['price'] ?>">
                            </td>
                            <td><input type="number" class="form-control quantity" name="quantity[]"
                                    value="<?= $item['quantity'] ?>"></td>
                            <td><input type="number" class="form-control total" name="total[]" step="0.000001"
                                    value="<?= $item['total'] ?>" readonly></td>
                            <td>
                                <input type="text" class="form-control location" name="location[]"
                                    value="<?= esc(ucfirst($item['location'] ?? '')) ?>" placeholder="Enter Delivery Location">
                            </td>
                            <td class="text-center">
                                <span class="remove-item-btn" title="Remove"><i class="fas fa-trash text-danger"></i></span>
                            </td>
                            <input type="hidden" name="item_order[]" class="item-order" value="<?= $index + 1 ?>">
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr class="item-row">
                        <td><input type="text" name="description[]" class="form-control" placeholder="Description"></td>
                        <td><input type="number" name="price[]" class="form-control price" step="0.000001" min="0"
                                inputmode="decimal"></td>
                        <td><input type="number" name="quantity[]" class="form-control quantity"></td>
                        <td><input type="number" name="total[]" class="form-control total" readonly></td>
                        <td>
                            <input type="text" class="form-control location" name="location[]"
                                value="<?= esc(ucfirst($item['location'] ?? '')) ?>" placeholder="Enter Delivery Location">
                        </td>
                        <td class="text-center">
                            <span class="remove-item-btn" title="Remove"><i class="fas fa-trash text-danger"></i></span>
                        </td>
                    </tr>
                <?php endif; ?>

            </tbody>
        </table>

        <button type="button" class="btn btn-outline-secondary mb-3" id="add-item">Add More Item</button>

        <table class="table totals">
            <tr>
                <td><strong>Sub Total:</strong></td>
                <td><span id="sub_total_display">0.000000</span> KWD</td>
            </tr>
            <tr>
                <td><strong>Discount:</strong></td>
                <td>
                    <input type="number" name="discount" id="discount" class="form-control w-50 d-inline"
                        value="<?= isset($invoice['discount']) ? number_format((float) $invoice['discount'], 6, '.', '') : '0.000000' ?>"
                        min="0" step="0.000001"> KWD

                </td>
            </tr>

            <!-- <tr>
                <td><strong>Discount:</strong></td>
                <td>
                    <input type="number" name="discount" id="discount" class="form-control w-50 d-inline"
                        value="<?= esc($invoice['discount'] ?? 0) ?>" min="0"> %
                </td>
            </tr> -->
            <tr>
                <td><strong>Total:</strong></td>
                <td><strong><span id="total_display">0.000000</span> KWD</strong></td>
            </tr>
        </table>
        <input type="hidden" name="estimate_id" value="<?= $invoice['estimate_id'] ?? '' ?>">
        <input type="hidden" name="invoice_id"
            value="<?= isset($invoice['invoice_id']) ? $invoice['invoice_id'] : '' ?>">
        <input type="hidden" name="original_status"
            value="<?= isset($invoice['status']) ? esc($invoice['status']) : 'unpaid' ?>">
        <input type="hidden" id="is_converted" value="<?= !empty($is_converted) ? 1 : 0 ?>">
        <div class="text-end">
            <a href="<?= base_url('invoicelist') ?>" class="btn btn-secondary">Discard</a>
            <!-- <button type="submit" id="save-invoice-btn" class="btn btn-primary" -->
            <button type="submit" id="save-invoice-btn" class="btn btn-primary" <?= (!empty($invoice) && empty($is_converted)) ? 'disabled' : '' ?>>
                Generate Invoice
            </button>
        </div>
    </form>
</div>
<!-- Customer Modal -->
<div class="modal fade" id="customerModal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="customerForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Customer</h5>
                    <button type="button" class="close" data-bs-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <label>Name</label>
                    <input type="text" id="popup_name" class="form-control mb-2" required>
                    <label>Address</label>
                    <textarea id="popup_address" class="form-control" rows="3" required></textarea>
                    <div class="alert alert-danger d-none mt-2" id="customerError"></div>
                    <div class="mb-3">
                        <label>Maximum Discount (KWD)</label>
                        <input type="number" name="max_discount" id="max_discount" class="form-control" min="0"
                            step="0.000001" placeholder="Enter maximum discount amount">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" id="saveCustomerBtn" class="btn btn-primary" disabled>Save</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>
</div>
<?php include "common/footer.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    let initialFormData;

    let maxCustomerDiscount = 0;

    $(document).ready(function () {
        $('#customer_id').select2({
            placeholder: "Select Customer",
            width: 'resolve'
        });

        initialFormData = $('#invoice-form').serialize();
        if ($('#is_converted').val() === "1") {
            $('#save-invoice-btn').prop('disabled', false);
        } else {
            $('#save-invoice-btn').prop('disabled', true);
        }

        function calculateTotals() {
            let subtotal = 0;

            // Calculate subtotal from all item rows
            $('.item-row').each(function () {
                let qty = parseFloat($(this).find('.quantity').val()) || 0;
                let price = parseFloat($(this).find('.price').val()) || 0;
                let total = qty * price;
                $(this).find('.total').val(total.toFixed(6));
                subtotal += total;
            });

            $('#sub_total_display').text(subtotal.toFixed(6));

            // Get the discount value from the input field
            let discountFromInput = parseFloat($('#discount').val()) || 0;

            // For calculation, the discount applied cannot be more than the subtotal
            let effectiveDiscount = Math.min(discountFromInput, subtotal);

            // Calculate the final total using the effective discount
            let finalTotal = subtotal - effectiveDiscount;

            // Ensure the total doesn't go below zero
            if (finalTotal < 0) {
                finalTotal = 0;
            }

            $('#total_display').text(finalTotal.toFixed(6));
        }

        function updateSaveButtonState() {
            if ($('#is_converted').val() === "1") {
                $('#save-invoice-btn').prop('disabled', false);
                return;
            }
            const currentFormData = $('#invoice-form').serialize();
            const hasChanged = currentFormData !== initialFormData;
            $('#save-invoice-btn').prop('disabled', !hasChanged);
        }
        updateSaveButtonState();
        $('#invoice-form input, #invoice-form select, #invoice-form textarea').on('input change', updateSaveButtonState);
        $(document).on('click', '.remove-item-btn', function () {
            $(this).closest('tr').remove();
            calculateTotals();
            updateSaveButtonState();
            const currentData = $('#invoice-form').serialize();
            const hasChanged = currentData !== initialFormData;
            $('#save-invoice-btn').prop('disabled', !hasChanged);
        });



        $('#add-item').click(function () {
            const row = `
            <tr class="item-row">
                <td><input type="text" name="description[]" class="form-control" placeholder="Description"></td>
                <td><input type="text" name="price[]" class="form-control price" step="0.000001"></td>
                <td><input type="number" name="quantity[]" class="form-control quantity" step="0.01"></td>
                <td><input type="number" name="total[]" class="form-control total" step="0.000001" readonly></td>
                <td><input type="text" name="location[]" class="form-control location" placeholder="Enter Delivery Location">
                <td class="text-center"><span class="remove-item-btn" title="Remove"><i class="fas fa-trash text-danger"></i></span></td>
            </tr>`;
            $('#item-container').append(row);
            calculateTotals();


            const currentFormData = $('#invoice-form').serialize();
            const hasChanged = currentFormData !== initialFormData;
            $('#save-invoice-btn').prop('disabled', !hasChanged);
        });

        $('#popup_name').on('input', function () {
            let value = $(this).val();
            let capitalized = value.replace(/\b\w/g, char => char.toUpperCase());
            $(this).val(capitalized);
        });

        $('#popup_address').on('input', function () {
            let value = $(this).val();
            let capitalized = value.replace(/\b\w/g, char => char.toUpperCase());
            $(this).val(capitalized);
        });

        document.getElementById('phone_number').addEventListener('input', function () {
            let val = this.value;
            this.value = val.replace(/(?!^)\+/g, '').replace(/[^0-9\s\-\(\)\+]/g, '');
        });

        $(document).on('input', 'input[name="description[]"]', function () {
            let value = $(this).val();
            let capitalized = value.replace(/\b\w/g, char => char.toUpperCase());
            $(this).val(capitalized);
        });

        // $(document).on('click', '.remove-item-btn', function () {
        //     $(this).closest('tr').remove();
        //     calculateTotals();

        //     const currentFormData = $('#invoice-form').serialize();
        //     const hasChanged = currentFormData !== initialFormData;
        //     $('#save-invoice-btn').prop('disabled', !hasChanged);
        // });

        $(document).on('input', '.price', function () {
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

        $(document).on('input', '.price, .quantity, #discount', calculateTotals);
        calculateTotals();

        $('#addCustomerBtn').click(function () {
            $('#popup_name').val('');
            $('#popup_address').val('');
            $('#customerModal').modal('show');
        });

        $('#customer_id').on('change', function () {
            let customerId = $(this).val();

            if (customerId) {
                // Fetch customer address
                $.post("<?= site_url('customer/get_address') ?>", {
                    customer_id: customerId
                }, function (res) {
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
                    success: function (res) {
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

        let existingCustomerId = $('#customer_id').val();
        if (existingCustomerId) {
            $.ajax({
                url: '<?= base_url("customer/get_discount") ?>/' + existingCustomerId,
                type: 'GET',
                dataType: 'json',
                success: function (res) {
                    if (res.discount !== undefined) {
                        maxCustomerDiscount = parseFloat(res.discount) || 0;

                    }
                }
            });
        }
        $('#invoice-form').submit(function (e) {
            e.preventDefault();
            // const currentFormData = $('#invoice-form').serialize();
            // if (currentFormData === initialFormData) {
            //     showAlert('No Changes Made.', 'info');
            //     return;
            // }
            const $submitBtn = $('#save-invoice-btn');
            $submitBtn.prop('disabled', true).text('Generating...');
            const customerId = $('#customer_id').val();
            const customerAddress = $('#customer_address').val()?.trim();
            const phoneNumber = $('#phone_number').val()?.trim();

            if (!customerId || !customerAddress || !phoneNumber) {
                showAlert('Please Fill All Mandatory Fields.', 'danger');
                $submitBtn.prop('disabled', false).text('Generate Invoice');
                return;
            }

            let validItemExists = false;
            $('.item-row').each(function () {
                const desc = $(this).find('input[name="description[]"]').val().trim();
                const price = parseFloat($(this).find('input[name="price[]"]').val()) || 0;
                const qty = parseFloat($(this).find('input[name="quantity[]"]').val()) || 0;
                if (desc && price > 0 && qty > 0) {
                    validItemExists = true;
                    return false;
                }
            });

            if (!validItemExists) {
                showAlert('Please Enter At Least One Valid Item With Description, Price, and Quantity.', 'danger');
                $submitBtn.prop('disabled', false).text('Generate Invoice');
                return;
            }

            $('.item-row').each(function () {
                const desc = $(this).find('input[name="description[]"]').val().trim();
                const price = parseFloat($(this).find('input[name="price[]"]').val()) || 0;
                const qty = parseFloat($(this).find('input[name="quantity[]"]').val()) || 0;
                if (!desc && price === 0 && qty === 0) {
                    $(this).remove();
                }
            });

            const formData = new FormData(this);
            $.ajax({
                url: "<?= site_url('invoice/save') ?>",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function (res) {
                    if (res.status === 'success') {
                        showAlert(res.message, 'success');
                        setTimeout(() => window.location.href = res.redirect, 1000);
                    } else {
                        showAlert(res.message || 'Failed to save invoice.', 'danger');
                        $submitBtn.prop('disabled', false).text('Generate Invoice');
                    }
                },
                error: function () {
                    showAlert('Server error while saving.', 'danger');
                    $submitBtn.prop('disabled', false).text('Generate Invoice');
                }
            });
        });
        $('#discount').tooltip({
            trigger: 'manual',
            placement: 'top'
        });

        $('#discount').on('input', function () {
            let val = parseFloat($(this).val()) || 0;

            if (maxCustomerDiscount === 0) {
                $(this).val(0);
                $(this).attr('data-bs-original-title', 'No discount is Set for The Selected Customer').tooltip('show');
                setTimeout(() => {
                    $('#discount').tooltip('hide');
                }, 2000);
            } else if (val > maxCustomerDiscount) {
                $(this).val(maxCustomerDiscount);
                $(this).attr('data-bs-original-title', 'Unable to increase beyond max discount for this customer').tooltip('show');
                setTimeout(() => {
                    $('#discount').tooltip('hide');
                }, 2000);
            } else {
                $(this).tooltip('hide');
            }
        });

        function toggleCustomerSaveButton() {
            const name = $('#popup_name').val().trim();
            const address = $('#popup_address').val().trim();


            if (name && address) {
                $('#saveCustomerBtn').prop('disabled', false).removeClass('btn-secondary').addClass('btn-primary');
            } else {
                $('#saveCustomerBtn').prop('disabled', true).removeClass('btn-primary').addClass('btn-primary');
            }
        }
        $('#popup_name, #popup_address').on('input', toggleCustomerSaveButton);
        $('#customerForm').submit(function (e) {
            e.preventDefault();

            const $submitBtn = $('#saveCustomerBtn');
            $submitBtn.prop('disabled', true).text();

            const name = $('#popup_name').val().trim();
            const address = $('#popup_address').val().trim();
            const max_discount = $('#max_discount').val().trim();

            if (!name || !address) {
                $('#customerError').removeClass('d-none').text('Name and address are required.');
                $submitBtn.prop('disabled', false).text('Save');
                return;
            }

            $.ajax({
                url: "<?= base_url('customer/create') ?>",
                type: "POST",
                data: {
                    name,
                    address,
                    max_discount
                },
                dataType: "json",
                success: function (res) {
                    if (res.status === 'success') {
                        const newOption = new Option(res.customer.name, res.customer.customer_id, true, true);
                        $('#customer_id').append(newOption).trigger('change');

                        $('#popup_name').val('');
                        $('#popup_address').val('');
                        $('#max_discount').val('');
                        $('#customerModal').modal('hide');
                        toggleCustomerSaveButton(); // Reset button state

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
                        $submitBtn.prop('disabled', false).text('Save');
                    }
                },
                error: function () {
                    $('.alert')
                        .removeClass('d-none alert-success')
                        .addClass('alert-danger')
                        .text('Server Error Occurred While Creating Customer.')
                        .fadeIn()
                        .delay(3000)
                        .fadeOut();
                    $submitBtn.prop('disabled', false).text('Save');
                }
            });
        });

        function showAlert(message, type) {
            $('.alert')
                .removeClass('d-none alert-success alert-danger')
                .addClass('alert-' + type)
                .text(message)
                .fadeIn().delay(3000).fadeOut();
        }
        $(window).on('keydown', function (e) {
            if (e.ctrlKey && e.key === 'Enter') {
                e.preventDefault();
                $('#save-invoice-btn').trigger('click');
            }

            if (e.ctrlKey && e.key.toLowerCase() === 'f') {
                e.preventDefault();
                $('#add-item').trigger('click');
            }
        });
    });

    function updateItemOrders() {
        $('#item-container tr.item-row').each(function (index) {
            $(this).find('.item-order').val(index + 1);
        });
    }

    // // Call this after adding or removing an item
    // $('#add-item').click(function () {
    //     const row = `
    //     <tr class="item-row">
    //         <td><input type="text" name="description[]" class="form-control" placeholder="Description"></td>
    //         <td><input type="text" name="price[]" class="form-control price" step="0.001"></td>
    //         <td><input type="number" name="quantity[]" class="form-control quantity" step="0.01"></td>
    //         <td><input type="number" name="total[]" class="form-control total" step="0.001" readonly></td>
    //         <td><input type="text" name="location[]" class="form-control location" placeholder="Enter Delivery Location"></td>
    //         <td class="text-center"><span class="remove-item-btn" title="Remove"><i class="fas fa-trash text-danger"></i></span></td>
    //         <input type="hidden" name="item_order[]" class="item-order" value="0">
    //     </tr>`;
    //     $('#item-container').append(row);
    //     updateItemOrders();
    //     calculateTotals();
    // });

    // $(document).on('click', '.remove-item-btn', function () {
    //     $(this).closest('tr').remove();
    //     updateItemOrders();
    //     calculateTotals();
    // });

</script>