<?php

namespace App\Models;

use CodeIgniter\Model;

class EnquiryHistoryModel extends Model
{
    protected $table = 'enquiries_history';
    protected $primaryKey = 'history_id';

    protected $useAutoIncrement = true;
    protected $returnType = 'array';

    protected $allowedFields = [
        'enquiry_id',
        'revision_no',
        'revision_label',
        'date',
        'note',
        'comments',
        'stage',
        'status',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by'
    ];

    protected $useTimestamps = false;
}
