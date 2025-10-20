<?php
namespace App\Controllers\admin;

use App\Controllers\BaseController;
use App\Models\admin\RolesModel;
use App\Models\admin\RoleMenuModel;

class Roles extends BaseController
{
    public function __construct()
    {
        $this->rolesModel = new RolesModel();
        $this->roleMenuModel = new RoleMenuModel();
        $this->session = \Config\Services::session();
        $this->input = \Config\Services::request();

        if (!$this->session->has('user_id')) {
            header('Location: ' . base_url('admin'));
            exit();
        }
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
    public function edit($id)
    {
        $role = $this->rolesModel->find($id);
        $permissions = $this->roleMenuModel->where('role_id', $id)->findAll();
        $access = [];

        foreach ($permissions as $perm) {
            $access[$perm['menu_name']] = $perm['access'];
        }

        $data = [
            'role' => $role,
            'access' => $access,
        ];

        $template = view('admin/common/header');
        $template .= view('admin/common/left_menu');
        $template .= view('admin/add_role', $data);
        $template .= view('admin/common/footer');
        $template .= view('admin/page_scripts/rolesjs');

        return $template;
    }

public function deleteRole()
{
    $role_id = $this->request->getPost('role_id');

    if (!$role_id) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Role ID is required.'
        ]);
    }

    $this->rolesModel->update($role_id, [
        'status'     => 9, 
        'updated_at' => date("Y-m-d H:i:s")
    ]);

    return $this->response->setJSON([
        'success'  => true,
        'message' => 'Role deleted successfully.'
    ]);
}


// table list
    public function roleListAjax()
{
    $draw = intval($this->request->getPost('draw') ?? 1);
    $start = intval($this->request->getPost('start') ?? 0);
    $length = intval($this->request->getPost('length') ?? 10);
    $searchValue = $this->request->getPost('search')['value'] ?? '';

    $condition = "status != 9"; // exclude deleted rows
    if (!empty($searchValue)) {
        $searchValue = trim(preg_replace('/\s+/', ' ', $searchValue));
        $noSpaceSearch = str_replace(' ', '', strtolower($searchValue));
        $condition .= " AND REPLACE(LOWER(role_name), ' ', '') LIKE '%" .
            $this->rolesModel->db->escapeLikeString($noSpaceSearch) . "%'";
    }

    $columns = ['role_name', 'status', 'role_id'];
    $orderColumnIndex = intval($this->request->getPost('order')[0]['column'] ?? 0);
    $orderDir = $this->request->getPost('order')[0]['dir'] ?? 'desc';
    $orderBy = $columns[$orderColumnIndex] ?? 'role_id';

    $records = $this->rolesModel->getAllFilteredRecords($condition, $start, $length, $orderBy, $orderDir);

    $data = [];
    $slno = $start + 1;

    foreach ($records as $row) {
        $rowData = [];
        $rowData['slno'] = $slno++;
        $rowData['role_name'] = !empty($row->role_name) ? ucfirst($row->role_name) : 'N/A';

        $rowData['status_switch'] = '<span class="badge badge-sm ' 
            . ($row->status == 1 ? 'bg-gradient-success' : 'bg-gradient-secondary') 
            . ' status-toggle" data-id="' . $row->role_id . '" style="cursor:pointer">'
            . ($row->status == 1 ? 'Active' : 'Inactive') 
            . '</span>';

        $rowData['actions'] = '
            <div class="text-start">
                <a href="' . base_url('admin/roles/edit/' . $row->role_id) . '" title="Edit" style="margin-right:5px;">
                    <i class="bi bi-pencil-square"></i>
                </a>
                <i class="bi bi-trash text-danger icon-clickable" onclick="confirmDelete(' . $row->role_id . ')"></i>
            </div>';

        $rowData['role_id'] = $row->role_id;
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

 public function store()
    {
        $request   = $this->request;
        $role_id   = $request->getPost('role_id');
        $role_name = trim($request->getPost('role_name'));
        $menus     = $request->getPost('menus') ?? [];

        if (empty($role_name)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Role Name Is Required'
            ]);
        }
        $normalized_role_name = strtolower(str_replace(' ', '', $role_name));

        $duplicate = $this->rolesModel
            ->where("REPLACE(LOWER(TRIM(role_name)), ' ', '') =", $normalized_role_name)
            ->where('role_id !=', $role_id)
            ->first();

        if ($duplicate) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Role Already Exists.'
            ]);
        }
        if (!empty($role_id)) {
            $existingRole = $this->rolesModel->find($role_id);

            if (!$existingRole) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Role Not Found'
                ]);
            }

            $oldRoleName = $existingRole['role_name'] ?? '';
            $oldMenus    = $this->roleMenuModel->where('role_id', $role_id)->findColumn('menu_name') ?? [];

            sort($oldMenus);
            $newMenus = $menus;
            sort($newMenus);

            $nameChanged  = ($oldRoleName !== $role_name);
            $menusChanged = ($oldMenus !== $newMenus);

            if (!$nameChanged && !$menusChanged) {
                return $this->response->setJSON([
                    'status'  => 'info',
                    'message' => 'No Changes Made.'
                ]);
            }
            if ($nameChanged) {
                $this->rolesModel->update($role_id, ['role_name' => $role_name]);
            }
            if ($menusChanged) {
                $this->roleMenuModel->where('role_id', $role_id)->delete();
                if (!empty($menus)) {
                    $data = [];
                    foreach ($menus as $menuName) {
                        $data[] = [
                            'role_id'   => $role_id,
                            'menu_name' => $menuName,
                            'access'    => 1
                        ];
                    }
                    $this->roleMenuModel->insertBatch($data);
                }
            }
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => $nameChanged && $menusChanged
                    ? 'Role and permissions updated successfully!'
                    : ($nameChanged ? 'Role Updated Successfully!' : 'Permissions Updated Successfully!')
            ]);
        }
        $role_id = $this->rolesModel->insert([
            'role_name' => $role_name,
            'status'    => 1
        ], true); 
        if (!empty($menus)) {
            $data = [];
            foreach ($menus as $menuName) {
                $data[] = [
                    'role_id'   => $role_id,
                    'menu_name' => $menuName,
                    'access'    => 1
                ];
            }
            $this->roleMenuModel->insertBatch($data);
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Role Added Successfully!'
        ]);
    }




   public function changeStatus()
{
    $roleId = $this->request->getPost('role_id');
    $newStatus = $this->request->getPost('status');

    if (!$roleId || !$newStatus) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Invalid request'
        ]);
    }

    $role = $this->rolesModel->getByid($roleId);

    if (!$role) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Role not found'
        ]);
    }

    $update = $this->rolesModel->updateRolesStatus($roleId, ['status' => $newStatus]);

    if ($update) {
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Role Status Updated Successfully!',
            'new_status' => $newStatus
        ]);
    } else {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to update Role Status!',
            'new_status' => $newStatus
        ]);
    }
}




}
    
	

		
	




