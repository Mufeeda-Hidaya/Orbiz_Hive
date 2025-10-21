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
        $data['singleCompany'] = false;
        $data['singleCompanyId'] = null;

        if ($isAdminLogin) {
            $companyModel = new Managecompany_Model();
            $companies = $companyModel->where('company_status', 1)->findAll();

            // Automatically pick the one company if exists
            if (count($companies) === 1) {
                $data['singleCompany'] = true;
                $data['singleCompanyId'] = $companies[0]['company_id'];
            }

            $data['companies'] = $companies;
        } else {
            $data['companies'] = [];
        }

        return view('login', $data);
    }

    public function authenticate()
    {
        $email      = $this->request->getPost('email');
        $password   = $this->request->getPost('password');
        $companyId  = $this->request->getPost('company_id');
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

            // Auto-assign the single company if not passed from form
            if (empty($companyId)) {
                $companyModel = new Managecompany_Model();
                $company = $companyModel->where('company_status', 1)->first();
                $companyId = $company ? $company['company_id'] : null;
            }

            $result->company_id = $companyId;
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
            'company_id'    => $result->company_id,
            'user_status'   => $result->user_status
        ]);

        return $this->response->setJSON([
            'status'   => 1,
            'user_Id'  => $result->user_id
        ]);
    }
}
