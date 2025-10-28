<?php namespace App\Controllers;

use App\Models\CashReceiptModel;
use CodeIgniter\API\ResponseTrait;

class CashReceipt extends BaseController
{
    use ResponseTrait;

    protected $cashModel;

    public function __construct()
    {
        $this->cashModel = new CashReceiptModel();
    }

    public function index()
    {
        return view('cashlist');
    }

public function ajaxListJson()
{
    $session = session();
    $company_id = $session->get('company_id'); 

    $request = service('request');
    $draw = $request->getPost('draw') ?? 1;
    $fromstart = $request->getPost('start') ?? 0;
    $tolimit = $request->getPost('length') ?? 10;
    $order = $request->getPost('order')[0]['dir'] ?? 'desc';
    $columnIndex = $request->getPost('order')[0]['column'] ?? 1;
    $search = $request->getPost('search')['value'] ?? '';

    $columnMap = [
        0 => 'invoice_id',
        1 => 'invoice_id', // Sl No
        2 => 'customer_id',
        3 => 'invoice_date',
        4 => 'total_amount',
        5 => 'paid_amount',
        6 => 'balance_amount',
        7 => 'status',
        8 => 'payment_mode'
    ];

    $orderColumn = $columnMap[$columnIndex] ?? 'invoice_id';
    $receipts = $this->cashModel->getAllFilteredCashReceipts(
        $company_id,
        $search,
        $fromstart,
        $tolimit,
        $orderColumn,
        $order
    );

    $data = [];
    $slno = $fromstart + 1;

    foreach ($receipts as $row) {
        $data[] = [
            'slno' => $slno++,
            'payment_id' => $row['invoice_id'],
            'customer_name' => $row['customer_name'],
            'payment_date' => date('d-m-Y', strtotime($row['invoice_date'])),
            'amount' => $row['total_amount'],
            'paid_amount' => $row['paid_amount'] ?? 0,
            'balance_amount' => $row['balance_amount'] ?? ($row['total_amount'] ?? 0),
            'payment_status' => $row['status'],
            'payment_mode' => $row['payment_mode']
        ];
    }

    $total = $this->cashModel->getAllCashReceiptsCount($company_id);
    $filteredTotal = $this->cashModel->getFilteredCashReceiptsCount($company_id, $search)->filReceipts;

    return $this->response->setJSON([
        'draw' => intval($draw),
        'recordsTotal' => $total,
        'recordsFiltered' => $filteredTotal,
        'data' => $data
    ]);
}

    public function delete()
    {
        $id = $this->request->getPost('id');
        if (!$id) return $this->respond(['status' => 'error','message' => 'No ID provided.']);

        $receipt = $this->cashModel->find($id);
        if (!$receipt) return $this->respond(['status' => 'error','message' => 'Cash Receipt Not Found.']);

        $this->cashModel->delete($id);
        return $this->respond(['status' => 'success','message' => 'Cash Receipt Deleted Successfully.']);
    }
}
