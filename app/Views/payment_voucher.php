<?php include "common/header.php"; ?>
<!DOCTYPE html>
<html>
    <head>
        <title>Payment Voucher</title>
        <style>
            body {
                font-family: Arial, sans-serif; 
                font-size: 14px; 
            }
             .outer-container {
                  width: 100%;         
                max-width: 900px;    
                margin: 0 auto;       
                padding: 0 15px;
            }
            .voucher-container { 
                          width: 800px; 
                margin: 0 auto;
                /* padding: 20px; */
                position: relative;
                background: url('<?= ASSET_PATH ?>assets/images/invoice-bg.png') no-repeat;
                background-size: 44%;
                background-position: 52% 50%;
                background-color: white;
                border: 3px solid #a1263a;
                border-radius: 10px;
                margin-top: 2px;
                margin-left: 18%;
            }
            .header { 
                text-align: center; 
                margin-bottom: 10px; 
            }
            .header img {
                max-height: 70px; 
                display: block; 
                margin: auto; 
            }
            .company-name { 
                font-size: 20px; 
                font-weight: bold; 
                margin-top: 10px; 
            }
            .voucher-title { 
                text-align: center;
                font-size: 38px;  
            }
            .voucher-sub { 
                text-align: center;    
                font-size: 24px; 
                margin-bottom: 25px; 
            }    
            .amount-box {
                position: absolute;
                top: 120px;
                left: 40px;
                border: 1px solid #000;
                width: 175px;
                font-weight: bold;
                text-align: center;
                border-radius: 15px;
                overflow: hidden;
            }
            .amount-box table {
                width: 100%;
                border-collapse: collapse;
            }
            .amount-box td {
                border: 1px solid #000;
                padding: 4px 6px 35px; 
            }
            .amount-box th {
                border: 1px solid #000;
                font-size: 13px;
                background: #f9f9f9;
            }

            .field { 
                margin:25px 20px; 
                /* font-weight: bold;  */
            }
            .label { 
                width: 200px; 
            }
            .dots { 
                border-bottom: 1px dotted #000; 
                display: inline-block; 
                width: 68%; 
                vertical-align: middle; 
            }
            .voucher-no{  
                position: absolute;
                top: 16%;
                left: 76%;
                /* font-weight: bold; */
            }
            .voucher-meta {
                text-align: right; 
                margin-bottom: 45px; 
                /* font-weight: bold;  */
                position: relative;
                right: 40px;
            }
            .signatures { 
                margin-top: 50px; 
                display: flex; 
                justify-content: space-between; 
            }
            .sign-box { 
                width: 45%; 
                text-align: center; 
                padding-top: 10px; 
                /* font-weight: bold;  */
            }
            .bottom-footer { 
                text-align: center; 
                font-size: 12px; 
                margin-top: 40px; 
                line-height: 1.5; 
                background:  #a1263a; 
                color: #fff; 
                padding: 10px; 
            }
            .label{
                font-size:15px;
            }
             .no-print{
                position:relative;
                left:90px;
            }
                      @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            body {
                margin: 0;
                padding: 0;
                font-size: 14px;
                line-height: 1.4;
            }

            .no-print,
            .header button,
            .footer,
            .sidebar,
            .navbar {
                display: none !important;
            }

            .outer-container {
                margin: 0;
                padding: 0;
                width: 100%;
            }

            .voucher-container {
                margin: 0 auto !important;   
                width: 100% !important;    
                max-width: 800px;         
                border: 3px solid #a1263a;
                page-break-inside: avoid;  
            }

            .voucher-container * {
                box-sizing: border-box;
            }
        }

        </style>
    </head>
    <body>
        <div class="outer-container" >
            <div class="no-print">
                <button  onclick="window.print()"
                    style="background-color: #991b36; color: white; padding: 8px 16px;     margin-left: 82%; border: none; border-radius: 5px;">
                    Print
                </button>
                <button  onclick="window.location.href='<?= base_url('cashlist') ?>'"
                    style="background-color: #a1263a; color: white; padding: 8px 16px; border: none; border-radius: 5px;">
                    Discard
                </button>
            </div>
            <div class="voucher-container">
                <div class="header">
                    <?php if (!empty($company['company_logo'])): ?>
                        <img src="<?= base_url('public/uploads/' . $company['company_logo']) ?>" 
                            alt="Company Logo" style=" max-height: 55px; width: 35%; margin-top: 8px;">
                    <?php endif; ?>
                </div>
                <div class="amount-box">
                    <table>
                        <tr>
                            <th>K.D.دينار</th>
                            <th>Fils فلس</th>
                        </tr>
                        <tr>
                            <td><?= isset($invoice['amount']) ? sprintf("%02d", ($invoice['amount']*100)%100) : '' ?></td>
                            <td><?= isset($invoice['amount']) ? floor($invoice['amount']) : '' ?></td>
                        </tr>
                    </table>
                </div>
                <div class="voucher-title"><strong>سند صرف</strong></div>
                <div class="voucher-sub"><strong>PAYMENT VOUCHER</strong></div>
                <div class=" col-6 voucher-no">
                    <span class="label">No.:</span> 
                    <span style="display:inline-block; ">&nbsp;</span>
                    <span style="display: inline-block; width: 85px; "> <?= esc($invoice['invoice_no'] ?? '') ?></span>: رقم  
                </div> 
                <div class="voucher-meta">
                
                    Date<span class="dots" style=" width: 20%; text-align: center;"> <?= date('d-m-Y') ?></span>: التاريخ
                </div>
                <div class="field">
                    <span class="label">Paid To Mr./Mrs.: </span>
                    <span class="dots"><?= esc($invoice['customer_name'] ?? '') ?></span> :مدفوع للسيد/السيدة   
                </div>
                <div class="field">
                    <span class="label">The Sum of K.D.:</span>
                    <span class="dots" style="width:66%;"></span> مجموع الدينار الكويتي 
                </div>
                <div class="field">
                    <span class="label">Bank: </span>
                    <span class="dots" style=" width: 25%;"><?= esc($invoice['bank'] ?? '') ?></span> :بنك Cash / Cheque No. / K-Net 
                    <span class="dots" style=" width: 22%;"><?= esc($invoice['cheque_no'] ?? '') ?></span> كي-نت  /رقم النقد / الشيك
                </div>
                <div class="field">
                    <span class="label">Details:</span>
                    <span class="dots" style="width: 83%;"><?= esc($invoice['details'] ?? '') ?></span> :تفاصيل
                </div>
                <div class="signatures">
                    <div class="sign-box">Accountant Sig. / توقيع المحاسب
                        <span class="dots" style="width: 75%; margin-top: 35px;"></span>
                    </div>
                    <div class="sign-box">Receiver Sig. / توقيع المستلم
                        <span class="dots" style="width: 75%; margin-top: 35px;"></span>
                    </div>
                </div>
                <!-- Footer -->
                <div class="bottom-footer">
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
        </div>
    </body>
</html>
<?php include "common/footer.php"; ?>
