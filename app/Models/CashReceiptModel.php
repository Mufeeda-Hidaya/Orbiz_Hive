<?php namespace App\Models;

use CodeIgniter\Model;

class CashReceiptModel extends Model
{
    protected $table = 'invoices';
    protected $primaryKey = 'invoice_id';
    protected $allowedFields = [
        'estimate_id','company_id','customer_id','customer_address','customer_phone','customer_email',
        'total_amount','status','user_id','discount','invoice_date','billing_address',
        'shipping_address','lpo_no','phone_number','delivery_date','paid_amount','balance_amount','payment_mode'
    ];

    public function getAllFilteredCashReceipts($company_id, $search = '', $start = 0, $length = 10, $orderColumn = 'invoice_id', $orderDir = 'DESC')
    {
        $builder = $this->db->table('invoices i')
            ->select('i.*, c.name AS customer_name')
            ->join('customers c', 'c.customer_id = i.customer_id', 'left')
            ->where('i.status !=', 'unpaid')
            ->where('i.company_id', $company_id);

        if (!empty($search)) {
            $search = strtolower(trim($search));
            $searchNoSpace = str_replace(' ', '', $search);
            $searchWithUnderscore = str_replace(' ', '_', $search);

            $builder->groupStart()
                //  Customer name search (ignore spaces)
                ->where("REPLACE(LOWER(c.name),' ','') LIKE '%{$searchNoSpace}%'", null, false)
                //  Status search
                ->orWhere("LOWER(i.status) LIKE '%{$search}%'", null, false)
                //  Payment mode search (matches: bank link, bank_link, banklink)
                ->orWhere("LOWER(i.payment_mode) LIKE '%{$searchWithUnderscore}%'", null, false)
                ->orWhere("REPLACE(LOWER(i.payment_mode),'_','') LIKE '%{$searchNoSpace}%'", null, false)
            ->groupEnd();
        }

        return $builder->orderBy($orderColumn, $orderDir)
                       ->limit($length, $start)
                       ->get()
                       ->getResultArray();
    }

    public function getAllCashReceiptsCount($company_id)
    {
        return $this->db->table('invoices')
            ->where('company_id', $company_id)
            ->countAllResults();
    }

    public function getFilteredCashReceiptsCount($company_id, $search = '')
    {
        $builder = $this->db->table('invoices i')
            ->join('customers c', 'c.customer_id = i.customer_id', 'left')
            ->where('i.status !=', 'unpaid')
            ->where('i.company_id', $company_id);

        if (!empty($search)) {
            $search = strtolower(trim($search));
            $searchNoSpace = str_replace(' ', '', $search);
            $searchWithUnderscore = str_replace(' ', '_', $search);

            $builder->groupStart()
                ->where("REPLACE(LOWER(c.name),' ','') LIKE '%{$searchNoSpace}%'", null, false)
                ->orWhere("LOWER(i.status) LIKE '%{$search}%'", null, false)
                ->orWhere("LOWER(i.payment_mode) LIKE '%{$searchWithUnderscore}%'", null, false)
                ->orWhere("REPLACE(LOWER(i.payment_mode),'_','') LIKE '%{$searchNoSpace}%'", null, false)
            ->groupEnd();
        }

        return (object)['filReceipts' => $builder->countAllResults()];
    }
}
