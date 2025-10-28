<?php namespace App\Controllers;

use App\Models\Managecompany_Model;
use App\Models\InvoiceModel;
use App\Models\customerModel;
use App\Models\Manageuser_Model;

class ReceiptVoucher extends BaseController
{
    public function index($invoice_id = null)
    {
        // Load models
        $companyModel  = new Managecompany_Model();
        $invoiceModel  = new InvoiceModel();
        $customerModel = new customerModel();
        $userModel     = new Manageuser_Model(); 

        // Fetch details
        $company  = $companyModel->first(); // assuming single company
        $invoice  = $invoiceModel->find($invoice_id);
        $userName = session()->get('user_Name');
               $customer = null;
        if ($invoice && isset($invoice['customer_id'])) {
            $customer = $customerModel->find($invoice['customer_id']);
        }

        return view('print_receipt', [
            'company'  => $company,
            'invoice'  => $invoice,
            'customer' => $customer,
            'user_name'     => $userName,
        ]);
    }
}
