<?php
namespace App\Models;

use CodeIgniter\Model;

class SupplierModel extends Model
{
    protected $table      = 'suppliers';
    protected $primaryKey = 'supplier_id';
    protected $allowedFields = ['name', 'address', 'company_id', 'is_deleted'];
    protected $returnType = 'array';

    // Total suppliers (for a company)
    public function getAllSupplierCount($company_id = null)
    {
        $builder = $this->db->table($this->table)
            ->where('is_deleted', 0);

        if ($company_id) {
            $builder->where('company_id', $company_id);
        }

        $count = $builder->countAllResults();
        return (object)['totSuppliers' => $count];
    }

    // Filtered count for DataTables
    public function getFilteredSupplierCount($search = '', $company_id = null)
    {
        $search = trim($search);
        $builder = $this->db->table($this->table)
            ->where('is_deleted', 0);

        if ($company_id) {
            $builder->where('company_id', $company_id);
        }

        if (!empty($search)) {
            $normalizedSearch = str_replace(' ', '', strtolower($search));

            $builder->groupStart()
                ->like('suppliers.name', $search)
                ->orLike('suppliers.address', $search)
                ->orLike('suppliers.supplier_id', $search)
                ->orWhere("REPLACE(REPLACE(REPLACE(LOWER(suppliers.name), ' ', ''), '\n', ''), '\r', '') LIKE '%{$normalizedSearch}%'", null, false)
                ->orWhere("REPLACE(REPLACE(REPLACE(LOWER(suppliers.address), ' ', ''), '\n', ''), '\r', '') LIKE '%{$normalizedSearch}%'", null, false)
                ->groupEnd();
        }

        $count = $builder->countAllResults();
        return (object)['countSuppliers' => $count];
    }

    // Fetch filtered suppliers for DataTables
    public function getAllFilteredRecords($search = '', $fromstart = 0, $tolimit = 10, $orderColumn = 'supplier_id', $orderDir = 'DESC', $company_id = null)
    {
        $search = trim($search);
        $allowedColumns = ['supplier_id', 'name', 'address'];
        if (!in_array($orderColumn, $allowedColumns)) {
            $orderColumn = 'supplier_id';
        }
        $orderDir = strtoupper($orderDir) === 'ASC' ? 'ASC' : 'DESC';

        $builder = $this->db->table($this->table)
            ->where('is_deleted', 0);

        if ($company_id) {
            $builder->where('company_id', $company_id);
        }

        if (!empty($search)) {
            $normalizedSearch = str_replace(' ', '', strtolower($search));

            $builder->groupStart()
                ->like('suppliers.name', $search)
                ->orLike('suppliers.address', $search)
                ->orLike('suppliers.supplier_id', $search)
                ->orWhere("REPLACE(REPLACE(REPLACE(LOWER(suppliers.name), ' ', ''), '\n', ''), '\r', '') LIKE '%{$normalizedSearch}%'", null, false)
                ->orWhere("REPLACE(REPLACE(REPLACE(LOWER(suppliers.address), ' ', ''), '\n', ''), '\r', '') LIKE '%{$normalizedSearch}%'", null, false)
                ->groupEnd();
        }

        $builder->orderBy($orderColumn, $orderDir)
                ->limit($tolimit, $fromstart);

        return $builder->get()->getResultArray();
    }
}
