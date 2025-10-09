<?php

namespace App\Controllers\admin;

use App\Controllers\BaseController;
use App\Models\Admin\RolesModel;

class Roles extends BaseController
{
    // public function index()
    // {
    //     $template  = view('admin/common/header');
    //     $template .= view('admin/common/left_menu');
    //     $template .= view('admin/roles_table');
    //     $template .= view('admin/common/footer');
    //     return $template;
    // }
//     public function index()
// {
//     return 'Dashboard working!';
// }




    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->input = \Config\Services::request();
        $this->rolesModel = new \App\Models\Admin\RolesModel();
    }

    public function index()
    {
         if (!$this->session->get('ad_uid')) {
				return redirect()->to(base_url('admin'));
			}

        $allroles = $this->rolesModel->getAllRoles();
        $data['roles'] =  $allroles;
        // print_r($data['category']);
        // exit;
        $template  = view('admin/common/header');
         $template .= view('admin/common/left_menu');
        $template .= view('admin/roles_table',$data);
         $template .= view('admin/common/footer');
        $template.= view('admin/page_scripts/rolesjs');
        return $template;

        
    }
    public function addRoles($role_id = null)
	{
		if (!$this->session->get('ad_uid')) 
		{
			return redirect()->to(base_url('admin/roles'));
		}

		$data = [];
		 if ($role_id) {
			$cate = $this->rolesModel->getRolesByid($role_id);
		
			if (!$cate) {
				return redirect()->to('admin/roles')->with('error', 'Role not found');
			}
			
			 $data['roles'] = (array) $cate;
			
			
	        $template  = view('admin/common/header');
         $template .= view('admin/common/left_menu');
        $template .= view('admin/roles_table',$data);
         $template .= view('admin/common/footer');
        $template.= view('admin/page_scripts/rolesjs');
        return $template;
		}
		else
		{
		        $template  = view('admin/common/header');
         $template .= view('admin/common/left_menu');
        $template .= view('admin/roles_table',$data);
         $template .= view('admin/common/footer');
        $template.= view('admin/page_scripts/rolesjs');
        return $template;
		}
		
	}

    public function saveRoles() {
    $role_id = $this->input->getPost('role_id');
    $role_name = $this->input->getPost('role_name');

    if ($role_name) {
        // 🔍 Check if category name already exists
        $exists = $this->rolesModel->isRolesExists($role_name, $role_id);
        if ($exists) {
            return $this->response->setJSON([
                'status' => 'error',
                'field' => 'role_Name',
                'message' => 'Role Already Exists.'
            ]);
        }

        $data = [
            'role_Name' => $role_name,
            'role_Status' => 1,
            'role_Createdon' => date("Y-m-d H:i:s"),
            'role_Createdby' => $this->session->get('ad_uid'),
            'role_Modifyby' => $this->session->get('ad_uid'),
        ];

        if (empty($role_id)) {
            $CreateRoles = $this->rolesModel->roleInsert($data);
            return $this->response->setJSON([
                "status" => 1,
                "msg" => "Roles Created Successfully.",
                "redirect" => base_url('roles')
            ]);
        } else {
            $modifyRoles = $this->rolesModel->updateRoles($role_id, $data);
            return $this->response->setJSON([
                "status" => 1,
                "msg" => "Roles Updated Successfully.",
                "redirect" => base_url('admin/roles')
            ]);
        }
    } else {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'All fields are required.'
        ]);
    }
}

    public function changeStatus()
    {
        $roleId = $this->request->getPost('role_Id');
        $newStatus = $this->request->getPost('role_Status');
        
      //  $categoryModel =  new \App\Models\Admin\CategoryModel();
     //   $category = $categoryModel->getCategoryByid($catId);
             $roles = $this->rolesModel->getRolesByid($roleId);

        //$productModel = new \App\Models\Admin\ProductModel();
	    //$product = $productModel->getProductByid($catId);
        
        if (!$roles) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Roles not found'
            ]);
        }
    
        $update = $this->rolesModel->updateRoles($roleId, ['role_Status' => $newStatus]);
    
        if ($update) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Role Status Updated Successfully!',
                'new_status' => $newStatus
            ]);
        } else {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Role Status Updated Successfully!',
                'new_status' => $newStatus
            ]);
        }
    }
    
    //Category Delete

	public function deleteRoles($cat_id)
	{
		if ($role_id) {
			$modified_by = $this->session->get('ad_uid');
			$role_delete = $this->rolesModel->deleteRolesById( $role_id, $modified_by);
			if ($role_delete) {
				return $this->response->setJSON([
					'status' => 1,
					'message' => 'Roles deleted successfully.'
				]);
			} 
		} else {
			return $this->response->setJSON([
				'status' => 0,
				'message' => 'Invalid role ID.'
			]);
		}
	}
     
	

		
	// Listing table data
	
	public function ajaxList()
	{
	$model = new \App\Models\Admin\RolesModel();
    	//		$role_delete = $this->rolesModel->deleteRolesById( $role_id, $modified_by);
	$data = $model->getDatatables();
	$total = $model->countAll();
	$filtered = $model->countFiltered();

	foreach ($data as &$row) {
		// Default fallbacks
		$row['role_Name'] = $row['role_Name'] ?? 'N/A';

		
		// Status toggle switch
	$row['status_switch'] = '
<td class="align-middle text-center text-sm">
    <span class="badge badge-sm ' . ($row['role_Status'] == 1 ? 'bg-gradient-success' : 'bg-gradient-secondary') . '">
        ' . ($row['role_Status'] == 1 ? 'Active' : 'Inactive') . '
    </span>
</td>';

		// Action buttons
		$row['actions'] = '<a href="' . base_url('admin/roles/edit/' . $row['role_Id']) . '">
				<i class="bi bi-pencil-square"></i>
			</a>&nbsp;
			<i class="bi bi-trash text-danger icon-clickable"
			   onclick="confirmDelete(' . $row['role_Id'] . ')"></i>';
	}
	
	return $this->response->setJSON([
		'draw' => intval($this->request->getPost('draw')),
		'recordsTotal' => $total,
		'recordsFiltered' => $filtered,
		'data' => $data
	]);
	}


}

