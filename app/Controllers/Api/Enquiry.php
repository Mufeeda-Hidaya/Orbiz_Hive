<?php
namespace App\Controllers\Api;
use CodeIgniter\RESTful\ResourceController;
use App\Models\Api\EnquiryModel;
use App\Controllers\BaseController;
use App\Models\Api\LoginModel;
use App\Models\Api\EnquiryitemModel;
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
        $this->EnquiryitemModel = new EnquiryitemModel();
        $this->authService = new AuthService();
    }

    public function saveEnquiry()
    {
        $authHeader = AuthHelper::getAuthorizationToken($this->request);
        $user = $this->authService->getAuthenticatedUser($authHeader);
        if (!$user) {
            return $this->failUnauthorized('Invalid or missing token.');
        }

        $enquiryModel = new EnquiryModel();
        $EnquiryitemModel = new EnquiryitemModel();
        $customerModel = new CustomerModel();

        $input = $this->request->getJSON(true);
        if (!$input) {
            $input = $this->request->getPost();
        }

        $enquiryId = $input['enquiry_id'] ?? null;
        $name = trim($input['name'] ?? '');
        $contactPerson = trim($input['contact_person_name'] ?? '');
        $phone = trim($input['phone'] ?? '');
        $address = trim($input['address'] ?? '');
        $note = trim($input['note'] ?? '');
        $items = $input['items'] ?? [];

        if (empty($name) || empty($phone) || empty($address) || empty($items) || !is_array($items)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Name, phone, address, and at least one item are required.'
            ]);
        }

        //  Filter and validate items
        $validItems = [];
        foreach ($items as $item) {
            $desc = trim($item['description'] ?? '');
            $qty = floatval($item['quantity'] ?? 0);
            $images = $item['images'] ?? []; // multiple images as array

            if ($desc && $qty > 0) {
                $validItems[] = [
                    'description' => $desc,
                    'quantity' => $qty,
                    'images' => json_encode($images), // store JSON in DB
                ];
            }
        }

        if (empty($validItems)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Each item must have a valid description and quantity.'
            ]);
        }

        $userId = session()->get('user_id') ?? 1;
        // $companyId = 1;

        //  Handle customer
        $existingCustomer = $customerModel->where('name', $name)->first();
        if ($existingCustomer) {
            $customerId = $existingCustomer['customer_id'];
            $updateData = [];

            if (trim($existingCustomer['address']) !== $address) {
                $updateData['address'] = $address;
            }
            if (trim($existingCustomer['phone'] ?? '') !== $phone) {
                $updateData['phone'] = $phone;
            }
            if (trim($existingCustomer['contact_person_name'] ?? '') !== $contactPerson) {
                $updateData['contact_person_name'] = $contactPerson;
            }

            if (!empty($updateData)) {
                $updateData['updated_by'] = $userId;
                $updateData['updated_at'] = date('Y-m-d H:i:s');
                $customerModel->update($customerId, $updateData);
            }
        } else {
            $customerModel->insert([
                'name' => $name,
                'phone' => $phone,
                'contact_person_name' => $contactPerson,
                'address' => $address,
                // 'company_id' => $companyId,
                'created_by' => $userId,
                'created_at' => date('Y-m-d H:i:s'),
                'status' => 1
            ]);
            $customerId = $customerModel->getInsertID();
        }

        //  Update existing enquiry
        if (!empty($enquiryId)) {
            $existing = $enquiryModel->find($enquiryId);
            if (!$existing) {
                return $this->response->setJSON(['status' => false, 'message' => 'Enquiry not found.']);
            }

            $enquiryModel->update($enquiryId, [
                'customer_id' => $customerId,
                'name' => $name,
                'phone' => $phone,
                'address' => $address,
                // 'note' => $note,
                'updated_by' => $userId,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $EnquiryitemModel->where('enquiry_id', $enquiryId)->delete();

            foreach ($validItems as $item) {
                $EnquiryitemModel->insert([
                    'enquiry_id' => $enquiryId,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'images' => $item['images'],
                    'note' => $note,// store JSON
                    'status' => 1,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Enquiry updated successfully.',
                'data' => [
                    'enquiry_id' => $enquiryId,
                    'customer_id' => $customerId,
                    'name' => $name,
                    'contact_person_name' => $contactPerson,
                    'phone' => $phone,
                    'address' => $address,
                    'items' => $validItems
                ]
            ]);
        }

        // Create new enquiry
        $lastEnquiry = $enquiryModel
            ->orderBy('enquiry_id', 'DESC')
            ->first();

        if ($lastEnquiry && !empty($lastEnquiry['enquiry_no'])) {
            // Remove prefix OBENQ and convert to number
            $lastNumber = (int) str_replace('OBENQ', '', $lastEnquiry['enquiry_no']);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        // Generate enquiry number like admin
        $nextEnquiryNo = 'OBENQ' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        $enquiryModel->insert([
            'customer_id' => $customerId,
            'name' => $name,
            'phone' => $phone,
            'address' => $address,
            // 'company_id'  => $companyId,
            // 'user_id' => $userId,
            'enquiry_no' => $nextEnquiryNo,
            'status' => 1,
            'is_new' => 1,
            'note' => $note,
            'created_by' => $userId,
            'created_on' => date('Y-m-d H:i:s')
        ]);

        $newEnquiryId = $enquiryModel->getInsertID();

        foreach ($validItems as $item) {
            $EnquiryitemModel->insert([
                'enquiry_id' => $newEnquiryId,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'images' => $item['images'],
                'note' => $note,//  store image JSON here
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Enquiry created successfully.',
            'data' => [
                'enquiry_id' => $newEnquiryId,
                'customer_id' => $customerId,
                'name' => $name,
                'contact_person_name' => $contactPerson,
                'phone' => $phone,
                'address' => $address,
                'items' => $validItems,
                'note' => $note,
                'is_new' => 1,
            ]
        ]);
    }
    public function markViewed()
    {
        $input = $this->request->getJSON(true); // parse JSON from Postman
        $enquiryId = $input['enquiry_id'] ?? null;

        if (!$enquiryId) {
            return $this->response->setJSON([
                'status' => 400,
                'message' => 'Enquiry ID is required'
            ]);
        }

        $updated = $this->enquiryModel
            ->where('enquiry_id', $enquiryId)
            ->set(['is_new' => 0])
            ->update();

        if (!$updated) {
            return $this->response->setJSON([
                'status' => 404,
                'message' => 'No enquiry found or already marked as viewed'
            ]);
        }

        return $this->response->setJSON([
            'status' => 200,
            'message' => 'Enquiry marked as viewed'
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
        $pageSize = (int) $this->request->getGet('pageSize');
        $search = $this->request->getGet('search');

        if ($pageSize <= 0)
            $pageSize = 10;
        $offset = $pageIndex * $pageSize;
        $result = $this->enquiryModel->getAllEnquiries($pageSize, $offset, $search);
        foreach ($result['data'] as &$enquiry) {
            $enquiry['items'] = $this->EnquiryitemModel
                ->select('item_id, description, quantity, images')
                ->where('enquiry_id', $enquiry['enquiry_id'])
                ->where('status !=', 9)
                ->findAll();
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Enquiries fetched successfully.',
            'total' => $result['total'],
            'data' => $result['data']
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

        $items = $this->EnquiryitemModel
            ->select('item_id, description, quantity, images')
            ->where('enquiry_id', $id)
            ->where('status !=', 9)
            ->findAll();

        $enquiry['items'] = $items;

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Enquiry fetched successfully.',
            'data' => $enquiry
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
                'status' => false,
                'message' => 'Invalid or missing item ID.'
            ]);
        }

        $EnquiryitemModel = new EnquiryitemModel();
        $item = $EnquiryitemModel->find($itemId);

        if (!$item) {
            return $this->respond([
                'status' => false,
                'message' => 'Item not found.'
            ]);
        }

        if ($item['status'] == 9) {
            return $this->respond([
                'status' => false,
                'message' => "Item {$itemId} is already deleted."
            ]);
        }

        $updateData = [
            'status' => 9,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $EnquiryitemModel->update($itemId, $updateData);

        return $this->respond([
            'status' => 'success',
            'message' => "Item {$itemId} deleted successfully."
        ]);
    }

    public function uploadImage()
    {
        $enquiryItemModel = new EnquiryItemModel();

        $files = $this->request->getFiles();
        if (empty($files['images'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'No images uploaded.'
            ]);
        }

        $uploadPath = FCPATH . 'public/uploads/enquiry/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        // Automatically get the latest enquiry record
        $existingItem = $enquiryItemModel->orderBy('item_id', 'DESC')->first();
        if (!$existingItem) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'No enquiry found to attach images.'
            ]);
        }

        $uploadedFiles = [];
        $images = is_array($files['images']) ? $files['images'] : [$files['images']];

        foreach ($images as $file) {
            if (!$file->isValid())
                continue;

            $mime = $file->getClientMimeType();
            if (!in_array($mime, ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp']))
                continue;

            $newName = $file->getRandomName();
            $file->move($uploadPath, $newName);

            $uploadedFiles[] = [
                'file_name' => $newName,
                'file_url' => base_url('public/uploads/enquiry/' . $newName)
            ];
        }

        // Handle existing images in the record (if any)
        $existingImages = [];
        if (!empty($existingItem['images'])) {
            $decoded = json_decode($existingItem['images'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $existingImages = $decoded;
            }
        }

        // Merge old + new images
        $mergedImages = array_merge($existingImages, $uploadedFiles);

        // Return proper response
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'All images uploaded successfully.',
            // 'enquiry_id' => $existingItem['enquiry_id'], // fetched automatically
            'data' => $uploadedFiles
        ]);
    }
}