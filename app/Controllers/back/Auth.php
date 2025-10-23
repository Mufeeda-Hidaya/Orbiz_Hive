<?php
namespace App\Controllers;

use CodeIgniter\Controller;

class Auth extends Controller
{
    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('/'));
    }
}
