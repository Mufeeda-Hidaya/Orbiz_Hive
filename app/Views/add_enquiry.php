<?php include "common/header.php"; ?>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<div class="alert d-none text-center position-fixed" role=alert></div>
<!DOCTYPE html>
<html>

<head>
    <title>Enquiry</title>
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
                <h3><?= isset($estimate['enquiry_id']) ? 'Edit Enquiry' : 'Enquiry Generation' ?></h3>
            </div>
        </div>
        <form id="estimate-form">
             <input type="hidden" name="enquiry_id" id="enquiry_id" value="<?= isset($estimate['enquiry_id']) ? $estimate['enquiry_id'] : '' ?>">
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
                    <div class="estimate-title">Enquiry</div>
                    <div class="estimate-details">
                        <p class="mb-1" id="estimate-id-display">Enquiry No :
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
                            <th>Quantity</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="item-container">
                        <?php if (isset($items) && count($items) > 0): ?>
                            <?php foreach ($items as $item): ?>
                                <tr class="item-row">
                                    <td><input type="text" name="description[]" class="form-control"
                                            value="<?= $item['description'] ?>"></td>
                                    <td><input type="number" class="form-control price" step="0.000001" min="0" inputmode="decimal" name="price[]" value="<?= $item['price'] ?>">
                            </td>
                                    <td><input type="number" name="quantity[]" class="form-control quantity"
                                            value="<?= $item['quantity'] ?>"></td>
                                    <td><input type="number" name="total[]" class="form-control total" value="<?= $item['total'] ?>"
                                            readonly></td>
                                    <td class="text-center">
                                        <span class="remove-item-btn" title="Remove">
                                            <i class="fas fa-trash text-danger"></i>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr class="item-row">
                                <td><input type="text" name="description[]" class="form-control" placeholder="Description"></td>
    
                                <td><input type="number" name="quantity[]" class="form-control quantity"></td>
                                
                                <td class="text-center">
                                    <span class="remove-item-btn" title="Remove">
                                        <i class="fas fa-trash text-danger"></i>
                                    </span>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <button type="button" class="btn btn-outline-secondary mb-34" id="add-item">Add More Item</button>
            <input type="hidden" id="enquiry_id" value="<?= $estimate['enquiry_id'] ?? '' ?>">

            <div class="text-right">
                <a href="<?= base_url('supplierlist') ?>" class="btn btn-secondary">Discard</a>
                <button type="submit" id="generate-btn" class="btn btn-primary">Save</button>
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