<?php

namespace App\Models;

use CodeIgniter\Model;

class Rolemanagement_model extends Model
{
    protected $table = 'role_menus';
    protected $primaryKey = 'rolemenu_id';
    protected $allowedFields = ['role_id', 'menu_name', 'access'];
}
