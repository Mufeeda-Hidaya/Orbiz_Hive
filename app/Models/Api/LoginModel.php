<?php

namespace App\Models\Api;

use CodeIgniter\Model;

class LoginModel extends Model

{
    protected $table = 'user';
    protected $primaryKey = 'user_id';
    protected $allowedFields = ['name', 'email', 'phonenumber', 'password', 'role_id', 'company_id', 'jwt_token','last_login', 'status'];

}