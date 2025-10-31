<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Controllers\BaseController;
use App\Models\Api\EnquiryModel;
use App\Models\Api\EstimateModel;
use App\Models\Api\EstimateItemModel;
use App\Models\Api\JobOrderModel;
use App\Models\Api\JobOrderItemModel;
use App\Models\EnquiryItemModel;
use App\Models\Manageuser_Model;
use App\Models\customerModel;
use App\Libraries\AuthService;
use App\Helpers\AuthHelper;

class JobOrder extends ResourceController
{
    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = \Config\Services::session();
        $this->input = \Config\Services::request();
        $this->userModel = new Manageuser_Model();
        $this->customerModel = new customerModel();
        $this->enquiryModel = new EnquiryModel();
        $this->estimateModel = new EstimateModel();
        $this->estimateItemModel = new EstimateItemModel();
        $this->jobOrderModel = new JobOrderModel();
        $this->jobOrderItemModel = new JobOrderItemModel();
        $this->enquiryItemModel = new EnquiryItemModel();
        $this->authService = new AuthService();
    }

    public function convertToJobOrder($estimateId = null)
{
    $authHeader = AuthHelper::getAuthorizationToken($this->request);
    $user = $this->authService->getAuthenticatedUser($authHeader);

    if (!$user) {
        return $this->failUnauthorized('Invalid or missing token.');
    }

    if (empty($estimateId) || !is_numeric($estimateId)) {
        return $this->respond([
            'status' => false,
            'message' => 'Invalid or missing estimate ID.'
        ]);
    }

    $estimate = $this->estimateModel->getDetails($estimateId);
    if (!$estimate) {
        return $this->respond([
            'status' => false,
            'message' => 'Estimate not found.'
        ]);
    }

    if ($estimate['is_deleted'] == 1) {
        return $this->respond([
            'status' => false,
            'message' => "Estimate ID {$estimateId} is deleted and cannot be converted."
        ]);
    }

    if ($estimate['is_converted'] == 1) {
        return $this->respond([
            'status' => false,
            'message' => "Estimate ID {$estimateId} is already converted."
        ]);
    }

    // Get next joborder number
    $lastJobOrderNo = $this->jobOrderModel->selectMax('joborder_no')->first();
    $nextJobOrderNo = isset($lastJobOrderNo['joborder_no'])
        ? ((int)$lastJobOrderNo['joborder_no'] + 1)
        : 1;

    $progress = $this->request->getVar('progress') ?? '0%';

    $this->db->transBegin();

    // Insert into joborder (main table)
    $jobOrderData = [
        'estimate_id'  => $estimateId,
        'user_id'      => $user['user_id'],
        'customer_id'  => $estimate['customer_id'] ?? null,
        'company_id'   => $user['company_id'],
        'joborder_no'  => $nextJobOrderNo,
        'progress'     => $progress,
        'discount'     => $estimate['discount'] ?? 0,
        'sub_total'    => $estimate['sub_total'] ?? 0,
        'total_amount' => $estimate['total_amount'] ?? 0,
        'is_converted' => 0,
        'is_deleted'   => 0,
        'created_at'   => date('Y-m-d H:i:s'),
        'created_by'   => $user['user_id'],
    ];

    $jobOrderId = $this->jobOrderModel->insert($jobOrderData);

    if (!$jobOrderId) {
        $this->db->transRollback();
        return $this->respond(['status' => false, 'message' => 'Failed to create job order.']);
    }

    // Get estimate items
    $estimateItems = $this->estimateItemModel->getItemsByEstimateId($estimateId);

    if (empty($estimateItems)) {
        $this->db->transRollback();
        return $this->respond(['status' => false, 'message' => 'No items found for this estimate.']);
    }

    $savedItems = [];
    foreach ($estimateItems as $item) {
        $itemProgress = $item['progress'] ?? $progress; // item-wise progress if exists

        $itemData = [
            'joborder_id'           => $jobOrderId,
            'estimate_item_id'       => $item['estimate_item_id'] ?? null,
            'item_id'               => $item['item_id'],
            'description'           => $item['description'],
            'quantity'              => $item['quantity'],
            'unit'                  => $item['unit'] ?? null,
            'market_price'          => $item['market_price'],
            'selling_price'         => $item['selling_price'],
            'difference_percentage' => $item['difference_percentage'],
            'sub_total'             => $item['quantity'] * $item['selling_price'],
            'discount'              => $estimate['discount'] ?? 0,
            'progress'              => $itemProgress,
            'status'                => 1,
            'created_at'            => date('Y-m-d H:i:s'),
            'created_by'            => $user['user_id'],
        ];

        $inserted = $this->jobOrderItemModel->insert($itemData);
        if (!$inserted) {
            $this->db->transRollback();
            return $this->respond(['status' => false, 'message' => 'Failed to insert job order item.']);
        }

        $savedItems[] = $itemData;
    }

    // Mark estimate as converted
    $this->estimateModel->update($estimateId, ['is_converted' => 1]);

    if ($this->db->transStatus() === false) {
        $this->db->transRollback();
        return $this->respond(['status' => false, 'message' => 'Transaction failed.']);
    }

    $this->db->transCommit();

    return $this->respond([
        'status'  => true,
        'message' => "Estimate ID {$estimateId} successfully converted into a Job Order.",
        'data'    => [
            'joborder_id'  => $jobOrderId,
            'joborder_no'  => $nextJobOrderNo,
            'estimate_id'  => $estimateId,
            'customer_id'  => $estimate['customer_id'],
            'progress'     => $progress,
            'total_amount' => $estimate['total_amount'],
            'sub_total'    => $estimate['sub_total'],
            'items'        => $savedItems
        ]
    ]);
}


}
