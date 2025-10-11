<?php
namespace App\Controllers\admin;

use App\Controllers\BaseController;
use App\Models\admin\RolesModel;

class Roles extends BaseController
{
    public function __construct()
    {
        $this->rolesModel = new RolesModel();
    }

    public function index()
    {
        $template  = view('admin/common/header');
        $template .= view('admin/common/left_menu');
        $template .= view('admin/manage_roles'); 
        $template .= view('admin/common/footer');
        $template .= view('admin/page_scripts/rolesjs'); 
        return $template;
    }
    public function addRoles()
    {
        $template = view('admin/common/header');
        $template .= view('admin/common/left_menu');
        $template .= view('admin/add_role');
        $template .= view('admin/common/footer');
        $template .= view('admin/page_scripts/rolesjs');
        return $template;
    }

    public function roleListAjax()
    {
        $draw = intval($this->request->getPost('draw') ?? 1);
        $start = intval($this->request->getPost('start') ?? 0);
        $length = intval($this->request->getPost('length') ?? 10);
        $searchValue = $this->request->getPost('search')['value'] ?? '';

        $condition = "1=1";
        if (!empty($searchValue)) {
            $searchValue = trim(preg_replace('/\s+/', ' ', $searchValue));
            $noSpaceSearch = str_replace(' ', '', strtolower($searchValue));
            $condition .= " AND REPLACE(LOWER(role_Name), ' ', '') LIKE '%" .
                $this->rolesModel->db->escapeLikeString($noSpaceSearch) . "%'";
        }

        $columns = ['role_Name', 'role_Status', 'role_Id'];
        $orderColumnIndex = intval($this->request->getPost('order')[0]['column'] ?? 0);
        $orderDir = $this->request->getPost('order')[0]['dir'] ?? 'desc';
        $orderBy = $columns[$orderColumnIndex] ?? 'role_Id';

        $records = $this->rolesModel->getAllFilteredRecords($condition, $start, $length, $orderBy, $orderDir);

        $data = [];
        $slno = $start + 1;

        foreach ($records as $row) {
            $rowData = [];
            $rowData['slno'] = $slno++;
            $rowData['role_Name'] = !empty($row->role_Name) ? ucfirst($row->role_Name) : 'N/A';

            $rowData['status_switch'] = '<span class="badge badge-sm ' 
                . ($row->role_Status == 1 ? 'bg-gradient-success' : 'bg-gradient-secondary') 
                . ' status-toggle" data-id="' . $row->role_Id . '" style="cursor:pointer">'
                . ($row->role_Status == 1 ? 'Active' : 'Inactive') 
                . '</span>';

            $rowData['actions'] = '<a href="' . base_url('admin/roles/edit/' . $row->role_Id) . '">
                    <i class="bi bi-pencil-square" style="margin-right:5px;"></i>
                </a>
                <i class="bi bi-trash text-danger icon-clickable" onclick="confirmDelete(' . $row->role_Id . ')"></i>';

            $rowData['role_Id'] = $row->role_Id; 
            $data[] = $rowData;
        }

        $recordsTotal = $this->rolesModel->getAllCount();
        $recordsFilteredObj = $this->rolesModel->getFilterCount($condition);
        $recordsFiltered = $recordsFilteredObj->filRecords ?? 0;

        return $this->response->setJSON([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ]);
    }
}

    // public function addRoles($role_id = null)
	// {
	// 	if (!$this->session->get('ad_uid')) 
	// 	{
	// 		return redirect()->to(base_url('admin/roles'));
	// 	}

	// 	$data = [];
	// 	 if ($role_id) {
	// 		$cate = $this->rolesModel->getRolesByid($role_id);
		
	// 		if (!$cate) {
	// 			return redirect()->to('admin/roles')->with('error', 'Role not found');
	// 		}
			
	// 		 $data['roles'] = (array) $cate;
			
			
	//         $template  = view('admin/common/header');
    //      $template .= view('admin/common/left_menu');
    //     $template .= view('admin/manage_roles',$data);
    //      $template .= view('admin/common/footer');
    //     $template.= view('admin/page_scripts/rolesjs');
    //     return $template;
	// 	}
	// 	else
	// 	{
	// 	        $template  = view('admin/common/header');
    //      $template .= view('admin/common/left_menu');
    //     $template .= view('admin/manage_roles',$data);
    //      $template .= view('admin/common/footer');
    //     $template.= view('admin/page_scripts/rolesjs');
    //     return $template;
	// 	}
		
	// }

//     public function saveRoles() {
//     $role_id = $this->input->getPost('role_id');
//     $role_name = $this->input->getPost('role_name');

//     if ($role_name) {
//         // 🔍 Check if category name already exists
//         $exists = $this->rolesModel->isRolesExists($role_name, $role_id);
//         if ($exists) {
//             return $this->response->setJSON([
//                 'status' => 'error',
//                 'field' => 'role_Name',
//                 'message' => 'Role Already Exists.'
//             ]);
//         }

//         $data = [
//             'role_Name' => $role_name,
//             'role_Status' => 1,
//             'role_Createdon' => date("Y-m-d H:i:s"),
//             'role_Createdby' => $this->session->get('ad_uid'),
//             'role_Modifyby' => $this->session->get('ad_uid'),
//         ];

