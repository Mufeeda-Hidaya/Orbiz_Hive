<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\InvoiceModel;
use App\Models\InvoiceItemModel;
use App\Models\customerModel;
use App\Models\EstimateModel;
use App\Models\EstimateItemModel;
use App\Models\Manageuser_Model;
use App\Models\SalesModel;
use App\Models\Managecompany_Model;
use App\Models\TransactionModel;

use Google\Cloud\Translate\V2\TranslateClient;

class Invoice extends BaseController
{
    public function add()
{
    $customerModel = new customerModel();
    $companyId = session()->get('company_id');

    // Only fetch active (not deleted) customers
    $customers = $customerModel
        ->where('is_deleted', 0)
        ->where('company_id', $companyId)
        ->findAll();

    return view('invoice_form', [
        'customers' => $customers,
        'invoice' => []
    ]);
}


    public function list()
    {
        return view('invoicelist');
    }


    public function fetchInvoices()
    {
        $invoiceModel = new InvoiceModel();
        $data = $invoiceModel->getInvoiceListWithCustomer();
        return $this->response->setJSON(['data' => $data]);
    }

    
    public function invoicelistajax()
    {
        $session = session();

        $request = service('request');
        $draw = $request->getPost('draw');
        $start = $request->getPost('start');
        $length = $request->getPost('length');
        $searchValue = trim($request->getPost('search')['value'] ?? '');

        $orderColumnIndex = $request->getPost('order')[0]['column'] ?? 8;
        $orderDir = $request->getPost('order')[0]['dir'] ?? 'desc';

        $columns = [
            0 => 'invoices.invoice_id',
            1 => 'customers.name',
            2 => 'customers.address',
            3 => 'invoices.total_amount',
            4 => 'invoices.discount',
            5 => 'invoices.total_amount',
            6 => 'invoices.invoice_date',
            7 => 'invoices.invoice_id',
            8 => 'invoices.invoice_id'
        ];

        $orderByColumn = $columns[$orderColumnIndex] ?? 'invoices.invoice_id';
        $companyId = $session->get('company_id');

        $invoiceModel = new InvoiceModel();
        $itemModel = new InvoiceItemModel();
        $invoices = $invoiceModel->where('company_id', $companyId)->findAll();
        $totalRecords = $invoiceModel->getInvoiceCount($companyId);
        $filteredRecords = $invoiceModel->getFilteredCount($searchValue, $companyId);
        $records = $invoiceModel->getFilteredInvoices($searchValue, $start, $length, $orderByColumn, $orderDir, $companyId);

        $data = [];
        $slno = $start + 1;

        foreach ($records as $row) {
            $items = $itemModel->where('invoice_id', $row['invoice_id'])->findAll();

            $subtotal = 0;
            foreach ($items as $item) {
                $subtotal += $item['quantity'] * $item['price'];
            }

            $data[] = [
                'slno' => $slno++,
                'invoice_id' => $row['invoice_id'],
                'invoice_no' => isset($row['invoice_no']) ? $row['invoice_no'] : '',
                'customer_name' => $row['customer_name'],
                'customer_address' => $row['customer_address'],
                'subtotal' => $subtotal,  
                'discount' => $row['discount'],
                'total_amount' => $row['total_amount'],
                'status' => $row['status'] ?? 'unpaid',
                'company_id' => $companyId,
                'invoice_date' => date('d-m-Y', strtotime($row['invoice_date'])),
                'payment_mode' => !empty($row['payment_mode']) ? strtolower($row['payment_mode']) : '',



            ];
        }
        return $this->response->setJSON([
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }

    private function translateToArabic($text)
    {
        if (empty($text)) {
            return '';
        }

        $url = "https://api.mymemory.translated.net/get?q=" . urlencode($text) . "&langpair=en|ar";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        if (!$response) {
            return $text;
        }

        $result = json_decode($response, true);
        return $result['responseData']['translatedText'] ?? $text;
    }
    public function print($id)
    {
        $invoiceModel = new InvoiceModel();
        $customerModel = new customerModel();
        $companyModel = new Managecompany_Model();


        $invoice = $invoiceModel->getInvoiceWithItems($id);

        if (!$invoice) {
            return redirect()->to('/invoicelist')->with('error', 'Invoice not found.');
        }

        $customer = $customerModel->find($invoice['customer_id']);

        $companyId = session()->get('company_id');
        $company = $companyModel->find($companyId);

        $invoice['payment_mode'] = strtolower($invoice['payment_mode'] ?? '');

        $viewData = [
            'invoice' => $invoice,
            'items' => $invoice['items'],
            'user_name' => session()->get('user_name') ?? 'Salesman',
            'customer' => $customer ?? [],
            'company' => $company,
        ];

        if ($companyId == 69) {
            return view('invoice_print', $viewData);
        } elseif ($companyId == 70) {
            return view('generate_invoice', $viewData);
        } else {
            return view('invoice_print', $viewData);
        }
    }


  public function save()
{
    $request = $this->request;
    $invoiceModel = new InvoiceModel();
    $itemModel = new InvoiceItemModel();
    $customerModel = new CustomerModel();
    $transactionModel = new TransactionModel();

    $discount = (float) $request->getPost('discount');
    $customerId = $request->getPost('customer_id');
    $customer = $customerModel->find($customerId);
    $estimateId = $this->request->getPost('estimate_id');

    if (!$customer) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Invalid customer selected.'
        ]);
    }

