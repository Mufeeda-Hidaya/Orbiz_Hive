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
    public function getAllOrderCount($enquiryId)
    {
        return $this->db->table('enquiries')
            ->where('status !=', 9)
            ->where('enquiry_id', $enquiryId)
            ->countAllResults();
    }
    public function getAllFilteredRecords($enquiryId, $start, $length, $searchValue = '', $orderBy = 'enquiry_id', $orderDir = 'desc')
    {
    $builder = $this->db->table('enquiries')
        ->select('enquiry_id, product_name, product_desc, quantity')
        ->where('status !=', 9)
        ->where('enquiry_id', $enquiryId);

    if(!empty($searchValue)) {
        $builder->groupStart()
                ->orLike('product_name', $searchValue)
                ->orLike('product_desc', $searchValue)
                ->orLike('quantity', $searchValue)
                ->groupEnd();
    }

    $builder->orderBy($orderBy, $orderDir)
            ->limit($length, $start);

    return $builder->get()->getResult();
    }

    public function getFilterOrderCount($enquiryId, $searchValue = '')
    {
        $builder = $this->db->table('enquiries')
            ->where('status !=', 9)
            ->where('enquiry_id', $enquiryId);

        if(!empty($searchValue)) {
            $builder->groupStart()
                    ->orLike('product_name', $searchValue)
                    ->orLike('product_desc', $searchValue)
                    ->orLike('quantity', $searchValue)
                    ->groupEnd();
        }

        $row = $builder->select('COUNT(*) as filRecords')->get()->getRow();
        return $row ?? (object)['filRecords' => 0];
    }

}