<?php
namespace App\Models;
use CodeIgniter\Model;

class EstimateItemModel extends Model
{
    protected $table = 'estimate_items';
    protected $primaryKey = 'item_id';
    protected $allowedFields = ['estimate_id', 'description', 'price', 'quantity', 'total','item_order'];
}
