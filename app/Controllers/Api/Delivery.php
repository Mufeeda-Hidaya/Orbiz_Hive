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
use App\Models\Api\DeliveryModel;
use App\Models\Api\DeliveryItemModel;
use App\Libraries\AuthService;
use App\Helpers\AuthHelper;

class Delivery extends ResourceController
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
        $this->deliveryModel =new DeliveryModel();
        $this->deliveryItemModel = new DeliveryItemModel();
        $this->authService = new AuthService();
    }

    public function convertToDelivery($joborderId = null)
    {
        $authHeader = AuthHelper::getAuthorizationToken($this->request);
        $user = $this->authService->getAuthenticatedUser($authHeader);

        if (!$user) {
            return $this->failUnauthorized('Invalid or missing token.');
        }
        if (empty($joborderId) || !is_numeric($joborderId)) {
            return $this->respond([
                'status'  => false,
                'message' => 'Invalid or missing job order ID.'
            ]);
        }
        $jobOrder = $this->jobOrderModel->find($joborderId);
        if (!$jobOrder) {
            return $this->respond([
                'status'  => false,
                'message' => 'Job Order not found.'
            ]);
        }
        if ($jobOrder['is_deleted'] == 1) {
            return $this->respond([
                'status'  => false,
                'message' => "Job Order ID {$joborderId} is deleted and cannot be converted."
            ]);
        }
        if ($jobOrder['is_converted'] == 1) {
            return $this->respond([
                'status'  => false,
                'message' => 'This Job Order is already converted to Delivery.'
            ]);
        }

        $this->deliveryModel     = new DeliveryModel();
        $this->deliveryItemModel = new DeliveryItemModel();
        $this->db->transBegin();
        $lastDelivery = $this->deliveryModel->selectMax('delivery_no')->first();
        $nextDeliveryNo = isset($lastDelivery['delivery_no'])
            ? ((int)$lastDelivery['delivery_no'] + 1)
            : 1;
        $deliveryData = [
            'joborder_id'  => $joborderId,
            'user_id'      => $user['user_id'],
            'customer_id'  => $jobOrder['customer_id'],
            'company_id'   => $user['company_id'],
            'delivery_no'  => $nextDeliveryNo,
            'discount'     => $jobOrder['discount'],
            'sub_total'    => $jobOrder['sub_total'],
            'total_amount' => $jobOrder['total_amount'],
            'is_converted' => 0, 
            'is_deleted'   => 0,
            'created_at'   => date('Y-m-d H:i:s'),
            'created_by'   => $user['user_id'],
        ];

        $deliveryId = $this->deliveryModel->insert($deliveryData);
        if (!$deliveryId) {
            $this->db->transRollback();
            return $this->respond([
                'status'  => false,
                'message' => 'Failed to create delivery record.'
            ]);
        }
        $jobOrderItems = $this->jobOrderItemModel
            ->where('joborder_id', $joborderId)
            ->findAll();

        if (empty($jobOrderItems)) {
            $this->db->transRollback();
            return $this->respond([
                'status'  => false,
                'message' => 'No items found for this job order.'
            ]);
        }
        foreach ($jobOrderItems as $item) {
            $this->deliveryItemModel->insert([
                'delivery_id'      => $deliveryId,
                'joborder_item_id' => $item['joborder_item_id'],
                'description'      => $item['description'],
                'quantity'         => $item['quantity'],
                'sub_total'        => $item['sub_total'],
                'discount'         => $jobOrder['discount'],
                'status'           => 1,
                'delivery_status'  => 'pending',
                'created_at'       => date('Y-m-d H:i:s'),
                'created_by'       => $user['user_id'],
            ]);
        }
        $this->jobOrderModel->update($joborderId, [
            'is_converted' => 1
        ]);
        if ($this->db->transStatus() === false) {
            $this->db->transRollback();
            return $this->respond([
                'status'  => false,
                'message' => 'Transaction failed.'
            ]);
        }
        $this->db->transCommit();

        return $this->respond([
            'status'  => true,
            'message' => "Job Order ID {$joborderId} successfully converted into Delivery.",
            'data'    => [
                'delivery_id'  => $deliveryId,
                'joborder_id'  => $joborderId,
                'customer_id'  => $jobOrder['customer_id'],
                'sub_total'    => $jobOrder['sub_total'],
                'total_amount' => $jobOrder['total_amount']
            ]
        ]);
    }

    public function markAsDelivered($deliveryId = null)
    {
        $authHeader = AuthHelper::getAuthorizationToken($this->request);
        $user = $this->authService->getAuthenticatedUser($authHeader);

        if (!$user) {
            return $this->failUnauthorized('Invalid or missing token.');
        }

        if (empty($deliveryId) || !is_numeric($deliveryId)) {
            return $this->respond([
                'status'  => false,
                'message' => 'Invalid or missing delivery ID.'
            ]);
        }

        $this->deliveryModel     = new DeliveryModel();
        $this->deliveryItemModel = new DeliveryItemModel();

        $delivery = $this->deliveryModel->find($deliveryId);

        if (!$delivery || $delivery['is_deleted'] == 1) {
            return $this->respond([
                'status'  => false,
                'message' => "Delivery ID {$deliveryId} not found or deleted."
            ]);
        }

        $input         = $this->request->getJSON(true);
        $deliveredItems = $input['delivered_items'] ?? [];
        $deliveredAt   = !empty($input['delivered_at'])
            ? $input['delivered_at']
            : date('Y-m-d H:i:s');

        $this->db->transBegin();

        //  PARTIAL DELIVERY
        if (!empty($deliveredItems)) {
            foreach ($deliveredItems as $itemId) {
                $this->deliveryItemModel->update($itemId, [
                    'delivery_status' => 'delivered',
                    'delivered_at'    => $deliveredAt,
                    'updated_by'      => $user['user_id'],
                ]);
            }
        } 
        //  FULL DELIVERY (NO ITEMS PROVIDED)
        else {
            $this->deliveryItemModel
                ->where('delivery_id', $deliveryId)
                ->set([
                    'delivery_status' => 'delivered',
                    'delivered_at'    => $deliveredAt,
                    'updated_by'      => $user['user_id'],
                ])
                ->update();
        }

        //  Check if any items are still pending
        $pendingCount = $this->deliveryItemModel
            ->where('delivery_id', $deliveryId)
            ->where('delivery_status !=', 'delivered')
            ->countAllResults();

        //  If all items delivered → update delivery
        if ($pendingCount == 0) {
            $this->deliveryModel->update($deliveryId, [
                'delivery_status' => 'delivered',
                'delivered_at'    => $deliveredAt,
                'is_converted'    => 1,
                'updated_by'      => $user['user_id'],
            ]);
        }

        if ($this->db->transStatus() === false) {
            $this->db->transRollback();
            return $this->respond([
                'status'  => false,
                'message' => 'Failed to mark as delivered.'
            ]);
        }

        $this->db->transCommit();

        return $this->respond([
            'status'  => true,
            'message' => "Delivery ID {$deliveryId} marked as delivered successfully.",
            'data'    => [
                'delivery_id'  => $deliveryId,
                'delivered_at' => $deliveredAt,
                'is_converted' => ($pendingCount == 0) ? 1 : 0
            ]
        ]);
    }

    public function getAllDeliveries()
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

        $result = $this->deliveryModel->getAllDeliveries(
            $user['company_id'],
            $pageSize,
            $offset,
            $search
        );
        foreach ($result['data'] as &$delivery) {
            $delivery['items'] = $this->deliveryItemModel
                ->getItemsWithImages($delivery['delivery_id']);
        }
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Deliveries fetched successfully.',
            'total'   => $result['total'],
            'data'    => $result['data']
        ]);
    }

}