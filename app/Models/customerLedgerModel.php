<?php
namespace App\Models;
use CodeIgniter\Model;

class CustomerLedgerModel extends Model
{
    protected $table = 'customer_ledger';
    protected $primaryKey = 'ledger_id';
    protected $allowedFields = ['customer_id', 'estimate_id', 'transaction_date', 'amount', 'type', 'remarks', 'created_at'];
}
