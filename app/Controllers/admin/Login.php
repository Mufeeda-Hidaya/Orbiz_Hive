<?php
namespace App\Controllers\admin;

use App\Controllers\BaseController;
use App\Models\admin\LoginModel;

class Login extends BaseController
{
    protected $session;
    protected $request;

    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->request = \Config\Services::request();
    }

    public function index(): string
    {
        // If already logged in, redirect to dashboard
        if ($this->session->has('user_id')) {
            return redirect()->to(base_url('admin/dashboard'));
        }

        return view('admin/login');
    }

    public function login()
    {
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        if (empty($email) || empty($password)) {
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

        if ($user->status != 1) {
            return $this->response->setJSON([
                "success" => false,
                "message" => "Your account is inactive. Contact admin."
            ]);
        }

        // Set session
        $this->session->set([
            'user_id' => $user->user_id,
            'user_name' => $user->user_name,
            'user_email' => $user->email,
        ]);

        return $this->response->setJSON([
            "success" => true,
            "message" => "Login successful.",
            "redirect" => base_url('admin/dashboard')
        ]);
    }

    public function logout()
    {
        $this->session->destroy();
        return redirect()->to(base_url('admin'));
    }
}
