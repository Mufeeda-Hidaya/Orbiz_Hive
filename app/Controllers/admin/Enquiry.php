<?php

namespace App\Controllers\admin;

use App\Controllers\BaseController;
use App\Models\admin\EnquiryModel;

class Roles extends BaseController
{
     public function __construct()  
    {
        $this->session = \Config\Services::session();
        $this->input = \Config\Services::request();
    } 
    public function index()
    {
        
        $template = view('admin/common/header');
        $template.= view('admin/common/left_menu');
        $template.= view('admin/manage_enquiry');
        $template.= view('admin/common/footer');
        $template.= view('admin/page_scripts/userjs');
        return $template;
            
    }
}