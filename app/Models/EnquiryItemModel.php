<?php
namespace App\Models;

use CodeIgniter\Model;

class EnquiryItemModel extends Model
{
    protected $table = 'enquiry_items';
    protected $primaryKey = 'item_id';
    protected $allowedFields = ['enquiry_id','description','quantity', 'images', 'created_at','updated_at','status'];

    public function getItemsByEnquiryId($enquiryId)
    {
        return $this->select('item_id,description, quantity')
            ->where('enquiry_id', $enquiryId)
            ->where('status !=', 9)  
            ->findAll();
    }
}
