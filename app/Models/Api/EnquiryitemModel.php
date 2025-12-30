<?php
namespace App\Models\Api;

use CodeIgniter\Model;

class EnquiryItemModel extends Model
{
    protected $table = 'enquiry_items';
    protected $primaryKey = 'enquiry_item_id';
    protected $allowedFields = [
        'enquiry_id',
        'history_id',   // <-- add this
        'description',
        'quantity',
        'images',
        'status',
        'created_at',
        'updated_at'
    ];

    public function getItemsByEnquiryId($enquiryId)
    {
        return $this->select('description, quantity')
            ->where('enquiry_id', $enquiryId)
            ->where('status !=', 9)
            ->findAll();
    }
}
