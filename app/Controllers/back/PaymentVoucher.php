<?php namespace App\Controllers;

use App\Models\Managecompany_Model;
use App\Models\InvoiceModel;
use App\Models\customerModel;

class PaymentVoucher extends BaseController
{
    public function index($invoice_id = null)
    {
        // Load models
        $companyModel  = new Managecompany_Model();
        $invoiceModel  = new InvoiceModel();
        $customerModel = new customerModel();

        // Fetch details
        $company  = $companyModel->first(); // assuming single company
        $invoice  = $invoiceModel->find($invoice_id);
               $customer = null;
        if ($invoice && isset($invoice['customer_id'])) {
            $customer = $customerModel->find($invoice['customer_id']);
        }


        return view('payment_voucher', [
            'company'  => $company,
            'invoice'  => $invoice,
            'customer' => $customer,
        ]);
    }
    
}
