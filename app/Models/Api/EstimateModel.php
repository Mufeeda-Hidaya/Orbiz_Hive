<?php
namespace App\Models\Api;
use CodeIgniter\Model;

class EstimateModel extends Model
{
    protected $table = 'estimates';
    protected $primaryKey = 'estimate_id';
    protected $allowedFields = [
        'enquiry_id','user_id','customer_id','customer_address','discount','total_amount','sub_total','is_deleted',
        'date','phone_number','is_converted','company_id','estimate_no'
    ];
    public function getEstimates($companyId, $pageSize, $offset, $search = '')
    {
        $builder = $this->db->table('estimates AS e')
            ->select('e.estimate_id,e.estimate_no,e.customer_id,e.enquiry_id,e.phone_number AS customer_phone,c.name AS customer_name,
                c.address AS customer_address,e.total_amount, e.sub_total,e.date,e.company_id,e.is_converted
            ')
            ->join('customers AS c', 'c.customer_id = e.customer_id', 'left')
            ->where('e.company_id', $companyId)
            ->where('e.is_deleted', 0)
            ->where('e.is_converted !=', 1);

        if (!empty($search)) {
            $builder->groupStart()
                ->like('c.name', $search)
                ->orLike('c.address', $search)
                ->orLike('e.phone_number', $search)  
                ->orLike('e.estimate_no', $search)
                ->groupEnd();
        }

        $total = $builder->countAllResults(false);

        $data = $builder->orderBy('e.estimate_id', 'DESC')
            ->limit($pageSize, $offset)
            ->get()
            ->getResultArray();

        return [
            'total' => $total,
            'data'  => $data
        ];
    }

    public function getEstimateById($estimateId, $companyId)
    {
        return $this->db->table('estimates AS e')
            ->select('
                e.estimate_id,
                e.estimate_no,
                e.customer_id,
                e.enquiry_id,
                c.name AS customer_name,
                c.address AS customer_address,
                e.phone_number AS customer_phone,
                e.total_amount,
                e.sub_total,
                e.date,
                e.company_id
            ')
            ->join('customers AS c', 'c.customer_id = e.customer_id', 'left')
            ->where('e.estimate_id', $estimateId)
            ->where('e.company_id', $companyId)
            ->where('e.is_deleted', 0)
            ->get()
            ->getRowArray();
    }


    // estimate to job order conversion
    public function getDetails($estimateId)
    {
        return $this->db->table('estimates e')
            ->select('e.*, c.name, c.address as customer_address')
            ->join('customers c', 'c.customer_id = e.customer_id', 'left')
            ->where('e.estimate_id', $estimateId)
            ->get()
            ->getRowArray();
    }


}