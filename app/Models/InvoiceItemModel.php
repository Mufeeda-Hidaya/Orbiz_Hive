<?php
namespace App\Models;
use CodeIgniter\Model;

class InvoiceItemModel extends Model
{
    protected $table = 'invoice_items';
    protected $primaryKey = 'item_id';
    protected $allowedFields = ['invoice_id', 'item_name', 'price', 'quantity', 'total', 'location', 'item_order'];

}
