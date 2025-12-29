<?php

namespace App\Models;

use CodeIgniter\Model;

class EnquiryHistoryModel extends Model
{
    protected $table = 'enquiry_history';
    protected $primaryKey = 'history_id';
    protected $allowedFields = [
        'enquiry_id',
        'revision_no',
        'revision_label',
        'date',
        'note',
        'stage',
        'status',
        'comments',
        'images',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by'
    ];

    protected $useTimestamps = true; // auto-manage created_at & updated_at
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $returnType = 'array';

    // Optional: Add default ordering
    protected $orderBy = ['history_id' => 'DESC'];
}
