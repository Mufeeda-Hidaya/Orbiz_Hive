<?php
namespace App\Controllers;

use App\Models\SupplierModel;
use App\Models\EstimateModel;
use App\Models\EstimateItemModel;
use App\Models\customerModel;
use App\Models\EnquiryItemModel;
use CodeIgniter\Controller;

class Supplier extends BaseController
{
    public function __construct()
    {
        $session = \Config\Services::session();
        if (!$session->get('logged_in')) {
            return redirect()->to(base_url('/'))->send();
        }
    }

    // Create or Update 
    public function add_enquiry($id = null)
    {
        $customerModel = new CustomerModel();
        $enquiryModel = new SupplierModel();
        $enquiryItemModel = new EnquiryItemModel();

        $data['customers'] = $customerModel
            ->where('status', 1)
            ->orderBy('customer_id', 'DESC')
            ->findAll();

        $data['enquiry'] = null;
        $data['items'] = [];
        $data['note'] = ''; // special note

        if ($id) {
            $data['enquiry'] = $enquiryModel->find($id);

            // Load enquiry items WITH note and images
            $data['items'] = $enquiryItemModel->getItemsByEnquiryId($id);

            // Get note from first item (all items have the same note)
            if (!empty($data['items'])) {
                $data['note'] = $data['items'][0]['note'] ?? '';
            }
        }

        return view('add_enquiry', $data);
    }

    // public function saveEnquiry()
    // {
    //     $db = \Config\Database::connect();
    //     $db->transBegin();

    //     $enquiryModel = new SupplierModel();
    //     $enquiryItemModel = new EnquiryItemModel();
    //     $customerModel = new CustomerModel();

    //     $customerId = $this->request->getPost('customer_id');
    //     $address = trim($this->request->getPost('customer_address'));
    //     $descriptions = $this->request->getPost('description');
    //     $quantities = $this->request->getPost('quantity');
    //     $note = $this->request->getPost('note'); // single note
    //     $files = $this->request->getFiles();

    //     if (empty($customerId) || empty($address)) {
    //         return $this->response->setJSON([
    //             'status' => 'error',
    //             'message' => 'Please fill all required fields.'
    //         ]);
    //     }

    //     $customer = $customerModel->find($customerId);
    //     if (!$customer) {
    //         return $this->response->setJSON([
    //             'status' => 'error',
    //             'message' => 'Customer not found.'
    //         ]);
    //     }

    //     $userId = session()->get('user_id') ?? 1;

    //     // Generate enquiry no
    //     $last = $enquiryModel->orderBy('enquiry_id', 'DESC')->first();
    //     $next = $last ? ((int) str_replace('OBENQ', '', $last['enquiry_no']) + 1) : 1;
    //     $enquiryNo = 'OBENQ' . str_pad($next, 3, '0', STR_PAD_LEFT);

    //     // Insert enquiry
    //     $enquiryId = $enquiryModel->insert([
    //         'enquiry_no' => $enquiryNo,
    //         'customer_id' => $customerId,
    //         'name' => $customer['name'],
    //         'address' => $address,
    //         'phone' => $customer['phone'],
    //         'user_id' => $userId,
    //         'status' => 1,
    //         'created_by' => $userId,
    //         'note' => $note // save note in enquiry table
    //     ]);

    //     if (!$enquiryId) {
    //         $db->transRollback();
    //         return $this->response->setJSON([
    //             'status' => 'error',
    //             'message' => 'Failed to create enquiry'
    //         ]);
    //     }

    //     // Insert enquiry items
    //     foreach ($descriptions as $key => $desc) {

    //         $desc = trim($desc);
    //         $qty = (int) ($quantities[$key] ?? 0);

    //         if ($desc === '' || $qty <= 0)
    //             continue;

    //         $itemImages = [];

