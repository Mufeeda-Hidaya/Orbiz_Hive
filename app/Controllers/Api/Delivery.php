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
            'status' => false,
            'message' => 'Invalid or missing job order ID.'
        ]);
    }

    $jobOrder = $this->jobOrderModel->find($joborderId);
    if (!$jobOrder) {
        return $this->respond([
            'status' => false,
            'message' => 'Job Order not found.'
        ]);
    }

    if ($jobOrder['is_deleted'] == 1) {
        return $this->respond([
            'status' => false,
            'message' => "Job Order ID {$joborderId} is deleted and cannot be converted."
        ]);
    }

    $this->deliveryModel = new DeliveryModel();
    $this->deliveryItemModel = new DeliveryItemModel();

    $this->db->transBegin();

    // Generate next delivery number
    $lastDelivery = $this->deliveryModel->selectMax('delivery_no')->first();
    $nextDeliveryNo = isset($lastDelivery['delivery_no']) ? ((int)$lastDelivery['delivery_no'] + 1) : 1;

    // Insert into Delivery table
    $deliveryData = [
        'joborder_id' => $joborderId,
        'user_id'     => $user['user_id'],
        'customer_id' => $jobOrder['customer_id'],
        'company_id'  => $user['company_id'],
        'delivery_no' => $nextDeliveryNo,
        'discount'    => $jobOrder['discount'],
        'sub_total'   => $jobOrder['sub_total'],
        'total_amount'=> $jobOrder['total_amount'],
        'is_converted'=> 1,
        'is_deleted'  => 0,
        'created_at'  => date('Y-m-d H:i:s'),
        'created_by'  => $user['user_id'],
    ];

    $deliveryId = $this->deliveryModel->insert($deliveryData);

    if (!$deliveryId) {
        $this->db->transRollback();
        return $this->respond(['status' => false, 'message' => 'Failed to create delivery record.']);
    }

    // Fetch job order items
    $jobOrderItems = $this->jobOrderItemModel->where('joborder_id', $joborderId)->findAll();

    if (empty($jobOrderItems)) {
        $this->db->transRollback();
        return $this->respond(['status' => false, 'message' => 'No items found for this job order.']);
    }

    // Copy items
    foreach ($jobOrderItems as $item) {
        $this->deliveryItemModel->insert([
            'delivery_id'   => $deliveryId,
            'item_id'       => $item['item_id'],
            'description'   => $item['description'],
            'quantity'      => $item['quantity'],
            'selling_price' => $item['selling_price'],
            'sub_total'     => $item['quantity'] * $item['selling_price'],
            'discount'      => $jobOrder['discount'],
            'status'        => 1,
            'created_at'    => date('Y-m-d H:i:s'),
            'created_by'    => $user['user_id'],
        ]);
    }

    // Mark job order as converted
    $this->jobOrderModel->update($joborderId, ['is_converted' => 1]);

    if ($this->db->transStatus() === false) {
        $this->db->transRollback();
        return $this->respond(['status' => false, 'message' => 'Transaction failed.']);
    }

    $this->db->transCommit();

    return $this->respond([
        'status'  => true,
        'message' => "Job Order ID {$joborderId} successfully converted into Delivery.",
        'data'    => [
            'delivery_id'  => $deliveryId,
            'joborder_id'  => $joborderId,
            'customer_id'  => $jobOrder['customer_id'],
            'total_amount' => $jobOrder['total_amount'],
            'sub_total'    => $jobOrder['sub_total']
        ]
    ]);
}


// public function markAsDelivered($deliveryId = null)
// {
//     $authHeader = \App\Helpers\AuthHelper::getAuthorizationToken($this->request);
//     $user = $this->authService->getAuthenticatedUser($authHeader);

//     if (!$user) {
//         return $this->failUnauthorized('Invalid or missing token.');
//     }

//     if (empty($deliveryId) || !is_numeric($deliveryId)) {
//         return $this->respond([
//             'status' => false,
//             'message' => 'Invalid or missing delivery ID.'
//         ]);
//     }

//     $this->deliveryModel = new DeliveryModel();
//     $this->deliveryItemModel = new DeliveryItemModel();

//     $delivery = $this->deliveryModel->find($deliveryId);

//     if (!$delivery || $delivery['is_deleted'] == 1) {
//         return $this->respond([
//             'status' => false,
//             'message' => "Delivery ID {$deliveryId} not found or deleted."
//         ]);
//     }

//     // Optional: Allow partial delivery by receiving delivered item IDs
//     $input = $this->request->getJSON(true);
//     $deliveredItems = $input['delivered_items'] ?? []; // e.g. [1, 2, 3]

//     $this->db->transBegin();

//     if (!empty($deliveredItems)) {
//         // Mark specific items as delivered
//         foreach ($deliveredItems as $itemId) {
//             $this->deliveryItemModel->update($itemId, [
//                 'delivery_status' => 'delivered',
//                 'delivered_at' => date('Y-m-d H:i:s'),
//                 'updated_by' => $user['user_id'],
//             ]);
//         }

