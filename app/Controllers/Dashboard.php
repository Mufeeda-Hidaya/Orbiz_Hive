<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\EstimateModel;
use App\Models\Expense_Model;

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

    public function index()
    {
        $session = session();
        $roleName = strtolower($session->get('role_Name'));

        if ($roleName === 'admin') {

            $estimateModel = new EstimateModel();

            // Revenue = Confirmed Estimates
            $recentEstimates = $estimateModel->getRecentEstimatesWithCustomer();

            $dailyRevenue   = $this->getTodayEstimateRevenue();
            $monthlyRevenue = $this->getMonthlyEstimateRevenue();

            return view('dashboard', [
                'estimates'       => $recentEstimates,
                'dailyRevenue'    => $dailyRevenue,
                'monthlyRevenue'  => $monthlyRevenue
            ]);
        }

        return view('user_dashboard');
    }

    // ---------- Revenue from estimates ----------

    private function getTodayEstimateRevenue()
    {
        $estimateModel = new EstimateModel();
        $today = date('Y-m-d');

        $total = $estimateModel
            ->selectSum('net_total')
            ->where('date', $today)
            ->where('status', 1) // confirmed / active
            ->first();

        return (float)($total['net_total'] ?? 0);
    }

    private function getMonthlyEstimateRevenue()
    {
        $estimateModel = new EstimateModel();

        $total = $estimateModel
            ->selectSum('net_total')
            ->where('MONTH(date)', date('m'))
            ->where('YEAR(date)', date('Y'))
            ->where('status', 1)
            ->first();

        return (float)($total['net_total'] ?? 0);
    }

    // ---------- Expense ----------

    public function getTodayExpenseTotal()
    {
        $expenseModel = new Expense_Model();
        $today = date('Y-m-d');

        $total = $expenseModel
            ->selectSum('amount')
            ->where('date', $today)
            ->first();

        return $this->response->setJSON([
            'total' => (float)($total['amount'] ?? 0)
        ]);
    }

    public function getMonthlyExpenseTotal()
    {
        $expenseModel = new Expense_Model();

        $start = date('Y-m-01');
        $end   = date('Y-m-t');

        $total = $expenseModel
            ->selectSum('amount')
            ->where('date >=', $start)
            ->where('date <=', $end)
            ->first();

        return $this->response->setJSON([
            'total' => (float)($total['amount'] ?? 0)
        ]);
    }
}
