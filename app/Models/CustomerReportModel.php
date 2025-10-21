<?php
namespace App\Models;
use CodeIgniter\Model;

class CustomerReportModel extends Model
{
    protected $table = 'invoices'; // default table
    protected $primaryKey = 'invoice_id';
    
    public function getInvoicesByCustomer($customer_id)
    {
        return $this->db->table('invoices i')
            ->select('i.invoice_id AS id, i.invoice_no AS no, c.name AS customer,, i.discount, i.total_amount AS total, i.paid_amount AS paid, i.balance_amount AS balance, i.invoice_date AS date')
            ->join('customers c', 'c.customer_id = i.customer_id')
            ->where('i.customer_id', $customer_id)
            ->orderBy('i.invoice_id','DESC')
            ->get()
            ->getResultArray();
    }

    public function getEstimatesByCustomer($customer_id)
    {
        return $this->db->table('estimates e')
            ->select('e.estimate_id AS id, e.estimate_no AS no, c.name AS customer, , e.discount, e.total_amount AS total, NULL AS paid, NULL AS balance, e.date')
            ->join('customers c', 'c.customer_id = e.customer_id')
            ->where('e.customer_id', $customer_id)
            ->orderBy('e.estimate_id','DESC')
            ->get()
            ->getResultArray();
    }
}
