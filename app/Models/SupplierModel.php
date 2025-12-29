<?php
namespace App\Models;

use CodeIgniter\Model;

class SupplierModel extends Model
{
    protected $table = 'enquiries';
    protected $primaryKey = 'enquiry_id';
    protected $allowedFields = [
        'enquiry_no',
        'customer_id',
        'address',
        'phone',
        'name',
        'user_id',
        'created_by',
        'created_at',
        'is_deleted',
        'is_converted',
        'updated_by',
        'updated_at'
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
                ->like('e.name', $search)
                ->orLike('e.address', $search)
                ->orLike('e.enquiry_id', $search)
                ->orLike('c.contact_person_name', $search) //search contact person
                ->orWhere("REPLACE(LOWER(e.name),' ','') LIKE '%{$normalizedSearch}%'", null, false)
                ->orWhere("REPLACE(LOWER(e.address),' ','') LIKE '%{$normalizedSearch}%'", null, false)
                ->orWhere("REPLACE(LOWER(c.contact_person_name),' ','') LIKE '%{$normalizedSearch}%'", null, false)
                ->groupEnd();
        }


        return $builder->countAllResults();
    }

    // Fetch filtered records for DataTables
    public function getAllFilteredRecords(
        $search = '',
        $fromstart = 0,
        $tolimit = 10,
        $orderColumn = 'enquiry_id',
        $orderDir = 'DESC'
    ) {
        $allowedColumns = [
            'enquiry_id',
            'name',
            'address',
            'contact_person_name'
        ];

        if (!in_array($orderColumn, $allowedColumns)) {
            $orderColumn = 'enquiry_id';
        }

        $orderDir = strtoupper($orderDir) === 'ASC' ? 'ASC' : 'DESC';

        $builder = $this->db->table($this->table . ' e')
            ->select('
            e.enquiry_id,
            e.name,
            e.address,
            e.is_converted,
            c.contact_person_name
        ')
            ->join('customers c', 'c.customer_id = e.customer_id', 'left')
            ->where('e.is_deleted', 0);

        if (!empty($search)) {
            $normalizedSearch = str_replace(' ', '', strtolower($search));

            $builder->groupStart()
                ->like('e.name', $search)
                ->orLike('e.address', $search)
                ->orLike('e.enquiry_id', $search)
                ->orLike('c.contact_person_name', $search) // ✅ search contact person
                ->orWhere("REPLACE(LOWER(e.name),' ','') LIKE '%{$normalizedSearch}%'", null, false)
                ->orWhere("REPLACE(LOWER(e.address),' ','') LIKE '%{$normalizedSearch}%'", null, false)
                ->orWhere("REPLACE(LOWER(c.contact_person_name),' ','') LIKE '%{$normalizedSearch}%'", null, false)
                ->groupEnd();
        }

        // Order handling (table alias fix)
        if ($orderColumn === 'contact_person_name') {
            $builder->orderBy('c.contact_person_name', $orderDir);
        } else {
            $builder->orderBy('e.' . $orderColumn, $orderDir);
        }

        $builder->limit($tolimit, $fromstart);

        return $builder->get()->getResultArray();
    }

}