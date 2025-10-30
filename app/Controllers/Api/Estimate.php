<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\Api\EnquiryModel;
use App\Controllers\BaseController;
use App\Models\Api\LoginModel;  
use App\Models\EnquiryItemModel;
use App\Models\Manageuser_Model;
use App\Models\EstimateModel;
use App\Models\EstimateItemModel;
use App\Models\customerModel;
use App\Models\RoleModel;
use App\Libraries\Jwt;
use App\Libraries\AuthService;
use App\Helpers\AuthHelper;

class Estimate extends ResourceController
{
    protected $loginModel;

    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->input = \Config\Services::request();
        $this->userModel = new Manageuser_Model();
        $this->customerModel = new CustomerModel();
        $this->enquiryModel = new EnquiryModel();
        $this->enquiryItemModel = new EstimateModel();
        $this->enquiryItemModel = new EstimateItemModel();
        $this->enquiryItemModel = new EnquiryItemModel();
        $this->authService = new AuthService();
    }
}