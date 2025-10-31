<?php
 
namespace App\Controllers;
 
use App\Controllers\BaseController;
use App\Models\EstimateModel;
use App\Models\EstimateItemModel;
use App\Models\customerModel;
use App\Models\Manageuser_Model;
use App\Models\SupplierModel;
 use App\Models\Managecompany_Model;
 use App\Models\RoleModel;
 use Google\Cloud\Translate\V2\TranslateClient;


class Estimate extends BaseController
{
    public function estimatelist()
    {
        return view('estimatelist');
    }
    public function __construct(){
       $this->session = \Config\Services::session();
 
       $session = \Config\Services::session();
        if (!$session->get('logged_in')) {
            header('Location: ' . base_url('/'));
            exit;
        }
    } 
 
  public function add_estimate()
    {
        $db = \Config\Database::connect();
        $enquiry_id = $this->request->getGet('enquiry_id');
        $data = [];

        if ($enquiry_id) {
            $enquiry = $db->table('enquiries')
                          ->where('enquiry_id', $enquiry_id)
                          ->get()
                          ->getRowArray();

            if ($enquiry) {
                $data['estimate'] = [
                    'customer_id' => $enquiry['customer_id'] ?? '',
                    'customer_name' => $enquiry['name'] ?? '',
                    'customer_address' => $enquiry['address'] ?? '',
                    'phone_number' => $enquiry['phone'] ?? '',
                    'lpo_no' => '',
                    'discount' => 0,
                ];

                $items = $db->table('enquiry_items')
                            ->where('enquiry_id', $enquiry_id)
                            ->get()
                            ->getResultArray();

                $data['items'] = [];

                foreach ($items as $index => $item) {
                    $data['items'][] = [
                        'description'   => $item['description'] ?? $item['item_name'] ?? '',
                        'price'         => $item['price'] ?? $item['unit_price'] ?? 0,
                        'selling_price' => $item['selling_price'] ?? $item['price'] ?? $item['unit_price'] ?? 0,
                        'quantity'      => $item['quantity'] ?? 1,
                        'total'         => $item['total'] ?? 0,
                        'location'      => $item['location'] ?? '',
                        'item_order'    => $index + 1,
                    ];
                }
            }
        }
        $data['customers'] = $db->table('customers')
                                ->select('customer_id, name')
                                ->get()
                                ->getResultArray();

        return view('add_estimate', $data);
    }





