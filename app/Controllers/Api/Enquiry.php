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
    //Muffee code

    // public function saveEnquiry()
    // {
    //     $authHeader = AuthHelper::getAuthorizationToken($this->request);
    //     $user = $this->authService->getAuthenticatedUser($authHeader);
    //     if (!$user) {
    //         return $this->failUnauthorized('Invalid or missing token.');
    //     }

    //     $enquiryModel     = new EnquiryModel();
    //     $enquiryItemModel = new EnquiryItemModel();
    //     $customerModel    = new CustomerModel();

    //     $input = $this->request->getJSON(true);
    //     if (!$input) {
    //         $input = $this->request->getPost();
    //     }

    //     $enquiryId = $input['enquiry_id'] ?? null;
    //     $name      = trim($input['name'] ?? '');
    //     $address   = trim($input['address'] ?? '');
    //     $items     = $input['items'] ?? [];

    //     if (empty($name) || empty($address) || empty($items) || !is_array($items)) {
    //         return $this->response->setJSON([
    //             'status'  => false,
    //             'message' => 'Name, address, and at least one item are required.'
    //         ]);
    //     }

    //     $uploadDir = FCPATH . 'uploads/enquiry/';
    //     if (!is_dir($uploadDir)) {
    //         mkdir($uploadDir, 0777, true);
    //     }

    //     $validItems = [];
    //     foreach ($items as $index => $item) {
    //         $desc  = trim($item['description'] ?? '');
    //         $qty   = floatval($item['quantity'] ?? 0);
    //         $image = $item['image'] ?? null;
    //         $imagePath = null;

    //         if ($image && preg_match('/^data:image\/(\w+);base64,/', $image, $type)) {
    //             $image = substr($image, strpos($image, ',') + 1);
    //             $type = strtolower($type[1]);
    //             if (!in_array($type, ['jpg', 'jpeg', 'png', 'gif'])) {
    //                 return $this->response->setJSON(['status' => false, 'message' => 'Invalid image type.']);
    //             }
    //             $imageData = base64_decode($image);
    //             $fileName = uniqid('item_') . '.' . $type;
    //             file_put_contents($uploadDir . $fileName, $imageData);
    //             $imagePath = 'uploads/enquiry/' . $fileName;
    //         }
    //         elseif ($image && preg_match('/\.(jpg|jpeg|png|gif)$/i', $image)) {
    //             $imagePath = strpos($image, 'uploads/enquiry/') === false
    //                 ? 'uploads/enquiry/' . $image
    //                 : $image;
    //         }
    //         elseif (isset($_FILES['items']['name'][$index]['image'])) {
    //             $imgFile = $this->request->getFile("items[$index][image]");
    //             if ($imgFile && $imgFile->isValid() && !$imgFile->hasMoved()) {
    //                 $newName = $imgFile->getRandomName();
    //                 $imgFile->move($uploadDir, $newName);
    //                 $imagePath = 'uploads/enquiry/' . $newName;
    //             }
    //         }

    //         if ($desc && $qty > 0) {
    //             $validItems[] = [
    //                 'description' => $desc,
    //                 'quantity'    => $qty,
    //                 'image'       => $imagePath
    //             ];
    //         }
    //     }

    //     if (empty($validItems)) {
    //         return $this->response->setJSON(['status' => false, 'message' => 'Each item must have valid description and quantity.']);
    //     }

    //     $userId    = session()->get('user_id') ?? 1;
    //     $companyId = 1;
    //     $existingCustomer = $customerModel->where('name', $name)->first();
    //     if ($existingCustomer) {
    //         $customerId = $existingCustomer['customer_id'];
    //         if (trim($existingCustomer['address']) !== $address) {
    //             $customerModel->update($customerId, [
    //                 'address'    => $address,
    //                 'updated_by' => $userId,
    //                 'updated_at' => date('Y-m-d H:i:s')
    //             ]);
    //         }
    //     } else {
    //         $customerModel->insert([
    //             'name'       => $name,
    //             'address'    => $address,
    //             'company_id' => $companyId,
    //             'created_by' => $userId,
    //             'created_at' => date('Y-m-d H:i:s'),
    //             'is_deleted' => 0
    //         ]);
    //         $customerId = $customerModel->getInsertID();
    //     }
    //     if (!empty($enquiryId)) {
    //         $existing = $enquiryModel->find($enquiryId);
    //         if (!$existing) {
    //             return $this->response->setJSON(['status' => false, 'message' => 'Enquiry not found.']);
    //         }

    //         $enquiryModel->update($enquiryId, [
    //             'customer_id' => $customerId,
    //             'name'        => $name,
    //             'address'     => $address,
    //             'updated_by'  => $userId,
    //             'updated_at'  => date('Y-m-d H:i:s')
    //         ]);

    //         $enquiryItemModel->where('enquiry_id', $enquiryId)->delete();

    //         foreach ($validItems as $item) {
    //             $enquiryItemModel->insert([
    //                 'enquiry_id'  => $enquiryId,
    //                 'description' => $item['description'],
    //                 'quantity'    => $item['quantity'],
    //                 'images'      => $item['image'],
    //                 'status'      => 1,
    //                 'created_at'  => date('Y-m-d H:i:s')
    //             ]);
    //         }

    //         return $this->response->setJSON([
    //             'status'  => 'success',
    //             'message' => 'Enquiry updated successfully.',
    //             'data'    => [
    //                 'enquiry_id'  => $enquiryId,
    //                 'customer_id' => $customerId,
    //                 'name'        => $name,
    //                 'address'     => $address,
    //                 'items'       => $validItems
    //             ]
    //         ]);
    //     }

    //     $lastEnquiry = $enquiryModel->where('company_id', $companyId)->orderBy('enquiry_no', 'DESC')->first();
    //     $nextEnquiryNo = $lastEnquiry ? $lastEnquiry['enquiry_no'] + 1 : 1;

    //     $enquiryModel->insert([
    //         'customer_id' => $customerId,
    //         'name'        => $name,
    //         'address'     => $address,
    //         'company_id'  => $companyId,
    //         'user_id'     => $userId,
    //         'enquiry_no'  => $nextEnquiryNo,
    //         'is_deleted'  => 0,
    //         'created_by'  => $userId,
    //         'created_on'  => date('Y-m-d H:i:s')
    //     ]);

    //     $newEnquiryId = $enquiryModel->getInsertID();

    //     foreach ($validItems as $item) {
    //         $enquiryItemModel->insert([
    //             'enquiry_id'  => $newEnquiryId,
    //             'description' => $item['description'],
    //             'quantity'    => $item['quantity'],
    //             'images'      => $item['image'],
    //             'status'      => 1,
    //             'created_at'  => date('Y-m-d H:i:s')
    //         ]);
    //     }

    //     return $this->response->setJSON([
    //         'status'  => 'success',
    //         'message' => 'Enquiry created successfully.',
    //         'data'    => [
    //             'enquiry_id'  => $newEnquiryId,
    //             'customer_id' => $customerId,
    //             'name'        => $name,
    //             'address'     => $address,
    //             'items'       => $validItems
    //         ]
    //     ]);
    // }
 public function saveEnquiry()
{
    $authHeader = AuthHelper::getAuthorizationToken($this->request);
    $user = $this->authService->getAuthenticatedUser($authHeader);
    if (!$user) {
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
    $phone     = trim($input['phone'] ?? '');
    $address   = trim($input['address'] ?? '');
    $items     = $input['items'] ?? [];

    if (empty($name) || empty($phone) || empty($address) || empty($items) || !is_array($items)) {
        return $this->response->setJSON([
            'status'  => false,
            'message' => 'Name, phone, address, and at least one item are required.'
        ]);
    }

    //  Filter and validate items
    $validItems = [];
    foreach ($items as $item) {
        $desc = trim($item['description'] ?? '');
        $qty  = floatval($item['quantity'] ?? 0);
        $images = $item['images'] ?? []; // multiple images as array

        if ($desc && $qty > 0) {
            $validItems[] = [
                'description' => $desc,
                'quantity'    => $qty,
                'images'      => json_encode($images), // store JSON in DB
            ];
        }
    }

    if (empty($validItems)) {
        return $this->response->setJSON([
            'status'  => false,
            'message' => 'Each item must have a valid description and quantity.'
        ]);
    }

    $userId    = session()->get('user_id') ?? 1;
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

        if (!empty($updateData)) {
            $updateData['updated_by'] = $userId;
            $updateData['updated_at'] = date('Y-m-d H:i:s');
            $customerModel->update($customerId, $updateData);
        }
    } else {
        $customerModel->insert([
            'name'       => $name,
            'phone'      => $phone,
            'address'    => $address,
            // 'company_id' => $companyId,
            'created_by' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
            'is_deleted' => 0
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
            'name'        => $name,
            'phone'       => $phone,
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
                'images'      => $item['images'], // store JSON
                'status'      => 1,
                'created_at'  => date('Y-m-d H:i:s')
            ]);
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Enquiry updated successfully.',
            'data'    => [
                'enquiry_id'  => $enquiryId,
                'customer_id' => $customerId,
                'name'        => $name,
                'phone'       => $phone,
                'address'     => $address,
                'items'       => $validItems
            ]
        ]);
    }

    // Create new enquiry
    $lastEnquiry = $enquiryModel->orderBy('enquiry_no', 'DESC')->first();
    $nextEnquiryNo = $lastEnquiry ? $lastEnquiry['enquiry_no'] + 1 : 1;

    $enquiryModel->insert([
        'customer_id' => $customerId,
        'name'        => $name,
        'phone'       => $phone,
        'address'     => $address,
        // 'company_id'  => $companyId,
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
            'images'      => $item['images'], //  store image JSON here
            'status'      => 1,
            'created_at'  => date('Y-m-d H:i:s')
        ]);
    }

    return $this->response->setJSON([
        'status'  => 'success',
        'message' => 'Enquiry created successfully.',
        'data'    => [
            'enquiry_id'  => $newEnquiryId,
            'customer_id' => $customerId,
            'name'        => $name,
            'phone'       => $phone,
            'address'     => $address,
            'items'       => $validItems
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
                ->select('item_id, description, quantity, images')
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
            ->select('item_id, description, quantity, images')
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


// public function uploadImage()
// {
//     $enquiryItemModel = new EnquiryItemModel();

//     $files = $this->request->getFiles();
//     if (empty($files['images'])) {
//         return $this->response->setJSON([
//             'status'  => 'error',
//             'message' => 'No images uploaded.'
//         ]);
//     }

//     $uploadPath = FCPATH . 'uploads/enquiry/';
//     if (!is_dir($uploadPath)) {
//         mkdir($uploadPath, 0777, true);
//     }

//     // Get the most recent enquiry record
//     $existingItem = $enquiryItemModel->orderBy('item_id', 'DESC')->first();
//     if (!$existingItem) {
//         return $this->response->setJSON([
//             'status'  => 'error',
//             'message' => 'No enquiry found to attach images.'
//         ]);
//     }

//     $uploadedFiles = [];
//     $images = is_array($files['images']) ? $files['images'] : [$files['images']];

//     foreach ($images as $file) {
//         if (!$file->isValid()) continue;

//         $mime = $file->getClientMimeType();
//         if (!in_array($mime, ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'])) continue;

//         $newName = $file->getRandomName();
//         $file->move($uploadPath, $newName);
//         $imagePath = 'uploads/enquiry/' . $newName;

//         $uploadedFiles[] = [
//             'file_name' => $newName,
//             'file_url'  => base_url($imagePath)
//         ];
//     }

//     // Merge with existing images
//     $existingImages = [];
//     if (!empty($existingItem['images'])) {
//         $decoded = json_decode($existingItem['images'], true);
//         if (is_array($decoded)) {
//             $existingImages = $decoded;
//         } else {
//             $existingImages = array_filter(explode(',', $existingItem['images']));
//         }
//     }

//     $mergedImages = array_merge($existingImages, $uploadedFiles);

//     // Update the latest record
//     $enquiryItemModel->update($existingItem['item_id'], [
//         'images'     => json_encode($mergedImages),
//         'updated_at' => date('Y-m-d H:i:s')
//     ]);

//     return $this->response->setJSON([
//         'status'  => 'success',
//         'message' => 'All images uploaded successfully.',
//         'data'    => $uploadedFiles
//     ]);
// }
public function uploadImage()
{
    $enquiryItemModel = new EnquiryItemModel();

    $files = $this->request->getFiles();
    if (empty($files['images'])) {
        return $this->response->setJSON([
            'status'  => 'error',
            'message' => 'No images uploaded.'
        ]);
    }

    $uploadPath = FCPATH . 'uploads/enquiry/';
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0777, true);
    }

    // ✅ Automatically get the latest enquiry record
    $existingItem = $enquiryItemModel->orderBy('item_id', 'DESC')->first();
    if (!$existingItem) {
        return $this->response->setJSON([
            'status'  => 'error',
            'message' => 'No enquiry found to attach images.'
        ]);
    }

    $uploadedFiles = [];
    $images = is_array($files['images']) ? $files['images'] : [$files['images']];

    foreach ($images as $file) {
        if (!$file->isValid()) continue;

        $mime = $file->getClientMimeType();
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'])) continue;

        $newName = $file->getRandomName();
        $file->move($uploadPath, $newName);

        $uploadedFiles[] = [
            'file_name' => $newName,
            'file_url'  => base_url('uploads/enquiry/' . $newName)
        ];
    }

    // ✅ Handle existing images in the record (if any)
    $existingImages = [];
    if (!empty($existingItem['images'])) {
        $decoded = json_decode($existingItem['images'], true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $existingImages = $decoded;
        }
    }

    // ✅ Merge old + new images
    $mergedImages = array_merge($existingImages, $uploadedFiles);



    // ✅ Return proper response
    return $this->response->setJSON([
        'status'  => 'success',
        'message' => 'All images uploaded successfully.',
        // 'enquiry_id' => $existingItem['enquiry_id'], // fetched automatically
        'data'    => $uploadedFiles
    ]);
}








}