    // ✅ Validation for discount
    if (!empty($customer['max_discount']) && $discount > (float) $customer['max_discount']) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Discount cannot exceed maximum allowed discount (' . $customer['max_discount'] . 'KWD)'
        ]);
    }

    $invoiceId = $request->getPost('invoice_id');
    $originalStatus = $request->getPost('original_status');
    $companyId = session()->get('company_id');

    $totalAmount = $this->calculateTotal($request);

    $invoiceData = [
    'customer_id' => $customerId,
    'customer_address' => $customer['address'] ?? $request->getPost('customer_address') ?? '',
    'phone_number' => $customer['phone_number'] ?? $request->getPost('phone_number') ?? '',
    'lpo_no' => $request->getPost('lpo_no'),
    'total_amount' => $totalAmount,
    'discount' => $discount,
    'invoice_date' => date('Y-m-d'),
    'user_id' => session()->get('user_id') ?? 1,
    'status' => ($invoiceId && $originalStatus) ? $originalStatus : 'unpaid',
    'company_id' => $companyId,
    // ✅ Add these two lines to fix balance calculation
    'paid_amount' => 0,
    'balance_amount' => $totalAmount
];

    if ($invoiceId) {
        $invoiceModel->update($invoiceId, $invoiceData);
        $itemModel->where('invoice_id', $invoiceId)->delete();
        $message = 'Invoice Updated Successfully';
    } else {
        //  Generate company-specific invoice_no
        $lastInvoice = $invoiceModel
            ->where('company_id', $companyId)
            ->orderBy('invoice_no', 'DESC')
            ->first();

        $nextInvoiceNo = $lastInvoice ? $lastInvoice['invoice_no'] + 1 : 1;
        $invoiceData['invoice_no'] = $nextInvoiceNo;

        // Mark estimate as converted if applicable
        if (!empty($estimateId)) {
            $db = \Config\Database::connect();
            $db->table('estimates')->where('estimate_id', $estimateId)->update(['is_converted' => 1]);
        }

        $invoiceModel->insert($invoiceData);
        $invoiceId = $invoiceModel->getInsertID();
        $message = 'Generating Invoice';

        // ✅ Insert into transactions only if invoice is already "paid"
        if ($invoiceData['status'] === 'paid') {
            $transactionModel->insert([
                'customer_id' => $customerId,
                'invoice_id'  => $invoiceId,
                'user_id'     => session()->get('user_id') ?? 1,
                'company_id'  => $companyId,
                'invoice_amount' => $totalAmount,
                'paid_amount' => $totalAmount,   // full payment
                'partial_paid_amount' => $totalAmount,
                'payment_mode' => $request->getPost('payment_mode') ?? 'cash',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    // Save items
    $descriptions = $request->getPost('description');
    $quantities = $request->getPost('quantity');
    $prices = $request->getPost('price');
    $locations = $request->getPost('location'); 

    if ($descriptions && $quantities && $prices) {
        foreach ($descriptions as $i => $desc) {
            if (!empty($desc) && $quantities[$i] > 0 && $prices[$i] > 0) {
                $itemModel->insert([
                    'invoice_id' => $invoiceId,
                    'item_name' => ucfirst(trim($desc)),
                    'quantity' => $quantities[$i],
                    'price' => $prices[$i],
                    'location'   => $locations[$i] ?? null,
                    'item_order' => $i + 1 
                ]);
            }
        }
    }

    return $this->response->setJSON([
        'status' => 'success',
        'message' => $message,
        'invoice_id'  => $invoiceId,
        'invoice_no'  => $invoiceData['invoice_no'] ?? null,
        'redirect' => site_url('invoice/print/' . $invoiceId)
    ]);
}


    private function calculateTotal($request)
{
    $prices = $request->getPost('price') ?? [];
    $qtys = $request->getPost('quantity') ?? [];
    $discount = floatval($request->getPost('discount') ?? 0);
    $subtotal = 0;

    foreach ($prices as $i => $price) {
        $subtotal += floatval($price) * floatval($qtys[$i]);
    }

    // Cap discount to subtotal
    if ($discount > $subtotal) {
        $discount = $subtotal;
    }

    // Calculate total and round to 3 decimals (KWD standard)
    $total = round($subtotal - $discount, 6);
    return $total;
}


 public function edit($id)
{
    $invoiceModel = new InvoiceModel();
    $itemModel = new InvoiceItemModel();
    $customerModel = new customerModel();
    $transactionModel = new TransactionModel();

    $invoice = $invoiceModel->find($id);
    if (!$invoice) {
        return redirect()->to(base_url('invoicelist'))->with('error', 'Invoice not found.');
    }

    // ✅ Fetch customer details
    $customer = $customerModel->find($invoice['customer_id']);
    if ($customer) {
        $invoice['customer_name'] = $customer['name'] ?? '';
        $invoice['customer_address'] = $customer['address'] ?? '';
    } else {
        $invoice['customer_name'] = '';
        $invoice['customer_address'] = '';
    }

    // ✅ Fetch items in correct order
    $invoiceformitems = $itemModel
        ->where('invoice_id', $id)
        ->orderBy('item_order', 'ASC') // <---- FIXED HERE
        ->findAll();

    $companyId = session()->get('company_id');
    $customers = $customerModel
        ->where('is_deleted', 0)
        ->where('company_id', $companyId)
        ->findAll();

    $data = [
        'invoice' => $invoice,
        'invoiceformitems_og' => $invoiceformitems,
        'invoiceformitems' => $invoiceformitems,
        'customers' => $customers,
    ];

    return view('invoice_form', $data);
}


    public function delete($id = null)
    {
        if (!$id) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid ID']);
        }

        $model = new InvoiceModel();
        $deleted = $model->delete($id);

        return $this->response->setJSON([
            'status' => $deleted ? 'success' : 'error',
            'message' => $deleted ? 'Invoice deleted successfully.' : 'Failed to delete invoice.'
        ]);
    }
    public function printInvoice($id)
    {
        $model = new InvoiceModel();
        $invoice = $model->find($id);

        $itemModel = new InvoiceItemModel();
        $items = $itemModel->where('invoice_id', $id)->findAll();

        $user_id = $invoice['user_id'];
        $userModel = new Manageuser_Model();
        $user = $userModel->find($user_id);
        $user_name = $user['name'] ?? 'N/A';

        $invoice['payment_mode'] = strtolower($invoice['payment_mode'] ?? '');

        return view('invoice/invoice_print', [
            'invoice' => $invoice,
            'items' => $items,
            'user_name' => $user_name,
        ]);
    }
    // public function delivery_note($id)
    // {
    //     $invoiceModel = new InvoiceModel();
    //     $itemModel = new InvoiceItemModel();

    //     $invoice = $invoiceModel->find($id);
    //     $items = $itemModel->where('invoice_id', $id)->findAll();
    //     $customerModel = new customerModel();
    //     $customer = $customerModel->find($invoice['customer_id']);

    //     return view('delivery_note', [
    //         'invoice' => $invoice,
    //         'items' => $items,
    //         'customer' => $customer
    //     ]);

    // }
    public function delivery_note($id)
{
    $invoiceModel = new InvoiceModel();
    $itemModel = new InvoiceItemModel();
    $customerModel = new customerModel();

    // Find the invoice
    $invoice = $invoiceModel->find($id);
    if (!$invoice) {
        // Invoice not found, handle gracefully
        return redirect()->back()->with('error', 'Invoice not found.');
    }

    // Get items for the invoice
    $items = $itemModel->where('invoice_id', $id)->findAll();

    // Get customer info safely
    $customer = $customerModel->find($invoice['customer_id']);
    if (!$customer) {
        $customer = [
            'name' => '-N/A-',
            'email' => '-N/A-'
        ];
    }

    // Return view
    return view('delivery_note', [
        'invoice' => $invoice,
        'items' => $items,
        'customer' => $customer
    ]);
}

public function convertFromEstimate($estimateId)
{
    $estimateModel = new EstimateModel();
    $itemModel = new EstimateItemModel();
    $customerModel = new customerModel();

    $estimate = $estimateModel->find($estimateId);

    if (!$estimate) {
        return redirect()->back()->with('error', 'Estimate not found.');
    }

    // ✅ Fetch customer details
    $customer = $customerModel->find($estimate['customer_id']);

    // ✅ Fetch items in correct order (important line)
    $items = $itemModel
        ->where('estimate_id', $estimateId)
        ->orderBy('item_order', 'ASC') // ensures consistent order
        ->findAll();

    $companyId = session()->get('company_id');
    $customers = $customerModel
        ->where('is_deleted', 0)
        ->where('company_id', $companyId)
        ->findAll();

    // ✅ Set additional fields safely
    $estimate['customer_address'] = $customer['address'] ?? '';
    $estimate['phone_number'] = $estimate['phone_number'] ?? '';

    foreach ($items as &$item) {
        $item['item_name'] = ucfirst($item['description'] ?? '');
        $item['product_id'] = $item['product_id'] ?? '';
    }

    return view('invoice_form', [
        'invoice' => $estimate,
        'invoiceformitems' => $items,
        'customers' => $customers,
        'customer' => $customer,
        'is_converted' => true,
    ]);
}


    public function update_status()
    {
        $request = service('request');

        if (!$request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $data = $request->getJSON(true);
        $invoiceId = $data['invoice_id'] ?? null;
        $status = $data['status'] ?? null;
        $paymentMode = $data['payment_mode'] ?? null;

        log_message('info', 'UpdateStatus INPUT: ' . json_encode($data));

        $allowed = ['paid', 'unpaid', 'partial paid'];
        if (!$invoiceId || !in_array($status, $allowed)) {
            log_message('error', 'Invalid invoiceId or status: ' . $invoiceId . ', ' . $status);
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid data']);
        }

        $invoiceModel = new InvoiceModel();
        
        $invoice = $invoiceModel->find($invoiceId);

        if (!$invoice) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invoice not found']);
        }

        $updateData = ['status' => $status];

        if ($status === 'paid') {
            $updateData['paid_amount'] = $invoice['total_amount'];
            $updateData['balance_amount'] = 0;
        } elseif ($status === 'unpaid') {
            $updateData['paid_amount'] = 0;
            $updateData['balance_amount'] = $invoice['total_amount'];
        } else {
            $updateData['balance_amount'] = $invoice['total_amount'] - $invoice['paid_amount'];
        }

        if ($paymentMode) {
            $updateData['payment_mode'] = $paymentMode;
        }

        $updated = $invoiceModel->update($invoiceId, $updateData);

        if ($updated) {
        $transactionModel = new TransactionModel();
        $oldPaid = floatval($invoice['paid_amount'] ?? 0);
        $newPaid = floatval($updateData['paid_amount'] ?? $oldPaid);
        $delta = $newPaid - $oldPaid;

        if ($delta > 0) {
            $transactionModel->insert([
                'customer_id' => $invoice['customer_id'],
                'invoice_id' => $invoiceId,
                'user_id' => session()->get('user_id') ?? 1,
                'company_id' => $invoice['company_id'],
                'invoice_amount' => $invoice['total_amount'],
                'paid_amount' => $delta,               // just this payment
                'partial_paid_amount' => $newPaid,     // cumulative total paid
                'payment_mode' => $paymentMode ?? $invoice['payment_mode'] ?? '',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

            return $this->response->setJSON([
                'success' => true,
                'status' => $status,
                'paid_amount' => $updateData['paid_amount'] ?? $invoice['paid_amount'],
                'balance_amount' => $updateData['balance_amount']
            ]);
        } else {
            log_message('error', 'DB update failed for invoice ID ' . $invoiceId);
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update status'
            ]);
        }
    }
   public function update_partial_payment()
{
    $this->response->setContentType('application/json');
    $json = $this->request->getJSON();

    if (!$json || !isset($json->invoice_id) || !isset($json->paid_amount)) {
        return $this->response->setJSON(['success' => false, 'message' => 'Missing data']);
    }

    $invoice_id   = $json->invoice_id;
    $new_payment  = floatval($json->paid_amount);
    $payment_mode = trim($json->payment_mode);

    if ($new_payment <= 0) {
        return $this->response->setJSON(['success' => false, 'message' => 'Invalid payment amount']);
    }

    $invoiceModel = new InvoiceModel();
    $invoice = $invoiceModel->find($invoice_id);

    if (!$invoice) {
        return $this->response->setJSON(['success' => false, 'message' => 'Invoice not found']);
    }

    $total = floatval($invoice['total_amount']);
    $existing_paid = floatval($invoice['paid_amount'] ?? 0);

    $updated_paid = $existing_paid + $new_payment;
    $balance = $total - $updated_paid;

    // Determine status
    if ($updated_paid >= $total) {
        $status = 'paid';
        $updated_paid = $total; // prevent overpayment
        $balance = 0;
    } else {
        $status = 'partial paid';
    }

    $data = [
        'status' => $status,
        'paid_amount' => $updated_paid,
        'balance_amount' => $balance,
        'payment_mode' => $payment_mode
    ];

    $updated = $invoiceModel->update($invoice_id, $data);

    if ($updated && $new_payment > 0) {
        $transactionModel = new TransactionModel();
        $transactionModel->insert([
            'customer_id' => $invoice['customer_id'],
            'invoice_id'  => $invoice_id,
            'user_id'     => session()->get('user_id') ?? 1,
            'company_id'  => $invoice['company_id'],
            'invoice_amount' => $total,
            'paid_amount'  => $new_payment,       // this payment only
            'partial_paid_amount' => $updated_paid, // cumulative
            'payment_mode'  => $payment_mode,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    return $this->response->setJSON([
        'success' => $updated,
        'message' => $updated ? 'Updated' : 'Failed to update',
        'paid_amount' => $updated_paid,
        'balance_amount' => $balance,
        'status' => $status
    ]);
}


    public function report()
    {
        $salesModel = new SalesModel();
        $customerModel = new customerModel();
        $from = $this->request->getGet('from_date');
        $to = $this->request->getGet('to_date');
        $customer_id = $this->request->getGet('customer_id');
        $session = session();
        $companyId = $session->get('company_id');
        $customer = $salesModel->getCustomer($companyId);

        $data['customers'] = $customer;

        $data['invoices'] = $salesModel->getSalesReport($from, $to, $customer_id);

        return view('salesform', $data);
    }
    public function getSalesReportAjax()
    {
        $from = $this->request->getPost('fromDate');
        $to = $this->request->getPost('toDate');
        $customerId = $this->request->getPost('customerId');

        $salesModel = new SalesModel();
        $data = $salesModel->getFilteredSales($from, $to, $customerId);

        return $this->response->setJSON(['invoices' => $data]);
    }
    public function savePartialPayment()
    {
        $invoiceId = $this->request->getPost('invoice_id');
        $paidAmount = $this->request->getPost('paid_amount');
        $paymentMode = $this->request->getPost('payment_mode');

        if (!$invoiceId || !$paidAmount || !$paymentMode) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid data.']);
        }

        $invoiceModel = new InvoiceModel();

        // Update invoice
        $invoiceModel->update($invoiceId, [
            'paid_amount' => $paidAmount,
            'payment_mode' => $paymentMode
        ]);

        return $this->response->setJSON(['status' => 'success']);
    }

}
