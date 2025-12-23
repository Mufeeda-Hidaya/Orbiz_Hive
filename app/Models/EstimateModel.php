<?php
namespace App\Models;
use CodeIgniter\Model;

class EstimateModel extends Model
{
    protected $table = 'estimates';
    protected $primaryKey = 'estimate_id';
    protected $allowedFields = [
        'enquiry_id',
        'customer_id',
        'customer_address',
        'discount',
        'total_amount',
        'sub_total',
        'date',
        'phone_number',
        'is_converted',
        'estimate_no',
        'is_deleted'
    ];

    public function getLastEstimateNoByCompany()
    {
        $last = $this
            ->orderBy('estimate_no', 'DESC')
            ->first();
        return $last ? intval($last['estimate_no']) : 0;
    }

    public function insertEstimateWithItems($estimateData, $items)
    {
        if (!isset($estimateData['estimate_no'])) {
            $estimateData['estimate_no'] = $this->getLastEstimateNo() + 1;
        }


        $estimateId = $this->insert($estimateData);
        $itemModel = new \App\Models\EstimateItemModel();

        foreach ($items as $index => $item) {
            $item['estimate_id'] = $estimateId;
            if (!isset($item['item_order']))
                $item['item_order'] = $index + 1;
            $itemModel->insert($item);
        }

        return $estimateId;
    }

    public function updateEstimateWithItems($estimateId, $estimateData, $items)
    {
        $this->update($estimateId, $estimateData);
        $itemModel = new \App\Models\EstimateItemModel();
        $itemModel->where('estimate_id', $estimateId)->delete();

        foreach ($items as $index => $item) {
            $item['estimate_id'] = $estimateId;
            if (!isset($item['item_order']))
                $item['item_order'] = $index + 1;
            $itemModel->insert($item);
        }
    }




    public function getEstimateCount($companyId = 1)
    {
        $builder = $this->db->table('estimates')
            ->join('customers', 'customers.customer_id = estimates.customer_id', 'left')
            // ->where('estimates.company_id', $companyId)
            ->where('estimates.is_deleted', 0);

        return $builder->get()->getNumRows();
    }

    public function getFilteredCount($searchValue)
    {
        $searchValue = trim($searchValue);
        $builder = $this->db->table('estimates')
            ->join('customers', 'customers.customer_id = estimates.customer_id', 'left')
            // ->where('estimates.company_id', $companyId)
            ->where('estimates.is_deleted', 0);

        if (!empty($searchValue)) {
            $normalizedSearch = str_replace(' ', '', strtolower($searchValue));

            $builder->groupStart()
                ->like('customers.name', $searchValue)
                ->orLike('customers.address', $searchValue)
                ->orLike('estimates.estimate_id', $searchValue)
                ->orWhere("REPLACE(REPLACE(REPLACE(LOWER(customers.name), ' ', ''), '\n', ''), '\r', '') LIKE ?", ["%{$normalizedSearch}%"])
                ->orWhere("REPLACE(REPLACE(REPLACE(LOWER(customers.address), ' ', ''), '\n', ''), '\r', '') LIKE ?", ["%{$normalizedSearch}%"])
                ->groupEnd();
        }

        return $builder->get()->getNumRows();
    }



    public function getFilteredEstimates($searchValue, $start, $length, $orderByColumn, $orderDir)
    {
        $searchValue = trim($searchValue);

        $builder = $this->db->table('estimates')
            ->select('estimates.*, customers.name AS customer_name, customers.address AS customer_address')
            ->join('customers', 'customers.customer_id = estimates.customer_id', 'left')
            // ->where('estimates.company_id', $companyId)
            ->where('estimates.is_deleted', 0);

        if (!empty($searchValue)) {
            $normalizedSearch = str_replace(' ', '', strtolower($searchValue));

            $builder->groupStart()
                ->like('customers.name', $searchValue)
                ->orLike('customers.address', $searchValue)
                ->orLike('estimates.estimate_id', $searchValue)

                ->orWhere("REPLACE(REPLACE(REPLACE(LOWER(customers.name), ' ', ''), '\n', ''), '\r', '') LIKE '%{$normalizedSearch}%'", null, false)
                ->orWhere("REPLACE(REPLACE(REPLACE(LOWER(customers.address), ' ', ''), '\n', ''), '\r', '') LIKE '%{$normalizedSearch}%'", null, false)
                ->groupEnd();
        }

        $builder->orderBy($orderByColumn, $orderDir)
            ->limit($length, $start);

        return $builder->get()->getResultArray();
    }

    public function getRecentEstimatesWithCustomer($limit = 5)
    {
        // $companyId = session()->get('company_id');
        return $this->db->table('estimates')
            ->select('estimates.*, customers.name AS customer_name, customers.address AS customer_address')
            ->join('customers', 'customers.customer_id = estimates.customer_id', 'left')
            // ->where('estimates.company_id', $companyId)
            ->where('estimates.is_deleted', 0)
            ->orderBy('estimates.date', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    public function getLastEstimateIdByCompany()
    {
        return $this->select('estimate_id')
            // ->where('company_id', $companyId)
            ->orderBy('estimate_no', 'DESC')
            ->first();
    }

}
