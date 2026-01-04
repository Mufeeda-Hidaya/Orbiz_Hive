<?php
namespace App\Models\Api;

use CodeIgniter\Model;

class EnquiryModel extends Model
{
    protected $table      = 'enquiries';
    protected $primaryKey = 'enquiry_id';
    protected $allowedFields = [
        'enquiry_no','customer_id','address','phone','name','user_id',
    'created_by','created_at','company_id','status','is_new','updated_by','updated_at'
    ];

    public function getAllEnquiries($pageSize = 10, $offset = 0, $search = '')
    {
        $builder = $this->select('
                enquiries.enquiry_id,
                enquiries.enquiry_no,
                enquiries.status,
                enquiries.is_new,
                enquiries.created_at,
                enquiry_items.note AS enquiry_note,
                customers.name AS customer_name,
                customers.contact_person_name,
                customers.address AS customer_address,
                customers.phone AS customer_phone
            ')
            ->join('customers', 'customers.customer_id = enquiries.customer_id', 'left')
            ->join('enquiry_items', 'enquiry_items.enquiry_id = enquiries.enquiry_id', 'left')
            ->where('enquiries.status', 1)
            ->where('enquiries.status', 1);

        if (!empty($search)) {
            $builder->groupStart()
                ->like('enquiries.enquiry_no', $search)
                ->orLike('customers.name', $search)
                ->orLike('customers.contact_person_name', $search)
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
            ->where('enquiries.status', 0)
            ->first();
    }
    public function getEnquiryDetails($enquiryId)
    {
        return $this->select('
                enquiries.enquiry_id,
                enquiries.enquiry_no,
                enquiries.status,
                enquiry_items.note AS enquiry_note,
                customers.name AS customer_name,
                customers.address AS customer_address
            ')
            ->join('customers', 'customers.customer_id = enquiries.customer_id', 'left')
            ->join('enquiry_items', 'enquiry_items.enquiry_id = enquiries.enquiry_id', 'left')
            ->where('enquiries.enquiry_id', $enquiryId)
            ->first();
    }
    // for estimate save data fetching
    public function getDetails($enquiryId)
    {
        return $this->select('enquiries.*, customers.customer_id, customers.name AS customer_name,customers.contact_person_name, customers.address AS customer_address, customers.phone AS customer_phone')
                    ->join('customers', 'customers.customer_id = enquiries.customer_id', 'left')
                    ->where('enquiries.enquiry_id', $enquiryId)
                    ->first();
    }

}
