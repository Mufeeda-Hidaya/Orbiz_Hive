<?php

namespace App\Models\admin;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'user';
    protected $primaryKey = 'user_id';
    protected $allowedFields = ['name','role_id','email', 'phone', 'password','address', 'status', 'created_at', 'created_by', 'updated_at', 'updated_by'];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getAllRoles()
    {
        return $this->db->table('roles')
            ->where('status', 1)
            ->get()
            ->getResult();
    }

    public function userInsert($data) {
        return $this->db->table($this->table)->insert($data);
    }
 
    public function updateUser($user_id, $data) {
        return $this->db->table($this->table)
                        ->where('user_id', $user_id)
                        ->update($data);
    }
    public function getAllFilteredRecords($searchVal = '', $start = 0, $length = 10, $orderBy = 'u.user_id', $orderDir = 'desc')
    {
        $builder = $this->db->table('user u')
            ->select('u.user_id, u.name, u.email, u.status, r.role_name')
            ->join('roles r', 'r.role_id = u.role_id', 'left')
            ->where('u.status !=', 9);

        if (!empty($searchVal)) {
            $searchVal = trim(preg_replace('/\s+/', ' ', $searchVal));
            $noSpaceSearch = str_replace(' ', '', strtolower($searchVal));
            $escaped = $this->db->escapeLikeString($noSpaceSearch);
            $builder->where("( 
                REPLACE(LOWER(u.name), ' ', '') LIKE '%{$escaped}%' 
                OR REPLACE(LOWER(u.email), ' ', '') LIKE '%{$escaped}%' 
                OR REPLACE(LOWER(r.role_name), ' ', '') LIKE '%{$escaped}%'
            )", null, false);
        }

        $builder->orderBy($orderBy, $orderDir)
                ->limit($length, $start);

        $users = $builder->get()->getResult();
        foreach ($users as $user) {
            if (!empty($user->role_name)) {
                $user->role_name = ucwords(strtolower($user->role_name));
            }
        }

        return $users;
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
            ->join('roles r', 'r.role_id = u.role_id', 'left')
            ->where('u.status !=', 9);

        if (!empty($searchVal)) {
            $builder->groupStart()
                ->like('LOWER(u.name)', strtolower($searchVal))
                ->orLike('LOWER(u.email)', strtolower($searchVal))
                ->orLike('LOWER(r.role_name)', strtolower($searchVal))
                ->groupEnd();
        }

        $row = $builder->get()->getRow();
        return $row ? $row->filRecords : 0;
    }

    
    public function getUserByid($userId)
    {
        return $this->db->table($this->table)
            ->where('user_id', $userId)
            ->get()
            ->getRow();
    }

    
    public function updateStatus($userId, $data)
    {
        $builder = $this->db->table($this->table);
        $builder->where('user_id', $userId);
        $builder->update($data);
        return $this->db->affectedRows() > 0;
    }

}
