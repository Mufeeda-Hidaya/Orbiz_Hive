<?php

namespace App\Models;

use CodeIgniter\Model;

class CompanyLedgerModel extends Model
{
    protected $table = 'company_ledger';
    protected $primaryKey = 'ledger_id';

    protected $allowedFields = [
        'company_id',
        'invoice_id',
        'customer_id',
        'invoice_amount',
    ];
}