//         if (empty($role_id)) {
//             $CreateRoles = $this->rolesModel->roleInsert($data);
//             return $this->response->setJSON([
//                 "status" => 1,
//                 "msg" => "Roles Created Successfully.",
//                 "redirect" => base_url('roles')
//             ]);
//         } else {
//             $modifyRoles = $this->rolesModel->updateRoles($role_id, $data);
//             return $this->response->setJSON([
//                 "status" => 1,
//                 "msg" => "Roles Updated Successfully.",
//                 "redirect" => base_url('admin/roles')
//             ]);
//         }
//     } else {
//         return $this->response->setJSON([
//             'status' => 'error',
//             'message' => 'All fields are required.'
//         ]);
//     }
// }

    // public function changeStatus()
    // {
    //     $roleId = $this->request->getPost('role_Id');
    //     $newStatus = $this->request->getPost('role_Status');
        
    //   //  $categoryModel =  new \App\Models\Admin\CategoryModel();
    //  //   $category = $categoryModel->getCategoryByid($catId);
    //          $roles = $this->rolesModel->getRolesByid($roleId);

    //     //$productModel = new \App\Models\Admin\ProductModel();
	//     //$product = $productModel->getProductByid($catId);
        
    //     if (!$roles) {
    //         return $this->response->setJSON([
    //             'success' => false,
    //             'message' => 'Roles not found'
    //         ]);
    //     }
    
    //     $update = $this->rolesModel->updateRoles($roleId, ['role_Status' => $newStatus]);
    
    //     if ($update) {
    //         return $this->response->setJSON([
    //             'success' => true,
    //             'message' => 'Role Status Updated Successfully!',
    //             'new_status' => $newStatus
    //         ]);
    //     } else {
    //         return $this->response->setJSON([
    //             'success' => true,
    //             'message' => 'Role Status Updated Successfully!',
    //             'new_status' => $newStatus
    //         ]);
    //     }
    // }
    
    //Category Delete

	// public function deleteRoles($cat_id)
	// {
	// 	if ($role_id) {
	// 		$modified_by = $this->session->get('ad_uid');
	// 		$role_delete = $this->rolesModel->deleteRolesById( $role_id, $modified_by);
	// 		if ($role_delete) {
	// 			return $this->response->setJSON([
	// 				'status' => 1,
	// 				'message' => 'Roles deleted successfully.'
	// 			]);
	// 		} 
	// 	} else {
	// 		return $this->response->setJSON([
	// 			'status' => 0,
	// 			'message' => 'Invalid role ID.'
	// 		]);
	// 	}
	// }
     
	

		
	// Listing table data
	
// 	public function roleListAjax()
// {
//     $draw = intval($this->request->getPost('draw') ?? 1);
//     $start = intval($this->request->getPost('start') ?? 0);
//     $length = intval($this->request->getPost('length') ?? 10);
//     $searchValue = $this->request->getPost('search')['value'] ?? '';

//     $condition = "1=1";

//     if (!empty($searchValue)) {
//         $searchValue = trim(preg_replace('/\s+/', ' ', $searchValue));
//         $noSpaceSearch = str_replace(' ', '', strtolower($searchValue));
//         $condition .= " AND REPLACE(LOWER(role_Name), ' ', '') LIKE '%" .
//             $this->rolesModel->db->escapeLikeString($noSpaceSearch) . "%'";
//     }

//     $columns = ['role_Name', 'role_Status', 'role_Id'];
//     $orderColumnIndex = intval($this->request->getPost('order')[0]['column'] ?? 0);
//     $orderDir = $this->request->getPost('order')[0]['dir'] ?? 'desc';
//     $orderBy = $columns[$orderColumnIndex] ?? 'role_Id';

//     $records = $this->rolesModel->getAllFilteredRecords($condition, $start, $length, $orderBy, $orderDir);

//     $data = [];
//     $slno = $start + 1;

//     foreach ($records as $row) {
//         $rowData = [];
//         $rowData['slno'] = $slno++;
//         $rowData['role_Name'] = $row->role_Name ?? 'N/A';

//         // Status toggle
//         $rowData['status_switch'] = '<span class="badge badge-sm ' 
//             . ($row->role_Status == 1 ? 'bg-gradient-success' : 'bg-gradient-secondary') 
//             . ' status-toggle" data-id="' . $row->role_Id . '" style="cursor:pointer">'
//             . ($row->role_Status == 1 ? 'Active' : 'Inactive') 
//             . '</span>';

//         $rowData['actions'] = '<a href="' . base_url('admin/manage_roles/edit/' . $row->role_Id) . '">
//                 <i class="bi bi-pencil-square" style="margin-right:5px;"></i>
//             </a>
//             <i class="bi bi-trash text-danger icon-clickable" onclick="confirmDelete(' . $row->role_Id . ')"></i>';

//         $data[] = $rowData;
//     }

//     $recordsTotal = $this->rolesModel->getAllCount();
//     $recordsFilteredObj = $this->rolesModel->getFilterCount($condition);
//     $recordsFiltered = $recordsFilteredObj->filRecords ?? 0;

//     return $this->response->setJSON([
//         'draw' => $draw,
//         'recordsTotal' => $recordsTotal,
//         'recordsFiltered' => $recordsFiltered,
//         'data' => $data
//     ]);
// }




