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

        // $companyId = 1;

        // Load all customers
        $data['customers'] = $customerModel
            // ->where('is_deleted', 0)
            // ->where('company_id', $companyId)
            ->orderBy('customer_id', 'DESC')
            ->findAll();

        $data['enquiry'] = null;
        $data['items'] = [];

        if ($id) {
            // Load enquiry
            $data['enquiry'] = $enquiryModel->where('is_deleted', 0)->find($id);

            // Load items
            $data['items'] = $enquiryItemModel->where('enquiry_id', $id)->findAll();
        }

        return view('add_enquiry', $data);
    }


    public function saveEnquiry()
    {
        $enquiryModel = new SupplierModel();
        $enquiryItemModel = new EnquiryItemModel();
        $customerModel = new CustomerModel();

        $enquiryId = $this->request->getPost('enquiry_id');
        $customerId = $this->request->getPost('customer_id');
        $address = trim($this->request->getPost('customer_address'));
        $descriptions = $this->request->getPost('description');
        $quantities = $this->request->getPost('quantity');

        // -------------------------
        // 1. Validate inputs
        // -------------------------
        if (empty($customerId) || empty($address)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Please fill all required fields.'
            ]);
        }

        $validItems = 0;
        foreach ($descriptions as $key => $desc) {
            $desc = trim($desc);
            $qty = floatval($quantities[$key] ?? 0);
            if ($desc !== '' && $qty > 0) {
                $validItems++;
            }
        }
        if ($validItems === 0) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Please add at least one item with description and quantity.'
            ]);
        }

        // -------------------------
        // 2. Fetch customer details
        // -------------------------
        $customer = $customerModel->find($customerId);
        if (!$customer) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Customer not found.']);
        }

        $name = $customer['name'];
        $phone = $customer['phone'];
        $userId = session()->get('user_id') ?? 1;
        // $companyId = 1;

        // -------------------------
        // 3. Prepare enquiry data
        // -------------------------
        $enquiryData = [
            'customer_id' => $customerId,
            'name' => $name,
            'address' => $address,
            'phone' => $phone,
            'user_id' => $userId,
            // 'company_id'  => $companyId,
            'is_deleted' => 0,
            'created_by' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        // -------------------------
        // 4. Update existing enquiry
        // -------------------------
        if (!empty($enquiryId)) {
            $existing = $enquiryModel->find($enquiryId);
            if (!$existing) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Enquiry not found.'
                ]);
            }

            $hasChanges = (
                $existing['customer_id'] != $customerId ||
                $existing['address'] !== $address
            );

            if ($hasChanges) {
                $enquiryData['updated_by'] = $userId;
                $enquiryData['updated_at'] = date('Y-m-d H:i:s');

                $enquiryModel->update($enquiryId, $enquiryData);

                // Delete and reinsert items
                $enquiryItemModel->where('enquiry_id', $enquiryId)->delete();
                foreach ($descriptions as $key => $desc) {
                    $desc = trim($desc);
                    $qty = floatval($quantities[$key] ?? 0);
                    if ($desc !== '' && $qty > 0) {
                        $enquiryItemModel->insert([
                            'enquiry_id' => $enquiryId,
                            'description' => $desc,
                            'quantity' => $qty,
                            'created_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                }

                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Enquiry updated successfully.',
                    'enquiry_id' => $enquiryId
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => 'nochange',
                    'message' => 'No changes detected.',
                    'enquiry_id' => $enquiryId
                ]);
            }
        }

        // -------------------------
        // 5. New enquiry — Generate enquiry number
        // -------------------------
        $lastEnquiry = $enquiryModel
            // ->where('company_id', $companyId)
            ->orderBy('enquiry_no', 'DESC')
            ->first();

        $nextEnquiryNo = $lastEnquiry ? $lastEnquiry['enquiry_no'] + 1 : 1;
        $enquiryData['enquiry_no'] = $nextEnquiryNo;

        // Insert new enquiry
        $enquiryId = $enquiryModel->insert($enquiryData);

        // Insert enquiry items
        foreach ($descriptions as $key => $desc) {
            $desc = trim($desc);
            $qty = floatval($quantities[$key] ?? 0);
            if ($desc !== '' && $qty > 0) {
                $enquiryItemModel->insert([
                    'enquiry_id' => $enquiryId,
                    'description' => $desc,
                    'quantity' => $qty,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Enquiry generated successfully.',
            'enquiry_id' => $enquiryId,
            'enquiry_no' => $nextEnquiryNo
        ]);
    }


    // List all enquiries (basic)
    public function list()
    {
        $SupplierModel = new SupplierModel();
        $data['enquiries'] = $SupplierModel->where('is_deleted', 0)->findAll();
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
            1 => 'name',
            2 => 'address',
            3 => 'contact_person_name'
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
                'name' => ucwords(strtolower($row['name'] ?? '')),
                'address' => ucwords(strtolower($row['address'] ?? '')),
                'contact_person_name' => ucwords(strtolower($row['contact_person_name'] ?? '')),
                'is_converted' => $row['is_converted'] ?? 0,
            ];
        }

        // Get count
        $filteredTotal = $model->getFilteredSupplierCount($search);
        $totalRecords = $model->where('is_deleted', 0)->countAllResults();

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

        if ($model->update($id, ['is_deleted' => 1])) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Enquiry Deleted Successfully']);
        }
        return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to Delete Enquiry']);
    }

    public function convertToEstimate($id)
    {
        $supplierModel = new SupplierModel();

        // Check if enquiry exists
        $enquiry = $supplierModel->find($id);
        if (!$enquiry) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Enquiry not found");
        }

        // Mark enquiry as converted
        $supplierModel->update($id, ['is_converted' => 1]);

        // Redirect to estimate creation page with enquiry_id
        return redirect()->to(base_url('estimate/add_estimate?enquiry_id=' . $id));
    }

    public function markConverted()
    {
        $id = $this->request->getPost('enquiry_id');
        $model = new SupplierModel();

        if (!$id) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid enquiry ID.'
            ]);
        }

        if ($model->update($id, ['is_converted' => 1])) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Enquiry marked as converted.'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Failed to update enquiry.'
        ]);
    }



}
