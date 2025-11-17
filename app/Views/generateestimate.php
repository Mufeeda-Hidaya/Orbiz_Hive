<?php include "common/header.php"; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Cash Estimate</title>
  <style>
    .outer-container {
      width: fit-content;
      margin: auto;
      padding: 15px;
      background-color: #991b36;
      /* margin-left: 430px; */
    }

    .container {
      width: 720px;
      border: 5px solid #000;
      border-radius: 23px;
      padding: 20px;
      position: relative;
      background: url('<?= ASSET_PATH ?>assets/images/invoice-bg.png') no-repeat;
      background-size: 30%;
      background-position: 52% 60%;
      background-color: white;
    }

    .top-heading {
      text-align: center;
      margin-bottom: 5px;
    }

    .top-heading img {
      width: 138px;
    }

    .bottom-bar {
      text-align: center;
      font-size: 12px;
      color: white;
      background-color: #991b36;
      padding: 3px;
      margin-top: 0px;
    }

    .generate-table {
      width: 100%;
      border-collapse: collapse;
    }

    .generate-table td.padding-50 {
      height: 28px;
      border-top: none;
      border-bottom: none;
      border-left: 1px solid black;
      border-right: 1px solid black;
    }

    .generate-table.min_height tbody td {
      vertical-align: top;
      padding: 5px 6px;
      height: auto !important;
      border-top: none;
      border-bottom: none;
      border-left: 1px solid black;
      border-right: 1px solid black;

    }

    .generate-table th {
      background-color: #cfc7c7ff;
      color: black;
      min-height: 35px;
      padding: 8px;
      vertical-align: middle;
    }

    .summary-row td {
      padding: 2px 6px;
      height: 18px !important;
      font-weight: bold;
      background: #cfc7c7ff;
    }

    @media print {
      * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
      }

      .no-print,
      .header,
      .footer,
      .sidebar,
      .navbar {
        display: none !important;
      }

      body {
        margin: 0;
        padding: 0;
        font-size: 12px;
        line-height: 1;
      }
    }
  </style>
</head>

