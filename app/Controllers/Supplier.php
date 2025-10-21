<?php
namespace App\Controllers;

use App\Models\SupplierModel;
use App\Models\EstimateModel;
use App\Models\EstimateItemModel;
use App\Models\customerModel;
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
