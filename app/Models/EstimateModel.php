<?php
namespace App\Models;

use CodeIgniter\Model;

class EstimateModel extends Model
{
    protected $table = 'estimates';
    protected $primaryKey = 'estimate_id';

    protected $allowedFields = [
        'enquiry_id',
        'user_id',
        'customer_id',
        'customer_address',
        'phone_number',
        'date',
        'estimate_no',
        'revision_no',
        'revision_label',
        'transportation_cost',
        'discount',
        'sub_total',
        'total_amount',
        'status',
        'is_deleted',
        'is_converted',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by'
    ];

    /* ---------------- Estimate Number ---------------- */

    public function getLastEstimateNo()
    {
        $last = $this->orderBy('estimate_no', 'DESC')->first();
        return $last ? (int) $last['estimate_no'] : 0;
    }

    /* ---------------- Insert ---------------- */

    public function insertEstimateWithItems($estimateData, $items)
    {
        if (!isset($estimateData['estimate_no'])) {
            $estimateData['estimate_no'] = $this->getLastEstimateNo() + 1;
        }

        $this->db->transStart();

        $estimateId = $this->insert($estimateData);

        $itemModel = new \App\Models\EstimateItemModel();

        foreach ($items as $index => $item) {
            $item['estimate_id'] = $estimateId;
            $item['item_order'] = $item['item_order'] ?? ($index + 1);
            $itemModel->insert($item);
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new \RuntimeException('Failed to insert estimate');
        }

        return $estimateId;
    }

    /* ---------------- Update ---------------- */

    public function updateEstimateWithItems($estimateId, $estimateData, $items)
    {
        $this->update($estimateId, $estimateData);

        $itemModel = new \App\Models\EstimateItemModel();
        $itemModel->where('estimate_id', $estimateId)->delete();

        foreach ($items as $index => $item) {
            $item['estimate_id'] = $estimateId;
            $item['item_order'] = $item['item_order'] ?? ($index + 1);
            $itemModel->insert($item);
        }
    }

    /* ---------------- Datatable Helpers ---------------- */

    public function getEstimateCount()
    {
        return $this->db->table('estimates')
            ->where('status', 1)
            ->countAllResults();
    }

    public function getFilteredCount($searchValue)
    {
        $builder = $this->db->table('estimates')
            ->join('customers', 'customers.customer_id = estimates.customer_id', 'left')
            ->where('estimates.status', 1);

        if ($searchValue) {
            $builder->groupStart()
                ->like('customers.name', $searchValue)
                ->orLike('customers.address', $searchValue)
                ->orLike('estimates.estimate_no', $searchValue)
                ->groupEnd();
        }

        return $builder->countAllResults();
    }

    public function getFilteredEstimates($searchValue, $start, $length, $orderBy, $dir)
    {
        $builder = $this->db->table('estimates')
            ->select('estimates.*, customers.name customer_name, customers.address customer_address')
            ->join('customers', 'customers.customer_id = estimates.customer_id', 'left')
            ->where('estimates.status', 1);

        if ($searchValue) {
            $builder->groupStart()
                ->like('customers.name', $searchValue)
                ->orLike('customers.address', $searchValue)
                ->orLike('estimates.estimate_no', $searchValue)
                ->groupEnd();
        }

        return $builder
            ->orderBy($orderBy, $dir)
            ->limit($length, $start)
            ->get()
            ->getResultArray();
    }

    /* ---------------- Dashboard ---------------- */

    public function getRecentEstimatesWithCustomer($limit = 5)
    {
        return $this->db->table('estimates')
            ->select('estimates.*, customers.name customer_name')
            ->join('customers', 'customers.customer_id = estimates.customer_id', 'left')
            ->where('estimates.status', 1)
            ->orderBy('estimates.date', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }
}
