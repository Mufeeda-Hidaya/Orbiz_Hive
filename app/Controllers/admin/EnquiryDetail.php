<?php

namespace App\Controllers\admin;

use App\Controllers\BaseController;
use App\Models\EnquiryModel;
use App\Models\UserModel;
use App\Models\EnquiryDetailModel;

class EnquiryDetail extends BaseController
{
    protected $enquiryModel;
    protected $userModel;

    public function __construct()
    {
        $this->enquiryModel = new EnquiryModel();
        $this->userModel = new UserModel();
        $this->enquiryModel = new EnquiryDetailModel();
    }

    public function index()
    {
        $template  = view('admin/common/header');
        $template .= view('admin/common/left_menu');
        $template .= view('admin/view_enquiry'); 
        $template .= view('admin/common/footer');
        $template .= view('admin/page_scripts/enquiryjs');
        return $template;
    }

    public function view($enquiryId)
    {
        $enquiry = $this->enquiryDetailModel->getEnquiryWithUser($enquiryId);

        if (!$enquiry) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Enquiry not found');
        }

        $data = ['enquiry' => $enquiry];

        $template  = view('admin/common/header');
        $template .= view('admin/common/left_menu');
        $template .= view('admin/view_enquiry', $data);
        $template .= view('admin/common/footer');
        $template .= view('admin/page_scripts/enquiryDetailJs');

        return $template;
    }

    //  DataTable
    public function orderListAjax($enquiryId)
    {
        $draw = intval($this->request->getPost('draw') ?? 1);
        $start = intval($this->request->getPost('start') ?? 0);
        $length = intval($this->request->getPost('length') ?? 10);
        $searchValue = $this->request->getPost('search')['value'] ?? '';

        $condition = "1=1";

        if (!empty($searchValue)) {
            $searchValue = trim(preg_replace('/\s+/', ' ', $searchValue));
            $noSpaceSearch = str_replace(' ', '', strtolower($searchValue));
            $condition .= " AND REPLACE(LOWER(product_desc), ' ', '') LIKE '%" .
                $this->enquiryDetailModel->db->escapeLikeString($noSpaceSearch) . "%'";
        }

        $columns = ['product_desc', 'quantity', 'enquiry_id'];
        $orderColumnIndex = intval($this->request->getPost('order')[0]['column'] ?? 0);
        $orderDir = $this->request->getPost('order')[0]['dir'] ?? 'desc';
        $orderBy = $columns[$orderColumnIndex] ?? 'enquiry_id';

        $records = $this->enquiryDetailModel->getAllFilteredRecords($enquiryId, $condition, $start, $length, $orderBy, $orderDir);

        $data = [];
        $slno = $start + 1;

        foreach ($records as $row) {
            $data[] = [
                'slno' => $slno++,
                'product_desc' => $row->product_desc,
                'quantity' => $row->quantity,
                'action' => '<a href="#" class="btn btn-sm btn-primary">Edit</a> 
                             <a href="#" class="btn btn-sm btn-danger">Delete</a>'
            ];
        }

        $recordsTotal = $this->enquiryDetailModel->getAllOrderCount($enquiryId);
        $recordsFilteredObj = $this->enquiryDetailModel->getFilterOrderCount($enquiryId, $condition);
        $recordsFiltered = $recordsFilteredObj->filRecords ?? 0;

        return $this->response->setJSON([
            "draw" => $draw,
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            "data" => $data
        ]);
    }
}