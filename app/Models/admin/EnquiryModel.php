<?php
namespace App\Models\admin;
 
use CodeIgniter\Model;
 
class EnquiryModel extends Model {
 
    protected $table = 'user';
    protected $primaryKey = 'user_id';
    protected $allowedFields = ['user_id','product_name', 'quantity', 'status', 'created_at', 'updated_at'];
}