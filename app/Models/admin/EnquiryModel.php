<?php

namespace App\Models\admin;

use CodeIgniter\Model;

class EnquiryModel extends Model
{
    protected $table = 'enquiries';
    protected $primaryKey = 'enquiry_id';
    protected $allowedFields = ['user_id', 'product_name', 'product_desc', 'quantity', 'status', 'created_at', 'updated_at'];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getAllOrderCount()
    {
        return $this->db->table('enquiries')
            ->where('status !=', 9)
            ->countAllResults();
    }

    public function getAllFilteredRecords($condition, $start, $length, $orderBy = 'e.enquiry_id', $orderDir = 'desc')
    {
        return $this->db->table('enquiries e')
            ->select('e.enquiry_id, e.created_at, u.name , u.email')
            ->join('user u', 'u.user_id = e.user_id', 'left')
            ->where('e.status !=', 9)
             ->where($condition, null, false) 
            ->orderBy($orderBy, $orderDir)
            ->limit($length, $start)
            ->get()
            ->getResult();
    }

    public function getFilterOrderCount($condition)
    {
        $row = $this->db->table('enquiries e')
            ->select('COUNT(*) as filRecords')
            ->join('user u', 'u.user_id = e.user_id', 'left')
            ->where('e.status !=', 9)
             ->where($condition, null, false) 
            ->get()
            ->getRow();

        return $row ?? (object)['filRecords' => 0];
    }
    
}
