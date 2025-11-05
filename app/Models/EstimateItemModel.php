<?php
namespace App\Models;
use CodeIgniter\Model;

class EstimateItemModel extends Model
{
    protected $table = 'estimate_items';
    protected $primaryKey = 'item_id';
    protected $allowedFields = [
        'estimate_id',
        'quantity',
        'description',
        'material_cost',
        'labour_hour',
        'labour_rate',
        'production_type',
        'status',
        'labour_cost',
        'transportation_cost',
        'total_cost',
        'gp_percentage',
        'selling_price',
        'difference_percentage',
        'total',
        'item_order',
    ];
}

