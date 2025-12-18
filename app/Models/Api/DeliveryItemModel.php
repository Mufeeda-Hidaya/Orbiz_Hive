<?php
namespace App\Models\Api;

use CodeIgniter\Model;

class DeliveryItemModel extends Model
{
    protected $table = 'delivery_items';
    protected $primaryKey = 'delivery_item_id';
    protected $allowedFields = [
        'delivery_id', 'joborder_item_id', 'description', 'quantity',
        'selling_price', 'sub_total', 'discount', 'status', 'delivery_status', 'delivered_at',
        'created_at', 'created_by', 'updated_at', 'updated_by'
    ];
    public function getItemsWithImages($deliveryId)
{
    return $this->db->table('delivery_items di')
        ->select('
            di.delivery_item_id,
            di.delivery_id,
            di.joborder_item_id,
            di.description,
            di.quantity,
            di.sub_total,
            di.discount,
            di.status,
            di.delivery_status,
            ei.images
        ')
        ->join(
            'joborder_items joi',
            'joi.joborder_item_id = di.joborder_item_id',
            'left'
        )
        ->join(
            'enquiry_items ei',
            'ei.item_id = joi.enquiry_item_id',
            'left'
        )
        ->where('di.delivery_id', $deliveryId)
        ->get()
        ->getResultArray();
}

}
