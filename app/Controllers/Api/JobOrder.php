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
                'message' => 'Invalid estimate ID'
            ]);
        }
        $estimate = $this->estimateModel
            ->where('estimate_id', $estimateId)
            ->where('is_deleted', 0)
            ->first();

        if (!$estimate) {
            return $this->respond([
                'status' => false,
                'message' => 'Estimate not found'
            ]);
        }

        $input        = $this->request->getJSON(true);
        $progressList = $input['progress_list'] ?? [];
        $jobOrderId   = $input['joborder_id'] ?? null;
        if ($estimate['is_converted'] == 1 && empty($jobOrderId)) {
            return $this->respond([
                'status' => false,
                'message' => 'Estimate already converted to job order'
            ]);
        }

        $this->db->transBegin();
        $isUpdate = false;
        if ($jobOrderId) {

            $jobOrder = $this->jobOrderModel
                ->where('joborder_id', $jobOrderId)
                ->where('is_deleted', 0)
                ->first();

            if (!$jobOrder) {
                return $this->respond([
                    'status' => false,
                    'message' => 'Job order not found'
                ]);
            }

            $isUpdate = true;

            $this->jobOrderModel->update($jobOrderId, [
                'estimate_id'  => $estimateId,
                'discount'     => $estimate['discount'],
                'sub_total'    => $estimate['sub_total'],
                'total_amount' => $estimate['total_amount'],
                'updated_at'   => date('Y-m-d H:i:s'),
                'updated_by'   => $user['user_id'],
            ]);

        } else {

            $lastNo = $this->jobOrderModel->selectMax('joborder_no')->first();
            $nextNo = ($lastNo && $lastNo['joborder_no']) ? $lastNo['joborder_no'] + 1 : 1;

            $jobOrderId = $this->jobOrderModel->insert([
                'estimate_id'  => $estimateId,
                'user_id'      => $user['user_id'],
                'customer_id'  => $estimate['customer_id'],
                'company_id'   => $user['company_id'],
                'joborder_no'  => $nextNo,
                'discount'     => $estimate['discount'],
                'sub_total'    => $estimate['sub_total'],
                'total_amount' => $estimate['total_amount'],
                'is_converted' => 0,
                'is_deleted'   => 0,
                'created_at'   => date('Y-m-d H:i:s'),
                'created_by'   => $user['user_id'],
            ]);

            if (!$jobOrderId) {
                $this->db->transRollback();
                return $this->respond([
                    'status' => false,
                    'message' => 'Job order creation failed'
                ]);
            }
        }
        $estimateItems = $this->estimateItemModel
            ->select('estimate_items.*, enquiry_items.item_id AS enquiry_item_id')
            ->join(
                'enquiry_items',
                'enquiry_items.item_id = estimate_items.enquiry_item_id',
                'left'
            )
            ->where('estimate_items.estimate_id', $estimateId)
            ->where('estimate_items.status', 1)
            ->findAll();
        if (!$estimateItems) {
            $this->db->transRollback();
            return $this->respond([
                'status' => false,
                'message' => 'No estimate items found'
            ]);
        }

        $savedItems = [];

        foreach ($estimateItems as $item) {
            $progress = '0%';
            foreach ($progressList as $p) {
                if (
                    isset($p['estimate_item_id'], $p['progress']) &&
                    $p['estimate_item_id'] == $item['item_id']
                ) {
                    $progress = $p['progress'];
                    break;
                }
            }
            $existing = $this->jobOrderItemModel
                ->where('joborder_id', $jobOrderId)
                ->where('estimate_item_id', $item['item_id'])
                ->first();

            $jobItemData = [
                'joborder_id'      => $jobOrderId,
                'estimate_item_id' => $item['item_id'], 
                'enquiry_item_id'  => $item['enquiry_item_id'],
                'description'      => $item['description'],
                'quantity'         => $item['quantity'],
                'sub_total'        => $item['total'],
                'discount'         => $estimate['discount'],
                'progress'         => $progress,
                'status'           => 1,
                'updated_at'       => date('Y-m-d H:i:s'),
                'updated_by'       => $user['user_id'],
            ];

            if ($existing) {
                $this->jobOrderItemModel->update(
                    $existing['joborder_item_id'],
                    $jobItemData
                );
            } else {
                $jobItemData['created_at'] = date('Y-m-d H:i:s');
                $jobItemData['created_by'] = $user['user_id'];
                $this->jobOrderItemModel->insert($jobItemData);
            }

            $savedItems[] = $jobItemData;
        }
        $this->estimateModel->update($estimateId, [
            'is_converted' => 1
        ]);

        if (!$this->db->transStatus()) {
            $this->db->transRollback();
            return $this->respond([
                'status' => false,
                'message' => 'Transaction failed'
            ]);
        }

        $this->db->transCommit();

        return $this->respond([
            'status'  => true,
            'message' => $isUpdate
                ? 'Job order updated successfully'
                : 'Job order converted successfully',
            'data'    => [
                'joborder_id'  => $jobOrderId,
                'estimate_id'  => $estimateId,
                'total_amount' => $estimate['total_amount'],
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

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Job Orders fetched successfully.',
            'total'   => $result['total'],
            'data'    => $result['data']
        ]);
    }


    //Progress update
    public function updateJobOrderProgress()
{
    $json = $this->request->getJSON(true);
    $joborderId = $json['joborder_id'] ?? null;
    $progress = $json['progress'] ?? null;

    if (!$joborderId || $progress === null) {
        return $this->response->setJSON([
            'status' => false,
            'message' => 'joborder_id and progress are required'
        ]);
    }

    // Update all joborder_items for that joborder_id
    $builder = $this->db->table('joborder_items');
    $builder->where('joborder_id', $joborderId);
    $updateResult = $builder->update(['progress' => $progress]);

    if ($updateResult) {
        // Optionally update main joborder progress
        $this->db->table('joborder')
            ->where('joborder_id', $joborderId)
            ->update(['updated_at' => date('Y-m-d H:i:s')]);

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Job order progress updated successfully',
            'data' => [
                'joborder_id' => $joborderId,
                'progress' => $progress
            ]
        ]);
    } else {
        return $this->response->setJSON([
            'status' => false,
            'message' => 'Failed to update progress'
        ]);
    }
}





}
