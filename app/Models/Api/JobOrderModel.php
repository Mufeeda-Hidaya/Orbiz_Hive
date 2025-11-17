<?php
namespace App\Models\Api;

use CodeIgniter\Model;

class JobOrderModel extends Model
{
    protected $table = 'joborder';
    protected $primaryKey = 'joborder_id';
    protected $allowedFields = [
        'estimate_id',
        'user_id',
        'customer_id',
        'company_id',
        'joborder_no',
        'progress',
        'discount',
        'sub_total',
        'total_amount',
        'is_converted',
        'is_deleted',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by'
    ];
    // public function getAllJobOrders($companyId, $limit = 10, $offset = 0, $search = null)
    // {
    //     $builder = $this->db->table($this->table . ' jo');
    //     $builder->select(" jo.joborder_id,jo.joborder_no,jo.estimate_id,jo.customer_id,jo.total_amount, jo.sub_total,
    //                     jo.discount,jo.created_at,c.name AS customer_name,c.address AS customer_address,e.enquiry_id,e.enquiry_no,est.estimate_no");

    //     $builder->join('customers c', 'c.customer_id = jo.customer_id', 'left');
    //     $builder->join('estimates est', 'est.estimate_id = jo.estimate_id', 'left');
    //     $builder->join('enquiries e', 'e.enquiry_id = est.enquiry_id', 'left');

    //     $builder->where('jo.is_deleted', 0);
    //     $builder->where('jo.is_converted !=', 1);
    //     $builder->where('jo.company_id', $companyId);

    //     if (!empty($search)) {
    //         $builder->groupStart()
    //             ->like('jo.joborder_no', $search)
    //             ->orLike('c.name', $search)
    //             ->orLike('e.enquiry_no', $search)
    //             ->orLike('est.estimate_no', $search)
    //             ->groupEnd();
    //     }
    //     $countBuilder = clone $builder;
    //     $total = $countBuilder->countAllResults(false);

    //     $builder->orderBy('jo.created_at', 'DESC');
    //     $builder->limit($limit, $offset);

    //     $query = $builder->get();
    //     $data = $query->getResultArray();

    //     return [
    //         'total' => $total,
    //         'data'  => $data
    //     ];
    // }
    public function getAllJobOrders($companyId, $limit = 10, $offset = 0, $search = null)
{
    $builder = $this->db->table($this->table . ' AS jo');

    $builder->select("
        jo.joborder_id,
        jo.joborder_no,
        jo.estimate_id,
        jo.customer_id,
        jo.total_amount,
        jo.sub_total,
        jo.discount,
        jo.created_at,
        c.name AS customer_name,
        c.address AS customer_address,
        e.enquiry_id,
        e.enquiry_no,
        e.phone AS enquiry_phone,
        est.estimate_no,
        GROUP_CONCAT(DISTINCT ei.images) AS enquiry_images
    ");

    $builder->join('customers AS c', 'c.customer_id = jo.customer_id', 'left');
    $builder->join('estimates AS est', 'est.estimate_id = jo.estimate_id', 'left');
    $builder->join('enquiries AS e', 'e.enquiry_id = est.enquiry_id', 'left');
    $builder->join('enquiry_items AS ei', 'ei.enquiry_id = e.enquiry_id', 'left');

    $builder->where('jo.is_deleted', 0);
    $builder->where('jo.is_converted !=', 1);
    $builder->where('jo.company_id', $companyId);

    if (!empty($search)) {
        $builder->groupStart()
            ->like('jo.joborder_no', $search)
            ->orLike('c.name', $search)
            ->orLike('e.enquiry_no', $search)
            ->orLike('est.estimate_no', $search)
            ->groupEnd();
    }

    // Group by joborder_id to avoid duplicates due to GROUP_CONCAT
    $builder->groupBy('jo.joborder_id');

    // Count total
    $countBuilder = clone $builder;
    $total = $countBuilder->countAllResults(false);

    // Order & paginate
    $builder->orderBy('jo.created_at', 'DESC');
    $builder->limit($limit, $offset);

    // Fetch data
    $data = $builder->get()->getResultArray();

    // Convert images into array if you want
    foreach ($data as &$row) {
        $row['enquiry_images'] = !empty($row['enquiry_images'])
            ? explode(',', $row['enquiry_images'])
            : [];
    }

    return [
        'total' => $total,
        'data'  => $data
    ];
}

}
