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
        $estimateModel = new EstimateModel();
        $estimateItemModel = new EstimateItemModel();
        $customerModel = new customerModel();
 
        $companyId = 1;
        $data['customers'] = $customerModel
            ->where('is_deleted', 0)
            ->where('company_id', $companyId)
            ->orderBy('customer_id', 'DESC')
            ->findAll();
 
        $data['enquiry'] = null;
        $data['items'] = [];
 
        if ($id) {
            $data['enquiry'] = $estimateModel->find($id);
            $data['items'] = $estimateItemModel->where('enquiry_id', $id)->findAll();
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

    if (!$customer_id || !$address || empty($descriptions) || empty($quantities)) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Please fill all required fields and add at least one item.'
        ]);
    }

    $customer = $customerModel->find($customer_id);
    if (!$customer) {
        return $this->response->setJSON(['status'=>'error','message'=>'Customer not found']);
    }

    $name  = $customer['name'];
    $phone = $customer['phone'];
    $user_id = session()->get('user_id') ?? 1;

    // Create enquiry
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
        $enquiry_id = $enquiry_id;
        // Optional: delete old items and re-insert
        $enquiryItemModel->where('enquiry_id',$enquiry_id)->delete();
    } else {
        $enquiry_id = $enquiryModel->insert($enquiryData);
    }

    // Insert enquiry items
    foreach ($descriptions as $key => $desc) {
        if(!empty($desc) && !empty($quantities[$key])){
            $enquiryItemModel->insert([
                'enquiry_id' => $enquiry_id,
                'description'=> $desc,
                'quantity'   => $quantities[$key],
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    return $this->response->setJSON(['status'=>'success','message'=>'Enquiry saved successfully']);
}


    // Get customer Address
    public function get_address()
    {
        $enquiry_id = $this->request->getPost('enquiry_id');
        $model = new SupplierModel();
        $supplier = $model->find($enquiry_id);

        if ($supplier) {
            return $this->response->setJSON(['status' => 'success', 'address' => $supplier['address']]);
        }
        return $this->response->setJSON(['status' => 'error', 'message' => 'Supplier Not Found']);
    }

    // Search 
    public function search()
    {
        $term = $this->request->getGet('term');
        $model = new SupplierModel();
        $results = $model
            ->where('is_deleted', 0)
            ->like('name', $term)
            ->select('enquiry_id, name, address')
            ->orderBy('enquiry_id', 'DESC')
            ->findAll(10);

        $data = [];
        foreach ($results as $row) {
            $data[] = [
                'id'      => $row['enquiry_id'],
                'text'    => $row['name'],
                'address' => $row['address']
            ];
        }
        return $this->response->setJSON($data);
    }

    // List all enquiries (basic)
    public function list()
    {
        $session = session();
        $SupplierModel = new SupplierModel();
        $company_id = $session->get('company_id');

        $data['enquiries'] = $SupplierModel
            ->where('company_id', $company_id)
            ->where('is_deleted', 0)
            ->findAll();

        return view('supplierlist', $data);
    }

    // Fetch for DataTables
    public function fetch()
    {
        $session = session();
        $request = service('request');
        $model = new SupplierModel();
        $company_id = $session->get('company_id');

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

        $enquiries = $model->getAllFilteredRecords($search, $start, $length, $orderColumn, $orderDir, $company_id);

        $result = [];
        $slno = $start + 1;

        foreach ($enquiries as $row) {
            $result[] = [
                'slno'        => $slno++,
                'enquiry_id' => $row['enquiry_id'],
                'name'        => ucwords(strtolower($row['name'])),
                'address'     => ucwords(strtolower($row['address'])),
            ];
        }

        $filteredTotal = $model->getFilteredSupplierCount($search, $company_id)->countEnquiries;

        return $this->response->setJSON([
            'draw'            => intval($draw),
            'recordsTotal'    => $filteredTotal,
            'recordsFiltered' => $filteredTotal,
            'data'            => $result
        ]);
    }

    // Get single enquiry
    public function getSupplier($id)
    {
        $model = new SupplierModel();
        $supplier = $model->find($id);

        if ($supplier) {
            return $this->response->setJSON($supplier);
        }
        return $this->response->setJSON(['status' => 'error', 'message' => 'Supplier Not Found']);
    }

    // Edit 
    public function edit($id)
    {
        $model = new SupplierModel();
        $data['supplier'] = $model->find($id);

        if (!$data['supplier']) {
            return redirect()->to(base_url('supplier/list'))->with('error', 'Supplier Not Found');
        }
        return view('editsupplier', $data);
    }

    // Soft Delete 
    public function delete()
    {
        $id = $this->request->getPost('id');
        $model = new SupplierModel();

        if ($model->update($id, ['is_deleted' => 1])) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Supplier Deleted Successfully']);
        }
        return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to Delete Supplier']);
    }
}