//         // Check if all items are delivered → mark delivery as delivered
//         $pendingCount = $this->deliveryItemModel
//             ->where('delivery_id', $deliveryId)
//             ->where('delivery_status !=', 'delivered')
//             ->countAllResults();

//         if ($pendingCount == 0) {
//             $this->deliveryModel->update($deliveryId, [
//                 'delivery_status' => 'delivered',
//                 'delivered_at' => date('Y-m-d H:i:s'),
//                 'updated_by' => $user['user_id'],
//             ]);
//         }

//     } else {
//         // Mark entire delivery as delivered
//         $this->deliveryModel->update($deliveryId, [
//             'delivery_status' => 'delivered',
//             'delivered_at' => date('Y-m-d H:i:s'),
//             'updated_by' => $user['user_id'],
//         ]);

//         // Mark all items under it as delivered
//         $this->deliveryItemModel
//             ->where('delivery_id', $deliveryId)
//             ->set([
//                 'delivery_status' => 'delivered',
//                 'delivered_at' => date('Y-m-d H:i:s'),
//                 'updated_by' => $user['user_id'],
//             ])
//             ->update();
//     }

//     if ($this->db->transStatus() === false) {
//         $this->db->transRollback();
//         return $this->respond(['status' => false, 'message' => 'Failed to mark as delivered.']);
//     }

//     $this->db->transCommit();

//     return $this->respond([
//         'status' => true,
//         'message' => "Delivery ID {$deliveryId} marked as delivered successfully.",
//         'data' => [
//             'delivery_id' => $deliveryId,
//             'delivered_at' => date('Y-m-d H:i:s')
//         ]
//     ]);
// }

public function markAsDelivered($deliveryId = null)
{
    $authHeader = \App\Helpers\AuthHelper::getAuthorizationToken($this->request);
    $user = $this->authService->getAuthenticatedUser($authHeader);

    if (!$user) {
        return $this->failUnauthorized('Invalid or missing token.');
    }

    if (empty($deliveryId) || !is_numeric($deliveryId)) {
        return $this->respond([
            'status' => false,
            'message' => 'Invalid or missing delivery ID.'
        ]);
    }

    $this->deliveryModel = new DeliveryModel();
    $this->deliveryItemModel = new DeliveryItemModel();

    $delivery = $this->deliveryModel->find($deliveryId);

    if (!$delivery || $delivery['is_deleted'] == 1) {
        return $this->respond([
            'status' => false,
            'message' => "Delivery ID {$deliveryId} not found or deleted."
        ]);
    }

    // ✅ Get JSON payload
    $input = $this->request->getJSON(true);
    $deliveredItems = $input['delivered_items'] ?? []; // e.g. [1, 2, 3]
    $deliveredAt = !empty($input['delivered_at']) ? $input['delivered_at'] : date('Y-m-d H:i:s');

    $this->db->transBegin();

    if (!empty($deliveredItems)) {
        // ✅ Partial delivery
        foreach ($deliveredItems as $itemId) {
            $this->deliveryItemModel->update($itemId, [
                'delivery_status' => 'delivered',
                'delivered_at' => $deliveredAt,
                'updated_by' => $user['user_id'],
            ]);
        }

        // ✅ Check if all items are now delivered
        $pendingCount = $this->deliveryItemModel
            ->where('delivery_id', $deliveryId)
            ->where('delivery_status !=', 'delivered')
            ->countAllResults();

        if ($pendingCount == 0) {
            $this->deliveryModel->update($deliveryId, [
                'delivery_status' => 'delivered',
                'delivered_at' => $deliveredAt,
                'updated_by' => $user['user_id'],
            ]);
        }

    } else {
        // ✅ Full delivery
        $this->deliveryModel->update($deliveryId, [
            'delivery_status' => 'delivered',
            'delivered_at' => $deliveredAt,
            'updated_by' => $user['user_id'],
        ]);

        $this->deliveryItemModel
            ->where('delivery_id', $deliveryId)
            ->set([
                'delivery_status' => 'delivered',
                'delivered_at' => $deliveredAt,
                'updated_by' => $user['user_id'],
            ])
            ->update();
    }

    if ($this->db->transStatus() === false) {
        $this->db->transRollback();
        return $this->respond(['status' => false, 'message' => 'Failed to mark as delivered.']);
    }

    $this->db->transCommit();

    return $this->respond([
        'status' => true,
        'message' => "Delivery ID {$deliveryId} marked as delivered successfully.",
        'data' => [
            'delivery_id' => $deliveryId,
            'delivered_at' => $deliveredAt
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

        // Attach delivery items if available
        foreach ($result['data'] as &$delivery) {
            $delivery['items'] = $this->deliveryItemModel
                ->where('delivery_id', $delivery['delivery_id'])
                ->findAll();
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Deliveries fetched successfully.',
            'total'   => $result['total'],
            'data'    => $result['data']
        ]);
    }

}