<body>
  <div class="right_container">
    <div class="outer-container">
      <div class="no-print" style="display: flex; justify-content: flex-end; margin-bottom: 10px;">
        <button onclick="window.print()"
          style="background-color: #991b36; color: white; padding: 8px 16px; border: none; border-radius: 5px;">
          Print
        </button>

        <?php if (isset($estimate['is_converted']) && $estimate['is_converted'] == 1): ?>
          <button disabled
            style="background-color:white; color: black; padding: 8px 16px; border: none; border-radius: 5px; margin-left: 10px;">
            Converted
          </button>
        <?php else: ?>
          <!-- <button
            onclick="window.location.href='<?= base_url('invoice/convertFromEstimate/' . $estimate['estimate_id']) ?>'"
            style="background-color:#991b36; color: white; padding: 8px 16px; border: none; border-radius: 5px; margin-left: 10px;">
            Convert Invoice
          </button> -->
          <button onclick="window.location.href='<?= base_url('estimate/edit/' . $estimate['estimate_id']) ?>'"
            style="background-color:#991b36; color: white; padding: 8px 16px; border: none; border-radius: 5px; margin-left: 10px;">
            Edit Estimate
          </button>
        <?php endif; ?>
      </div>
      <div class="container">
        <div class="top-heading"
          style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
          <span style="font-size: 15px; font-weight: bold;">
            <?= esc(ucwords(strtolower($company['company_name'] ?? ''))) ?>
          </span>


          <a class="navbar-brand brand-logo" href="#">
          <img src="<?= ASSET_PATH; ?>assets/images/logo-new1.png"  alt="logo" /></a>
        </a>

          <span style="font-size: 15px; font-weight: bold; direction: rtl;">
            <?= esc($company['company_name_ar'] ?? '') ?>
          </span>
        </div>
        <div style="height: 3px; background-color:#a1263a"></div>
        <div class="row align-items-center" style="margin-bottom: 10px;">
          <div class="col-4 text-start" style="margin-top: 17px;">
            <div>
              <label style="font-weight: bold; margin-right: 4px;">No / رقم :</label>
              <input type="text" readonly value="<?= esc($estimate['estimate_no']) ?>"
                style="display: inline-block;  width: 80px; height: 30px; text-align:left; font-size: 14px;">
            </div>
          </div>
          <div class="col-4 text-center">
            <div
              style="background-color: #991b36; color: white;  margin-top: 13px; font-weight: bold; padding: 3px 30px; display: inline-block; border-radius: 10px; font-size: 13px;">
              تسعيرة <br>QUOTATION
            </div>
          </div>
          <div class="col-4 text-end" style="margin-top: 17px;">
            <div style="white-space: nowrap;">
              <label style="font-weight: bold; margin-right: 6px;">Date / التاريخ:</label>
              <input type="text" readonly value="<?= date('d-m-Y', strtotime($estimate['date'])) ?>"
                style="width: 80px; height: 30px; text-align: center; font-size: 14px;">
            </div>
          </div>
        </div>
        <div class="row mt-3">
          <div class=" col-6">
            <p><strong>To/إلى:</strong></p>
            <p><?= esc($estimate['customer_name'] ?? '') ?></p>
            <p><?= nl2br(esc($estimate['customer_address'] ?? '')) ?></p>
            <br>
          </div>
          <div class=" col-6 text-end">
            <p><strong>From/ من:</strong></p>
            <p><?= ucwords(strtolower(esc($user_name ?? ''))) ?></p>
            <p><?= ucwords(strtolower(esc($role_name ?? ''))) ?></p>
          </div>
        </div>
        <table class="generate-table min_height" style=" font-size: 14px;">
          <thead class="thead-dark">
            <tr>
              <th style="width: 10%;">رقم<br>Sl No</th>
              <th style="width: 38%;">التفاصيل<br>Description</th>
              <th style="width: 15%;">سعر الوحدة<br>Unit Price</th>
              <th style="width: 10%;">الكمية<br>Quantity</th>
              <th style="width: 20%;">المبلغ الإجمالي<br>Total Amount</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $si = 1;
            $grandTotal = 0.000000;

            foreach ($items as $item):
              $grandTotal = bcadd((string) $grandTotal, (string) $item['total'], 4); // 4 decimals
              ?>
              <tr>
                <td><?= $si++ ?></td>
                <td><?= esc($item['description']) ?></td>
                <td><?= number_format((float)($item['selling_price'] ?? 0), 4, '.', '') ?></td>
                <td><?= $item['quantity'] ?></td>
                <td><?= number_format($item['total'], 4, '.', '') ?></td>
              </tr>
            <?php endforeach; ?>

            <?php
            $minRows = 8;
            $currentRows = is_array($items) ? count($items) : 0;
            $emptyRows = max(0, $minRows - $currentRows);

            for ($i = 0; $i < $emptyRows; $i++) {
              echo '<tr class="padding-50">
                    <td>&nbsp;</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                  </tr>';

            }
            ?>

            <!-- Subtotal -->
          <tfoot class="summary-row">
            <tr>
              <td colspan="4" style="text-align: right;">SUBTOTAL</td>
              <td style="text-align: right; font-weight: 100;">
                <?= sprintf('%.4f', $grandTotal) ?> KWD
              </td>
            </tr>

            <!-- Discounts -->
            <tr>
              <td colspan="4" style="text-align: right;">DISCOUNT</td>
              <td style="text-align: right; font-weight: 100;">
                <?= sprintf('%.4f', $estimate['discount'] ?? 0) ?> KWD
              </td>
            </tr>

            <!-- Total -->
            <tr>
              <td colspan="4" style="text-align: right;">TOTAL</td>
              <td style="text-align: right; font-weight: 100;">
                <?= sprintf('%.4f', $estimate['total_amount'] ?? 0) ?> KWD
              </td>
            </tr>
          </tfoot>

          </tbody>
        </table>


        <div class=" d-flex">
          <div class="col-6 terms mt-3" style="font-size:11px;">
            <strong>TERMS & CONDITIONS</strong><br>
            1. This estimate is valid for 60 days.<br>
            2. Additional amount will be added according to the requirements.<br>
            3. Full payment is required to process the order.<br>
            4. Cancellation of processed order will not be accepted.
          </div>
          <div class="col-6 mt-3" style="font-size:12px;">
            <div class="text-end ">

            </div>
          </div>
        </div>
        <div class="footer-f">
          If you have any queries about this estimate, please contact<br>
          (<?= esc($company['company_name']) ?>,
          <?= esc($company['email']) ?>,
          <?= esc($company['phone']) ?>)<br>
          <strong>Thank You For Your Business!</strong>
        </div>
      </div>
      <!-- Bottom Bar -->
      <div class="bottom-bar">
        <div style="direction: rtl; text-align: center;"><?= esc($company['address_ar'] ?? '') ?></div>
        <div style="direction: ltr; text-align: center;"><?= esc($company['address'] ?? '') ?></div>
        <div style="margin-top: 5px;">
          📞 <?= esc($company['phone'] ?? '') ?> &nbsp;&nbsp; | &nbsp;&nbsp;
          📧 <a href="mailto:<?= esc($company['email'] ?? '') ?>" style="color: white; text-decoration: none;">
            <?= esc($company['email'] ?? '') ?>
          </a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
</div>
<?php include "common/footer.php"; ?>