<?php
namespace App\Models\Api;

use CodeIgniter\Model;

class JobOrderItemModel extends Model
{
    protected $table = 'joborder_items';
    protected $primaryKey = 'joborder_item_id';
    protected $allowedFields = [
        'joborder_id',
        'estimate_item_id',
        'item_id',
        'description',
        'quantity',
        'unit',
        'market_price',
        'selling_price',
        'difference_percentage',
        'sub_total',
        'discount',
        'progress',
        'status',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by'
    ];
}
