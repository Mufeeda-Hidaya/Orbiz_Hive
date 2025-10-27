<?php
namespace App\Models\Api;

use CodeIgniter\Model;

class EnquiryModel extends Model
{
    protected $table      = 'enquiries';
    protected $primaryKey = 'enquiry_id';
    protected $allowedFields = [
        'enquiry_no','customer_id','address','phone','name','user_id',
    'created_by','created_at','company_id','is_deleted', 'is_converted','updated_by','updated_at'
    ];

    public function getItemsByEnquiryId($enquiryId)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('enquiry_items');

        $items = $builder
            ->select('description, quantity, created_at')
            ->where('enquiry_id', $enquiryId)
            ->get()
            ->getResultArray();
        foreach ($items as &$item) {
            unset($item['created_on'], $item['updated_on'], $item['name'], $item['address']);
        }

        return $items;
    }
}