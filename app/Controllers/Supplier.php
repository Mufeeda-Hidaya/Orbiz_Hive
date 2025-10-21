<?php
namespace App\Controllers;

use App\Models\SupplierModel;
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

    // Create or Update Supplier
    public function create()
    {
        $session = session();
        $name = ucwords(strtolower(trim($this->request->getPost('name'))));
        $address = ucfirst(strtolower(trim($this->request->getPost('address'))));
        $supplier_id = $this->request->getPost('supplier_id');

        if (empty($name) || empty($address)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Name and Address are required'
            ]);
        }

        $model = new SupplierModel();
        $data = [
            'name'       => $name,
            'address'    => $address,
            'company_id' => $session->get('company_id'),
            'is_deleted' => 0
        ];

        if (!empty($supplier_id)) {
            $updated = $model->update($supplier_id, $data);

            if ($updated) {
                $data['supplier_id'] = $supplier_id;
                return $this->response->setJSON([
                    'status'   => 'success',
                    'message'  => 'Supplier Updated Successfully',
                    'supplier' => $data
                ]);
            }
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to Update Supplier'
            ]);
        } else {
            $id = $model->insert($data);
            if ($id) {
                $data['supplier_id'] = $id;
                return $this->response->setJSON([
                    'status'   => 'success',
                    'message'  => 'Supplier Created Successfully',
                    'supplier' => $data
                ]);
            }
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to Save Supplier'
            ]);
        }
    }

    // Get Supplier Address
    public function get_address()
    {
        $supplier_id = $this->request->getPost('supplier_id');
        $model = new SupplierModel();
        $supplier = $model->find($supplier_id);

        if ($supplier) {
            return $this->response->setJSON(['status' => 'success', 'address' => $supplier['address']]);
        }
        return $this->response->setJSON(['status' => 'error', 'message' => 'Supplier Not Found']);
    }

    // Search Supplier for Select2 / autocomplete
    public function search()
    {
        $term = $this->request->getGet('term');
        $model = new SupplierModel();
        $results = $model
            ->where('is_deleted', 0)
            ->like('name', $term)
            ->select('supplier_id, name, address')
            ->orderBy('supplier_id', 'DESC')
            ->findAll(10);

        $data = [];
        foreach ($results as $row) {
            $data[] = [
                'id'      => $row['supplier_id'],
                'text'    => $row['name'],
                'address' => $row['address']
            ];
        }
        return $this->response->setJSON($data);
    }

    // List all suppliers (basic)
    public function list()
    {
        $session = session();
        $SupplierModel = new SupplierModel();
        $company_id = $session->get('company_id');

        $data['suppliers'] = $SupplierModel
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
            0 => 'supplier_id',
            1 => 'name',
            2 => 'address'
        ];
        $orderColumn = $columnMap[$columnIndex] ?? 'supplier_id';

        $suppliers = $model->getAllFilteredRecords($search, $start, $length, $orderColumn, $orderDir, $company_id);

        $result = [];
        $slno = $start + 1;

        foreach ($suppliers as $row) {
            $result[] = [
                'slno'        => $slno++,
                'supplier_id' => $row['supplier_id'],
                'name'        => ucwords(strtolower($row['name'])),
                'address'     => ucwords(strtolower($row['address'])),
            ];
        }

        $filteredTotal = $model->getFilteredSupplierCount($search, $company_id)->countSuppliers;

        return $this->response->setJSON([
            'draw'            => intval($draw),
            'recordsTotal'    => $filteredTotal,
            'recordsFiltered' => $filteredTotal,
            'data'            => $result
        ]);
    }

    // Get single supplier
    public function getSupplier($id)
    {
        $model = new SupplierModel();
        $supplier = $model->find($id);

        if ($supplier) {
            return $this->response->setJSON($supplier);
        }
        return $this->response->setJSON(['status' => 'error', 'message' => 'Supplier Not Found']);
    }

    // Edit Supplier View
    public function edit($id)
    {
        $model = new SupplierModel();
        $data['supplier'] = $model->find($id);

        if (!$data['supplier']) {
            return redirect()->to(base_url('supplier/list'))->with('error', 'Supplier Not Found');
        }
        return view('editsupplier', $data);
    }

    // Soft Delete Supplier
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
