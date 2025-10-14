<?php
namespace App\Models\admin;

use CodeIgniter\Model;

class RolesModel extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'role_id';
    protected $allowedFields = [
        'role_name',
        'status',
        'created_on',
        'created_by',
        'updated_on',
        'updated_by'
    ];
    protected $useTimestamps = false;
    protected $returnType = 'array';


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

 
// public function isRolesExists($roleName, $excludeId = null) {
//     $builder = $this->db->table('roles');
//     $builder->where('role_Name', $roleName);
//     $builder->where('role_Status !=', 3); // Ignore soft-deleted categories

//     if ($excludeId) {
//         $builder->where('role_Id !=', $excludeId);
//     }

//     return $builder->get()->getRow();
// }
// 	public function getRolesByid($roleId){

// 			return $this->db->query("select * from roles where role_Id = '".$roleId."'")->getRow();
//     }
	 
//  public function roleInsert($data) {
//  	return $this->db->table('roles')->insert($data);
// 	 }
	
	

public function updateRoles($roleId, $data)
{
    $builder = $this->db->table('roles');
    $builder->where('role_id', $roleId); // make sure column name matches DB
    $builder->update($data);

    // Return true if any rows affected
    return $this->db->affectedRows() > 0;
}

public function getRolesByid($roleId)
{
    return $this->db->table('roles')
        ->where('role_id', $roleId)
        ->get()
        ->getRow();
}



}
	
	

    

?>