<?php
namespace App\Models\Api;

use CodeIgniter\Model;

class EnquiryModel extends Model
{
    protected $table = 'enquiries';
    protected $primaryKey = 'enquiry_id';
    protected $allowedFields = [
        'enquiry_no',
        'customer_id',
        'contact_person_name',
        'phone',
        'stage',
        'status',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at'
    ];


    public function getAllEnquiries($pageSize = 10, $offset = 0, $search = '')
    {
        $builder = $this->select('
            enquiries.enquiry_id,
            enquiries.enquiry_no,
            enquiries.stage AS enquiry_stage,

            customers.name AS customer_name,
            customers.contact_person_name,
            customers.address AS customer_address,
            customers.phone AS customer_phone,

            eh.history_id,
            eh.revision_no,
            eh.revision_label,
            eh.note,
            eh.comments,
            eh.stage AS history_stage,
            eh.status AS history_status,
            eh.date AS history_date,
            eh.created_at 
        ')
            ->join('customers', 'customers.customer_id = enquiries.customer_id', 'left')

            // ✅ JOIN LATEST HISTORY PER ENQUIRY
            ->join(
                '(SELECT enquiry_id, MAX(revision_no) AS max_revision 
              FROM enquiries_history 
              GROUP BY enquiry_id) latest',
                'latest.enquiry_id = enquiries.enquiry_id',
                'left'
            )
            ->join(
                'enquiries_history eh',
                'eh.enquiry_id = enquiries.enquiry_id 
             AND eh.revision_no = latest.max_revision',
                'left'
            )

            ->where('enquiries.status', 1)   // ACTIVE
            ->where('enquiries.stage', 1);   // ENQUIRY STAGE ONLY

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
            'data' => $data
        ];
    }

    public function getEnquiryWithCustomer($id)
    {
        return $this->select('
                enquiries.enquiry_id,
                enquiries.enquiry_no,
                customers.name AS customer_name,
                customers.contact_person_name,
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
                enquiries.stage,
                customers.name AS customer_name,
                customers.contact_person_name,
                customers.address AS customer_address
            ')
            ->join('customers', 'customers.customer_id = enquiries.customer_id', 'left')
            ->where('enquiries.enquiry_id', $enquiryId)
            ->first();
    }
    // for estimate save data fetching
    public function getDetails($enquiryId)
    {
        return $this->select('enquiries.*, customers.customer_id, customers.name AS customer_name,customers.contact_person_name , customers.address AS customer_address, customers.phone AS customer_phone')
            ->join('customers', 'customers.customer_id = enquiries.customer_id', 'left')
            ->where('enquiries.enquiry_id', $enquiryId)
            ->first();
    }

}