    //         // handle multiple images per item
    //         if (isset($files['item_images'][$key]) && is_array($files['item_images'][$key])) {
    //             foreach ($files['item_images'][$key] as $file) {
    //                 if ($file instanceof \CodeIgniter\HTTP\Files\UploadedFile && $file->isValid() && !$file->hasMoved()) {
    //                     $newName = $file->getRandomName();
    //                     $file->move(FCPATH . 'uploads/enquiry', $newName);
    //                     $itemImages[] = $newName; // store only filenames
    //                 }
    //             }
    //         }

    //         $insertData = [
    //             'enquiry_id' => $enquiryId,
    //             'description' => $desc,
    //             'quantity' => $qty,
    //             'note' => $note, // same note for all items
    //             'images' => !empty($itemImages) ? json_encode($itemImages) : null,
    //             'status' => 1,
    //             'created_at' => date('Y-m-d H:i:s'),
    //             'updated_at' => date('Y-m-d H:i:s')
    //         ];

    //         $enquiryItemModel->insert($insertData);
    //     }

    //     $db->transCommit();

    //     return $this->response->setJSON([
    //         'status' => 'success',
    //         'message' => 'Enquiry generated successfully',
    //         'enquiry_id' => $enquiryId,
    //         'enquiry_no' => $enquiryNo,
    //         'note' => $note,
    //         'images' => isset($itemImages) ? json_encode($itemImages) : null,
    //     ]);
    // }



