<?php
namespace App\Controllers\admin;
use App\Controllers\BaseController;
use App\Models\admin\UserModel;
use App\Models\admin\RolesModel;
 
class User extends BaseController
{
     
    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->input = \Config\Services::request();
        $this->userModel = new UserModel();
        $this->rolesModel = new RolesModel();

        if (!$this->session->has('user_id')) {
            header('Location: ' . base_url('admin'));
            exit();
        }
    }
    public function index()
    {
        
        $template = view('admin/common/header');
        $template.= view('admin/common/left_menu');
        $template.= view('admin/manage_user');
        $template.= view('admin/common/footer');
        $template.= view('admin/page_scripts/userjs');
        return $template;
   
    }
    public function addUser()
    {
        $data['roles'] = $this->userModel->getAllRoles();

        $template  = view('admin/common/header');
        $template .= view('admin/common/left_menu');
        $template .= view('admin/add_user', $data);
        $template .= view('admin/common/footer');
        $template .= view('admin/page_scripts/userjs');

        return $template;
    }
    public function edit($id)
    {
        $data['userData']  = $this->userModel->find($id);
        $data['roles'] = $this->userModel->getAllRoles();
    
        $template  = view('admin/common/header');
        $template .= view('admin/common/left_menu');
        $template .= view('admin/add_user', $data);  
        $template .= view('admin/common/footer');
        $template .= view('admin/page_scripts/userjs');
        return $template;
    }
   public function userListAjax()
{
    $draw      = $this->request->getPost('draw') ?? 1;
    $start     = $this->request->getPost('start') ?? 0;
    $length    = $this->request->getPost('length') ?? 10;
    $searchVal = $this->request->getPost('search')['value'] ?? '';

    $columns = [
        0 => 'u.user_id',
        1 => 'u.name',
        2 => 'u.email',
        3 => 'r.role_name',
        4 => 'u.status',
        5 => 'u.user_id'
    ];

    $orderColumnIndex = $this->request->getPost('order')[0]['column'] ?? 0;
    $orderDir = $this->request->getPost('order')[0]['dir'] ?? 'desc';
    $orderBy = $columns[$orderColumnIndex] ?? 'u.user_id';

    $users = $this->userModel->getAllFilteredRecords($searchVal, $start, $length, $orderBy, $orderDir);

    $result = [];
    $slno = $start + 1;

    foreach ($users as $user) {
        $statusBadge = '<span class="badge badge-sm ' 
            . ($user->status == 1 ? 'bg-gradient-success' : 'bg-gradient-secondary')
            . ' status-toggle" data-id="' . $user->user_id . '" style="cursor:pointer">'
            . ($user->status == 1 ? 'Active' : 'Inactive')
            . '</span>';

        $result[] = [
            'slno'          => $slno++,
            'name'          => $user->name,
            'email'         => $user->email,
            'role_name'     => $user->role_name ?? 'N/A',
            'status_switch' => $statusBadge,
            'user_id'       => $user->user_id
        ];
    }

    $totalCount    = $this->userModel->getAllUserCount();
    $filteredCount = $this->userModel->getFilterUserCount($searchVal);

    return $this->response->setJSON([
        "draw" => intval($draw),
        "recordsTotal" => intval($totalCount),
        "recordsFiltered" => intval($filteredCount),
        "data" => $result
    ]);
}
    public function saveUser() {
        $user_id  = $this->request->getPost('user_id');
        $name     = $this->request->getPost('name');
        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $phone = $this->request->getPost('phone');
        $new_password = $this->request->getPost('new_password');
        $confirm_password = $this->request->getPost('confirm_password');
        $role_id  = $this->request->getPost('role_id');

        if (empty($name) || empty($email) || empty($role_id)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'All Fields Are required.'
            ]);
        }

        if (!preg_match("/^[a-zA-Z0-9._%+-]+@gmail\.com$/", $email)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Please Enter A Valid Email Address.'
            ]);
        }
        $digitsOnly = preg_replace('/[^0-9]/', '', $phone);

        if (!empty($phone)) {
            if (!preg_match('/^[0-9 +]+$/', $phone)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Please enter a valid phone number (only digits, +, and space are allowed).'
                ]);
            }
            if (strlen(str_replace(' ', '', $phone)) < 6 || strlen(str_replace(' ', '', $phone)) > 15) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Phone number must be between 6 and 15 characters (including + and space).'
                ]);
            }
        }

        if (empty($user_id) && empty($password)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Password Is Required For Creating A New User.'
            ]);
        }
        $finalPassword = null;
        if (!empty($password) || !empty($new_password)) {
            $passToValidate = !empty($password) ? $password : $new_password;

            if (strlen($passToValidate) < 7) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Password Must Be At Least 7 Chracters Long.'
                ]);
            }
            if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $passToValidate)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Password Must Contain At Least One Special Character.'
                ]);
            }
            if (!empty($new_password) && $new_password !== $confirm_password) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'New Password And Confirm Password Do Not Match.'
                ]);
            }
            $finalPassword = password_hash($passToValidate, PASSWORD_DEFAULT);
        }
        $existingUser = $this->userModel
            ->where('email', $email)
            ->where('status !=', 9)
            ->first();

        if (empty($user_id)) {
            if ($existingUser) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Email Already Exists. Please Use Another Email.'
                ]);
            }
            $data = [
                'name'       => $name,
                'email'      => $email,
                'role_id'    => $role_id,
                'password'   => $finalPassword,
                'phone'      => $phone,
                'status'     => 1,
                'created_at' => date("Y-m-d H:i:s")
            ];
            $this->userModel->userInsert($data);
            return $this->response->setJSON([
                'success'  => true,
                'message'  => 'User Created Successfully.',
                'redirect' => base_url('admin/manage_user')
            ]);
        } 
        else {
            if ($existingUser && $existingUser['user_id'] != $user_id) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Email Already In Use By Another Account.'
                ]);
            }
            $data = [
                'name'       => $name,
                'email'      => $email,
                'role_id'    => $role_id,
                'phone'      => $phone,
                'updated_at' => date("Y-m-d H:i:s")
            ];
            if ($finalPassword) {
                $data['password'] = $finalPassword;
            }
            $this->userModel->updateUser($user_id, $data);
            return $this->response->setJSON([
                'success'  => true,
                'message'  => 'User Updated Successfully.',
                'redirect' => base_url('admin/manage_user')
            ]);
        }
    }

   public function deleteUser()
{
    $user_id = $this->request->getPost('user_id');

    if (!$user_id) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'User ID Is Required.'
        ]);
    }

    $this->userModel->update($user_id, [
        'status'     => 9,
        'updated_at' => date("Y-m-d H:i:s")
    ]);

    return $this->response->setJSON([
        'success'  => true,
        'message' => 'User Deleted Successfully.'
    ]);
}

public function changeStatus()
{
    $user_Id = $this->request->getPost('user_id');
    $newStatus = $this->request->getPost('status');

    if (!$user_Id || !$newStatus) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Invalid request'
        ]);
    }

    $users = $this->userModel->getUserByid($user_Id);

    if (!$users) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'User not found'
        ]);
    }

    $update = $this->userModel->updateStatus($user_Id, ['status' => $newStatus]);

    if ($update) {
        return $this->response->setJSON([
            'success' => true,
            'message' => 'User Status Updated Successfully!',
            'new_status' => $newStatus
        ]);
    } else {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed To Update User Status!',
            'new_status' => $newStatus
        ]);
    }
}

}