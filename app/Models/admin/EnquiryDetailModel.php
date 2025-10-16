<?php

namespace App\Models\admin;

use CodeIgniter\Model;

class EnquiryDetailModel extends Model
{
    protected $table = 'enquiries';
    protected $primaryKey = 'enquiry_id';
    protected $allowedFields =['user_id', 'product_name', 'product_desc', 'quantity', 'status', 'created_at', 'updated_at'];


    public function getEnquiryWithUser($enquiryId)
    {
        return $this->db->table('enquiries e')
            ->select('e.*, u.name, u.address, u.email, u.phone')
            ->join('user u', 'u.user_id = e.user_id', 'left')
            ->where('e.enquiry_id', $enquiryId)
            ->where('e.status !=', 9)
            ->get()
            ->getRow();
    }
    public function getEnquiry($enquiryId)
    {
        return $this->db->table($this->table)
            ->select('user_id, created_at')
            ->where('enquiry_id', $enquiryId)
            ->where('status !=', 9)
            ->get()
            ->getRow();
    }
    public function getEnquiryItems($userId, $createdAt)
    {
        return $this->db->table($this->table)
            ->select('enquiry_id, product_name, product_desc, quantity')
            ->where('status !=', 9)
            ->where('user_id', $userId)
            ->where('created_at', $createdAt)
            ->get()
            ->getResult();
    }

}