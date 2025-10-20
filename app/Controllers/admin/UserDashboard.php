<?php
namespace App\Controllers\admin;

use App\Controllers\BaseController;

class UserDashboard extends BaseController
{
    protected $session;

    public function __construct()
    {
        $this->session = session();
    }

    public function index()
    {
        if (!$this->session->get('user_id')) {
            return redirect()->to(base_url('admin'));
        }
        if ($this->session->get('role_id') == 1) {
            return redirect()->to(base_url('admin/dashboard'));
        }
        $template  = view('admin/common/header');
        $template .= view('admin/common/left_menu');
        $template .= view('admin/user_dashboard'); 
        $template .= view('admin/common/footer');

        return $template;
    }
}
