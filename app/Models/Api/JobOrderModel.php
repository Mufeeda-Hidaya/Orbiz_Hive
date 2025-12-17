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
    public function getAllJobOrders($companyId, $limit, $offset, $search = null)
    {
        $builder = $this->db->table('joborder jo')
            ->select('
                jo.joborder_id,
                jo.estimate_id,
                jo.user_id,
                jo.customer_id,
                jo.company_id,
                jo.joborder_no,
                jo.discount,
                jo.sub_total,
                jo.total_amount,
                jo.is_converted,
                jo.created_at,

                c.name AS customer_name,
                c.address AS customer_address,
                c.phone AS customer_phone,

                joi.joborder_item_id,
                joi.estimate_item_id,
                joi.enquiry_item_id,
                joi.description,
                joi.quantity,
                joi.sub_total AS item_sub_total,
                joi.discount AS item_discount,
                joi.progress AS item_progress,
                joi.status AS item_status,

                ei.images AS item_images
            ')
            ->join('customers c', 'c.customer_id = jo.customer_id', 'left')
            ->join('joborder_items joi', 'joi.joborder_id = jo.joborder_id', 'left')
            ->join('enquiry_items ei', 'ei.item_id = joi.enquiry_item_id', 'left')
            ->where('jo.company_id', $companyId)
            ->where('jo.is_deleted', 0)
            ->limit($limit, $offset)
            ->orderBy('jo.joborder_id', 'DESC');

        if (!empty($search)) {
            $builder->groupStart()
                ->like('jo.joborder_no', $search)
                ->orLike('joi.description', $search)
                ->orLike('c.name', $search)
                ->orLike('c.phone', $search)
                ->groupEnd();
        }

        $rows = $builder->get()->getResultArray();

        $data = [];

        foreach ($rows as $row) {
            $joborderId = $row['joborder_id'];

            if (!isset($data[$joborderId])) {
                $data[$joborderId] = [
                    'joborder_id'      => $row['joborder_id'],
                    'estimate_id'      => $row['estimate_id'],
                    'user_id'          => $row['user_id'],
                    'customer_id'      => $row['customer_id'],
                    'customer_name'    => $row['customer_name'],
                    'customer_address' => $row['customer_address'],
                    'customer_phone'   => $row['customer_phone'],
                    'company_id'       => $row['company_id'],
                    'joborder_no'      => $row['joborder_no'],
                    'discount'         => $row['discount'],
                    'sub_total'        => $row['sub_total'],
                    'total_amount'     => $row['total_amount'],
                    'is_converted'     => $row['is_converted'],
                    'created_at'       => $row['created_at'],
                    'items'            => []
                ];
            }

            if (!empty($row['joborder_item_id'])) {
                $data[$joborderId]['items'][] = [
                    'joborder_item_id' => $row['joborder_item_id'],
                    'estimate_item_id' => $row['estimate_item_id'],
                    'enquiry_item_id'  => $row['enquiry_item_id'],
                    'description'      => $row['description'],
                    'quantity'         => $row['quantity'],
                    'sub_total'        => $row['item_sub_total'],
                    'discount'         => $row['item_discount'],
                    'progress'         => $row['item_progress'],
                    'status'           => $row['item_status'],
                    'images'           => $row['item_images']
                ];
            }
        }

        $total = $this->db->table('joborder')
            ->where('company_id', $companyId)
            ->where('is_deleted', 0)
            ->countAllResults();

        return [
            'total' => $total,
            'data'  => array_values($data)
        ];
    }
}