    public function saveEnquiry()
    {
        $db = \Config\Database::connect();
        $db->transBegin();

        $enquiryModel = new SupplierModel();
        $enquiryItemModel = new EnquiryItemModel();
        $customerModel = new CustomerModel();

        $enquiryId = $this->request->getPost('enquiry_id'); // check if editing
        $customerId = $this->request->getPost('customer_id');
        $address = trim($this->request->getPost('customer_address'));
        $descriptions = $this->request->getPost('description');
        $quantities = $this->request->getPost('quantity');
        $note = $this->request->getPost('note');
        $files = $this->request->getFiles();

        if (empty($customerId) || empty($address)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Please fill all required fields.'
            ]);
        }

        $customer = $customerModel->find($customerId);
        if (!$customer) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Customer not found.'
            ]);
        }

        $userId = session()->get('user_id') ?? 1;
        $isEdit = !empty($enquiryId);
        // EDIT MODE
        if ($enquiryId) {
            $enquiryModel->update($enquiryId, [
                'customer_id' => $customerId,
                'name' => $customer['name'],
                'address' => $address,
                'phone' => $customer['phone'],
                'note' => $note,
                'updated_by' => $userId,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Delete old items (or soft delete)
            $enquiryItemModel->where('enquiry_id', $enquiryId)->delete();
        } else {
            // NEW MODE
            $last = $enquiryModel->orderBy('enquiry_id', 'DESC')->first();
            $next = $last ? ((int) str_replace('OBENQ', '', $last['enquiry_no']) + 1) : 1;
            $enquiryNo = 'OBENQ' . str_pad($next, 3, '0', STR_PAD_LEFT);

            $enquiryId = $enquiryModel->insert([
                'enquiry_no' => $enquiryNo,
                'customer_id' => $customerId,
                'name' => $customer['name'],
                'address' => $address,
                'phone' => $customer['phone'],
                'user_id' => $userId,
                'status' => 1,
                'created_by' => $userId,
                'note' => $note
            ]);
        }

        // Insert items
        foreach ($descriptions as $key => $desc) {
            $desc = trim($desc);
            $qty = (int) ($quantities[$key] ?? 0);
            if ($desc === '' || $qty <= 0)
                continue;

            $imageName = null;

            // Single image per item
            if (isset($files['item_image'][$key])) {
                $file = $files['item_image'][$key];
                if ($file instanceof \CodeIgniter\HTTP\Files\UploadedFile && $file->isValid() && !$file->hasMoved()) {
                    $imageName = $file->getRandomName();
                    $file->move(FCPATH . 'uploads/enquiry', $imageName);
                }
            }

            $enquiryItemModel->insert([
                'enquiry_id' => $enquiryId,
                'description' => $desc,
                'quantity' => $qty,
                'note' => $note,
                'images' => $imageName, // only single image
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        $db->transCommit();

        return $this->response->setJSON([
            'status' => 'success',
            'message' => $isEdit
                ? 'Enquiry updated successfully'
                : 'Enquiry created successfully',
            'enquiry_id' => $enquiryId,
            'note' => $note
        ]);

    }


    // List all enquiries (basic)
    public function list()
    {
        $SupplierModel = new SupplierModel();
        $data['enquiries'] = $SupplierModel->where('status', 0)->findAll();
        return view('supplierlist', $data);
    }

    // DataTable AJAX fetch
    public function fetch()
    {
        $request = service('request');
        $model = new SupplierModel();

        $draw = $request->getPost('draw') ?? 1;
        $start = $request->getPost('start') ?? 0;
        $length = $request->getPost('length') ?? 10;
        $order = $request->getPost('order');
        $search = trim($request->getPost('search')['value'] ?? '');

        $columnIndex = $order[0]['column'] ?? 0;
        $orderDir = $order[0]['dir'] ?? 'desc';

        $columnMap = [
            0 => 'enquiry_id',
            1 => 'enquiry_no',
            2 => 'name',
            3 => 'address',
            4 => 'created_at'
        ];
        $orderColumn = $columnMap[$columnIndex] ?? 'enquiry_id';

        // Fetch filtered data
        $enquiries = $model->getAllFilteredRecords($search, $start, $length, $orderColumn, $orderDir);

        $result = [];
        $slno = $start + 1;

        foreach ($enquiries as $row) {
            $result[] = [
                'slno' => $slno++,
                'enquiry_id' => $row['enquiry_id'],
                'enquiry_no' => $row['enquiry_no'],
                'name' => ucwords(strtolower($row['name'] ?? '')),
                'address' => ucwords(strtolower($row['address'] ?? '')),
                'status' => $row['status'],
                'enquiry_date' => date('Y-m-d', strtotime($row['created_at'])),
            ];
        }

        // Get count
        $filteredTotal = $model->getFilteredSupplierCount($search);
        $totalRecords = $model->where('status', 0)->countAllResults();

        return $this->response->setJSON([
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredTotal,
            'data' => $result
        ]);
    }

    // Edit 
    public function edit($id)
    {
        return $this->add_enquiry($id); // reuse add_enquiry logic
    }

    // Soft Delete 
    public function delete()
    {
        $id = $this->request->getPost('id');
        $model = new SupplierModel();

        if ($model->update($id, ['status' => 9])) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Enquiry Deleted Successfully']);
        }
        return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to Delete Enquiry']);
    }

    public function convertToEstimate($id)
    {
        $supplierModel = new SupplierModel();

        $enquiry = $supplierModel->find($id);
        if (!$enquiry) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Enquiry not found');
        }

        // Safety check
        if ($enquiry['status'] != 3) {
            return redirect()->back()->with('error', 'Enquiry not converted yet');
        }

        return redirect()->to(
            base_url('estimate/add_estimate?enquiry_id=' . $id)
        );
    }


    public function markConverted()
    {
        $enquiryId = $this->request->getPost('enquiry_id');

        if (!$enquiryId) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid enquiry ID'
            ]);
        }

        $supplierModel = new SupplierModel();
        $enquiry = $supplierModel->find($enquiryId);

        if (!$enquiry) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Enquiry not found'
            ]);
        }

        if ($enquiry['status'] == 3) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Enquiry already converted'
            ]);
        }

        $supplierModel->update($enquiryId, [
            'status' => 3 // Converted
        ]);

        return $this->response->setJSON([
            'status' => 'success'
        ]);
    }



}
