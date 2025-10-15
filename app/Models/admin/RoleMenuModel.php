<?php
namespace App\Models\admin;

use CodeIgniter\Model;

class RoleMenuModel extends Model
{
    protected $table = 'role_menus';
    protected $primaryKey = 'rolemenu_id';
    protected $allowedFields = [
        'role_id',
        'menu_name',
        'access',
        'created_on',
        'created_by',
        'updated_on',
        'updated_by'
    ];
    protected $useTimestamps = false;
    protected $returnType = 'array';
}