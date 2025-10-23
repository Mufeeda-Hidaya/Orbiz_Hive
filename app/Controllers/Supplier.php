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

    $companyId = 1;

    // Load all customers
    $data['customers'] = $customerModel
        ->where('is_deleted', 0)
        ->where('company_id', $companyId)
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
    $enquiryModel     = new SupplierModel();
    $enquiryItemModel = new EnquiryItemModel();
    $customerModel    = new CustomerModel();

    $enquiry_id   = $this->request->getPost('enquiry_id');
    $customer_id  = $this->request->getPost('customer_id');
    $address      = $this->request->getPost('customer_address');
    $descriptions = $this->request->getPost('description'); 
    $quantities   = $this->request->getPost('quantity');    

    // Validate customer and address
    if (!$customer_id || !$address) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Please fill all required fields.'
        ]);
    }

    // Validate at least one valid item
    $validItemExists = false;
    foreach ($descriptions as $key => $desc) {
        $desc = trim($desc);
        $qty  = floatval($quantities[$key] ?? 0);
        if ($desc !== '' && $qty > 0) {
            $validItemExists = true;
            break;
        }
    }

    if (!$validItemExists) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Please add at least one item with description and quantity.'
        ]);
    }

    $customer = $customerModel->find($customer_id);
    if (!$customer) {
        return $this->response->setJSON(['status'=>'error','message'=>'Customer not found']);
    }

    $name  = $customer['name'];
    $phone = $customer['phone'];
    $user_id = session()->get('user_id') ?? 1;

    $enquiryData = [
        'enquiry_no' => $enquiry_id ? null : 'ENQ-'.strtoupper(uniqid()),
        'customer_id'=> $customer_id,
        'name'       => $name,
        'address'    => $address,
        'user_id'    => $user_id,
        'phone'      => $phone,
        'created_by' => $user_id,
        'created_at' => date('Y-m-d H:i:s'),
        'company_id' => 1,
        'is_deleted' => 0
    ];

    if ($enquiry_id) {
        $enquiryData['updated_by'] = $user_id;
        $enquiryData['updated_at'] = date('Y-m-d H:i:s');
        $enquiryModel->update($enquiry_id, $enquiryData);
        $enquiryItemModel->where('enquiry_id', $enquiry_id)->delete();
    } else {
        $enquiry_id = $enquiryModel->insert($enquiryData);
    }

    foreach ($descriptions as $key => $desc) {
        $desc = trim($desc);
        $qty  = floatval($quantities[$key] ?? 0);
        if ($desc !== '' && $qty > 0) {
            $enquiryItemModel->insert([
                'enquiry_id' => $enquiry_id,
                'description'=> $desc,
                'quantity'   => $qty,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    return $this->response->setJSON(['status'=>'success','message'=>'Enquiry saved successfully']);
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
            2 => 'address'
        ];
        $orderColumn = $columnMap[$columnIndex] ?? 'enquiry_id';

        // Fetch filtered data
        $enquiries = $model->getAllFilteredRecords($search, $start, $length, $orderColumn, $orderDir);

        $result = [];
        $slno = $start + 1;

        foreach ($enquiries as $row) {
            $result[] = [
                'slno'       => $slno++,
                'enquiry_id' => $row['enquiry_id'],
                'name'       => ucwords(strtolower($row['name'] ?? '')),
                'address'    => ucwords(strtolower($row['address'] ?? '')),
            ];
        }

        // Get count
        $filteredTotal = $model->getFilteredSupplierCount($search);
        $totalRecords = $model->where('is_deleted', 0)->countAllResults();

        return $this->response->setJSON([
            'draw'            => intval($draw),
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $filteredTotal,
            'data'            => $result
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
}
