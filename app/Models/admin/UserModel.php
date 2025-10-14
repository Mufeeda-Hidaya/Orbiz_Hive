<?php

namespace App\Models\admin;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'user';
    protected $primaryKey = 'user_id';
    protected $allowedFields = ['user_name', 'email', 'phone', 'password', 'status', 'created_at', 'created_by', 'updated_at', 'updated_by'];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function userInsert($data) {
        return $this->db->table($this->table)->insert($data);
    }
 
    public function updateUser($user_id, $data) {
        return $this->db->table($this->table)
                        ->where('user_id', $user_id)
                        ->update($data);
    }
 
    public function getAllRoles()
    {
        return $this->db->table('roles')
                        ->select('role_id, role_name')
                        ->where('status', 1)
                        ->get()
                        ->getResult();
    }

    public function getAllFilteredRecords($searchVal = '', $start = 0, $length = 10, $orderBy = 'u.user_id', $orderDir = 'desc')
{
    $builder = $this->db->table('user u')
        ->select('u.user_id, u.user_name, u.email, u.status, r.role_Name')
        ->join('roles r', 'r.role_Id = u.role_Id', 'left')
        ->where('u.status !=', 9);

    if (!empty($searchVal)) {
        $searchVal = trim(preg_replace('/\s+/', ' ', $searchVal));
        $noSpaceSearch = str_replace(' ', '', strtolower($searchVal));
        $escaped = $this->db->escapeLikeString($noSpaceSearch);
        $builder->where("( 
            REPLACE(LOWER(u.user_name), ' ', '') LIKE '%{$escaped}%' 
            OR REPLACE(LOWER(u.email), ' ', '') LIKE '%{$escaped}%' 
            OR REPLACE(LOWER(r.role_Name), ' ', '') LIKE '%{$escaped}%'
        )", null, false);
    }

    $builder->orderBy($orderBy, $orderDir)
            ->limit($length, $start);

    return $builder->get()->getResult();
}


    public function getAllUserCount()
    {
        return $this->db->table('user')
            ->where('status !=', 9)
            ->countAllResults();
    }

    public function getFilterUserCount($searchVal = '')
    {
        $builder = $this->db->table('user u')
            ->select('COUNT(*) as filRecords')
            ->join('roles r', 'r.role_Id = u.role_Id', 'left')
            ->where('u.status !=', 9);

        if (!empty($searchVal)) {
            $builder->groupStart()
                ->like('LOWER(u.user_name)', strtolower($searchVal))
                ->orLike('LOWER(u.email)', strtolower($searchVal))
                ->orLike('LOWER(r.role_Name)', strtolower($searchVal))
                ->groupEnd();
        }

        $row = $builder->get()->getRow();
        return $row ? $row->filRecords : 0;
    }
        public function update($user_Id, $data)
    {
        $builder = $this->db->table('user');
        $builder->where('role_id', $user_Id); 
        $builder->update($data);

        return $this->db->affectedRows() > 0;
    }

    public function getByid($user_Id)
    {
        return $this->db->table('user')
            ->where('role_id', $user_Id)
            ->get()
            ->getRow();
    }

}
