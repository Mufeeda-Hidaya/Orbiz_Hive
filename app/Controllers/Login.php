<?php
namespace App\Controllers;

use App\Models\Login_Model;
use App\Models\Rolemanagement_Model;
use App\Models\RoleModel;
use App\Models\Managecompany_Model;
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
        $email      = $this->request->getPost('email');
        $password   = $this->request->getPost('password');
        $loginMode  = $this->request->getPost('login_mode');

        if (!$email || !$password) {
            return $this->response->setJSON(['status' => 0, 'message' => 'Email And Password Are Required']);
        }

        $loginModel = new Login_Model();
        $result = $loginModel->authenticateNow($email, $password);

        if (is_array($result) && isset($result['status']) && $result['status'] == 0) {
            return $this->response->setJSON($result);
        }

        if (!$result) {
            return $this->response->setJSON(['status' => 0, 'message' => 'Invalid Credentials']);
        }

        $roleModel = new RoleModel();
        $role = $roleModel->find($result->role_id);
        $roleName = $role ? $role['role_name'] : '';

        $roleMenuModel = new Rolemanagement_Model();
        $permissions = $roleMenuModel
            ->where('role_id', $result->role_id)
            ->where('access', 1)
            ->findAll();
        $allowedMenus = array_column($permissions, 'menu_name');

        if ($loginMode === 'admin_with_company') {
            // Admin login only
            if ($result->role_id != 1) {
                return $this->response->setJSON(['status' => 0, 'message' => 'Only Admins Can Log In Here']);
            }
        } else {
            // Normal user login
            if ($result->role_id == 1) {
                return $this->response->setJSON([
                    'status' => 0,
                    'message' => 'Click \'Login as Admin\' To Log In As An Administrator'
                ]);
            }
        }

        // Save session
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
            'status'   => 1,
            'user_Id'  => $result->user_id
        ]);
    }
}
