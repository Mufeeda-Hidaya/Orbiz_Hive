<?php

namespace App\Models\Api;

use CodeIgniter\Model;

class CustomerModel extends Model
{
    protected $table = 'customers';
    protected $primaryKey = 'customer_id';

    protected $allowedFields = [
        'name',
        'contact_person_name',
        'address',
        'phone',
        'status'
    ];

    /**
     * Search active customers by name
     */
    public function searchCustomer($keyword)
    {
        return $this->select('customer_id, name, contact_person_name, address, phone')
                    ->like('name', $keyword)
                    ->where('status !=', 'Deleted')
                    ->orderBy('name', 'ASC')
                    ->limit(10)
                    ->findAll();
    }
}
