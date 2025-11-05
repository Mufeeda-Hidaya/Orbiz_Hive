<?php
namespace App\Models\Api;

use CodeIgniter\Model;

class DeliveryModel extends Model
{
    protected $table = 'delivery';
    protected $primaryKey = 'delivery_id';
    protected $allowedFields = [
        'joborder_id', 'user_id', 'customer_id', 'company_id',
        'delivery_no', 'discount', 'sub_total', 'total_amount','delivery_status',
        'is_converted', 'is_deleted', 'created_at', 'created_by',
        'updated_at', 'updated_by'
    ];
}
