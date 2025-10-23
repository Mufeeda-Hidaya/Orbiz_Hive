<?php
namespace App\Controllers;

use App\Models\customerModel;
use CodeIgniter\Controller;

class Customer extends BaseController
{
    public function __construct()
    {
       $session = \Config\Services::session();
        if (!$session->get('logged_in')) {
            header('Location: ' . base_url('/'));
            exit;
        }
    }
    public function create()
    {
        $session = session();
        $company_id = $this->request->getPost('company_id') ?? $session->get('company_id') ?? 1;
        $user_id = $session->get('user_id'); 

        $name = ucwords(strtolower(trim($this->request->getPost('name'))));
        $address = ucfirst(strtolower(trim($this->request->getPost('address'))));
        $customer_id = $this->request->getPost('customer_id'); 
        $phone = preg_replace('/[^0-9\+\-\(\)\s]/', '', trim($this->request->getPost('phone')));
        $max_discount = $this->request->getPost('max_discount');

        // Validate required fields
        if (empty($name) || empty($address) || empty($user_id)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'All fields Are Required'
            ]);
        }

        $model = new customerModel();
        $data = [
            'name' => $name,
            'address' => $address,
            'phone' => $phone,
            'company_id' => $company_id,
            'user_id' => $user_id, // always from session
            'max_discount' => round((float)($max_discount ?? 0), 6)
        ];

        if (!empty($customer_id)) {
            $updated = $model->update($customer_id, $data);

            if ($updated) {
                $data['customer_id'] = $customer_id;
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Customer Updated Successfully',
                    'customer' => $data
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Failed To Update Customer.'
                ]);
            }

        } else {
            // Insert new customer
            $id = $model->insert($data);

            if ($id) {
                $data['customer_id'] = $id;
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Customer Created Successfully',
                    'customer' => $data
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Failed To Save Customer.'
                ]);
            }
        }
    }


    public function get_address()
    {
        $customer_id = $this->request->getPost('customer_id');
        $model = new customerModel();
        $customer = $model->find($customer_id);

        if ($customer) {
            return $this->response->setJSON(['status' => 'success', 'address' => $customer['address']]);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Customer Not Found']);
        }
    }
    public function search()
{
    $term = $this->request->getGet('term');

    $model = new customerModel();
    $results = $model
        ->where('is_deleted', 0) 
        ->like('name', $term)
        ->select('customer_id, name, address')
        ->orderBy('customer_id', 'DESC') 
        ->findAll(10); 

    $data = [];
    foreach ($results as $row) {
        $data[] = [
            'id' => $row['customer_id'],
            'text' => $row['name'],
            'address' => $row['address']
        ];
    }

    return $this->response->setJSON($data);
}

public function list()
{
    $session = session();
    $customerModel = new customerModel();
    $company_id = $session->get('company_id');

   $data['customers'] = $customerModel
        ->where('company_id', $company_id)
        ->where('is_deleted', 0)
        ->findAll();

    return view('customerlist', $data);
}

public function fetch()
{
    $session = session();
    $request = service('request');
    $model = new CustomerModel();
    $company_id = $session->get('company_id');

    $draw   = $request->getPost('draw') ?? 1;
    $start  = $request->getPost('start') ?? 0;
    $length = $request->getPost('length') ?? 10;
    $order  = $request->getPost('order');
    $search = trim($request->getPost('search')['value'] ?? '');

    // Column mapping for sorting
    $columnMap = [
        0 => 'customer_id', // hidden
        1 => 'name',
        2 => 'address',
        3 => 'phone'
    ];

    $columnIndex = $order[0]['column'] ?? 0;
    $orderDir = $order[0]['dir'] ?? 'desc';
    $orderColumn = $columnMap[$columnIndex] ?? 'customer_id';

    $customers = $model->getAllFilteredRecords($search, $start, $length, $orderColumn, $orderDir, $company_id);

    $result = [];
    $slno = $start + 1;

    foreach ($customers as $row) {
        $result[] = [
            'slno'        => $slno++,
            'customer_id' => $row['customer_id'],
            'name'        => ucwords(strtolower($row['name'])),
            'address'     => ucwords(strtolower($row['address'])),
            'phone'       => $row['phone'] ?? ''
        ];
    }

    $filteredTotal = $model->getFilteredCustomerCount($search, $company_id)->filCustomers;

    return $this->response->setJSON([
        'draw' => intval($draw),
        'recordsTotal' => $filteredTotal,
        'recordsFiltered' => $filteredTotal,
        'data' => $result
    ]);
}



public function getCustomer($id)
{
    $model = new customerModel();
    $customer = $model->find($id);

    if ($customer) {
        return $this->response->setJSON([
            'status' => 'success',
            'customer' => $customer
        ]);
    } else {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Customer not found'
        ]);
    }
}


public function edit($id)
{
    $model = new customerModel();
    $data['customer'] = $model->find($id);

    if (!$data['customer']) {
        return redirect()->to(base_url('customer/list'))->with('error', 'Customer Not Found');
    }

    return view('editcustomer', $data);
}


public function delete()
{
    $id = $this->request->getPost('id');
    $model = new customerModel();

    if ($model->update($id, ['is_deleted' => 1])) {
        return $this->response->setJSON(['status' => 'success', 'message' => 'Customer Deleted Successfully.']);
    }else {
        return $this->response->setJSON(['status' => 'error', 'message' => 'Failed To Delete Customer.']);
    }
}
public function viewByCustomer($customerId)
{
    $estimateModel = new Estimate_Model(); 
    $data['estimates'] = $estimateModel->where('customer_id', $customerId)->findAll();
    $data['customer'] = (new Customer_Model())->find($customerId);
    
    return view('estimate/customer_estimates', $data); 
}
public function get_discount($id)
{
    $customerModel = new \App\Models\customerModel();
    $customer = $customerModel->find($id);

    if ($customer) {
        return $this->response->setJSON([
            'discount' => isset($customer['max_discount'])
                ? round((float)$customer['max_discount'], 6)
                : 0.000000
        ]);
    } else {
        return $this->response->setJSON(['discount' => 0.000000]);
    }
}

}