 public function save()
{
    $estimateId   = $this->request->getPost('estimate_id');
    $customerId   = $this->request->getPost('customer_id');
    $address      = trim($this->request->getPost('customer_address'));
    $discount     = floatval($this->request->getPost('discount') ?? 0);
    $description  = $this->request->getPost('description');
    $price        = $this->request->getPost('price'); 
    $sellingPrice = $this->request->getPost('selling_price'); 
    $quantity     = $this->request->getPost('quantity');
    $total        = $this->request->getPost('total');
    $customerName = trim($this->request->getPost('customer_name'));
    $phoneNumber  = trim($this->request->getPost('phone_number'));
    $maxDiscount  = floatval($this->request->getPost('max_discount') ?? 0);

    if (empty($customerId) || empty($address)) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Please Fill Customer Name and Address.'
        ]);
    }

    // Validate items
    $validItems = 0;
    foreach ($description as $desc) {
        if (!empty(trim($desc))) $validItems++;
    }
    if ($validItems === 0) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Please Fill at Least One Item With Description.'
        ]);
    }

    // Calculate subtotal
    $subtotal = '0.0000';
    foreach ($total as $t) {
        $subtotal = bcadd($subtotal, (string)$t, 4);
    }

    // Validate discount
    if ($discount > $subtotal || ($maxDiscount > 0 && $discount > $maxDiscount)) {
        $allowedDiscount = $maxDiscount > 0 ? min($subtotal, $maxDiscount) : $subtotal;
        return $this->response->setJSON([
            'status' => 'error',
            'message' => "Discount cannot exceed maximum allowed: " . number_format($allowedDiscount, 4) . " AED."
        ]);
    }

    $grandTotal = bcsub($subtotal, (string)$discount, 4);

    $estimateData = [
        'customer_id'      => $customerId,
        'customer_address' => $address,
        'discount'         => $discount,
        'sub_total'        => $subtotal,
        'total_amount'     => $grandTotal,
        'date'             => date('Y-m-d'),
        'phone_number'     => $phoneNumber,
        'company_id'       => 1
    ];
    $itemsArray = [];
    foreach ($description as $key => $desc) {
        $descVal = trim($desc);
        $mPrice  = floatval($this->request->getPost('market_price')[$key] ?? 0); 
        $sPrice  = floatval($sellingPrice[$key] ?? 0);
        $qty     = floatval($quantity[$key] ?? 0);

        if ($descVal === '' || $sPrice <= 0 || $qty <= 0) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Each item must have description, selling price, and quantity.'
            ]);
        }

        // Calculate total
        $lineTotal = bcmul((string)$sPrice, (string)$qty, 4);

        //  Calculate difference percentage (based on market vs selling)
        $differencePercentage = 0;
        if ($mPrice > 0) {
            $differencePercentage = (($sPrice - $mPrice) / $mPrice) * 100;
        }

        //  Push item to array
        $itemsArray[] = [
            'description'           => $descVal,
            'market_price'          => number_format($mPrice, 4, '.', ''),
            'selling_price'         => number_format($sPrice, 4, '.', ''),
            'difference_percentage' => number_format($differencePercentage, 2, '.', ''),
            'quantity'              => number_format($qty, 4, '.', ''),
            'total'                 => $lineTotal,
            'item_order'            => $key + 1
        ];
    }



    $estimateModel = new EstimateModel();

    if (!empty($estimateId)) {
        $estimateModel->updateEstimateWithItems($estimateId, $estimateData, $itemsArray);
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Estimate Updated Successfully.',
            'estimate_id' => $estimateId
        ]);
    } else {
        $estimateId = $estimateModel->insertEstimateWithItems($estimateData, $itemsArray);
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Estimate Generated Successfully.',
            'estimate_id' => $estimateId
        ]);
    }
}


 public function estimatelistajax()
{
    $request = service('request');
    $draw = $request->getPost('draw');
    $start = $request->getPost('start');
    $length = $request->getPost('length');
    $searchValue = trim($request->getPost('search')['value'] ?? '');

    $orderColumnIndex = $request->getPost('order')[0]['column'] ?? 8;
    $orderDir = $request->getPost('order')[0]['dir'] ?? 'desc';

    $columns = [
        0 => 'estimate_id',         
        1 => 'customers.name',
        2 => 'customers.address',
        3 => 'estimates.total_amount', 
        4 => 'estimates.discount',
        5 => 'estimates.total_amount',
        6 => 'estimates.date',
        7 => 'estimates.estimate_id',
        8 => 'estimates.estimate_id'
    ];
    $orderByColumn = $columns[$orderColumnIndex] ?? 'estimates.estimate_id';

    $companyId = 1;

    $estimateModel = new EstimateModel();
    $itemModel = new EstimateItemModel();

    $totalRecords = $estimateModel->getEstimateCount($companyId);
    $filteredRecords = $estimateModel->getFilteredCount($searchValue, $companyId);
    $records = $estimateModel->getFilteredEstimates($searchValue, $start, $length, $orderByColumn, $orderDir, $companyId);

    $data = [];
    $slno = $start + 1;

    foreach ($records as $row) {
        $items = $itemModel->where('estimate_id', $row['estimate_id'])->findAll();
        $descList = array_column($items, 'description');

        $subtotal = '0.0000';
        foreach ($items as $item) {
            $subtotal = bcadd((string)$subtotal, (string)$item['total'], 4);
        }

        //  Format values to 4 decimals
        $formattedSubtotal = number_format((float)$subtotal, 4, '.', '');
        $formattedTotal = number_format((float)$row['total_amount'], 4, '.', '');
        $formattedDiscount = number_format((float)($row['discount'] ?? 0), 4, '.', '');

        $data[] = [
            'slno'              => $slno++,
            'estimate_id'       => $row['estimate_id'],
            'estimate_no'       => $row['estimate_no'],
            'customer_name'     => $row['customer_name'],
            'customer_address'  => $row['customer_address'],
            'subtotal'          => $formattedSubtotal,
            'discount'          => $formattedDiscount,
            'total_amount'      => $formattedTotal,
            'date'              => $row['date'],
            'description'       => implode(', ', $descList),
            'is_converted'      => $row['is_converted'],
        ];
    }

    return $this->response->setJSON([
        'draw' => intval($draw),
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $filteredRecords,
        'data' => $data,
    ]);
}

 
        public function delete()
    {
        $estimate_id = $this->request->getPost('estimate_id');

        if (!$estimate_id) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid Estimate ID.'
            ]);
        }

        $estimateModel = new EstimateModel();
        $update = $estimateModel->update($estimate_id, ['is_deleted' => 1]);

        if ($update) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Estimate deleted successfully.'
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to delete estimate. Please try again.'
            ]);
        }
    }

 
  public function edit($id)
{
    $estimateModel = new EstimateModel();
    $estimateItemModel = new EstimateItemModel();
    $customerModel = new CustomerModel();

    // Get the estimate + customer info
    $estimate = $estimateModel
        ->select('estimates.*, customers.address AS customer_address, customers.name AS customer_name')
        ->join('customers', 'customers.customer_id = estimates.customer_id', 'left')
        ->where('estimates.estimate_id', $id)
        ->first();

    if (!$estimate) {
        return redirect()->to('estimatelist')->with('error', 'Estimate not found.');
    }

    // Get all items including market_price
    $items = $estimateItemModel
        ->select('item_id, estimate_id, description, market_price, selling_price, quantity, total, item_order')
        ->where('estimate_id', $id)
        ->orderBy('item_order', 'ASC')
        ->findAll();

    // Optional debugging (use once)
    // echo '<pre>'; print_r($items); exit;

    $customer = $customerModel->find($estimate['customer_id']);

    $data = [
        'estimate'   => $estimate,
        'items'      => $items,
        'customers'  => $customerModel->where('is_deleted', 0)->orderBy('customer_id', 'DESC')->findAll(),
        'customer'   => $customer
    ];

    return view('add_estimate', $data);
}

