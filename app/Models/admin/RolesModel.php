<?php 
namespace App\Models\Admin;

use CodeIgniter\Model;

class RolesModel extends Model {
	
        public function __construct() {
            $this->db = \Config\Database::connect();
        }
    
       
        public function getAllRoles() {
            return $this->db->query("SELECT * FROM roles WHERE role_Status <> 3")->getResultArray();
        }
         
    
public function isRolesExists($roleName, $excludeId = null) {
    $builder = $this->db->table('roles');
    $builder->where('role_Name', $roleName);
    $builder->where('role_Status !=', 3); // Ignore soft-deleted categories

    if ($excludeId) {
        $builder->where('role_Id !=', $excludeId);
    }

    return $builder->get()->getRow();
}
	public function getRolesByid($catId){

			return $this->db->query("select * from roles where role_Id = '".$roleId."'")->getRow();
    }
	 
 public function roleInsert($data) {
 	return $this->db->table('roles')->insert($data);
	 }
	
	

public function updateRoles($roleId, $data)
{
    $this->db->table('roles')
        ->where('role_Id', $roleId)
        ->update($data);

    $roles = $this->db->table('roles')
     //   ->select('cat_Discount_Value, cat_Discount_Type')
        ->where('role_Id', $roleId)
		->where('role_Status', 1)
        ->get()
        ->getRow();

    if (!$roles) {
        return false;
    }

}

	// delete category
	
		public function deleteRolesById($role_id, $modified_by)
		{

			
				return $this->db->table('roles')
					->where('role_Id', $role_id)
					->update([
						'role_Status'   => 3,
						'role_Modifyon' => date('Y-m-d H:i:s'),
						'role_Modifyby' => $modified_by
					]);
		
		}
	
		
	//**************************Data table */
				
	protected $table = 'roles';
    protected $primaryKey = 'role_Id';
    protected $allowedFields = ['role_Name' ,'role_Status']; // Adjust to your table

    // For DataTables
    public function getDatatables()
	{
		$builder = $this->db->table('roles c');
		
		// Select required fields including category and subcategory names
		$builder->select('c.*');
		
		// Only fetch rows of active staffs
		$builder->where('c.role_Status !=', 3);

		// Add search logic if required
		$postData = service('request')->getPost();
		if (!empty($postData['search']['value'])) {
			$builder->groupStart()
					->like('c.role_Name', $postData['search']['value'])
					->groupEnd();
		}

		// Add pagination (limit and offset)
		if (!empty($postData['length']) && $postData['length'] != -1) {
			$builder->limit($postData['length'], $postData['start']);
		}

		// Apply ordering if provided
		if (!empty($postData['order'])) {
			$columns = ['c.role_Id ', 'c.role_Name', 'c.role_Status'];
			$orderCol = $columns[$postData['order'][0]['column']];
			$orderDir = $postData['order'][0]['dir'];
			$builder->orderBy($orderCol, $orderDir);
		}

		// Execute the query and return the result
		return $builder->get()->getResultArray();
	}


	public function countAll()
	{
		return $this->db->table('roles')
			->where('role_Status !=', 3)
			->countAllResults();
	}

	public function countFiltered()
	{
		$builder = $this->db->table('roles c');

		// Only fetch rows where either staffs exists
		$builder->where('c.role_Status !=', 3);
	 
		$postData = service('request')->getPost();
		if (!empty($postData['search']['value'])) {
			$builder->groupStart()
					->like('c.role_Name', $postData['search']['value'])
					->groupEnd();
		}
		return $builder->countAllResults();
	}
    }

    

?>