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

    $input = $this->request->getJSON(true);
    $progressList = $input['progress_list'] ?? [];
    $jobOrderId = $input['joborder_id'] ?? null; 
    $this->db->transBegin();

    $isUpdate = false;
    if ($jobOrderId) {
        $existingJobOrder = $this->jobOrderModel
            ->where('joborder_id', $jobOrderId)
            ->where('is_deleted', 0)
            ->first();

        if (!$existingJobOrder) {
            return $this->respond([
                'status' => false,
                'message' => "Job Order ID {$jobOrderId} not found for update."
            ]);
        }

        $isUpdate = true;
        $this->jobOrderModel->update($jobOrderId, [
            'estimate_id'  => $estimateId,
            'discount'     => $estimate['discount'] ?? 0,
            'sub_total'    => $estimate['sub_total'] ?? 0,
            'total_amount' => $estimate['total_amount'] ?? 0,
            'updated_at'   => date('Y-m-d H:i:s'),
            'updated_by'   => $user['user_id'],
        ]);
    } else {
        $lastJobOrderNo = $this->jobOrderModel->selectMax('joborder_no')->first();
        $nextJobOrderNo = isset($lastJobOrderNo['joborder_no'])
            ? ((int)$lastJobOrderNo['joborder_no'] + 1)
            : 1;

        $jobOrderData = [
            'estimate_id'  => $estimateId,
            'user_id'      => $user['user_id'],
            'customer_id'  => $estimate['customer_id'] ?? null,
            'company_id'   => $user['company_id'],
            'joborder_no'  => $nextJobOrderNo,
            'discount'     => $estimate['discount'] ?? 0,
            'sub_total'    => $estimate['sub_total'] ?? 0,
            'total_amount' => $estimate['total_amount'] ?? 0,
            'is_converted' => 1,
            'is_deleted'   => 0,
            'created_at'   => date('Y-m-d H:i:s'),
            'created_by'   => $user['user_id'],
        ];

        $jobOrderId = $this->jobOrderModel->insert($jobOrderData);
        if (!$jobOrderId) {
            $this->db->transRollback();
            return $this->respond(['status' => false, 'message' => 'Failed to create job order.']);
        }
    }
    $estimateItems = $this->estimateItemModel->getItemsByEstimateId($estimateId);
    if (empty($estimateItems)) {
        $this->db->transRollback();
        return $this->respond(['status' => false, 'message' => 'No items found for this estimate.']);
    }

    $savedItems = [];
    foreach ($estimateItems as $item) {
        $itemProgress = '0%';
        foreach ($progressList as $progressData) {
            if (
                isset($progressData['item_id'], $progressData['progress']) &&
                $progressData['item_id'] == $item['item_id']
            ) {
                $itemProgress = $progressData['progress'];
                break;
            }
        }
        $existingItem = $this->jobOrderItemModel
            ->where('joborder_id', $jobOrderId)
            ->where('item_id', $item['item_id'])
            ->first();

        $itemData = [
            'joborder_id'           => $jobOrderId,
            'item_id'               => $item['item_id'],
            'description'           => $item['description'],
            'quantity'              => $item['quantity'],
            'market_price'          => $item['market_price'],
            'selling_price'         => $item['selling_price'],
            'difference_percentage' => $item['difference_percentage'],
            'sub_total'             => $item['quantity'] * $item['selling_price'],
            'discount'              => $estimate['discount'] ?? 0,
            'progress'              => $itemProgress,
            'status'                => 1,
            'updated_at'            => date('Y-m-d H:i:s'),
            'updated_by'            => $user['user_id'],
        ];

        if ($existingItem) {
            $this->jobOrderItemModel->update($existingItem['joborder_item_id'], $itemData);
        } else {
            $itemData['created_at'] = date('Y-m-d H:i:s');
            $itemData['created_by'] = $user['user_id'];
            $this->jobOrderItemModel->insert($itemData);
        }

        $savedItems[] = $itemData;
    }
    $this->estimateModel->update($estimateId, ['is_converted' => 1]);

    if ($this->db->transStatus() === false) {
        $this->db->transRollback();
        return $this->respond(['status' => false, 'message' => 'Transaction failed.']);
    }

    $this->db->transCommit();
    $message = $isUpdate
        ? "Job Order ID {$jobOrderId} successfully updated."
        : "Estimate ID {$estimateId} successfully converted into a Job Order.";

    return $this->respond([
        'status'  => true,
        'message' => $message,
        'data'    => [
            'joborder_id'  => $jobOrderId,
            'estimate_id'  => $estimateId,
            'customer_id'  => $estimate['customer_id'],
            'total_amount' => $estimate['total_amount'],
            'sub_total'    => $estimate['sub_total'],
            'items'        => $savedItems
        ]
    ]);
}

    public function getAllJobOrders()
    {
        $authHeader = AuthHelper::getAuthorizationToken($this->request);
        $user = $this->authService->getAuthenticatedUser($authHeader);

        if (!$user) {
            return $this->failUnauthorized('Invalid or missing token.');
        }

        $pageIndex = (int) $this->request->getGet('pageIndex');
        $pageSize  = (int) $this->request->getGet('pageSize');
        $search    = $this->request->getGet('search');

        if ($pageSize <= 0) $pageSize = 10;
        $offset = $pageIndex * $pageSize;

        $result = $this->jobOrderModel->getAllJobOrders(
            $user['company_id'],
            $pageSize,
            $offset,
            $search
        );
        foreach ($result['data'] as &$jobOrder) {
            $jobOrder['items'] = $this->jobOrderItemModel
                ->where('joborder_id', $jobOrder['joborder_id'])
                ->findAll();
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Job Orders fetched successfully.',
            'total'   => $result['total'],
            'data'    => $result['data']
        ]);
    }





}
