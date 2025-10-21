<?php include "common/header.php"; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Cash Estimate</title>
  <style>
    .outer-container {
      /* width: calc(100vw - 272px);
      margin-left: auto; */
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

    .generate-table {
      width: 100%;
      border-collapse: collapse;
    }
/* Remove horizontal borders in tbody but keep vertical borders */
.generate-table tbody td {
    border-top: none;    /* Remove top horizontal line */
    border-bottom: none; /* Remove bottom horizontal line */
    border-left: 1px solid #000;  /* Keep vertical borders */
    border-right: 1px solid #000; /* Keep vertical borders */
}


    .generate-table.min_height {
      /* min-height: 350px; */
    }

    .generate-table td.padding-50 {
      padding: 50px !important;
    }

    .generate-table.min_height tbody td {
      vertical-align: top;
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

    .bottom-bar {
      text-align: center;
      font-size: 12px;
      color: white;
      background-color: #991b36;
      padding: 3px;
      margin-top: 0px;
    }

    .table-footer {
      display: flex;
      justify-content: space-between;
      margin-top: 30px;
      font-weight: bold;
    }

    .table-footer div {
      width: 48%;
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
          <button
            onclick="window.location.href='<?= base_url('invoice/convertFromEstimate/' . $estimate['estimate_id']) ?>'"
            style="background-color: #991b36; color: white; padding: 8px 16px; border: none; border-radius: 5px; margin-left: 10px;">
            Convert Invoice
          </button>
          <button onclick="window.location.href='<?= base_url('estimate/edit/' . $estimate['estimate_id']) ?>'"
            style="background-color: #991b36; color: white; padding: 8px 16px; border: none; border-radius: 5px; margin-left: 10px;">
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

          <?php if (!empty($company['company_logo'])): ?>
            <img src="<?= base_url('public/uploads/' . $company['company_logo']) ?>" alt="Company Logo"
              style="max-height: 55px; width: 30%; margin-bottom: 2px;">
          <?php endif; ?>

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
                style="display: inline-block; width: 80px; height: 30px; text-align:left; font-size: 14px;">
            </div>
          </div>
          <div class="col-4 text-center">
            <div
              style="background-color: #991b36; color: white; margin-top: 13px; font-weight: bold; padding: 3px 30px; display: inline-block; border-radius: 10px; font-size: 13px;">
              تسعيرة <br>QUOTATION
            </div>
          </div>
          <div class="col-4 text-end" style="margin-top: 17px;">
            <div style="white-space: nowrap;">
              <label style="font-weight: bold; margin-right: 6px;">Date / التاريخ:</label>
              <input type="text" readonly value="<?= date('d-m-Y', strtotime($estimate['date'])) ?>"
                style="width: 80px; height: 30px; text-align: center;  font-size: 14px;">
            </div>
          </div>
        </div>
        <div class="col-md-6" style=" margin-top: 30px;">
          <strong>TO/إلى :M/S.<?= esc($estimate['customer_name'] ?? 'Customer Name') ?></strong><br>
        </div>
        <div style="height: 2px; background-color: #ddd;"></div>
        <div class="row mt-2">
          <div class="col-8" style=" font-size: 13px;">
            Person Name:
            <?= esc($estimate['customer_name'] ?? '') ?><br>
            Business Name:
            <?= esc($company_name) ?><br>
            Address:
            <?= nl2br(esc($estimate['customer_address'] ?? '')) ?><br>
            Contact Number:
            <?= esc($estimate['phone_number']) ?>
          </div>
        </div>
        <table class="generate-table min_height" style=" font-size: 14px;">
          <thead class="thead-dark">
            <tr>
              <th style="width: 10%;">رقم<br>SR. No</th>
              <th style="width: 40%;">التفاصيل<br>Description</th>
              <th style="width: 10%;">الكمية<br>QTY</th>
              <th style="width: 20%;">سعر الوحدة<br>Unit Price (KD)</th>
              <th style="width: 20%;">المبلغ الإجمالي<br>Total Amount (KD)</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $si = 1;
            $grandTotal = '0.000';
            foreach ($items as $item):
              // Ensure $item['total'] and $item['price'] are strings for BCMath
              $grandTotal = bcadd($grandTotal, (string) $item['total'], 6);
              ?>
              <tr>
                <td><?= $si++ ?></td>
                <td><?= esc($item['description']) ?></td>
                <td><?= esc($item['quantity']) ?></td>
                <td><?= number_format((float) $item['price'], 6, '.', '') ?></td>
                <td><?= bcadd('0', (string) $item['total'], 6) ?></td>
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
            <!-- <tr><td  class="padding-50"></td> <td></td><td></td><td></td><td></td></tr> -->
            <?php
				$discount = $estimate['discount'] ?? 0; // fixed KWD value
				$totalAfterDiscount = $grandTotal - $discount;
			?>

            <!-- Subtotal -->
            <tfoot class="summary-row">
              <tr>
                <td colspan="4" style="text-align: right;">Subtotal</td>
                <td style="text-align: right; font-weight: 100;">
                  <?= bcadd('0', (string) $grandTotal, 6) ?> KWD
                </td>
              </tr>

              <!-- Discount -->
              <tr>
                <td colspan="4" style="text-align: right;">Discount</td>
                <td style="text-align: right; font-weight: 100;">
                  <?= sprintf("%.3f", $discount) ?>KWD
                </td>
              </tr>
			  
			  

              <!-- Grand Total -->
              <tr>
                <td colspan="4" style="text-align: right;">Grand Total</td>
                <td style="text-align: right; font-weight: 100;">
                  <?= bcadd('0', (string) $totalAfterDiscount, 6) ?> KWD
                </td>
              </tr>
            </tfoot>

          </tbody>
        </table>


        <div class="mt-2" style="font-size: 13px;">
          <strong>دفعة مقدمة 70% والرصيد 30% بعد التسليم <br>Advance 70% Balance 30% After Delivery</strong>
        </div>
        <div class="row mt-2">
          <div class="amount-words col-6" style="font-size: 13px;">
            <b>بالكلمات:</b><br><span id="amount-words" style="font-size: 13px; "></span>
            <?= ucwords($amountInWords ?? '') ?>
          </div>
          <div class="table-footer" style="font-size:13px;">
            <div><b>Receipient Name /اسم المستلم:</b><br><?= esc(ucfirst(strtolower($user_name ?? ''))) ?>
</div>
            <div style="text-align: right;"><b>Receipient Signature / توقيع المستلم</b></div>
          </div>
        </div>
      </div>
      <!-- /.container -->
      <!-- Bottom Bar -->
      <div class="bottom-bar">
        <div style="direction: rtl; text-align: center;">
          <?= esc($company['address_ar'] ?? '') ?>
        </div>
        <div style="direction: ltr; text-align: center;">
          <?= esc($company['address'] ?? '') ?>
        </div>
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
<script>
  function numberToWords(num) {
    const a = ['', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven',
      'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'];
    const b = ['', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];
    num = num.toString().replace(/,/g, '');

    let [dinars, fils] = num.split('.');

    if (dinars.length > 9) return 'overflow';
    dinars = parseInt(dinars, 10);
    fils = parseInt((fils || '0').padEnd(6, '0').slice(0, 6));

    const convert = (n) => {
      if (n < 20) return a[n];
      if (n < 100) return b[Math.floor(n / 10)] + (n % 10 ? '-' + a[n % 10] : '');
      if (n < 1000) {
        return a[Math.floor(n / 100)] + ' hundred' +
          (n % 100 ? ' and ' + convert(n % 100) : '');
      }
      if (n < 1000000) {
        return convert(Math.floor(n / 1000)) + ' thousand' +
          (n % 1000 ? (n % 1000 < 100 ? ' and ' : ' ') + convert(n % 1000) : '');
      }
      if (n < 1000000000) {
        return convert(Math.floor(n / 1000000)) + ' million' +
          (n % 1000000 ? (n % 1000000 < 100 ? ' and ' : ' ') + convert(n % 1000000) : '');
      }
      return '';
    };


    let words = '';
    if (dinars > 0) words += convert(dinars) + ' Kuwaiti Dinar';
    if (fils > 0) words += (words ? ' and ' : '') + convert(fils) + ' Fils';
    return words || 'Zero';
  }


  function numberToArabicWords(num) {
    const ones = ['', 'واحد', 'اثنان', 'ثلاثة', 'أربعة', 'خمسة', 'ستة', 'سبعة', 'ثمانية', 'تسعة'];
    const tens = ['', 'عشرة', 'عشرون', 'ثلاثون', 'أربعون', 'خمسون', 'ستون', 'سبعون', 'ثمانون', 'تسعون'];
    const teens = ['أحد عشر', 'اثنا عشر', 'ثلاثة عشر', 'أربعة عشر', 'خمسة عشر', 'ستة عشر', 'سبعة عشر', 'ثمانية عشر', 'تسعة عشر'];

    function convert_hundreds(n) {
      let result = '';
      const hundred = Math.floor(n / 100);
      const remainder = n % 100;

      if (hundred > 0) {
        if (hundred === 1) result += 'مائة';
        else if (hundred === 2) result += 'مائتان';
        else result += ones[hundred] + 'مائة';
      }

      if (remainder > 0) {
        if (result) result += ' و ';
        result += convert_tens(remainder);
      }

      return result;
    }

    function convert_tens(n) {
      if (n < 10) return ones[n];
      if (n >= 11 && n <= 19) return teens[n - 11];
      const ten = Math.floor(n / 10);
      const one = n % 10;

      if (one === 0) return tens[ten];
      return ones[one] + ' و ' + tens[ten];
    }

    function convert_group(n, groupName, dualName, pluralName) {
      if (n === 0) return '';
      if (n === 1) return groupName;
      if (n === 2) return dualName;
      if (n >= 3 && n <= 10) return convert_hundreds(n) + ' ' + pluralName;
      return convert_hundreds(n) + ' ' + groupName;
    }

    function convertNumber(n) {
      if (n === 0) return 'صفر';

      const million = Math.floor(n / 1000000);
      const thousand = Math.floor((n % 1000000) / 1000);
      const rest = n % 1000;

      let parts = [];
      if (million > 0) parts.push(convert_group(million, 'مليون', 'مليونان', 'ملايين'));
      if (thousand > 0) parts.push(convert_group(thousand, 'ألف', 'ألفان', 'آلاف'));
      if (rest > 0) parts.push(convert_hundreds(rest));

      return parts.join(' و ');
    }

    num = num.toString().replace(/,/g, '');
    let [dinars, fils] = num.split('.');
    dinars = parseInt(dinars || '0', 10);
    fils = parseInt((fils || '0').padEnd(6, '0').slice(0, 6));

    let words = '';
    if (dinars > 0) words += convertNumber(dinars) + ' دينار';
    if (fils > 0) words += (words ? ' و ' : '') + convertNumber(fils) + ' فلس';
    return words || 'صفر';
  }

  <?php
  $totalAmount = $estimate['total_amount'] ?? '0.000';
  $grandTotal = bcadd((string) $totalAmount, '0', 6); // exact 3 decimals, no rounding
  ?>
  const grandTotal = <?= json_encode($grandTotal) ?>;
  // const grandTotal = <?= json_encode(number_format($estimate['total_amount'] ?? 0, 6, '.', '')) ?>;

  let englishWords = numberToWords(grandTotal);
  englishWords = englishWords.toLowerCase().replace(/\b\w/g, c => c.toUpperCase());

  const arabicWords = numberToArabicWords(grandTotal);

  document.getElementById("amount-words").innerHTML = `
    ${englishWords}<br><span style="font-family: 'Amiri', serif;">${arabicWords}</span>
  `;
</script>