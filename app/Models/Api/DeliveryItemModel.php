<?php
namespace App\Models\Api;

use CodeIgniter\Model;

class DeliveryItemModel extends Model
{
    protected $table = 'delivery_items';
    protected $primaryKey = 'delivery_item_id';
    protected $allowedFields = [
        'delivery_id', 'item_id', 'description', 'quantity',
        'selling_price', 'sub_total', 'discount', 'status',
        'created_at', 'created_by', 'updated_at', 'updated_by'
    ];
}
