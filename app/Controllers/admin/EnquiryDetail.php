<?php

namespace App\Controllers\admin;

use App\Controllers\BaseController;
use App\Models\admin\EnquiryModel;
use App\Models\admin\UserModel;
use App\Models\admin\EnquiryDetailModel;

class EnquiryDetail extends BaseController
{
    protected $enquiryModel;
    protected $userModel;
    protected $enquiryDetailModel;

    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->input = \Config\Services::request();
        $this->enquiryModel = new EnquiryModel();
        $this->userModel = new UserModel();
        $this->enquiryDetailModel = new EnquiryDetailModel();
    }
    public function index($enquiryId)
    {
        $enquiry = $this->enquiryDetailModel->getEnquiryWithUser($enquiryId);
        $data['enquiry'] = $enquiry;

        $template  = view('admin/common/header');
        $template .= view('admin/common/left_menu');
        $template .= view('admin/view_enquiry', $data);
        $template .= view('admin/common/footer');
        $template .= view('admin/page_scripts/enquiryjs');
        return $template;
    }

    public function orderDetailAjax($enquiryId)
    {
        $draw = intval($this->request->getPost('draw') ?? 1);
        $start = intval($this->request->getPost('start') ?? 0);
        $length = intval($this->request->getPost('length') ?? 10);
        $searchValue = $this->request->getPost('search')['value'] ?? '';

        $enquiry = $this->enquiryDetailModel->db->table('enquiries')
            ->select('user_id, created_at')
            ->where('enquiry_id', $enquiryId)
            ->where('status !=', 9)
            ->get()
            ->getRow();

        if (!$enquiry) {
            return $this->response->setJSON([
                "draw" => $draw,
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => []
            ]);
        }

        $builder = $this->enquiryDetailModel->db->table('enquiries')
            ->select('enquiry_id, product_name, product_desc, quantity')
            ->where('status !=', 9)
            ->where('user_id', $enquiry->user_id)
            ->where('created_at', $enquiry->created_at);

        if (!empty($searchValue)) {
            $searchValueNormalized = strtolower(preg_replace('/\s+/', '', $searchValue));
            $builder->groupStart()
                ->where("REPLACE(LOWER(product_name), ' ', '') LIKE '%" . $this->enquiryDetailModel->db->escapeLikeString($searchValueNormalized) . "%'", null, false)
                ->orWhere("REPLACE(LOWER(product_desc), ' ', '') LIKE '%" . $this->enquiryDetailModel->db->escapeLikeString($searchValueNormalized) . "%'", null, false)
                ->orWhere("REPLACE(LOWER(quantity), ' ', '') LIKE '%" . $this->enquiryDetailModel->db->escapeLikeString($searchValueNormalized) . "%'", null, false)
                ->groupEnd();
        }

        $columns = ['product_name', 'product_desc', 'quantity', 'enquiry_id'];
        $orderColumnIndex = intval($this->request->getPost('order')[0]['column'] ?? 0);
        $orderDir = $this->request->getPost('order')[0]['dir'] ?? 'asc';
        $orderBy = $columns[$orderColumnIndex] ?? 'enquiry_id';
        $builder->orderBy($orderBy, $orderDir);

        $recordsTotal = $this->enquiryDetailModel->getAllOrderCount($enquiryId);
        $recordsFiltered = $builder->countAllResults(false);
        $records = $builder->limit($length, $start)->get()->getResult();

        $data = [];
        $slno = $start + 1;
        foreach ($records as $row) {
            $data[] = [
                'slno' => $slno++,
                'product_name' => $row->product_name,
                'product_desc' => $row->product_desc,
                'quantity' => $row->quantity,
                'enquiry_id' => $row->enquiry_id
            ];
        }

        return $this->response->setJSON([
            "draw" => $draw,
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            "data" => $data
        ]);
    }
    public function deleteEnquiry()
    {
        $enquiryId = $this->request->getPost('enquiry_id');

        if (!$enquiryId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Enquiry ID Is Required.'
            ]);
        }

        $this->enquiryDetailModel->update($enquiryId, [
            'status'     => 9,
            'updated_at' => date("Y-m-d H:i:s")
        ]);

        return $this->response->setJSON([
            'success'  => true,
            'message' => 'Enquiry Deleted Successfully.'
        ]);
    }



}
