<?php include "common/header.php"; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Cash Invoice</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      font-size: 14px;
      margin: 0;
      padding: 0;
      background-color: #fff;
    }

    /* New outer brown container */
    .outer-container {
      width: fit-content;
      margin: auto;
      padding: 15px;
      background-color: #991b36;
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

    .invoice-type {
      background-color: #991b36;
      color: white;
      font-weight: bold;
      padding: 5px 20px;
      display: inline-block;
      border-radius: 4px;
      margin: 5px auto;
      font-size: 14px;
    }

    .invoice-header {
      display: flex;
      flex-direction: column;
      gap: 5px;
      justify-content: space-between;
      margin-top: 15px;
    }

    .invoice-header>div {
      display: flex;
      font-weight: bold;
      width: 100%;
    }


    .invoice-header .half {
      width: 50%;
    }

    .invoice-header input {
      border: 1px solid #000;
      width: 100px;
      height: 35px;
    }

    .invoice-header span {
      width: 90%;
      /* text-decoration: underline; */
      border-bottom: 1px solid black;
      margin: 0 5px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    table,
    th,
    {
    border: 1px solid black;
    }

    /* table.min_height {
      min-height: 350px;
    } */
    table.min_height {
      width: 100%;
      border-collapse: collapse;
      table-layout: fixed;
      min-height: 350px;
    }

    table.min_height tbody td {
      vertical-align: top;
      padding: 5px 0;
      height: 20px !important;
    }

    tbody td {
      border-top: 1px solid transparent;
      border-bottom: 1px solid transparent;
    }

    tbody tr:last-child td {
      border-bottom: 1px solid black;
    }

    /* Remove inner cell borders inside tbody */
    tbody td {
      border-top: none !important;
      border-bottom: none !important;
      border-left: 1px solid #000;
      border-right: 1px solid #000;
    }



    /* Ensure the last row still has a bottom border */
    tbody tr:last-child {
      border-bottom: 1px solid #000;
    }

    th {
      background-color: #cfc7c7ff;
      text-align: center;
      font-weight: bold;
      padding: 2px;
    }

    td {
      text-align: center;
      height: 25px;
      padding: 4px;
      word-wrap: break-word;
      word-break: break-word;
      white-space: normal;
    }

    .table-footer {
      display: flex;
      justify-content: space-between;
      margin-top: 30px;
      font-weight: bold;
    }

    .amount-words {
      margin-top: 20px;
      margin-bottom: 20px;
      font-weight: bold;
    }

    .table-footer div {
      width: 48%;
    }

    .bottom-bar {
      text-align: center;
      font-size: 12px;
      color: white;
      background-color: #991b36;
      padding: 3px;
      margin-top: 0px;

    }

    .tfoot {
      background-color: #cfc7c7ff;
    }

    .partial-row {
      display: flex;
      justify-content: end;
      width: 300px;
      margin-bottom: 5px;
      gap: 53px;
    }

    .partial {
      font-weight: bold;
    }

    .value {
      text-align: right;
      min-width: 100px;
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
        line-height: 1.4;
      }

      table {
        border-collapse: collapse;
        width: 100%;
        table-layout: fixed;
      }

      table th,
      table td {
        border: 1px solid #000;
        padding: 4px;
        font-size: 10px;
        word-break: break-word;
      }

      td:nth-child(2) {
        max-width: 250px;
        white-space: normal;
      }

      /* Optional: Avoid page breaks inside rows */
      tr {
        page-break-inside: avoid;
      }

      /* Optional: Remove background images if not needed */
      body,
      table {
        background: none !important;
      }

      /* .container {
        min-width: 690px;
        min-height: 900px;
      } */
    }
  </style>
</head>

<body>
  <div class="right_container">
    <div class="outer-container">
      <!-- <div class="no-print" style="display: flex; justify-content: flex-end; margin-bottom: 10px;">
        <button onclick="window.print()"
          style="background-color: #991b36; color: white; padding: 8px 16px; border: none; border-radius: 5px;">
          Print
        </button>
        <?php if (!in_array(strtolower($invoice['status']), ['paid', 'partial paid'])): ?>
          <button id="editinvoicebtn"
            onclick="window.location.href='<?= base_url('invoice/edit/' . $invoice['invoice_id']) ?>'"
            style="background-color: #991b36; color: white; padding: 8px 16px; border: none; border-radius: 5px; margin-left: 10px; cursor: pointer;">
            Edit Invoice
          </button>
        <?php endif; ?>
        <button id="deliveryNoteBtn"
          onclick="window.location.href='<?= base_url('invoice/delivery_note/' . $invoice['invoice_id']) ?>'"
          style="display: <?= in_array(strtolower($invoice['status']), ['paid', 'partial paid']) ? 'inline-block' : 'none' ?>;
                background-color: #991b36; color: white; padding: 8px 16px; border: none; border-radius: 5px; margin-left: 10px;">
          Delivery Note
        </button>

        <?php
        $status = strtolower($invoice['status'] ?? 'unpaid');
        $btnLabel = ucfirst($status);
        $btnColor = $status === 'paid' ? '#28a745' : ($status === 'partial paid' ? '#ffc107' : '#991b36');
        ?>
        <div class="btn-group ml-2 position-relative" style="z-index: 1000; margin-left: 10px;">
          <button id="statusBtn" type="button" class="btn btn-sm"
            style="background-color: <?= $btnColor ?>; color: white; padding: 8px 16px; border-radius: 5px;"
            <?= $status === 'paid' ? 'disabled title="Fully paid invoice cannot be changed"' : 'onclick="toggleStatusOptions()"' ?>>
            <?= $btnLabel ?>
          </button>

          <?php if ($status === 'unpaid' || $status === 'partial paid'): ?>
            <div class="dropdown" style="position: relative;">
              <div id="statusOptions" class="dropdown-menu p-2"
                style="position: absolute; top: 100%; right: 0px; z-index: 1050; box-shadow: 0 4px 8px rgba(0,0,0,0.1); display: none;">
                <a href="#" class="dropdown-item text-success fw-semibold" onclick="updateStatus('paid')">
                  <i class="fas fa-check-circle me-2"></i> Mark as Paid
                </a>
                <a href="#" class="dropdown-item text-warning fw-semibold" onclick="openPartialPayment()">
                  <i class="fas fa-hourglass-half me-2"></i> Partial Payment
                </a>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div> -->
      <div class="no-print" style="text-align: right; margin-bottom: 20px;">
        <button onclick="window.print()" class="btn btn-sm"
          style="background-color: #991b36; color: white;">Print</button>
        <button onclick="downloadPDF()" class="btn btn-sm" style="background-color: #991b36; color: white;">Download
          PDF</button>
        <button onclick="window.location.href='<?= base_url('invoice/print/' . $invoice['invoice_id']) ?>'"
          class="btn btn-sm" style="background-color: #991b36; color: white;">
          Discard
        </button>

      </div>



      <div class="container">
        <div class="d-flex align-items-center text-center" style="margin-bottom: 5px; width:100%;">
          <!-- Company Name (English) -->
          <div class="col-4 text-start">
            <span style="font-size: 13px; font-weight: bold;">
              <?= esc(ucwords(strtolower($company['company_name'] ?? ''))) ?>
            </span>
          </div>

          <!-- Company Logo -->
          <div class="col-4">
            <?php if (!empty($company['company_logo'])): ?>
              <img src="<?= base_url('public/uploads/' . $company['company_logo']) ?>" alt="Company Logo"
                style="max-height: 50px;">
            <?php else: ?>
              <img src="<?= base_url('public/uploads/default-logo.png') ?>" alt="Company Logo" style="max-height: 50px;">
            <?php endif; ?>
          </div>

          <!-- Company Name (Arabic) -->
          <div class="col-4 text-end">
            <span style="font-size: 14px; font-weight: bold; direction: rtl;">
              <?= esc($company['company_name_ar'] ?? '') ?>
            </span>
          </div>
        </div>

        <hr>
        <div class="row align-items-center" style="margin-bottom: 10px;">
          <div class="col-4 text-start">
            <div>
              <label style="font-weight: bold; margin-right: 4px;">Invoice No :</label>
              <span
                style="display: inline-block; width: 87px; height: 23px; line-height: 23px; text-align: left; color: black;">
                <?= esc($invoice['invoice_no']) ?>
              </span>
            </div>

          </div>
          <div class="col-4 text-center">
            <div
              style="background-color: #991b36; color: white; font-weight: bold; padding: 3px 15px; display: inline-block; border-radius: 4px; font-size: 13px;">
              ملاحظة التسليم<br> DELIVERY NOTE
            </div>
          </div>
          <div class="col-4 text-end">
            <div class="delivery-date">
              <strong>Delivery Date:</strong>
              <span id="deliveryDate" class="delivery-value" style="color: black;">
                <?= !empty($invoice['delivery_date']) ? date('d-m-Y', strtotime($invoice['delivery_date'])) : '' ?>
              </span>
            </div>

          </div>
        </div>

        <div class="invoice-header">
          <div class="col-12">
            Address: <span><?= esc($customer['address'] ?? '') ?>
            </span>:عنوان
          </div>
        </div>

        <!-- Invoice Table -->
        <table class="min_height">
          <thead>
            <tr>
              <th style="width: 10%; border: 1px solid #000; padding: 8px;">SR. NO</th>
              <th style="width: 37%; border: 1px solid #000; padding: 8px;">DESCRIPTION</th>
              <th style="width: 14%; border: 1px solid #000; padding: 8px;">Unit</th>
              <th style="width: 10%; border: 1px solid #000; padding: 8px;">Qty</th>
              <th style="width: 14%; border: 1px solid #000; padding: 8px;">LOCATION</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $minRows = 8; // minimum rows
            $i = 1;

            usort($items, function($a, $b) {
        return $a['item_id'] <=> $b['item_id'];
    });

            foreach ($items as $item):
              ?>
              <tr>
                <td style="border: 1px solid #000; text-align: center; "><?= $i++ ?></td>
                <td style="border: 1px solid #000; text-align: left; padding-left: 6px;"><?= esc($item['item_name'] ?? '-') ?></td>
                <td style="border: 1px solid #000; text-align: center;"><?= number_format($item['price'], 3) ?></td>
                <td style="border: 1px solid #000; text-align: center;"><?= esc($item['quantity']) ?></td>
                <td style="border: 1px solid #000; text-align: left; padding-left: 6px;""><?= esc(ucfirst($item['location'] ?? '-')) ?></td>
              </tr>
            <?php endforeach; ?>

            <?php
            $emptyRows = max(0, $minRows - count($items));
            for ($j = 0; $j < $emptyRows; $j++):
              ?>
              <tr class="empty-row">
                <td>&nbsp;</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
            <?php endfor; ?>
          </tbody>

        </table>

        <div class="d-flex w-100 position-relative" style="margin-top: 10px;">
          <div class="col-6 ms-auto">
            <hr>
            <?php
            $company_name = !empty($company['company_name']) ? $company['company_name'] : 'Unknown Company';
            ?>
            <div class="text-center" style="font-size: 15px;">
              For <?= esc(ucwords(strtolower($company_name))) ?>
            </div>
          </div>
        </div>

        <div class="table-footer">
          <div>Received By/ تم الاستلام بواسطة </div>
          <div style="text-align: right;">Issued By / صادرة عن</div>
        </div>
        <div class="table-footer">
          <div> Signature / إمضاء</div>
          <div style="text-align: right;">Signature / إمضاء</div>
        </div>
      </div> <!-- /.container -->
      <!-- Bottom Bar -->
      <div class="bottom-bar">
        الراي ، قطعة ٣ ، شارع ٣٢ ، مبنى رقم ٤٣٧ ، محل رقم ٤ ، بالقرب من زجاج الروان ، الشويخ - الكويت<br>
        Al-Rai, Block 3, Street 32, Build No. 437, Shop No. 4, Near Al Rawan Glass, Shuwaik - Kuwait<br>
        📞 +965 6006 0102 &nbsp;&nbsp; | &nbsp;&nbsp;
        📧 <a href="mailto:alraiprintpress@gmail.com" style="color: white; text-decoration: none;">
          alraiprintpress@gmail.com
        </a>
      </div>
    </div>
  </div>

  <!-- Partial Payment Modal -->

</body>

</html>
</div>
<?php include "common/footer.php"; ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
  function downloadPDF() {
    const element = document.querySelector('.outer-container');

    // Clone the element so we can modify it without affecting the page
    const clone = element.cloneNode(true);

    // Remove buttons in the clone only
    clone.querySelectorAll('.no-print').forEach(el => el.remove());

    const opt = {
      margin: [0.5, 0.5, 0.5, 0.5],
      filename: 'DeliveryNote-<?= $invoice['invoice_id'] ?>.pdf',
      image: { type: 'jpeg', quality: 0.98 },
      html2canvas: { scale: 2, useCORS: true },
      jsPDF: { unit: 'pt', format: 'a4', orientation: 'portrait' },
      pagebreak: { mode: ['avoid-all', 'css', 'legacy'] }
    };

    // Generate PDF from the clone
    html2pdf().set(opt).from(clone).save();
  }


  function formatDateToDDMMYYYY(date) {
    const d = new Date(date);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    return `${day}.${month}.${year}`;
  }


  document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('deliveryDate').textContent = formatDateToDDMMYYYY(new Date());
  });

  // window.onbeforeprint = function () {
  //   document.getElementById('deliveryDate').textContent = formatDateToDDMMYYYY(new Date());
  // };
</script>