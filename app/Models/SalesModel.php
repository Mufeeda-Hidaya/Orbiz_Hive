<?php
namespace App\Models;

use CodeIgniter\Model;

class SalesModel extends Model
{
    protected $table = 'invoices';
    protected $primaryKey = 'invoice_id';
    protected $allowedFields = ['customer_id', 'invoice_date', 'total_amount', 'status'];

    public function getSalesReport($from = null, $to = null, $customer_id = null)
    {
        $companyId = session()->get('company_id');
        $builder = $this->db->table('invoices')
            ->select('invoices.invoice_id, invoices.invoice_date, invoices.total_amount, invoices.status, customers.name as customer_name')
            ->join('customers', 'customers.customer_id = invoices.customer_id');

        if ($companyId) {
            $builder->where('invoices.company_id', $companyId);
        }

        if ($from) {
            $builder->where('DATE(invoices.invoice_date) >=', $from);
        }

        if ($to) {
            $builder->where('DATE(invoices.invoice_date) <=', $to);
        }

        if ($customer_id) {
            $builder->where('invoices.customer_id', $customer_id);
        }

        $builder->orderBy('invoices.invoice_id', 'DESC');

        return $builder->get()->getResultArray();
    }
    public function getCustomer($companyId)
    {
        $sql = "SELECT customer_id, name, address, company_id 
            FROM customers 
            WHERE company_id = ?";
        return $this->db->query($sql, [$companyId])->getResultArray();
    }
    public function getFilteredSales($from = null, $to = null, $customerId = null)
    {
        $companyId = session()->get('company_id');
        $builder = $this->db->table('invoices');
        $builder->select('invoices.invoice_id, invoices.invoice_date, customers.name as customer_name, invoices.total_amount, invoices.status');
        $builder->join('customers', 'customers.customer_id = invoices.customer_id');

        if ($companyId) {
            $builder->where('invoices.company_id', $companyId);
        }
        if (!empty($from)) {
            $from = date('Y-m-d', strtotime(str_replace('/', '-', $from)));
            $builder->where('DATE(invoices.invoice_date) >=', $from);
        }
        if (!empty($to)) {
            $to = date('Y-m-d', strtotime(str_replace('/', '-', $to)));
            $builder->where('DATE(invoices.invoice_date) <=', $to);
        }
        if (!empty($customerId)) {
            $builder->where('invoices.customer_id', $customerId);
        }

        $builder->orderBy('invoices.invoice_id', 'DESC');
        return $builder->get()->getResultArray();

    }

}
