<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Controllers\BaseController;
use App\Models\Api\EnquiryModel;
use App\Models\Api\EstimateModel;
use App\Models\Api\EstimateItemModel;
use App\Models\EnquiryItemModel;
use App\Models\Manageuser_Model;
use App\Models\customerModel;
use App\Libraries\AuthService;
use App\Helpers\AuthHelper;
use Config\Database;

class Estimate extends ResourceController
{
    protected $db;
    protected $authService;
    protected $enquiryModel;
    protected $estimateModel;
    protected $estimateItemModel;
    protected $enquiryItemModel;

    public function __construct()
    {
        $this->db = Database::connect(); 
        $this->session = \Config\Services::session();
        $this->input = \Config\Services::request();

        $this->userModel = new Manageuser_Model();
        $this->customerModel = new customerModel();
        $this->enquiryModel = new EnquiryModel();
        $this->estimateModel = new EstimateModel();
        $this->estimateItemModel = new EstimateItemModel();
        $this->enquiryItemModel = new EnquiryItemModel();
        $this->authService = new AuthService();
    }

    public function convertToEstimate($enquiryId = null)
    {
        $authHeader = AuthHelper::getAuthorizationToken($this->request);
        $user = $this->authService->getAuthenticatedUser($authHeader);

        if (!$user) {
            return $this->failUnauthorized('Invalid or missing token.');
        }

        if (empty($enquiryId) || !is_numeric($enquiryId)) {
            return $this->respond([
                'status' => false,
                'message' => 'Invalid or missing enquiry ID.'
            ]);
        }

        $enquiry = $this->enquiryModel->getDetails($enquiryId);
        if (!$enquiry) {
            return $this->respond([
                'status' => false,
                'message' => 'Enquiry not found.'
            ]);
        }

        if ($enquiry['is_deleted'] == 1) {
            return $this->respond([
                'status' => false,
                'message' => "Enquiry ID {$enquiryId} is deleted and cannot be converted into an estimate."
            ]);
        }

        if ($enquiry['is_converted'] == 1) {
            return $this->respond([
                'status' => false,
                'message' => "Enquiry ID {$enquiryId} is already converted to an estimate."
            ]);
        }
        $lastEstimateNo = $this->estimateModel
            ->selectMax('estimate_no')
            ->first();

        $nextEstimateNo = isset($lastEstimateNo['estimate_no'])
            ? ((int)$lastEstimateNo['estimate_no'] + 1)
            : 1;
        $this->db->transBegin();

        $estimateData = [
            'enquiry_id'       => $enquiryId,
            'user_id'          => $user['user_id'],
            'customer_id'      => $enquiry['customer_id'] ?? null,
            'customer_address' => $enquiry['customer_address'] ?? '',
            'discount'         => 0,
            'total_amount'     => 0,
            'sub_total'        => 0,
            'date'             => date('Y-m-d H:i:s'),
            'phone_number'     => $enquiry['customer_phone'] ?? '',
            'is_converted'     => 0,
            'company_id'       => $user['company_id'],
            'estimate_no'      => $nextEstimateNo,
        ];

        $estimateId = $this->estimateModel->insert($estimateData);
        if (!$estimateId) {
            $this->db->transRollback();
            return $this->respond(['status' => false, 'message' => 'Failed to create estimate.']);
        }
        $enquiryItems = $this->enquiryItemModel->getItemsByEnquiryId($enquiryId);
        $payloadItems = json_decode(json_encode($this->request->getVar('items')), true);

        $totalAmount = 0;
        $savedItems = [];

        foreach ($enquiryItems as $item) {
            $itemId = $item['item_id'];
            $inputItem = null;

            foreach ($payloadItems as $pi) {
                if ($pi['item_id'] == $itemId) {
                    $inputItem = $pi;
                    break;
                }
            }

            if (!$inputItem) continue;

            $marketPrice  = isset($inputItem['market_price']) ? (float)$inputItem['market_price'] : 0;
            $sellingPrice = isset($inputItem['selling_price']) ? (float)$inputItem['selling_price'] : 0;
            $difference   = isset($inputItem['difference']) ? (float)$inputItem['difference'] : 0;

            $estimateItemData = [
                'estimate_id'           => $estimateId,
                'description'           => $item['description'],
                'market_price'          => $marketPrice,
                'selling_price'         => $sellingPrice,
                'difference_percentage' => $difference,
                'quantity'              => $item['quantity'],
                'status'                => 1,
                'total'                 => $sellingPrice * $item['quantity'],
                'item_order'            => 1,
            ];

            $inserted = $this->estimateItemModel->insert($estimateItemData);
            if (!$inserted) {
                $this->db->transRollback();
                return $this->respond(['status' => false, 'message' => 'Failed to add item to estimate.']);
            }

            $totalAmount += $estimateItemData['total'];
            $savedItems[] = $estimateItemData;
        }

        $this->estimateModel->update($estimateId, [
            'total_amount' => $totalAmount,
            'sub_total'    => $totalAmount
        ]);

        $this->enquiryModel->update($enquiryId, ['is_converted' => 1]);

        if ($this->db->transStatus() === false) {
            $this->db->transRollback();
            return $this->respond(['status' => false, 'message' => 'Database transaction failed.']);
        }

        $this->db->transCommit();

        return $this->respond([
            'status'  => true,
            'message' => "Enquiry ID {$enquiryId} successfully converted into an estimate.",
            'data'    => [
                'estimate_id'       => $estimateId,
                'estimate_no'       => $nextEstimateNo,
                'user_id'           => $user['user_id'], 
                'enquiry_id'        => $enquiryId,
                'customer_id'       => $enquiry['customer_id'],
                'customer_name'     => $enquiry['customer_name'],
                'customer_address'  => $enquiry['customer_address'],
                'total_amount'      => $totalAmount,
                'items'             => $savedItems
            ]
        ]);
    }
     public function getAllEstimates()
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

        $result = $this->estimateModel->getAllEstimates($user['company_id'], $pageSize, $offset, $search);

        foreach ($result['data'] as &$estimate) {
            $estimate['items'] = $this->estimateItemModel->getItemsByEstimateId($estimate['estimate_id']);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Estimates fetched successfully.',
            'total'   => $result['total'],
            'data'    => $result['data']
        ]);
    }
    public function getEstimateById($id)
    {
        $authHeader = AuthHelper::getAuthorizationToken($this->request);
        $user = $this->authService->getAuthenticatedUser($authHeader);
        if (!$user) {
            return $this->failUnauthorized('Invalid or missing token.');
        }

        $estimate = $this->estimateModel->getEstimateById($id, $user['company_id']);
        if (!$estimate) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Estimate not found.'
            ]);
        }

        $estimate['items'] = $this->estimateItemModel->getItemsByEstimateId($estimate['estimate_id']);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Estimate fetched successfully.',
            'data'    => $estimate
        ]);
    }

    public function deleteEstimate($id)
    {
        $authHeader = AuthHelper::getAuthorizationToken($this->request);
        $user = $this->authService->getAuthenticatedUser($authHeader);
        if (!$user) {
            return $this->failUnauthorized('Invalid or missing token.');
        }

        $estimate = $this->estimateModel->find($id);
        if (!$estimate) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Estimate not found.'
            ]);
        }

        if ($estimate['is_deleted'] == 1) {
            return $this->response->setJSON([
                'success' => false,
                'message' =>"Estimate {$id} already deleted."
            ]);
        }

        $this->estimateModel->update($id, [
            'is_deleted' => 1,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => "Estimate {$id} deleted successfully."
        ]);
    }
}