<?php namespace App\Models;

use CodeIgniter\Model;

class TransactionModel extends Model
{
    protected $table = 'transactions';
    protected $primaryKey = 'transaction_id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'customer_id', 'invoice_id', 'user_id', 'company_id',
        'invoice_amount', 'paid_amount', 'partial_paid_amount',
        'payment_mode', 'created_at', 'updated_at'
    ];

    // If your transactions table has created_at and updated_at columns:
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
