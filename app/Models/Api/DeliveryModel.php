<?php
namespace App\Models\Api;

use CodeIgniter\Model;

class DeliveryModel extends Model
{
    protected $table = 'delivery';
    protected $primaryKey = 'delivery_id';
    protected $allowedFields = [
        'joborder_id', 'user_id', 'customer_id', 'company_id',
        'delivery_no', 'discount', 'sub_total', 'total_amount','delivery_status',
        'is_converted', 'is_deleted', 'created_at', 'created_by',
        'updated_at', 'updated_by'
    ];

     public function getAllDeliveries($companyId, $limit = 10, $offset = 0, $search = null)
    {
        $builder = $this->db->table($this->table . ' d');
        $builder->select("
            d.delivery_id, d.delivery_no, d.joborder_id, d.customer_id, d.total_amount,
            d.delivery_status, d.delivered_at, d.created_at,
            c.name AS customer_name, c.address AS customer_address,
            jo.joborder_no
        ");

        $builder->join('customers c', 'c.customer_id = d.customer_id', 'left');
        $builder->join('joborder jo', 'jo.joborder_id = d.joborder_id', 'left');
        $builder->where('d.is_deleted', 0);
        $builder->where('d.company_id', $companyId);

        if (!empty($search)) {
            $builder->groupStart()
                ->like('d.delivery_no', $search)
                ->orLike('c.name', $search)
                ->orLike('jo.joborder_no', $search)
                ->groupEnd();
        }

        $countBuilder = clone $builder;
        $total = $countBuilder->countAllResults(false);

        $builder->orderBy('d.created_at', 'DESC');
        $builder->limit($limit, $offset);

        $query = $builder->get();
        $data = $query->getResultArray();

        return [
            'total' => $total,
            'data'  => $data
        ];
    }
}


