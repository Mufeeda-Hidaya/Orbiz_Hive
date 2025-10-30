<?php
namespace App\Models\Api;
use CodeIgniter\Model;

class EstimateItemModel extends Model
{
    protected $table = 'estimate_items';
    protected $primaryKey = 'item_id';
    protected $allowedFields = [
        'estimate_id',
        'description',
        'market_price',
        'selling_price',
        'difference_percentage',
        'quantity',
        'status',
        'total',
        'item_order'
    ];

   public function getItemsByEstimateId($estimateId)
{
    return $this->select('item_id, description, market_price, selling_price, difference_percentage, quantity, total')
                ->where('estimate_id', $estimateId)
                ->where('status', 1) 
                ->findAll();
}

}

