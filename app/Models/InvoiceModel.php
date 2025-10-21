<?php
namespace App\Models;

use CodeIgniter\Model;

class InvoiceModel extends Model
{
    protected $table = 'invoices';
    protected $primaryKey = 'invoice_id';

    protected $allowedFields = 
    ['customer_id', 
    'customer_address',
    'phone_number',
    'lpo_no',
    'discount', 
    'total_amount',
    'invoice_date',
    'status',
    'delivery_date',
    'paid_amount',
    'balance_amount',
    'user_id',
    'company_id',
    'payment_mode',
    'invoice_no'];
    protected $returnType = 'array';
    
     public function getInvoiceCount($companyId)
    {
          return $this->where('company_id', $companyId)->countAllResults();
    }
    public function getInvoiceListWithCustomer()
{
    return $this->select('invoices.*, customers.name as customer_name, (SELECT item_name FROM invoice_items WHERE invoice_items.invoice_id = invoices.invoice_id LIMIT 1) as item_name')
                ->join('customers', 'customers.customer_id = invoices.customer_id', 'left')
                ->orderBy('invoices.invoice_id', 'desc')
                ->findAll();
}
    public function getFilteredCount($searchValue, $companyId)
    {
        $searchValue = trim($searchValue);
        $builder = $this->db->table('invoices')
            ->join('customers', 'customers.customer_id = invoices.customer_id', 'left')
            ->where('invoices.company_id', $companyId);

        if (!empty($searchValue)) {
    $normalizedSearch = preg_replace('/\s+/', '', strtolower($searchValue)); 

    $builder->groupStart()
        ->like('customers.name', $searchValue)
        ->orLike('customers.address', $searchValue)
        ->orLike('invoices.invoice_id', $searchValue)
        ->orWhere("REPLACE(REPLACE(REPLACE(LOWER(customers.name), ' ', ''), '\n', ''), '\r', '') LIKE '%{$normalizedSearch}%'", null, false)
        ->orWhere("REPLACE(REPLACE(REPLACE(LOWER(customers.address), ' ', ''), '\n', ''), '\r', '') LIKE '%{$normalizedSearch}%'", null, false)
    ->groupEnd();
}

        return $builder->countAllResults();
    }




   public function getFilteredInvoices($searchValue = '', $start = 0, $length = 10, $orderColumn = 'invoice_id', $orderDir = 'desc', $companyId = null)
{
    $searchValue = trim($searchValue);
    $builder = $this->db->table('invoices')
        ->select('invoices.invoice_id, invoices.invoice_no,invoices.customer_id, invoices.discount, invoices.total_amount, invoices.invoice_date, invoices.phone_number, invoices.lpo_no, invoices.status, customers.name AS customer_name, customers.address AS customer_address')
        ->join('customers', 'customers.customer_id = invoices.customer_id', 'left')
        ->join('user', 'user.user_id = invoices.user_id', 'left')
        ->where('invoices.company_id', $companyId); 
   if (!empty($searchValue)) {
        $normalizedSearch = str_replace(' ', '', strtolower($searchValue));

        $builder->groupStart()
            ->like('customers.name', $searchValue)
            ->orLike('customers.address', $searchValue)
            ->orLike('invoices.invoice_id', $searchValue)

           ->orWhere("REPLACE(REPLACE(REPLACE(LOWER(customers.name), ' ', ''), '\n', ''), '\r', '') LIKE '%{$normalizedSearch}%'", null, false)
        ->orWhere("REPLACE(REPLACE(REPLACE(LOWER(customers.address), ' ', ''), '\n', ''), '\r', '') LIKE '%{$normalizedSearch}%'", null, false)
        ->groupEnd();
    }

    return $builder->orderBy($orderColumn, $orderDir)
                   ->limit($length, $start)
                   ->get()->getResultArray();
}

   public function getInvoiceWithItems($id)
{
    $invoice = $this->select(
        'invoices.invoice_id,
        invoices.invoice_no,
         invoices.customer_id,
         invoices.phone_number,
         invoices.customer_address,
         invoices.discount,
         invoices.total_amount,
         invoices.paid_amount,
         invoices.balance_amount,
         invoices.status,
         invoices.lpo_no,
         invoices.invoice_date,
         company.company_name AS company_name,
         customers.name AS customer_name, 
         customers.address AS customer_address'
    )
    ->join('customers', 'customers.customer_id = invoices.customer_id', 'left')
    ->join('user', 'user.user_id = invoices.user_id', 'left')
    ->join('company', 'company.company_id = user.company_id', 'left')
    ->where('invoices.invoice_id', $id)
    ->first();

   if ($invoice) {
            $itemModel = new InvoiceItemModel();
            $invoice['items'] = $itemModel
                ->where('invoice_id', $id)
                ->orderBy('item_order', 'ASC') // <-- fixed ordering
                ->findAll();
        }

    return $invoice;
}
public function getTodayRevenue($companyId)
{
    $today = date('Y-m-d');
    $tomorrow = date('Y-m-d', strtotime('+1 day'));

    $row = $this->selectSum('total_amount')
        ->where('company_id', $companyId)
        ->where('invoice_date >=', $today)
        ->where('invoice_date <', $tomorrow)
        ->get()
        ->getRow();

    return $row ? (float)$row->total_amount : 0;
}
public function getMonthlyRevenue($companyId)
{
    $start = date('Y-m-01');
    $end = date('Y-m-t');

    $row = $this->selectSum('total_amount')
        ->where('company_id', $companyId)
        ->where('invoice_date >=', $start)
        ->where('invoice_date <=', $end)
        ->get()
        ->getRow();

    return $row ? (float)$row->total_amount : 0;
}
   public function getInvoicesWithCustomer()
    {
        return $this->select('invoices.*, customers.name as customer_name')
                    ->join('customers', 'customers.customer_id = invoices.customer_id', 'left')
                    ->findAll();
    }
public function getLastInvoiceIdByCompany($companyId)
{
    return $this->select('invoice_id')
                ->where('company_id', $companyId)
                ->orderBy('invoice_no', 'DESC')
                ->first();
}


}
