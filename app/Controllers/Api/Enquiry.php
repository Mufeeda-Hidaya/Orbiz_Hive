<?php

namespace App\Controllers\Api;

use App\Models\Api\EnquiryModel;
use App\Controllers\BaseController;
use App\Models\Api\LoginModel;  
use App\Models\Manageuser_Model;
use App\Models\customerModel;
use App\Models\EnquiryItemModel;
// use App\Models\RoleModel;
// use App\Libraries\Jwt;
// use App\Libraries\AuthService;
// use App\Helpers\AuthHelper;

class Enquiry extends BaseController
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
        // $this->authService = new AuthService();
    }
    public function saveEnquiry()
    {
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
        $pageIndex = (int) $this->request->getGet('pageIndex');
        $pageSize  = (int) $this->request->getGet('pageSize');
        $search    = $this->request->getGet('search');

        if ($pageSize <= 0) {
            $pageSize = 10;
        }

        $offset = $pageIndex * $pageSize;

        $builder = $this->enquiryModel
            ->select('enquiries.*, customers.name AS customer_name, customers.address AS customer_address')
            ->join('customers', 'customers.customer_id = enquiries.customer_id', 'left')
            ->where('enquiries.is_deleted', 0);

        if (!empty($search)) {
            $builder->groupStart()
                    ->like('enquiries.name', $search)
                    ->orLike('customers.name', $search)
                    ->orLike('customers.address', $search)
                    ->groupEnd();
        }

        $total = $builder->countAllResults(false);

        $enquiries = $builder
            ->orderBy('enquiries.created_at', 'DESC')
            ->findAll($pageSize, $offset);

        foreach ($enquiries as &$enquiry) {
            // Fetch related items from the EnquiryModel
            $items = $this->enquiryModel->getItemsByEnquiryId($enquiry['enquiry_id']);

            // 🧹 Clean item fields (remove timestamps, etc.)
            foreach ($items as &$item) {
                unset(
                    $item['created_at'],
                    $item['updated_at'],
                    $item['created_on'],
                    $item['updated_on'],
                    $item['name'],
                    $item['address']
                );
            }

            $enquiry['items'] = $items;

            // 🧹 Remove unwanted fields from enquiry
            unset(
                $enquiry['company_id'],
                $enquiry['created_on'],
                $enquiry['updated_on'],
                $enquiry['created_at'],
                $enquiry['updated_at'],
                $enquiry['customer_name'],
                $enquiry['customer_address'],
                $enquiry['name'],
                $enquiry['address']
            );
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Enquiries fetched successfully.',
            'data'    => $enquiries,
            'total'   => $total
        ]);
    }



    // ✅ Get single enquiry by ID
    public function getEnquiryById($id)
    {
        $enquiry = $this->enquiryModel
            ->select('enquiries.*, customers.name AS customer_name, customers.address AS customer_address')
            ->join('customers', 'customers.customer_id = enquiries.customer_id', 'left')
            ->where('enquiries.enquiry_id', $id)
            ->where('enquiries.is_deleted', 0)
            ->first();

        if (!$enquiry) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Enquiry not found.'
            ]);
        }

        $items = $this->enquiryItemModel
            ->select('description, quantity, created_at')
            ->where('enquiry_id', $id)
            ->findAll();

        foreach ($items as &$item) {
            unset($item['created_on'], $item['updated_on']);
        }

        $enquiry['items'] = $items;
        unset($enquiry['company_id'], $enquiry['created_on'], $enquiry['updated_on']);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Enquiry fetched successfully.',
            'data'    => $enquiry
        ]);
    }

    // ✅ Soft delete
    public function deleteEnquiry($id)
    {
        $enquiry = $this->enquiryModel->find($id);
        if (!$enquiry) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Enquiry not found.'
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
}