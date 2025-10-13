<?php
namespace App\Models\admin;

use CodeIgniter\Model;

class LoginModel extends Model
{
    protected $table = 'user';
    protected $primaryKey = 'user_id';
    protected $allowedFields = ['user_name', 'email','phone', 'password', 'status', 'created_at','created_by', 'updated_at','updated_by'];

    public function checkLoginUser($email, $password)
    {
        $user = $this->where('email', $email)->first();

        if (!$user) {
            return 'invalid';
        }

        if (!password_verify($password, $user['password'])) {
            return 'invalid';
        }

        return (object) $user;
    }
} 