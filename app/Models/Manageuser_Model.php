<?php
namespace App\Models;
use CodeIgniter\Model;


class Manageuser_Model extends Model
{
    protected $table = 'user';
    protected $primaryKey = 'user_id';
    protected $allowedFields = ['name', 'email', 'phonenumber', 'password', 'role_id', 'company_id','user_status'];

    public function getAllUserCount()
    {
        $db = \Config\Database::connect();
        $session = \Config\Services::session();
        $companyId = $session->get('company_id');
        return $db->query("SELECT COUNT(*) as totuser FROM user  WHERE company_id = ?", [$companyId])->getRow();
    }
    public function getFilterUserCount($condition)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('user');
        $session = \Config\Services::session();
        $companyId = $session->get('company_id');

        $builder->select('COUNT(*) as totuser');
        $builder->join('role_acces', 'role_acces.role_id = user.role_id', 'left');

        // Apply existing condition
        if (!empty($condition)) {
            $builder->where($condition);
        }

        // Apply company filter
        $builder->where('user.company_id', $companyId);

        return $builder->get()->getRow();
    }

    public function getAllFilteredRecords($condition, $fromstart, $tolimit, $orderColumn, $orderDir, $roleId)
    {

        $builder = $this->db->table('user');

        $builder->select('user.*, role_acces.role_name');

        $builder->join('role_acces', 'role_acces.role_id = user.role_id', 'left');

        // Only apply condition if role is not Admin (1)

       $session = \Config\Services::session();
        $companyId = $session->get('company_id');

       if (!empty($companyId)) {
            $builder->where('user.company_id', $companyId);
        }
        if (!empty($condition) && $condition !== "1=1") {
            $builder->where($condition);
        }

        $builder->orderBy($orderColumn, $orderDir);

        $builder->limit($tolimit, $fromstart);

        return $builder->get()->getResult();

    }





}