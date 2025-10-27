<?php
namespace App\Models;

use CodeIgniter\Model;

class SupplierModel extends Model
{
    protected $table      = 'enquiries';
    protected $primaryKey = 'enquiry_id';
    protected $allowedFields = [
        'enquiry_no','customer_id','address','phone','name','user_id',
    'created_by','created_at','company_id','is_deleted', 'is_converted','updated_by','updated_at'
    ];
    protected $returnType = 'array';
    // protected $defaultCompanyId = 1; 

    public function getFilteredSupplierCount($search = '')
    {
        $builder = $this->db->table($this->table)
            ->where('is_deleted', 0);

        if (!empty($search)) {
            $normalizedSearch = str_replace(' ', '', strtolower($search));
            $builder->groupStart()
                ->like('name', $search)
                ->orLike('address', $search)
                ->orLike('enquiry_id', $search)
                ->orWhere("REPLACE(REPLACE(REPLACE(LOWER(name), ' ', ''), '\n', ''), '\r', '') LIKE '%{$normalizedSearch}%'", null, false)
                ->orWhere("REPLACE(REPLACE(REPLACE(LOWER(address), ' ', ''), '\n', ''), '\r', '') LIKE '%{$normalizedSearch}%'", null, false)
                ->groupEnd();
        }

        return $builder->countAllResults();
    }

    // Fetch filtered records for DataTables
    public function getAllFilteredRecords($search = '', $fromstart = 0, $tolimit = 10, $orderColumn = 'enquiry_id', $orderDir = 'DESC')
    {
        $allowedColumns = ['enquiry_id', 'name', 'address'];
        if (!in_array($orderColumn, $allowedColumns)) {
            $orderColumn = 'enquiry_id';
        }
        $orderDir = strtoupper($orderDir) === 'ASC' ? 'ASC' : 'DESC';

        $builder = $this->db->table($this->table)
            ->where('is_deleted', 0);

        if (!empty($search)) {
            $normalizedSearch = str_replace(' ', '', strtolower($search));
            $builder->groupStart()
                ->like('name', $search)
                ->orLike('address', $search)
                ->orLike('enquiry_id', $search)
                ->orWhere("REPLACE(REPLACE(REPLACE(LOWER(name), ' ', ''), '\n', ''), '\r', '') LIKE '%{$normalizedSearch}%'", null, false)
                ->orWhere("REPLACE(REPLACE(REPLACE(LOWER(address), ' ', ''), '\n', ''), '\r', '') LIKE '%{$normalizedSearch}%'", null, false)
                ->groupEnd();
        }

        $builder->orderBy($orderColumn, $orderDir)
                ->limit($tolimit, $fromstart);

        return $builder->get()->getResultArray();
    }
}