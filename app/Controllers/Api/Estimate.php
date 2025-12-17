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

        $enquiry = $this->enquiryModel
            ->where('enquiry_id', $enquiryId)
            ->where('is_deleted', 0)
            ->first();

        if (!$enquiry) {
            return $this->respond(['status' => false, 'message' => 'Enquiry not found']);
        }

        $estimateId = $this->request->getVar('estimate_id');
        $existingEstimate = $estimateId ? $this->estimateModel->find($estimateId) : null;

        if (!$existingEstimate && !empty($enquiry['is_converted'])) {
            return $this->respond(['status' => false, 'message' => 'Already converted']);
        }

        $settings = $this->settingsModel
            ->where('company_id', $user['company_id'])
            ->first();

        if (!$settings) {
            return $this->respond(['status' => false, 'message' => 'Settings not found']);
        }

        $defaultGP = (float) $settings['gp_percentage'];
        $defaultLabourRate = (float) $settings['labour_rate'];
        $isNewEstimate = empty($existingEstimate);

        $this->db->transBegin();
        if ($existingEstimate) {

            $this->estimateModel->update($estimateId, [
                'customer_id' => $enquiry['customer_id'] ?? null,
                'customer_address' => $enquiry['address'] ?? '',
                'phone_number' => $enquiry['phone'] ?? '',
                'company_id' => $user['company_id']
            ]);

            $this->estimateItemModel
                ->where('estimate_id', $estimateId)
                ->delete();

        } else {

            $last = $this->estimateModel->selectMax('estimate_no')->first();
            $nextEstimateNo = ($last && $last['estimate_no']) ? $last['estimate_no'] + 1 : 1;

            $this->estimateModel->insert([
                'enquiry_id' => $enquiryId,
                'user_id' => $user['user_id'],
                'customer_id' => $enquiry['customer_id'] ?? null,
                'customer_address' => $enquiry['address'] ?? '',
                'phone_number' => $enquiry['phone'] ?? '',
                'discount' => 0,
                'total_amount' => 0,
                'sub_total' => 0,
                'date' => date('Y-m-d H:i:s'),
                'company_id' => $user['company_id'],
                'estimate_no' => $nextEstimateNo
            ]);

            $estimateId = $this->estimateModel->getInsertID();

            if (!$estimateId) {
                $this->db->transRollback();
                return $this->respond(['status' => false, 'message' => 'Estimate creation failed']);
            }

            $this->enquiryModel->update($enquiryId, ['is_converted' => 1]);
        }
        $payloadItems = $this->request->getVar('items');
        if (empty($payloadItems)) {
            $this->db->transRollback();
            return $this->respond(['status' => false, 'message' => 'Items payload missing']);
        }

        $payloadItems = json_decode(json_encode($payloadItems), true);
        $enquiryItems = $this->enquiryItemModel
            ->where('enquiry_id', $enquiryId)
            ->findAll();

        $totalAmount = 0;
        $savedItems = [];

        foreach ($enquiryItems as $item) {

            $input = null;
            foreach ($payloadItems as $pi) {
                if ($pi['item_id'] == $item['item_id']) {
                    $input = $pi;
                    break;
                }
            }

            if (!$input) continue;

            $materialCost = 0;
            $labourHour = 0;
            $labourCost = 0;
            $transportCost = 0;
            $cost = 0;
            $totalCost = 0;
            $sellingPrice = 0;

            $workType = $input['work_type'];
            $gp = (float) ($input['gp_percentage'] ?? $defaultGP);

            if ($workType === 'own_production') {
                $materialCost = (float) ($input['material_cost'] ?? 0);
                $labourHour = (float) ($input['labour_hour'] ?? 0);
                $labourCost = (float) ($input['labour_cost'] ?? 0);
                $transportCost = (float) ($input['transportation_cost'] ?? 0);
                $totalCost = $materialCost + $labourCost + $transportCost;

            } elseif ($workType === 'sub_contract') {
                $cost = (float) ($input['cost'] ?? 0);
                $totalCost = $cost;

            } else {
                $totalCost = (float) ($input['cost'] ?? 0);
            }

            if (!empty($input['selling_price']) && $input['selling_price'] > 0) {
                $sellingPrice = (float) $input['selling_price'];
                if ($totalCost > 0) {
                    $gp = (($sellingPrice - $totalCost) / $totalCost) * 100;
                }
            } else {
                $sellingPrice = $totalCost + ($totalCost * $gp / 100);
            }

            $lineTotal = $sellingPrice * $item['quantity'];

            $estimateItemData = [
                'estimate_id' => $estimateId,
                'enquiry_item_id'   => $item['item_id'],
                'description' => $item['description'],
                'work_type' => $workType,
                'material_cost' => $materialCost,
                'labour_hour' => $labourHour,
                'labour_rate' => $defaultLabourRate,
                'transportation_cost' => $transportCost,
                'labour_cost' => $labourCost,
                'cost' => $cost,
                'total_cost' => $totalCost,
                'gp_percentage' => round($gp, 2),
                'selling_price' => round($sellingPrice, 2),
                'quantity' => $item['quantity'],
                'total' => $lineTotal,
                'status' => 1,
                'item_order' => 1
            ];

            $this->estimateItemModel->insert($estimateItemData);

            $savedItems[] = $estimateItemData;
            $totalAmount += $lineTotal;
        }

        $this->estimateModel->update($estimateId, [
            'sub_total' => $totalAmount,
            'total_amount' => $totalAmount
        ]);

        if (!$this->db->transStatus()) {
            $this->db->transRollback();
            return $this->respond(['status' => false, 'message' => 'Transaction failed']);
        }

        $this->db->transCommit();

        return $this->respond([
            'status' => true,
            'message' => $isNewEstimate ? 'Estimate created successfully' : 'Estimate updated successfully',
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

        $result = $this->estimateModel
            ->getEstimates($user['company_id'], $pageSize, $offset, $search);

        foreach ($result['data'] as &$estimate) {

            $combinedItems = [];

            // Enquiry items
            $enquiryItems = [];
            if (!empty($estimate['enquiry_id'])) {
                $enquiryItems = $this->enquiryItemModel
                    ->getItemsByEnquiryId($estimate['enquiry_id']);
            }

            // Estimate items
            $estimateItems = $this->estimateItemModel
                ->getEstimateitemsById($estimate['estimate_id']);

            // Map enquiry_item_id => total
            $estimateItemMap = [];
            foreach ($estimateItems as $ei) {
                $estimateItemMap[$ei['enquiry_item_id']] = $ei['total'];
            }

            // Merge
            foreach ($enquiryItems as $item) {
                $item['total'] = $estimateItemMap[$item['item_id']] ?? 0;
                $combinedItems[] = $item;
            }

            $estimate['items'] = $combinedItems;
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
        $estimate['enquiry_items'] = [];
        if (!empty($estimate['enquiry_id'])) {
            $estimate['enquiry_items'] =
                $this->enquiryItemModel->getItemsByEnquiryId($estimate['enquiry_id']);
        }
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