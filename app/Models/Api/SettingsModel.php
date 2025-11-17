<?php
namespace App\Models\Api;
use CodeIgniter\Model;

class SettingsModel extends Model
{
    protected $table = 'settings';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'company_id',
        'gp_percentage',
        'labour_rate'
    ];
}
