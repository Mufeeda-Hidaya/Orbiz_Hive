<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Expense_Model;
use App\Models\SupplierModel;
use App\Models\Manageuser_Model;

class Expense extends BaseController
{
    public function __construct()
    {
       $session = \Config\Services::session();
        if (!$session->get('logged_in')) {
            header('Location: ' . base_url('/'));
            exit;
        }
    }
    public function index()
    {
        return view('addexpenselist');
    }

    public function create($id = null)
    {
        $SupplierModel = new SupplierModel();
        $session    = session();
        $companyId  = $session->get('company_id');
        $data = [
            'isEdit' => !empty($id),
            'expense' => null,
           'suppliers' => $SupplierModel
            ->where('company_id', $companyId)
            ->where('is_deleted', 0)   // exclude deleted
            ->findAll()
        
        ];

        if ($id) {
            $expenseModel = new Expense_Model();
            $data['expense'] = $expenseModel->find($id);
        }

        return view('addexpense', $data);
    }

    public function store()
    {
        $expenseModel = new Expense_Model();
         $session = session();
            $companyId = $session->get('company_id'); 


        $id           = $this->request->getPost('id');
        $date         = $this->request->getPost('date');
        $convertedDate = date('Y-m-d', strtotime(str_replace('/', '-', $date)));
        $particular   = $this->request->getPost('particular');
        $amount       = $this->request->getPost('amount');
        $payment_mode = $this->request->getPost('payment_mode');
        $reference    = $this->request->getPost('reference');
        $supplier_id  = $this->request->getPost('supplier_id');

        if (empty($date) || empty($particular) || empty($amount) || empty($payment_mode)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Please Fill All Mandatory Fields.'
            ]);
        }

        $data = [
            'date' => $convertedDate,
            'particular'   => $particular,
            'amount'       => $amount,
            'payment_mode' => $payment_mode,
            'reference'    => $reference,
            'company_id'   => $companyId,
            'supplier_id'  => $supplier_id
        ];

        if (!empty($id)) {
            $existing = $expenseModel->find($id);
            if (!$existing) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Expense Not Found.'
                ]);
            }

            $hasChanges = (
                $existing['date'] !== $data['date'] ||
                $existing['particular'] !== $data['particular'] ||
                $existing['amount'] != $data['amount'] ||
                $existing['payment_mode'] !== $data['payment_mode'] ||
                $existing['reference'] !== $data['reference'] ||
                $existing['supplier_id'] != $data['supplier_id']  
            );

            if ($hasChanges) {
                $expenseModel->update($id, $data);
                $msg = 'Expense Updated Successfully.';
            } else {
                $msg = 'No Changes Detected.';
            }
        } else {
           $expenseModel->insert($data);
            $msg = 'Expense Created Successfully.';
            return $this->response->setJSON([
                'status' => 'success',
                'message' => $msg,
                'redirect_to_list' => false
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => $msg,
             'redirect_to_list' => !empty($id)
        ]);
    }

 public function getExpensesAjax()
{
     $session = session();
    $model = new Expense_Model();
    $userModel = new Manageuser_Model();

    $draw = $_POST['draw'];
    $fromstart = $_POST['start'];
    $tolimit = $_POST['length'];
    $orderColumnIndex = $_POST['order'][0]['column'] ?? 1; 
    $orderDir = $_POST['order'][0]['dir'] ?? 'desc';
    $search = $_POST['search']['value'];
    $slno = $fromstart + 1;

    $columns = ['slno', 'date', 'particular','s.name', 'amount', 'payment_mode', 'id'];
    $orderBy = $columns[$orderColumnIndex] ?? 'date';

    if ($orderBy === 'slno' || $orderBy === 'id') {
        $orderBy = 'date';
    }

    $condition = "1=1";
     $user = $userModel->find($session->get('user_id'));
    $roleId = $user['role_id'];
    $companyId = $session->get('company_id');

    if ($roleId == 1) { 
        if (!empty($companyId)) {
            $condition .= " AND company_id = " . (int) $companyId;
        }
    } else {
         $condition .= " AND company_id = " . (int) $user['company_id'];
    }

    $search = trim(preg_replace('/\s+/', ' ', $search)); 

    if (!empty($search)) {
        $noSpaceSearch = str_replace(' ', '', strtolower($search));

        $condition .= " AND (
            REPLACE(LOWER(payment_mode), ' ', '') LIKE '%{$noSpaceSearch}%' 
            OR REPLACE(LOWER(particular), ' ', '') LIKE '%{$noSpaceSearch}%' 
           OR REPLACE(LOWER(s.name), ' ', '') LIKE '%{$noSpaceSearch}%'
            OR REPLACE(LOWER(amount), ' ', '') LIKE '%{$noSpaceSearch}%' 
            OR DATE_FORMAT(date, '%d-%m-%Y') LIKE '%$search%'
        )";
    }

    $totalRec = $model->getAllFilteredRecords($condition, $fromstart, $tolimit, $orderBy, $orderDir);

    $result = [];
    foreach ($totalRec as $expense) {
        $formattedDate = date('d-m-Y', strtotime($expense->date)); 
        $result[] = [
            'slno'         => $slno++,
            'id'           => $expense->id,
            'date'         => $formattedDate, 
            'particular'   => $expense->particular,
            'amount'       => (float) $expense->amount,
            'payment_mode' => $expense->payment_mode ,
           'supplier_name'=> $expense->supplier_name ?? 'N/A',

        ];
    }

    $totExpenseCount = $model->getAllExpenseCount();
    $totFilterCounts = $model->getFilterExpenseCount($condition);

    $response = [
        "draw" => intval($draw),
        "iTotalRecords" => $totExpenseCount->totexpense ?? 0,
        "iTotalDisplayRecords" => $totFilterCounts->filRecords ?? 0,
        "data" => $result
    ];

    return $this->response->setJSON($response);
}

   public function delete()
{
    $expense_id = $this->request->getPost('expense_id');

    if (!$expense_id) {
        return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid expense ID']);
    }

    $expenseModel = new Expense_Model();
    $expenseModel->delete($expense_id);

    return $this->response->setJSON(['status' => 'success']);
}


    //for report
    public function report()
    {
        return view('expensereport');
    }

   public function getExpenseReportAjax()
{
    $model = new Expense_Model();
     $session = session();
    $companyId = $session->get('company_id');

    $date     = $this->request->getPost('date');       
    $month    = $this->request->getPost('month');
    $year     = $this->request->getPost('year');
    $fromDate = $this->request->getPost('fromDate');
    $toDate   = $this->request->getPost('toDate');

    $builder = $model->builder();
    $builder->where('company_id', $companyId);

    
    if (!empty($fromDate) && !empty($toDate)) {
        $builder->where('date >=', $fromDate)->where('date <=', $toDate);
    }
    
    elseif (!empty($date)) {
        $builder->where('DATE(date)', $date);
    }
    
    elseif (!empty($month) && !empty($year)) {
        $builder->where('MONTH(date)', $month);
        $builder->where('YEAR(date)', $year);
    }
   
    elseif (!empty($year)) {
        $builder->where('YEAR(date)', $year);
    }

    $builder->orderBy('date', 'DESC')->orderBy('id', 'DESC');

    $data = $builder->get()->getResult();

    return $this->response->setJSON($data);

}

    }

    
