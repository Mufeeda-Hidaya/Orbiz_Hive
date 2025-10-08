<?php

namespace App\Controllers\admin;

use App\Controllers\BaseController;

class Roles extends BaseController
{
    public function index()
    {
        $template  = view('admin/common/header');
        $template .= view('admin/common/left_menu');
        $template .= view('admin/roles');
        $template .= view('admin/common/footer');
        return $template;
    }
//     public function index()
// {
//     return 'Dashboard working!';
// }
}
