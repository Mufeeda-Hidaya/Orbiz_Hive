<?php
namespace App\Models\Api;

use CodeIgniter\Model;

class JobOrderModel extends Model
{
    protected $table = 'joborder';
    protected $primaryKey = 'joborder_id';
    protected $allowedFields = [
        'estimate_id',
        'user_id',
        'customer_id',
        'company_id',
        'joborder_no',
        'progress',
        'discount',
        'sub_total',
        'total_amount',
        'is_converted',
        'is_deleted',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by'
    ];
}
