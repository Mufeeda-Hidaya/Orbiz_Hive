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
            'invoice_id'     => 0, // placeholder
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
            invoices.invoice_id,
            invoices.invoice_date,
            invoices.status,
            invoices.total_amount,
            invoices.paid_amount,
            invoices.balance_amount,
            invoices.payment_mode,
            customers.name AS customer_name
        ')
        ->join('customers', 'customers.customer_id = invoices.customer_id', 'left')
        ->where('invoices.company_id', $companyId)
        ->groupStart()
            ->where('LOWER(TRIM(invoices.status))', 'paid')
            ->orWhere('LOWER(TRIM(invoices.status))', 'partial paid')
        ->groupEnd();

    if (!empty($from) && !empty($to)) {
        $builder->where('invoices.invoice_date >=', $from)
                ->where('invoices.invoice_date <=', $to);
    }

    if (!empty($month) && !empty($year)) {
        $builder->where('MONTH(invoices.invoice_date)', $month)
                ->where('YEAR(invoices.invoice_date)', $year);
    }

    $builder->orderBy('invoices.invoice_date', 'DESC');
    $results = $builder->get()->getResultArray();

    return $this->response->setJSON([
        'status' => 'success',
        'data' => $results
    ]);
}

}
