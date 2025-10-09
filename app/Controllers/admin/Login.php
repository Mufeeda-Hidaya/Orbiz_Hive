<?php
namespace App\Controllers\admin;

use App\Controllers\BaseController;
use App\Models\admin\LoginModel;

class Login extends BaseController
{
    protected $session;
    protected $request;

    public function index(): string
    {
        return view('admin/login');     
    }
    public function __construct()  
    {
        $this->session = \Config\Services::session();
        $this->input = \Config\Services::request();
    }
    public function login()
    {
        $session = session();
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
    
        if (!$email || !$password) {
            return $this->response->setJSON([
                "success" => false,
                "message" => "Please enter both Email and Password."
            ]);
        }
        $loginModel = new LoginModel();
        $user = $loginModel->checkLoginUser($email, $password);
        if ($user === 'invalid') {
            return $this->response->setJSON([
                "success" => false,
                "message" => "Invalid Email or Password."
            ]);
        }

        // if ($user === 'removed') {
        //     return $this->response->setJSON([
        //         "success" => false,
        //         "message" => "Access Denied. Your Account Has Been Removed."
        //     ]);
        // }

        // if ($user === 'suspended') {
        //     return $this->response->setJSON([
        //         "success" => false,
        //         "message" => "Your Account Has Been Suspended By Admin."
        //     ]);
        // }

        // Set session data
        $session->set([
            'user_id'    => $user->user_id,
            'user_name'  => $user->user_name,
            'user_email' => $user->email,
        ]);

        return $this->response->setJSON([
            "success" => true,
            "message" => "Login Successful",
            "redirect" => base_url('admin/dashboard')
        ]);
    }

    public function logout()
    {
        $this->session->destroy();
        return redirect()->to(base_url('admin'));
    }
}