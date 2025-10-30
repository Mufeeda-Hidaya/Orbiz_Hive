<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\Api\EnquiryModel;
use App\Controllers\BaseController;
use App\Models\Api\LoginModel;  
use App\Models\EnquiryItemModel;
use App\Models\Manageuser_Model;
use App\Models\customerModel;
use App\Models\RoleModel;
use App\Libraries\Jwt;
use App\Libraries\AuthService;
use App\Helpers\AuthHelper;

class Enquiry extends ResourceController
{
    protected $loginModel;

    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->input = \Config\Services::request();
        $this->userModel = new Manageuser_Model();
        $this->customerModel = new CustomerModel();
        $this->enquiryModel = new EnquiryModel();
        $this->enquiryItemModel = new EnquiryItemModel();
        $this->authService = new AuthService();
    }
    public function saveEnquiry()
    {
        $authHeader = AuthHelper::getAuthorizationToken($this->request);
        $user = $this->authService->getAuthenticatedUser($authHeader);
        if(!$user){
            return $this->failUnauthorized('Invalid or missing token.');
        }
        $enquiryModel     = new EnquiryModel();
        $enquiryItemModel = new EnquiryItemModel();
        $customerModel    = new CustomerModel();

        $input = $this->request->getJSON(true);
        if (!$input) {
            $input = $this->request->getPost();
        }

        $enquiryId = $input['enquiry_id'] ?? null;
        $name      = trim($input['name'] ?? '');
        $address   = trim($input['address'] ?? '');
        $items     = $input['items'] ?? [];

        if (empty($name) || empty($address) || empty($items) || !is_array($items)) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Name, address, and at least one item are required.'
            ]);
        }

        $validItems = [];
        foreach ($items as $item) {
            $desc = trim($item['description'] ?? '');
            $qty  = floatval($item['quantity'] ?? 0);
            if ($desc && $qty > 0) {
                $validItems[] = [
                    'description' => $desc,
                    'quantity'    => $qty
                ];
            }
        }

        if (empty($validItems)) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Each item must have valid description and quantity.'
            ]);
        }

        $userId     = session()->get('user_id') ?? 1;
        $companyId  = 1;

        $existingCustomer = $customerModel->where('name', $name)->first();

        if ($existingCustomer) {
            $customerId = $existingCustomer['customer_id'];
            if (trim($existingCustomer['address']) !== $address) {
                $customerModel->update($customerId, [
                    'address'    => $address,
                    'updated_by' => $userId,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
        } else {
            $customerModel->insert([
                'name'       => $name,
                'address'    => $address,
                'company_id'  => $companyId,
                'created_by' => $userId,
                'created_at' => date('Y-m-d H:i:s'),
                'is_deleted' => 0
            ]);
            $customerId = $customerModel->getInsertID();
        }
        if (!empty($enquiryId)) {
            $existing = $enquiryModel->find($enquiryId);
            if (!$existing) {
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => 'Enquiry not found.'
                ]);
            }
            $enquiryModel->update($enquiryId, [
                'customer_id' => $customerId,
                'name'        => $name,
                'address'     => $address,
                'updated_by'  => $userId,
                'updated_at'  => date('Y-m-d H:i:s')
            ]);
            $enquiryItemModel->where('enquiry_id', $enquiryId)->delete();

            foreach ($validItems as $item) {
                $enquiryItemModel->insert([
                    'enquiry_id'  => $enquiryId,
                    'description' => $item['description'],
                    'quantity'    => $item['quantity'],
                    'status'      => 1,
                    'created_at'  => date('Y-m-d H:i:s')
                ]);
            }

            return $this->response->setJSON([
                'status'     => 'success',
                'message'    => 'Enquiry updated successfully.',
                'enquiry_id' => $enquiryId,
                'customer'   => [
                    'customer_id' => $customerId,
                    'name'        => $name,
                    'address'     => $address
                ]
            ]);
        }
        $lastEnquiry = $enquiryModel
            ->where('company_id', $companyId)
            ->orderBy('enquiry_no', 'DESC')
            ->first();

        $nextEnquiryNo = $lastEnquiry ? $lastEnquiry['enquiry_no'] + 1 : 1;

        $enquiryModel->insert([
            'customer_id' => $customerId,
            'name'        => $name,
            'address'     => $address,
            'company_id'  => $companyId,
            'user_id'     => $userId,
            'enquiry_no'  => $nextEnquiryNo,
            'is_deleted'  => 0,
            'created_by'  => $userId,
            'created_on'  => date('Y-m-d H:i:s')
        ]);

        $newEnquiryId = $enquiryModel->getInsertID();

        foreach ($validItems as $item) {
            $enquiryItemModel->insert([
                'enquiry_id'  => $newEnquiryId,
                'description' => $item['description'],
                'quantity'    => $item['quantity'],
                'status'      => 1,
                'created_at'  => date('Y-m-d H:i:s')
            ]);
        }

        return $this->response->setJSON([
            'status'     => 'success',
            'message'    => 'Enquiry created successfully.',
            'enquiry_id' => $newEnquiryId,
            'customer'   => [
                'customer_id' => $customerId,
                'name'        => $name,
                'address'     => $address
            ]
        ]);
    }
    public function getAllEnquiries()
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
        $result = $this->enquiryModel->getAllEnquiries($pageSize, $offset, $search);
        foreach ($result['data'] as &$enquiry) {
            $enquiry['items'] = $this->enquiryItemModel
                ->select('item_id,description, quantity')
                ->where('enquiry_id', $enquiry['enquiry_id'])
                ->where('status !=', 9)
                ->findAll();
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Enquiries fetched successfully.',
            'total'   => $result['total'],
            'data'    => $result['data']
        ]);
    }

    public function getEnquiryById($id)
    {
        $authHeader = AuthHelper::getAuthorizationToken($this->request);
        $user = $this->authService->getAuthenticatedUser($authHeader);
        if (!$user) {
            return $this->failUnauthorized('Invalid or missing token.');
        }
        $enquiry = $this->enquiryModel->getEnquiryWithCustomer($id);

        if (!$enquiry) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Enquiry not found.'
            ]);
        }

        $items = $this->enquiryItemModel
            ->select('item_id,description, quantity')
            ->where('enquiry_id', $id)
            ->where('status !=', 9)
            ->findAll();

        $enquiry['items'] = $items;

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Enquiry fetched successfully.',
            'data'    => $enquiry
        ]);
    }
    public function deleteEnquiry($id)
    {
        $authHeader = AuthHelper::getAuthorizationToken($this->request);
        $user = $this->authService->getAuthenticatedUser($authHeader);
        if (!$user) {
            return $this->failUnauthorized('Invalid or missing token.');
        }

        $enquiry = $this->enquiryModel->find($id);
        if (!$enquiry) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Enquiry not found.'
            ]);
        }
        if ($enquiry['is_deleted'] == 1) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Enquiry already deleted.'
            ]);
        }
        $this->enquiryModel->update($id, [
            'is_deleted' => 1,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Enquiry deleted successfully.'
        ]);
    }
    public function deleteItem($itemId = null)
    {
        $authHeader = AuthHelper::getAuthorizationToken($this->request);
        $user = $this->authService->getAuthenticatedUser($authHeader);
        if (!$user) {
            return $this->failUnauthorized('Invalid or missing token.');
        }
        if (empty($itemId) || !is_numeric($itemId)) {
            return $this->respond([
                'status'  => false,
                'message' => 'Invalid or missing item ID.'
            ]);
        }

        $enquiryItemModel = new EnquiryItemModel();
        $item = $enquiryItemModel->find($itemId);

        if (!$item) {
            return $this->respond([
                'status'  => false,
                'message' => 'Item not found.'
            ]);
        }

        if ($item['status'] == 9) {
            return $this->respond([
                'status'  => false,
                'message' => "Item {$itemId} is already deleted."
            ]);
        }

        $updateData = [
            'status'     => 9,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $enquiryItemModel->update($itemId, $updateData);

        return $this->respond([
            'status'  => 'success',
            'message' => "Item {$itemId} deleted successfully."
        ]);
    }
    // public function convertToEstimate($enquiryId = null)
    // {
    //     $authHeader = AuthHelper::getAuthorizationToken($this->request);
    //     $user = $this->authService->getAuthenticatedUser($authHeader);
    //     if (!$user) {
    //         return $this->failUnauthorized('Invalid or missing token.');
    //     }
    //     if (empty($enquiryId) || !is_numeric($enquiryId)) {
    //         return $this->respond([
    //             'status'  => false,
    //             'message' => 'Invalid or missing enquiry ID.'
    //         ]);
    //     }

    //     $enquiry = $this->enquiryModel->getEnquiryDetails($enquiryId);
    //     if (!$enquiry) {
    //         return $this->respond([
    //             'status'  => false,
    //             'message' => 'Enquiry not found.'
    //         ]);
    //     }

    //     if ($enquiry['is_deleted'] == 1) {
    //         return $this->respond([
    //             'status'  => false,
    //             'message' => "Enquiry ID {$enquiryId} is deleted and cannot be converted into an estimate."
    //         ]);
    //     }

    //     if ($enquiry['is_converted'] == 1) {
    //         return $this->respond([
    //             'status'  => false,
    //             'message' => "Enquiry ID {$enquiryId} is already converted to an estimate."
    //         ]);
    //     }

    //     $converted = $this->enquiryModel->convertEnquiry($enquiryId);
    //     if (!$converted) {
    //         return $this->respond([
    //             'status'  => false,
    //             'message' => 'Failed to convert enquiry.'
    //         ]);
    //     }
    //     $items = $this->enquiryItemModel->getItemsByEnquiryId($enquiryId);

    //     $response = [
    //         'enquiry_id'       => $enquiry['enquiry_id'],
    //         'enquiry_no'       => $enquiry['enquiry_no'],
    //         'customer_name'    => $enquiry['customer_name'],
    //         'customer_address' => $enquiry['customer_address'],
    //         'is_converted'     => 1,
    //         'items'            => $items
    //     ];

    //     return $this->respond([
    //         'status'  => true,
    //         'message' => "Enquiry ID {$enquiryId} successfully converted into an estimate.",
    //         'data'    => $response
    //     ]);
    // }

}