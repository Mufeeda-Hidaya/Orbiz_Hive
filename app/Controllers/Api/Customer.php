<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\Api\CustomerModel;

class Customer extends BaseController
{
    public function search()
    {
        $keyword = $this->request->getGet('name');

        if (!$keyword) {
            return $this->response->setJSON([
                'status'  => 400,
                'success' => false,
                'message' => 'Search keyword is required'
            ]);
        }

        $model = new CustomerModel();
        $customers = $model->searchCustomer($keyword);

        return $this->response->setJSON([
            'status'  => 200,
            'success' => true,
            'data'    => $customers
        ]);
    }
}
