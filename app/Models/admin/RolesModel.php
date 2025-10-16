<?php
namespace App\Models\admin;

use CodeIgniter\Model;

class RolesModel extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'role_id';
    protected $allowedFields = ['role_id', 'role_name', 'status'];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getAllFilteredRecords($condition, $start, $length, $orderBy, $orderDir)
    {
        return $this->db->table($this->table)
            ->where($condition)
            ->orderBy($orderBy, $orderDir)
            ->limit($length, $start)
            ->get()
            ->getResult();
    }

    public function getAllCount()
    {
        return $this->db->table($this->table)->countAll();
    }

    public function getFilterCount($condition)
    {
        return $this->db->table($this->table)
            ->select('COUNT(*) as filRecords')
            ->where($condition)
            ->get()
            ->getRow();
    }

//  save and update
    public function isRoleExists($roleName, $roleId = null)
    {
        $builder = $this->db->table($this->table)->where('role_name', $roleName);
        if ($roleId) $builder->where('role_id !=', $roleId);
        return $builder->countAllResults() > 0;
    }

    public function insertRole($data)
    {
        $this->db->table($this->table)->insert($data);
        return $this->db->insertID();
    }

    public function updateRole($roleId, $data)
    {
        return $this->db->table($this->table)->where('role_id', $roleId)->update($data);
    }
	
// status change
    public function updateRolesStatus($roleId, $data)
    {
        $builder = $this->db->table('roles');
        $builder->where('role_id', $roleId); 
        $builder->update($data);
        return $this->db->affectedRows() > 0;
    }

    public function getByid($roleId)
    {
        return $this->db->table('roles')
            ->where('role_id', $roleId)
            ->get()
            ->getRow();
    }

public function getAllRoles()
    {
        return $this->db->table('roles')->get()->getResult();
    }


}
	
	

    

?>