<?php
namespace App\Models\Api;

use CodeIgniter\Model;

class EnquiryModel extends Model
{
    protected $table      = 'enquiries';
    protected $primaryKey = 'enquiry_id';
    protected $allowedFields = [
        'enquiry_no','customer_id','address','phone','name','user_id',
    'created_by','created_at','is_deleted', 'is_converted','updated_by','updated_at'
    ];

    public function getAllEnquiries($pageSize = 10, $offset = 0, $search = '')
    {
        $builder = $this->select('
                enquiries.enquiry_id,
                enquiries.enquiry_no,
                customers.name AS customer_name,
                customers.address AS customer_address,
                customers.phone AS customer_phone
            ')
            ->join('customers', 'customers.customer_id = enquiries.customer_id', 'left')
            ->where('enquiries.is_deleted', 0)
            ->where('enquiries.is_converted', 0);

        if (!empty($search)) {
            $builder->groupStart()
                ->like('enquiries.enquiry_no', $search)
                ->orLike('customers.name', $search)
                ->orLike('customers.address', $search)
                ->groupEnd();
        }
        $total = $builder->countAllResults(false);
        $data = $builder
            ->orderBy('enquiries.enquiry_id', 'DESC')
            ->findAll($pageSize, $offset);

        return [
            'total' => $total,
            'data'  => $data
        ];
    }

    public function getEnquiryWithCustomer($id)
    {
        return $this->select('
                enquiries.enquiry_id,
                enquiries.enquiry_no,
                customers.name AS customer_name,
                customers.address AS customer_address
            ')
            ->join('customers', 'customers.customer_id = enquiries.customer_id', 'left')
            ->where('enquiries.enquiry_id', $id)
            ->where('enquiries.is_deleted', 0)
            ->first();
    }
    public function getEnquiryDetails($enquiryId)
    {
        return $this->select('
                enquiries.enquiry_id,
                enquiries.enquiry_no,
                enquiries.is_deleted,
                enquiries.is_converted,
                customers.name AS customer_name,
                customers.address AS customer_address
            ')
            ->join('customers', 'customers.customer_id = enquiries.customer_id', 'left')
            ->where('enquiries.enquiry_id', $enquiryId)
            ->first();
    }
    // for estimate save data fetching
    public function getDetails($enquiryId)
    {
        return $this->select('enquiries.*, customers.customer_id, customers.name AS customer_name, customers.address AS customer_address, customers.phone AS customer_phone')
                    ->join('customers', 'customers.customer_id = enquiries.customer_id', 'left')
                    ->where('enquiries.enquiry_id', $enquiryId)
                    ->first();
    }

}
