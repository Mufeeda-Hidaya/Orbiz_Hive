<?php
namespace App\Controllers;

use App\Models\Login_Model;
use App\Models\Rolemanagement_Model;
use App\Models\RoleModel;
use App\Controllers\BaseController;

class Login extends BaseController
{
    public function __construct()
    {
        $this->session = \Config\Services::session();
    }

    public function index()
    {
        $uri = service('uri');
        $isAdminLogin = ($uri->getSegment(1) === 'admin');

        $data['isAdminLogin'] = $isAdminLogin;

        return view('login', $data);
    }

public function authenticate()
{
    $email     = $this->request->getPost('email');
    $password  = $this->request->getPost('password');
    $loginMode = $this->request->getPost('login_mode');

    if (!$email || !$password) {
        return $this->response->setJSON(['status' => 0, 'message' => 'Email And Password Are Required']);
    }

    $loginModel = new \App\Models\Login_Model();
    $result = $loginModel->authenticateNow($email, $password);

    if (!$result) {
        return $this->response->setJSON(['status' => 0, 'message' => 'Invalid Credentials']);
    }

    $roleModel = new \App\Models\RoleModel();
    $roleData  = $roleModel->find($result->role_id);

    if (!$roleData) {
        return $this->response->setJSON(['status' => 0, 'message' => 'Role not found']);
    }

    $roleName = $roleData['role_name'] ?? '';

    // Decode role permissions (IDs)
    $menuIds = [];
    if (!empty($roleData['role_permissions'])) {
        $decoded = json_decode($roleData['role_permissions'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $decoded = explode(',', $roleData['role_permissions']);
        }
        $menuIds = array_map('intval', $decoded);
    }

    // Map menu names to sidebar keys
    
$menuNameToKey = [
    'Dashboard'  => 'dashboard',
    'Enquiry'    => 'supplier',
    'Quotation'  => 'estimatelist',
    'Job Order'  => 'invoices',
    'Delivery'   => 'delivery',
    'Users'      => 'adduserlist',
    'Customer'   => 'customer'
];



    // Fetch allowed menus from role_menus
    $allowedMenus = [];
    if (!empty($menuIds)) {
        $menuModel = new \App\Models\Rolemanagement_Model();
        $menuRecords = $menuModel
            ->whereIn('rolemenu_id', $menuIds)
            ->where('status', 1)
            ->findAll();

        foreach ($menuRecords as $menu) {
            $name = $menu['menu_name'];
            if (isset($menuNameToKey[$name])) {
                $allowedMenus[] = $menuNameToKey[$name];
            }
        }
    }

    // Set session
    $this->session->set([
        'user_id'       => $result->user_id,
        'user_Name'     => $result->name,
        'role_Id'       => $result->role_id,
        'role_Name'     => $roleName,
        'allowed_menus' => $allowedMenus,
        'status'        => 1,
        'logged_in'     => true,
        'user_status'   => $result->user_status
    ]);

    return $this->response->setJSON([
        'status'  => 1,
        'user_Id' => $result->user_id
    ]);
}



}