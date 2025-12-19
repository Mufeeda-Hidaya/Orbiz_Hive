<?php

namespace App\Models;
use CodeIgniter\Model;

class CustomerModel extends Model
{
    protected $table = 'customers';
    protected $primaryKey = 'customer_id';
    protected $allowedFields = ['name', 'client_name', 'address', 'phone', 'company_id', 'shipping_address', 'is_deleted', 'max_discount'];

    // Count all customers
    public function getAllCustomerCount()
    {
        return $this->db->table($this->table)
            ->where('is_deleted', 0)
            ->countAllResults();
    }

    // Count filtered customers
    public function getFilteredCustomerCount($search = '', $company_id = null)
    {
        $builder = $this->db->table($this->table . ' AS customers');
        $builder->where('customers.is_deleted', 0);

        if ($company_id) {
            $builder->where('customers.company_id', $company_id);
        }

        if (!empty($search)) {
            $normalizedSearch = str_replace(' ', '', strtolower($search));
            $builder->groupStart()
                ->like('customers.name', $search)
                ->orLike('customers.client_name', $search)
                ->orLike('customers.address', $search)
                ->orLike('customers.phone', $search)
                ->orLike('customers.customer_id', $search)
                ->orWhere("REPLACE(REPLACE(REPLACE(LOWER(customers.name), ' ', ''), '\n', ''), '\r', '') LIKE '%{$normalizedSearch}%'", null, false)
                ->orWhere("REPLACE(REPLACE(REPLACE(LOWER(customers.client_name), ' ', ''), '\n', ''), '\r', '') LIKE '%{$normalizedSearch}%'", null, false)
                ->orWhere("REPLACE(REPLACE(REPLACE(LOWER(customers.address), ' ', ''), '\n', ''), '\r', '') LIKE '%{$normalizedSearch}%'", null, false)
                ->orWhere("REPLACE(REPLACE(REPLACE(LOWER(customers.phone), ' ', ''), '\n', ''), '\r', '') LIKE '%{$normalizedSearch}%'", null, false)
                ->groupEnd();
        }

        return (object) ['filCustomers' => $builder->countAllResults()];
    }

    // Get filtered records with pagination
    public function getAllFilteredRecords($search = '', $fromstart = 0, $tolimit = 10, $orderColumn = 'customer_id', $orderDir = 'DESC', $company_id = null)
    {
        $allowedColumns = ['customer_id', 'name', 'client_name', 'address', 'phone'];
        if (!in_array($orderColumn, $allowedColumns))
            $orderColumn = 'customer_id';
        $orderDir = strtoupper($orderDir) === 'ASC' ? 'ASC' : 'DESC';

        $builder = $this->db->table($this->table . ' AS customers');
        $builder->where('customers.is_deleted', 0);

        if ($company_id)
            $builder->where('customers.company_id', $company_id);

        if (!empty($search)) {
            $normalizedSearch = str_replace(' ', '', strtolower($search));
            $builder->groupStart()
                ->like('customers.name', $search)
                ->orLike('customers.client_name', $search)
                ->orLike('customers.address', $search)
                ->orLike('customers.phone', $search)
                ->orLike('customers.customer_id', $search)
                ->orWhere("REPLACE(REPLACE(REPLACE(LOWER(customers.name), ' ', ''), '\n', ''), '\r', '') LIKE '%{$normalizedSearch}%'", null, false)
                ->orWhere("REPLACE(REPLACE(REPLACE(LOWER(customers.client_name), ' ', ''), '\n', ''), '\r', '') LIKE '%{$normalizedSearch}%'",null,false)
                ->orWhere("REPLACE(REPLACE(REPLACE(LOWER(customers.address), ' ', ''), '\n', ''), '\r', '') LIKE '%{$normalizedSearch}%'", null, false)
                ->orWhere("REPLACE(REPLACE(REPLACE(LOWER(customers.phone), ' ', ''), '\n', ''), '\r', '') LIKE '%{$normalizedSearch}%'", null, false)
                ->groupEnd();
        }

        $builder->orderBy("customers.$orderColumn", $orderDir)
            ->limit($tolimit, $fromstart);

        return $builder->get()->getResultArray();
    }
}
