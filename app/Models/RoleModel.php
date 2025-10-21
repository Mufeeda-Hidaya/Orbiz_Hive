<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table = 'role_acces';
    protected $primaryKey = 'role_id';
    protected $allowedFields = ['role_id','role_name', 'company_id', 'created_at', 'updated_at'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getAllRoleCount($company_id)
{
    return $this->db->table($this->table)
        ->where('company_id', $company_id)
        ->countAllResults();
}

   public function getAllFilteredRecords($condition, $fromstart, $tolimit, $orderBy = 'role_id', $orderDir = 'desc', $company_id)
{
    $condition = "company_id = {$company_id} AND " . $condition;
    return $this->db->query("SELECT * FROM role_acces WHERE $condition ORDER BY $orderBy $orderDir LIMIT $fromstart, $tolimit")->getResult();
}


    
public function getFilterRoleCount($condition, $company_id)
{
    $condition = "company_id = {$company_id} AND " . $condition;
    return $this->db->query("SELECT COUNT(*) AS filRecords FROM role_acces WHERE $condition")->getRow();
}
}
