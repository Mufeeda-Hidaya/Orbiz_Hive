<?php

namespace App\Controllers;
use App\Models\Login_Model;

class Dashboard extends BaseController
{
    public function __construct()
    {
        $session = \Config\Services::session();
        if (!$session->get('logged_in')) {
            header('Location: ' . base_url('/'));
            exit;
        }
    }
//    public function index()
// {
//     $session = session();
//     $company_id = $session->get('company_id'); // Get logged-in user's company

//     // Get recent estimates
//     $estimateModel = new \App\Models\EstimateModel();
//     $recentEstimates = $estimateModel->getRecentEstimatesWithCustomer($company_id);

//     // Get revenue data
//     $invoiceModel = new \App\Models\InvoiceModel();
//     $dailyRevenue = $invoiceModel->getTodayRevenue($company_id);
//     $monthlyRevenue = $invoiceModel->getMonthlyRevenue($company_id);

//     return view('dashboard', [
//         'estimates' => $recentEstimates,
//         'dailyRevenue' => $dailyRevenue,
//         'monthlyRevenue' => $monthlyRevenue
//     ]);
// }

public function index()
{
    $session = session();
    $company_id = $session->get('company_id');
    $roleName = strtolower($session->get('role_Name')); // normalize

    if ($roleName === 'admin') {
        // ADMIN DASHBOARD
        $estimateModel = new \App\Models\EstimateModel();
        $recentEstimates = $estimateModel->getRecentEstimatesWithCustomer($company_id);

        $invoiceModel = new \App\Models\InvoiceModel();
        $dailyRevenue = $invoiceModel->getTodayRevenue($company_id);
        $monthlyRevenue = $invoiceModel->getMonthlyRevenue($company_id);

        return view('dashboard', [
            'estimates' => $recentEstimates,
            'dailyRevenue' => $dailyRevenue,
            'monthlyRevenue' => $monthlyRevenue
        ]);
    } else {
        // USER DASHBOARD
        return view('user_dashboard');
    }
}



   public function getTodayExpenseTotal()
{
    $expenseModel = new \App\Models\Expense_Model();
    $session = session();
    $company_id = $session->get('company_id'); // company from logged-in user
    $today = date('Y-m-d');

    $total = $expenseModel
        ->selectSum('amount')
        ->where('date', $today)
        ->where('company_id', $company_id)
        ->first();

    return $this->response->setJSON([
        'total' => (float)($total['amount'] ?? 0)
    ]);
}

public function getMonthlyExpenseTotal()
{
    $expenseModel = new \App\Models\Expense_Model();
    $session = session();
    $company_id = $session->get('company_id'); // company from logged-in user

    $start = date('Y-m-01'); // First day of month
    $end = date('Y-m-t');    // Last day of month

    $total = $expenseModel
        ->selectSum('amount')
        ->where('date >=', $start)
        ->where('date <=', $end)
        ->where('company_id', $company_id)
        ->first();

    return $this->response->setJSON([
        'total' => (float)($total['amount'] ?? 0)
    ]);
}


public function getTodayRevenueTotal()
{
    $invoiceModel = new \App\Models\InvoiceModel();
    $session = session();
    $company_id = $session->get('company_id');
    $today = date('Y-m-d');

    $total = $invoiceModel
        ->selectSum('paid_amount') 
        ->where('invoice_date', $today)
        ->where('company_id', $company_id)
        ->whereIn('status', ['paid', 'partial paid']) // only include paid & partial
        ->first();

    return $this->response->setJSON(['total' => (float)($total['paid_amount'] ?? 0)]);
}

public function getMonthlyRevenueTotal()
{
    $invoiceModel = new \App\Models\InvoiceModel();
    $session = session();
    $company_id = $session->get('company_id');

    $total = $invoiceModel
        ->selectSum('paid_amount') 
        ->where('MONTH(invoice_date)', date('m'))
        ->where('YEAR(invoice_date)', date('Y'))
        ->where('company_id', $company_id)
        ->whereIn('status', ['paid', 'partial paid'])
        ->first();

    return $this->response->setJSON(['total' => (float)($total['paid_amount'] ?? 0)]);
}


}
