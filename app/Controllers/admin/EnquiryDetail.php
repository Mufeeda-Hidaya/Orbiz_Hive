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

        if (!$this->session->has('user_id')) {
            header('Location: ' . base_url('admin'));
            exit();
        }
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
        $enquiry = $this->enquiryDetailModel->getEnquiry($enquiryId);

        if (!$enquiry) {
            return $this->response->setJSON(["data" => []]);
        }
        $records = $this->enquiryDetailModel->getEnquiryItems($enquiry->user_id, $enquiry->created_at);
        $data = [];
        $slno = 1;
        foreach ($records as $row) {
            $data[] = [
                'slno' => $slno++,
                'product_name' =>  ucwords(strtolower($row->product_name)),
                'product_desc' =>  ucwords(strtolower($row->product_desc)),
                'quantity' => $row->quantity,
                'enquiry_id' => $row->enquiry_id
            ];
        }
        return $this->response->setJSON(["data" => $data]);
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
    public function edit($id)
    {
        $data['enquiry'] = $this->enquiryDetailModel->find($id);

        $template  = view('admin/common/header');
        $template .= view('admin/common/left_menu');
        $template .= view('admin/add_enquiry', $data);
        $template .= view('admin/common/footer');
        $template .= view('admin/page_scripts/enquiryjs');
        return $template;
    }
    public function update()
    {
        $id = $this->request->getPost('enquiry_id');
        $data = [
            'product_name' => $this->request->getPost('product_name'),
            'product_desc' => $this->request->getPost('product_desc'),
            'quantity'     => $this->request->getPost('quantity')
        ];

        $updated = $this->enquiryModel->update($id, $data);

        if ($updated) {
            return $this->response->setJSON(['status' => true, 'message' => 'Enquiry Updated Successfully.']);
        } else {
            return $this->response->setJSON(['status' => false, 'message' => 'Failed To Update Enquiry.']);
        }
    }


}
