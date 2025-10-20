<?php
namespace App\Models\admin;

use CodeIgniter\Model;

class LoginModel extends Model
{
    protected $table = 'user';
    protected $primaryKey = 'user_id';
    protected $allowedFields = ['user_name', 'email', 'country_code','phone', 'password', 'status', 'created_at','created_by', 'updated_at','updated_by'];

    public function checkLoginUser($email, $password)
    {
        $users = $this->where('email', $email)
                      ->orderBy('user_id', 'DESC')
                      ->findAll();
        if (!$users) {
            return 'invalid';
        }
        foreach ($users as $user) {
            if ($user['status'] == 9) {
                continue;
            }
            if (!password_verify($password, $user['password'])) {
                continue;
            }
            if ($user['role_id'] != 1 && $user['status'] == 2) {
                return 'suspended';
            }
            return (object) $user;
        }
        return 'invalid';
    }
} 