<?php include "common/header.php"; ?>

<div class="form-control mb-3 right_container">

    <!-- Heading with Back to List Button on the Right -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Estimates for <?= ucfirst($customer['name']) ?></h4>
        <a href="<?= base_url('customer/list') ?>" class="btn btn-secondary">
            Back to List
        </a>
    </div>

    <?php if (empty($estimates)) : ?>
        <p>No Estimates Found For This Customer.</p>
    <?php else : ?>
        <div class="table-responsive">
            <table class="table table-bordered align-middle text-center">
                <thead>
                    <tr>
                        <th><strong>Estimate No</strong></th>
<th><strong>Estimate Date</strong></th>
<th><strong>Items</strong></th>
<th><strong>Subtotal</strong></th>
<th><strong>Discount (%)</strong></th>
<th><strong>Total</strong></th>

                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($estimates as $est) : ?>
                        <tr>
                            <td><?= $est['estimate_no'] ?></td>
                            <td><?= date('d-m-Y', strtotime($est['date'])) ?></td>
                            <td class="text-start">
                                <ul class="mb-0 ps-3">
                                    <?php foreach ($est['items'] as $item) : ?>
                                        <li><?= $item['description'] ?> (<?= $item['quantity'] ?> x <?= number_format($item['price'], 3) ?>)</li>
                                    <?php endforeach ?>
                                </ul>
                            </td>
                            <td><?= number_format($est['subtotal'], 3) ?></td>
                            <td><?= $est['discount'] ?></td>
                            <td><?= number_format($est['total_amount'], 3) ?></td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
</div>
<?php include "common/footer.php"; ?>
