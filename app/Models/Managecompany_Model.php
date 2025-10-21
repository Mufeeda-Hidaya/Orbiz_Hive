<?php

namespace App\Models;

use CodeIgniter\Model;

class Managecompany_Model extends Model
{
    protected $table = 'company'; // DB table name
    protected $primaryKey = 'company_id'; // primary key
    protected $allowedFields = ['company_name', 'company_name_ar','address', 'address_ar','billing_address','tax_number', 'company_logo', 'email', 'phone','company_status']; // fields allowed for insert/update

    
    /**
     * Get total count of active companies (company_status = 1) 
     * @return object Contains property 'totcompanies'
     */
    public function getAllCompanyCount()
{
    return $this->db
        ->table($this->table)
        ->where('company_status', 1) // only active companies
        ->selectCount('*', 'totcompanies')  // count all records 
        ->get()
        ->getRow(); // return single row object
}    

/**
     * Get total count of filtered companies based on search
     * @param string $search Search string
     * @return object Contains property 'filCompanies'
     */
      public function getFilteredCompanyCount($search = '')
{
    $builder = $this->db->table($this->table);
    $builder->where('company_status', 1);

    if ($search) {
        $search = strtolower(trim($search));
        $search = str_replace(' ', '', $search);

        $builder->groupStart()
            ->like("REPLACE(LOWER(company_name), ' ', '')", $search)
            ->orLike("REPLACE(LOWER(address), ' ', '')", $search)
            ->orLike("REPLACE(LOWER(tax_number), ' ', '')", $search)
            ->orLike("REPLACE(LOWER(email), ' ', '')", $search)
            ->orLike("REPLACE(LOWER(phone), ' ', '')", $search)
            ->groupEnd();
    }

    $count = $builder->countAllResults();

    return (object)['filCompanies' => $count];
}
    
    public function getAllFilteredRecords($search = '', $fromstart = 0, $tolimit = 10, $orderColumn = 'company_id', $orderDir = 'DESC')
    {
        $allowedColumns = ['company_id', 'company_name', 'address', 'tax_number', 'email', 'phone', 'company_logo'];
        if (!in_array($orderColumn, $allowedColumns)) {
            $orderColumn = 'company_id';
        }

        $orderDir = strtoupper($orderDir) === 'ASC' ? 'ASC' : 'DESC';

        $builder = $this->db->table($this->table);
        $builder->where('company_status', 1);

        if ($search) {
            $search = strtolower(trim($search));
            $search = str_replace(' ', '', $search);

            $builder->groupStart()
                ->like("REPLACE(LOWER(company_name), ' ', '')", $search)
                ->orLike("REPLACE(LOWER(address), ' ', '')", $search)
                ->orLike("REPLACE(LOWER(tax_number), ' ', '')", $search)
                ->orLike("REPLACE(LOWER(email), ' ', '')", $search)
                ->orLike("REPLACE(LOWER(phone), ' ', '')", $search)
                ->groupEnd();
        }

        $builder->orderBy($orderColumn, $orderDir)
                ->limit($tolimit, $fromstart);

        return $builder->get()->getResultArray();
    }
    public function getActiveCompanies()
{
    return $this->where('company_status', 1)->findAll();
}

}
