<?php 
namespace App\Controllers;
use App\Models\CustomerReportModel;
use CodeIgniter\Controller;

class CustomerReport extends Controller
{
    public function index()
    {
        $companyId = session()->get('company_id'); // current logged-in company
        
        $customerModel = new \App\Models\customerModel();
        $data['customers'] = $customerModel
            ->where('company_id', $companyId)  // only customers of this company
            ->where('is_deleted', 0)           // exclude deleted customers
            ->findAll();

        return view('customer_report', $data);
    }

    public function getReport()
    {
        $request = \Config\Services::request();
        $customer_id = $request->getPost('customer_id');
        $type = $request->getPost('type'); // 'invoice' or 'estimate'
        $companyId = session()->get('company_id'); // current logged-in company
        $db = db_connect();

        $data = [];

        if ($type == 'invoice') {
            $builder = $db->table('invoices i')
                ->select('i.*, c.name as customer_name')
                ->join('customers c', 'c.customer_id = i.customer_id AND c.company_id = '.$companyId.' AND c.is_deleted = 0', 'left')
                ->where('i.company_id', $companyId)
                ->orderBy('i.invoice_id', 'DESC');

            if(!empty($customer_id)){
                $builder->where('i.customer_id', $customer_id);
            }

            $invoices = $builder->get()->getResultArray();

            foreach ($invoices as $inv) {
                $items = $db->table('invoice_items')->where('invoice_id', $inv['invoice_id'])->get()->getResultArray();
                $subtotal = array_sum(array_map(fn($item) => $item['quantity'] * $item['price'], $items));

                $data[] = [
                    'id' => $inv['invoice_id'],
                    'no' => $inv['invoice_no'],
                    'customer' => $inv['customer_name'],
                    'subtotal' => $subtotal,
                    'discount' => $inv['discount'],
                    'total' => $inv['total_amount'],
                    'paid' => $inv['paid_amount'],
                    'balance' => $inv['balance_amount'],
                    'date' => $inv['invoice_date']
                ];
            }
        }

        if ($type == 'estimate') {
            $builder = $db->table('estimates e')
                ->select('e.*, c.name as customer_name')
                ->join('customers c', 'c.customer_id = e.customer_id AND c.company_id = '.$companyId.' AND c.is_deleted = 0', 'left')
                ->where('e.company_id', $companyId)
                ->orderBy('e.estimate_id', 'DESC');

            if(!empty($customer_id)){
                $builder->where('e.customer_id', $customer_id);
            }

            $estimates = $builder->get()->getResultArray();

            foreach ($estimates as $est) {
                $items = $db->table('estimate_items')->where('estimate_id', $est['estimate_id'])->get()->getResultArray();
                $subtotal = array_sum(array_map(fn($item) => $item['quantity'] * $item['price'], $items));

                $data[] = [
                    'id' => $est['estimate_id'],
                    'no' => $est['estimate_no'],
                    'customer' => $est['customer_name'],
                    'subtotal' => $subtotal,
                    'discount' => $est['discount'],
                    'total' => $est['total_amount'],
                    'paid' => null,
                    'balance' => null,
                    'date' => $est['date']
                ];
            }
        }

        return $this->response->setJSON($data);
    }
}
