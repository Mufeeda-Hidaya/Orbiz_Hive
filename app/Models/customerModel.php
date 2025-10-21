<?php
namespace App\Models;

use CodeIgniter\Model;

class customerModel extends Model
{
    protected $table = 'customers';
    protected $primaryKey = 'customer_id';
    protected $allowedFields = ['name', 'address','company_id','shipping_address','is_deleted', 'max_discount'];
    protected $returnType = 'array';

    public function getAllCustomerCount()
    {
        return $this->db->query("SELECT COUNT(*) AS totcustomers FROM {$this->table}")->getRow();
    }

    public function getFilteredCustomerCount($search = '', $company_id = null)
{
     $search = trim($search);
    $builder = $this->db->table($this->table);
    $builder->where('is_deleted', 0);

    if ($company_id) {
        $builder->where('company_id', $company_id);
    }

   if (!empty($search)) {
    $normalizedSearch = str_replace(' ', '', strtolower($search)); 

    $builder->groupStart()
        ->like('customers.name', $search)
        ->orLike('customers.address', $search)
        ->orLike('customers.customer_id', $search)
        ->orWhere("REPLACE(REPLACE(REPLACE(LOWER(customers.name), ' ', ''), '\n', ''), '\r', '') LIKE '%{$normalizedSearch}%'", null, false)
        ->orWhere("REPLACE(REPLACE(REPLACE(LOWER(customers.address), ' ', ''), '\n', ''), '\r', '') LIKE '%{$normalizedSearch}%'", null, false)
        ->groupEnd();
}

    $count = $builder->countAllResults();
    return (object)['filCustomers' => $count];
}

public function getAllFilteredRecords($search = '', $fromstart = 0, $tolimit = 10, $orderColumn = 'customer_id', $orderDir = 'DESC', $company_id = null)
{
    $search = trim($search);
    $allowedColumns = ['customer_id', 'name', 'address'];
    if (!in_array($orderColumn, $allowedColumns)) {
        $orderColumn = 'customer_id';
    }
    $orderDir = strtoupper($orderDir) === 'ASC' ? 'ASC' : 'DESC';

    $builder = $this->db->table($this->table);
    $builder->where('is_deleted', 0);

    if ($company_id) {
        $builder->where('company_id', $company_id);
    }

   if (!empty($search)) {
    $normalizedSearch = str_replace(' ', '', strtolower($search)); 

    $builder->groupStart()
        ->like('customers.name', $search)
        ->orLike('customers.address', $search)
        ->orLike('customers.customer_id', $search)
         ->orWhere("REPLACE(REPLACE(REPLACE(LOWER(customers.name), ' ', ''), '\n', ''), '\r', '') LIKE '%{$normalizedSearch}%'", null, false)
        ->orWhere("REPLACE(REPLACE(REPLACE(LOWER(customers.address), ' ', ''), '\n', ''), '\r', '') LIKE '%{$normalizedSearch}%'", null, false)
        ->groupEnd();
}

    $builder->orderBy($orderColumn, $orderDir)
            ->limit($tolimit, $fromstart);

    return $builder->get()->getResultArray();
}

}
