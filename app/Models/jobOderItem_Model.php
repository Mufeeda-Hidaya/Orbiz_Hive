<?php
namespace App\Models;
use CodeIgniter\Model;

class JobOrderItem_Model extends Model
{
    protected $table = 'joborder_items';
    protected $primaryKey = 'item_id';
    protected $allowedFields = [
        'joborder_id',
        'customer_id',
        'description',
        'market_price',
        'selling_price',
        'difference_percentage',
        'quantity',
        'total',
        'discount'
    ];

}
