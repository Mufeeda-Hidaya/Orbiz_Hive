<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Controllers\BaseController;
use App\Models\Api\EnquiryModel;
use App\Models\Api\EstimateModel;
use App\Models\Api\EstimateItemModel;
use App\Models\Api\SettingsModel;
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
        $this->settingsModel = new SettingsModel();
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
            return $this->respond(['status' => false, 'message' => 'Invalid enquiry ID']);
        }

        // Fetch enquiry
        $enquiry = $this->enquiryModel->where('enquiry_id', $enquiryId)->first();

        if (!$enquiry)
            return $this->respond(['status' => false, 'message' => 'Enquiry not found']);

        if ($enquiry['is_deleted'])
            return $this->respond(['status' => false, 'message' => "Enquiry deleted"]);

        $estimateId = $this->request->getVar('estimate_id');

        if (!$estimateId && $enquiry['is_converted']) {
            return $this->respond(['status'=>false,'message'=>"Already converted"]);
        }

        // Fetch settings
        $settings = $this->settingsModel->where('company_id', $user['company_id'])->first();

        if (!$settings)
            return $this->respond(['status' => false, 'message' => 'Settings not found']);

        $defaultGP = floatval($settings['gp_percentage']);
        $defaultLabourRate = floatval($settings['labour_rate']);

        $this->db->transBegin();

        if ($estimateId) 
        {
            $existing = $this->estimateModel->find($estimateId);

            if (!$existing) {
                return $this->respond(['status' => false, 'message' => 'Estimate not found']);
            }

            // Update only basic fields
            $this->estimateModel->update($estimateId, [
                'customer_id'      => $enquiry['customer_id'] ?? null,
                'customer_address' => $enquiry['address'] ?? '',
                'phone_number'     => $enquiry['phone'] ?? '',
                'company_id'       => $user['company_id']
            ]);

            // Delete old items
            $this->estimateItemModel->where('estimate_id', $estimateId)->delete();

        } 
        else 
        {
            $last = $this->estimateModel->selectMax('estimate_no')->first();
            $nextEstimateNo = ($last && $last['estimate_no']) ? $last['estimate_no'] + 1 : 1;

            $estimateData = [
                'enquiry_id'       => $enquiryId,
                'user_id'          => $user['user_id'],
                'customer_id'      => $enquiry['customer_id'] ?? null,
                'customer_address' => $enquiry['address'] ?? '',
                'phone_number'     => $enquiry['phone'] ?? '',
                'discount'         => 0,
                'total_amount'     => 0,
                'sub_total'        => 0,
                'date'             => date('Y-m-d H:i:s'),
                'is_converted'     => 0,
                'company_id'       => $user['company_id'],
                'estimate_no'      => $nextEstimateNo
            ];

            $this->estimateModel->insert($estimateData);
            $estimateId = $this->estimateModel->getInsertID();

            if (!$estimateId) {
                $this->db->transRollback();
                return $this->respond(['status' => false, 'message' => 'Estimate creation failed']);
            }

            $this->enquiryModel->update($enquiryId, ['is_converted' => 1]);
        }
        $payloadItems = $this->request->getVar('items');
        $payloadItems = json_decode(json_encode($payloadItems), true);

        $enquiryItems = $this->enquiryItemModel
            ->where('enquiry_id', $enquiryId)
            ->findAll();

        $totalAmount = 0;
        $savedItems = [];

        foreach ($enquiryItems as $item) {

            $inputItem = null;
            foreach ($payloadItems as $pi) {
                if ($pi['item_id'] == $item['item_id']) {
                    $inputItem = $pi;
                    break;
                }
            }

            if (!$inputItem) continue;

            $workType = $inputItem['work_type'];
            $gp = floatval($inputItem['gp_percentage'] ?? $defaultGP);
            $labourRate = $defaultLabourRate;

            if ($workType === "own_production") {

                $materialCost  = floatval($inputItem['material_cost'] ?? 0);
                $labourHour    = floatval($inputItem['labour_hour'] ?? 0);
                $labourCost    = floatval($inputItem['labour_cost'] ?? 0);
                $transportCost = floatval($inputItem['transportation_cost'] ?? 0);

                $totalCost = $materialCost + $labourCost + $transportCost;
                $sellingPrice = $totalCost + ($totalCost * $gp / 100);

            } else {

                $totalCost = floatval($inputItem['cost'] ?? 0);
                $sellingPrice = $totalCost + ($totalCost * $gp / 100);

                $materialCost = 0;
                $labourHour = 0;
                $labourCost = 0;
                $transportCost = 0;
            }

            $total = $sellingPrice * $item['quantity'];

            $estimateItemData = [
                'estimate_id' => $estimateId,
                'description' => $item['description'],
                'work_type' => $workType,
                'material_cost' => $materialCost,
                'labour_hour' => $labourHour,
                'labour_rate' => $labourRate,
                'transportation_cost' => $transportCost,
                'labour_cost' => $labourCost,
                'total_cost' => $totalCost,
                'gp_percentage' => $gp,
                'selling_price' => $sellingPrice,
                'quantity' => $item['quantity'],
                'total' => $total,
                'status' => 1,
                'item_order' => 1
            ];

            $this->estimateItemModel->insert($estimateItemData);

            $savedItems[] = $estimateItemData;
            $totalAmount += $total;
        }

        $this->estimateModel->update($estimateId, [
            'total_amount' => $totalAmount,
            'sub_total' => $totalAmount
        ]);

        if (!$this->db->transStatus()) {
            $this->db->transRollback();
            return $this->respond(['status' => false, 'message' => 'Transaction failed']);
        }

        $this->db->transCommit();

        return $this->respond([
            'status' => true,
            'message' => $estimateId ? "Estimate updated successfully" : "Estimate created successfully",
            'data' => [
                'estimate_id' => $estimateId,
                'total_amount' => $totalAmount,
                'items' => $savedItems
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
    public function getSettingsData()
    {
        $authHeader = AuthHelper::getAuthorizationToken($this->request);
        $user = $this->authService->getAuthenticatedUser($authHeader);
        if (!$user) {
            return $this->failUnauthorized('Invalid or missing token.');
        }

        $settings = $this->settingsModel
            ->where('company_id', $user['company_id'])
            ->first();

        if (!$settings) {
            return $this->respond([
                'status'  => false,
                'message' => 'Settings not found'
            ]);
        }

        return $this->respond([
            'status'  => true,
            'message' => 'Settings fetched successfully',
            'data'    => [
                'gp_percentage' => floatval($settings['gp_percentage']),
                'labour_rate'   => floatval($settings['labour_rate'])
            ]
        ]);
    }
}