<?php
namespace App\Models\Api;
use CodeIgniter\Model;

class EstimateItemModel extends Model
{
    protected $table = 'estimate_items';
    protected $primaryKey = 'item_id';
    protected $allowedFields = [
        'estimate_id',
        'enquiry_item_id',
        'description',
        'work_type',
        'material_cost',
        'labour_hour',
        'cost',
        'labour_rate',
        'transportation_cost',
        'labour_cost',
        'total_cost',
        'gp_percentage',
        'selling_price',
        'discount',
        'difference_percentage',
        'quantity',
        'status',
        'total',
        'item_order'
    ];

    public function getItemsByEstimateId($estimateId)
    {
        return $this->select('item_id, estimate_id,description, work_type, material_cost,labour_hour, labour_rate,labour_cost,transportation_cost,
                            total_cost,gp_percentage,selling_price,quantity,total, item_order,status,
                            ')
        ->where('estimate_id', $estimateId)
        ->where('status', 1)
        ->orderBy('item_id', 'ASC')  
        ->findAll();
    }
    public function getEstimateitemsById($estimateId)
    {
        return $this->select('enquiry_item_id, total')
            ->where('estimate_id', $estimateId)
            ->where('status !=', 9)
            ->findAll();
    }



}

