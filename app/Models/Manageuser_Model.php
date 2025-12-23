<?php
namespace App\Models;
use CodeIgniter\Model;

class Manageuser_Model extends Model
{
    protected $table = 'user';
    protected $primaryKey = 'user_id';
    protected $allowedFields = ['name', 'email', 'phonenumber', 'password', 'role_id', 'jwt_token','status'];

    //  Custom soft delete implementation
    public function softDelete($user_id)
    {
        return $this->update($user_id, ['status' => 9]);
    }

    public function getAllUserCount($condition = "user.status != 9")
    {
        $db = \Config\Database::connect();
        $session = \Config\Services::session();
        // $companyId = $session->get('company_id');

        $query = "SELECT COUNT(*) as totuser FROM user WHERE $condition";
        // if (!empty($companyId)) {
        //     $query .= " AND company_id = " . (int)$companyId;
        // }

        return $db->query($query)->getRow();
    }

    public function getFilterUserCount($condition)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('user');
        $session = \Config\Services::session();
        // $companyId = $session->get('company_id');

        $builder->select('COUNT(*) as totuser');
        $builder->join('role_acces', 'role_acces.role_id = user.role_id', 'left');

        if (!empty($condition)) {
            $builder->where($condition);
        }

        // Always exclude deleted users
        $builder->where('user.status !=', 9);

        // if (!empty($companyId)) {
        //     $builder->where('user.company_id', $companyId);
        // }

        return $builder->get()->getRow();
    }

    public function getAllFilteredRecords($condition, $fromstart, $tolimit, $orderColumn, $orderDir, $roleId)
    {
        $builder = $this->db->table('user');
        $builder->select('user.*, role_acces.role_name');
        $builder->join('role_acces', 'role_acces.role_id = user.role_id', 'left');

        $session = \Config\Services::session();
        // $companyId = $session->get('company_id');

        $builder->where('user.status !=', 9);

        // if (!empty($companyId)) {
        //     $builder->where('user.company_id', $companyId);
        // }

        if (!empty($condition) && $condition !== "1=1") {
            $builder->where($condition);
        }

        $builder->orderBy($orderColumn, $orderDir);
        $builder->limit($tolimit, $fromstart);

        return $builder->get()->getResult();
    }
}
