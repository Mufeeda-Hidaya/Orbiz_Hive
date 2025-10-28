<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\Api\LoginModel;  
use App\Models\Manageuser_Model;
use App\Models\RoleModel;
use App\Libraries\Jwt;
use App\Libraries\AuthService;
use App\Helpers\AuthHelper;

class Login extends BaseController
{
    protected $loginModel;

    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->input = \Config\Services::request();
        $this->loginModel = new LoginModel();  
        $this->userModel  = new Manageuser_Model();
        $this->roleModel  = new RoleModel();
        $this->authService = new AuthService();
    }

    public function login()
{
    $data = $this->request->getJSON(true);

    if (empty($data['email']) || empty($data['password'])) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Email And Password Are Required.',
            'data'    => []
        ]);
    }

    $user = $this->loginModel
        ->where('email', $data['email'])
        ->where('status !=', 9)
        ->first();

    if (!$user) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Invalid Email Or Password.',
            'data'    => []
        ]);
    }

    if ($user['status'] == 2) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Your Account Has Been Suspended By The Admin.',
            'data'    => []
        ]);
    }

    if ($user['status'] == 9) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'This Account Has Been Deleted.',
            'data'    => []
        ]);
    }
    $inputPassword = $data['password'];
    $dbPassword    = $user['password'];
    $isValid = false;

    if (password_verify($inputPassword, $dbPassword)) {
        $isValid = true;
    } elseif (md5($inputPassword) === $dbPassword) {
        $isValid = true;
    }

    if (!$isValid) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Invalid Email Or Password.',
            'data'    => []
        ]);
    }
    $jwt  = new Jwt();
    $now  = date('Y-m-d H:i:s');
    $token = $jwt->encode(['user_id' => $user['user_id'], 'email' => $user['email']]);
    $this->loginModel->update($user['user_id'], [
        'jwt_token'  => $token,
        'last_login' => $now
    ]);
    return $this->response->setJSON([
        'success' => true,
        'message' => 'Login successful',
        'data' => [
            'user_id'   => $user['user_id'],
            'name'      => $user['name'] ?? '',
            'email'     => $user['email'],
            'phone'     => $user['phonenumber'] ?? '',
            'role_id'   => $user['role_id'] ?? null,
            'status'    => $user['status'],
            'jwt_token' => $token
        ]
    ]);
}
   public function logout()
{
    $authHeader = AuthHelper::getAuthorizationToken($this->request);
    if (empty($authHeader)) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Authorization token is missing.',
            'data'    => []
        ]);
    }
    $auth = new AuthService();
    $user = $auth->getAuthenticatedUser($authHeader);

    if (!$user || empty($user['user_id'])) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Invalid or expired token.',
            'data'    => []
        ]);
    }
    $this->loginModel->update($user['user_id'], [
        'jwt_token' => null,
    ]);

    return $this->response->setJSON([
        'success' => true,
        'message' => 'Logout successful. Token removed.',
        'data'    => [
            'user_id' => $user['user_id']
        ]
    ]);
}




}
