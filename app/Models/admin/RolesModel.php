<?php
namespace App\Models\admin;

use CodeIgniter\Model;

class RolesModel extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'role_Id';
    protected $allowedFields = ['role_Name', 'role_Status', 'role_Createdon', 'role_Createdby', 'role_Modifyon', 'role_Modifyby'];

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
	
	

// public function updateRoles($roleId, $data)
// {
//     $this->db->table('roles')
//         ->where('role_Id', $roleId)
//         ->update($data);

//     $roles = $this->db->table('roles')
//      //   ->select('cat_Discount_Value, cat_Discount_Type')
//         ->where('role_Id', $roleId)
// 		->where('role_Status', 1)
//         ->get()
//         ->getRow();

//     if (!$roles) {
//         return false;
//     }

// }

	// delete category
	
		// public function deleteRolesById($role_id, $modified_by)
		// {

			
		// 		return $this->db->table('roles')
		// 			->where('role_Id', $role_id)
		// 			->update([
		// 				'role_Status'   => 3,
		// 				'role_Modifyon' => date('Y-m-d H:i:s'),
		// 				'role_Modifyby' => $modified_by
		// 			]);
		
		// }
	
		
	//**************************Data table */
				
// 	public function getAllFilteredRecords($condition, $start, $length, $orderBy, $orderDir)
//     {
//         return $this->db->table($this->table)
//             ->where($condition)
//             ->orderBy($orderBy, $orderDir)
//             ->limit($length, $start)
//             ->get()
//             ->getResult(); 
//     }
//     public function getAllCount()
//     {
//         return $this->db->table($this->table)->countAll();
//     }
//     public function getFilterCount($condition)
//     {
//         return $this->db->table($this->table)
//             ->select('COUNT(*) as filRecords')
//             ->where($condition)
//             ->get()
//             ->getRow(); 
//     }
// }

    

?>