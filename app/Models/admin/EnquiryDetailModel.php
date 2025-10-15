<?php

namespace App\Models;

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
    public function getAllOrderCount($enquiryId)
    {
        return $this->db->table('enquiries')
            ->where('status !=', 9)
            ->where('enquiry_id', $enquiryId)
            ->countAllResults();
    }
    public function getAllFilteredRecords($enquiryId, $condition, $start, $length, $orderBy = 'enquiry_id', $orderDir = 'desc')
    {
        return $this->db->table('enquiries')
            ->select('enquiry_id, product_desc, quantity')
            ->where('status !=', 9)
            ->where('enquiry_id', $enquiryId)
            ->where($condition, null, false)
            ->orderBy($orderBy, $orderDir)
            ->limit($length, $start)
            ->get()
            ->getResult();
    }
    public function getFilterOrderCount($enquiryId, $condition)
    {
        $row = $this->db->table('enquiries')
            ->select('COUNT(*) as filRecords')
            ->where('status !=', 9)
            ->where('enquiry_id', $enquiryId)
            ->where($condition, null, false)
            ->get()
            ->getRow();

        return $row ?? (object)['filRecords' => 0];
    }
}