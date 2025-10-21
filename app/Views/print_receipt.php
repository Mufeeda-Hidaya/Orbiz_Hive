<?php include "common/header.php"; ?>
<!DOCTYPE html>
<html>
    <head>
        <title>Receipt Voucher</title>
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
                font-size: 33px;  
            }
            .voucher-sub { 
                text-align: center;    
                font-size: 21px; 
                margin-bottom: 64px; 
            }    
            .amount-box {
                display: flex;
                margin-top: 10px;
                gap: 1px;
                position: absolute;
                top: 120px;
                left: 43px;
            }

            .amount-field {
                text-align: center;
            }

            .amount-label {
                /* font-weight: bold; */
                display: block;
                margin-bottom: 5px;
            }
            .amount-value {
                border: 2px solid black;
                border-radius: 12px;
                    padding: 20px 45px;
                /* min-width: 75px; */
                font-size: 18px;
                font-weight: bold;
            }
            .field { 
                margin:25px; 
                /* font-weight: bold;  */
                margin-left: 21px;
                margin-right: 21px;
            }
            .label { 
                width: 200px; 
            }
            .dots { 
                border-bottom: 1px dotted #000; 
                display: inline-block; 
                width: 59%; 
                vertical-align: middle; 
            }
            .voucher-no{  
                position: absolute;
                top: 20%;
                left: 70%;
            }
            .voucher-meta {
                text-align: right; 
                margin-bottom: 45px; 
                /* font-weight: bold;  */
                position: relative;
                right: 46px;
            }
            .signatures { 
                margin-top: 25px; 
                display: flex; 
                justify-content: space-between; 
                margin-left: 20px;
            }
            .sign-box { 
                width: 40%; 
                text-align: left; 
                padding-top: 10px; 
                /* font-weight: bold;  */
            }
            .sign-label {
                display: flex;
                justify-content: left;
                gap: 125px; 
            }
            .sign-cash{
                 display: flex;
                justify-content: right;
                gap: 95px; 
                margin-right: 76px;
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
                left:80px;
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
                    style="background-color: #a1263a; color: white; padding: 8px 16px;     margin-left: 82%; border: none; border-radius: 5px; margin-left: 10px;">
                    Discard
                </button>
            </div>
            <div class="voucher-container">
                
                <div class="header">
                    <?php if (!empty($company['company_logo'])): ?>
                        <img src="<?= base_url('public/uploads/' . $company['company_logo']) ?>" 
                            alt="Company Logo" style=" max-height: 70px; width: 35%; margin-top: 8px;">
                    <?php endif; ?>
                </div>
                <div class="voucher-title"><strong> سند قبض</strong></div>
                <div class="voucher-sub"><strong>Receipt Voucher</strong></div>
                <div class="amount-box">
                <div class="amount-field">
                    <span class="amount-label">K.D. دينار</span>
                    <div class="amount-value">
                        
                    </div>
                </div>

                <div class="amount-field">
                    <span class="amount-label">Fils فلس</span>
                    <div class="amount-value" style="padding: 20px 33px ;">
                        
                    </div>
                </div>

            </div>
                <div class=" col-6 voucher-no">
                    <span class="label" style="font-size: 20px;"><strong>No:</strong> <?= esc($invoice['invoice_no'] ?? '') ?></span> 
                </div> 
                <div class="voucher-meta">
                
                    Date:<span class="dots" style=" width: 20%; text-align: center;"> <?= date('d-m-Y') ?></span> التاريخ:
                </div>
                <div class="field">
                    <span class="label">Received From Mr/Ms: </span>
                    <span class="dots"></span> استلمت من السيد/الآنسة 
                </div>
                <div class="field">
                    <span class="label">The Sum of K.D.</span>
                    <span class="dots" style="width:65%;"></span> مجموع الدينار الكويتي 
                </div>
                <div class="field">
                    <span class="label"> Cash / Cheque No. / K-Net</span>
                    <span class="dots" style=" width: 54%;"></span> كي-نت  /رقم النقد / الشيك
                </div>
                <div class="field">
                    <span class="label">Being Of:</span>
                    <span class="dots" style="width: 81%;"></span>كونه من
                </div>
                <div class="col-12 signatures">
                    <div class="col-6 sign-box">
                    <div class="sign-label">
                        <span>Receiver</span>
                        <span>المتلقي</span>
                    </div>
                        <span class="dots" style="width: 70%; margin-top: 40px;"></span>
                    </div>
                    <div class="col-6 sign-box">
                    <div class="sign-cash">
                        <span>Cashier </span>
                        <span> أمين الصندوق</span>
                    </div>  
                        <span class="dots" style="width: 76%; margin-top: 40px;"></span>
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
