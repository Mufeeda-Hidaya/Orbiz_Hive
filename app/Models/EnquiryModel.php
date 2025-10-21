<?php
namespace App\Models;

use CodeIgniter\Model;

class EnquiryModel extends Model
{
    protected $table = 'enquiries';
    protected $primaryKey = 'enquiry_id';
    protected $allowedFields = [
        'enquiries_no', 'user_id', 'customer_id', 'description',
        'quantity', 'status', 'created_at', 'created_by', 'updated_at', 'updated_by'
    ];
}
