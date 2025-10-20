<?php
namespace App\Models\admin;

use CodeIgniter\Model;

class RoleMenuModel extends Model
{
    protected $table = 'role_menus';
    protected $primaryKey = 'rolemenu_id';
    protected $allowedFields = ['rolemenu_id','role_id','menu_name','access'];
}
