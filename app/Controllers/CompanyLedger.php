<?php

namespace App\Controllers;

use App\Models\CompanyLedgerModel;
use App\Models\Managecompany_Model;

class CompanyLedger extends BaseController
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
        $companyModel = new Managecompany_Model();
        $data['companies'] = $companyModel->findAll();

        return view('companyledger', $data);
    }

    public function save()
    {
        $companyId = $this->request->getPost('company_id');

        if (!$companyId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Company ID is required']);
        }

        $ledgerModel = new CompanyLedgerModel();

        $ledgerModel->insert([
            'company_id'     => $companyId,
            'joborder_id'     => 0, // placeholder
            'customer_id'    => 0, // placeholder
            'invoice_amount' => 0  // placeholder
        ]);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Ledger Entry Created For The Company.']);
    }
public function getPaidInvoices()
{
    $session = session();
    $companyId = $session->get('company_id');

    if (!$companyId) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Company ID is missing from session.'
        ]);
    }

    $from     = $this->request->getPost('from');
    $to       = $this->request->getPost('to');
    $month    = $this->request->getPost('month');
    $year     = $this->request->getPost('year');

    $invoiceModel = new \App\Models\InvoiceModel();

    $builder = $invoiceModel->builder()
        ->select('
            joborder.joborder_id,
            joborder_id.invoice_date,
            joborder.status,
            joborder.total_amount,
            joborder.paid_amount,
            joborder.balance_amount,
            joborder.payment_mode,
            customers.name AS customer_name
        ')
        ->join('customers', 'customers.customer_id = joborder.customer_id', 'left')
        ->where('joborder.company_id', $companyId)
        ->groupStart()
            ->where('LOWER(TRIM(joborder.status))', 'paid')
            ->orWhere('LOWER(TRIM(joborder.status))', 'partial paid')
        ->groupEnd();

    if (!empty($from) && !empty($to)) {
        $builder->where('joborder.invoice_date >=', $from)
                ->where('joborder.invoice_date <=', $to);
    }

    if (!empty($month) && !empty($year)) {
        $builder->where('MONTH(joborder.invoice_date)', $month)
                ->where('YEAR(joborder.invoice_date)', $year);
    }

    $builder->orderBy('joborder.invoice_date', 'DESC');
    $results = $builder->get()->getResultArray();

    return $this->response->setJSON([
        'status' => 'success',
        'data' => $results
    ]);
}

}
