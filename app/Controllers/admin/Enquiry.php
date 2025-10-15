<?php

namespace App\Controllers\admin;

use App\Controllers\BaseController;
use App\Models\admin\EnquiryModel;
use App\Models\admin\UserModel;

class Enquiry extends BaseController
{
    protected $session;
    protected $input;
    protected $enquiryModel;

    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->input = \Config\Services::request();
        $this->enquiryModel = new EnquiryModel();
    }

    public function index()
    {
        $template = view('admin/common/header');
        $template .= view('admin/common/left_menu');
        $template .= view('admin/manage_enquiry');
        $template .= view('admin/common/footer');
        $template .= view('admin/page_scripts/enquiryjs'); 
        return $template;
    }

public function view($id)
{
    $enquiry = $this->enquiryModel
        ->select('e.enquiry_id, e.created_at, e.user_id, e.product_name, e.quantity, u.name, u.email')
        ->from('enquiries e')
        ->join('user u', 'u.user_id = e.user_id', 'left')
        ->where('e.enquiry_id', $id)
        ->get()
        ->getRow();

    $data['enquiry'] = $enquiry ? $enquiry : null;

    $template  = view('admin/common/header');
    $template .= view('admin/common/left_menu');
    $template .= view('admin/view_enquiry', $data);
    $template .= view('admin/common/footer');

    return $template;
}
    public function orderListAjax()
    {
        $draw = intval($this->request->getPost('draw') ?? 1);
        $start = intval($this->request->getPost('start') ?? 0);
        $length = intval($this->request->getPost('length') ?? 10);
        $searchValue = $this->request->getPost('search')['value'] ?? '';

        $condition = "1=1";

        if (!empty($searchValue)) {
            $searchValue = trim(preg_replace('/\s+/', ' ', $searchValue));
            $noSpaceSearch = str_replace(' ', '', strtolower($searchValue));
            $condition .= " AND REPLACE(LOWER(u.name), ' ', '') LIKE '%" .
                $this->enquiryModel->db->escapeLikeString($noSpaceSearch) . "%'";
        }

        $columns = ['u.name', 'e.created_at', 'e.enquiry_id'];
        $orderColumnIndex = intval($this->request->getPost('order')[0]['column'] ?? 0);
        $orderDir = $this->request->getPost('order')[0]['dir'] ?? 'desc';
        $orderBy = $columns[$orderColumnIndex] ?? 'e.enquiry_id';

        $records = $this->enquiryModel->getAllFilteredRecords($condition, $start, $length, $orderBy, $orderDir);

        $data = [];
        $slno = $start + 1;

        foreach ($records as $row) {
            $data[] = [
                'slno' => $slno++,
                'enquiry_id' => $row->enquiry_id,
                'customer_name' => $row->name,
                'created_at' => date("d M Y", strtotime($row->created_at))
            ];
        }

        $recordsTotal = $this->enquiryModel->getAllOrderCount();
        $recordsFilteredObj = $this->enquiryModel->getFilterOrderCount($condition);
        $recordsFiltered = $recordsFilteredObj->filRecords ?? 0;

        return $this->response->setJSON([
            "draw" => $draw,
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            "data" => $data
        ]);
    }
    


}