private function translateToArabic($text)
{
    if (empty($text)) {
        return '';
    }

    $url = "https://api.mymemory.translated.net/get?q=" . urlencode($text) . "&langpair=en|ar";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    if (!$response) {
        return $text;
    }

    $result = json_decode($response, true);
    return $result['responseData']['translatedText'] ?? $text;
}

  public function generateEstimate($id)
{
    $estimateModel = new EstimateModel();
    $itemModel = new EstimateItemModel();
    $userModel = new Manageuser_Model();
    $companyModel = new Managecompany_Model();
    $roleModel = new RoleModel();

    // Fetch estimate with customer info
    $estimate = $estimateModel
        ->select('estimates.*, customers.name AS customer_name, customers.address AS customer_address')
        ->join('customers', 'customers.customer_id = estimates.customer_id', 'left')
        ->where('estimate_id', $id)
        ->first();

    if (!$estimate) {
        return redirect()->to('/estimatelist')->with('error', 'Estimate not found.');
    }

    // Fetch related data
    // $items = $itemModel->where('estimate_id', $id)->findAll();
    $items = $itemModel->where('estimate_id', $id)->orderBy('item_order', 'ASC')->findAll();

    $userId = session()->get('user_id');
    $userName = session()->get('user_Name');
    $roleId = session()->get('role_Id'); 
    $companyId = 1;

    // Get role name
    $roleName = session()->get('role_Name');
    if (!$roleName && $roleId) {
        $role = $roleModel->find($roleId);
        $roleName = $role['role_name'] ?? '';
    }
    $companyId = $estimate['company_id'] ?? session()->get('company_id');
    $company = $companyModel->find($companyId) ?? [
        'company_name' => '',
        'company_name_ar' => '',
        'email' => '',
         'address' => '',
         'address_ar'   => '',
        'phone' => ''
    ];
  if (empty($company['company_name_ar']) && !empty($company['company_name'])) {
        $translated = $this->translateToArabic($company['company_name']);

        if (!empty($translated)) {
            $companyModel->update($companyId, ['company_name_ar' => $translated]);
            $company['company_name_ar'] = $translated;
        }
    }
     if (empty(trim($company['address_ar'])) && !empty(trim($company['address']))) {
        $translatedAddress = $this->translateToArabic($company['address']);
        if (!empty($translatedAddress) && $translatedAddress !== $company['address']) {
            $companyModel->update($companyId, ['address_ar' => $translatedAddress]);
            $company['address_ar'] = $translatedAddress;
        }
    }
    $data = [
        'estimate'      => $estimate,
        'items'         => $items,
        'user_id'       => $userId,
        'user_name'     => $userName,
        'role_name'     => $roleName,
        'company_name'  => $company['company_name'] ?? '',
        'company_name_ar'  => $company['company_name_ar'] ?? '',
          'address'      => $company['address'] ?? '',
        'address_ar'   => $company['address_ar'] ?? '',
        'company'   => $company
    ];

    // Load view
    if ($companyId == 1) {
        return view('generateestimate', $data);
    } elseif ($companyId == 1) {
        return view('generatequotation', $data);
    } else {
        return view('generateestimate', $data);
    }
}


        // dashboardlisting
    public function recentEstimates()
{
    $estimateModel = new EstimateModel();
    $itemModel = new EstimateItemModel();

    $estimates = $estimateModel->getRecentEstimatesWithCustomer(10); 

    foreach ($estimates as &$est) {
        // $items = $itemModel->where('estimate_id', $est['estimate_id'])->findAll();
        $items = $itemModel->where('estimate_id', $est['estimate_id'])->orderBy('item_order', 'ASC')->findAll();


        $subtotal = '0.000000';
        foreach ($items as $item) {
            $subtotal = bcadd((string)$subtotal, (string)$item['total'], 6);
        }

        $est['sub_total'] = $subtotal; // 6 decimals, no rounding
    }

    return $this->response->setJSON($estimates);
}

 
    public function viewByCustomer($customerId)
{
    $estimateModel = new EstimateModel();
    $itemModel = new EstimateItemModel();
    $customerModel = new customerModel();

    $estimates = $estimateModel
        ->where('customer_id', $customerId)
        ->orderBy('date', 'desc')
        ->findAll();

    foreach ($estimates as &$est) {
        // $items = $itemModel->where('estimate_id', $est['estimate_id'])->findAll();
        $items = $itemModel->where('estimate_id', $est['estimate_id'])->orderBy('item_order', 'ASC')->findAll();


        // Calculate subtotal with 6 decimals
        $subtotal = '0.0000';
        foreach ($items as $item) {
            $subtotal = bcadd((string)$subtotal, (string)$item['total'], 4);
        }

        $est['items'] = $items;
        $est['subtotal'] = $subtotal; // 6 decimals, no rounding
    }

    $customer = $customerModel->find($customerId);

    return view('customer_estimates', [
        'estimates' => $estimates,
        'customer' => $customer
    ]);
}

    // public function convertFromEnquiry($enquiryId)
    // {
    //     $enquiryModel = new SupplierModel();
    //     $customerModel = new customerModel();

    //     $enquiry = $enquiryModel->find($enquiryId);
    //     if (!$enquiry) {
    //         return redirect()->back()->with('error', 'Enquiry not found.');
    //     }

    //     // Fetch customer details (if linked)
    //     $customer = $customerModel->where('customer_id', $enquiry['customer_id'] ?? null)->first();

    //     $companyId = session()->get('company_id');
    //     $customers = $customerModel
    //         ->where('is_deleted', 0)
    //         ->where('company_id', $companyId)
    //         ->findAll();

    //     // Pass enquiry details to estimate creation view
    //     return view('estimate_form', [
    //         'enquiry' => $enquiry,
    //         'customers' => $customers,
    //         'customer' => $customer,
    //         'is_converted' => true,
    //     ]);
    // }
}