<?php
namespace App\Models;

use CodeIgniter\Model;

class SupplierModel extends Model
{
    protected $table      = 'enquiries';
    protected $primaryKey = 'enquiry_id';
    protected $allowedFields = [
        'enquiry_no','customer_id','address','phone','name','user_id',
    'created_by','created_at','company_id','is_deleted','updated_by','updated_at'
    ];
    protected $returnType = 'array';
    // protected $defaultCompanyId = 1; 

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
                ->like('enquiries.name', $search)
                ->orLike('enquiries.address', $search)
                ->orLike('enquiries.enquiry_id', $search)
                ->orWhere("REPLACE(REPLACE(REPLACE(LOWER(enquiries.name), ' ', ''), '\n', ''), '\r', '') LIKE '%{$normalizedSearch}%'", null, false)
                ->orWhere("REPLACE(REPLACE(REPLACE(LOWER(enquiries.address), ' ', ''), '\n', ''), '\r', '') LIKE '%{$normalizedSearch}%'", null, false)
                ->groupEnd();
        }

        $count = $builder->countAllResults();
        return (object)['countSuppliers' => $count];
    }

    // Fetch filtered suppliers for DataTables
    public function getAllFilteredRecords($search = '', $fromstart = 0, $tolimit = 10, $orderColumn = 'enquiry_id', $orderDir = 'DESC', $company_id = null)
    {
        $search = trim($search);
        $allowedColumns = ['enquiry_id', 'name', 'address'];
        if (!in_array($orderColumn, $allowedColumns)) {
            $orderColumn = 'enquiry_id';
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
                ->like('enquiries.name', $search)
                ->orLike('enquiries.address', $search)
                ->orLike('enquiries.enquiry_id', $search)
                ->orWhere("REPLACE(REPLACE(REPLACE(LOWER(enquiries.name), ' ', ''), '\n', ''), '\r', '') LIKE '%{$normalizedSearch}%'", null, false)
                ->orWhere("REPLACE(REPLACE(REPLACE(LOWER(enquiries.address), ' ', ''), '\n', ''), '\r', '') LIKE '%{$normalizedSearch}%'", null, false)
                ->groupEnd();
        }

        $builder->orderBy($orderColumn, $orderDir)
                ->limit($tolimit, $fromstart);

        return $builder->get()->getResultArray();
    }
}