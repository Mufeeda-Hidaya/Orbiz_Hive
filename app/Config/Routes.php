<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('dashboard','Dashboard::index');

$routes->get('logout', 'Auth::logout');

$routes->post('admin/login/authenticate', 'Login::authenticate');
$routes->get('admin/login','Login::index');

$routes->post('manageuser/userlistajax', 'Manageuser::userlistajax');
$routes->get('manageuser', 'Manageuser::index');
$routes->get('adduser', 'Manageuser::index');
$routes->get('adduser/(:num)', 'Manageuser::index/$1'); 
$routes->get('adduserlist', 'Manageuser::add');
$routes->post('manageuser/save', 'Manageuser::save');
$routes->get('manageuser/getUser/(:num)', 'Manageuser::getUser/$1');
$routes->post('manageuser/delete', 'Manageuser::delete');
$routes->post('manageuser/userlist', 'Manageuser::userlist');





$routes->get('managecompany', 'Managecompany::index'); // load company management main page
$routes->post('managecompany/save', 'Managecompany::save');  // save (add/update) company
$routes->get('managecompany/list', 'Managecompany::companyList');// show company list page
$routes->get('companylist', 'Managecompany::companyList'); // alias for company list
$routes->get('managecompany/getCompany/(:num)', 'Managecompany::getCompany/$1');  // get company details by ID
$routes->get('addcompany', 'Managecompany::add'); // load add company form          
$routes->get('addcompany/(:num)', 'Managecompany::add/$1');  // load edit form by company ID
$routes->post('managecompany/delete', 'Managecompany::delete'); // delete company by ID
$routes->get('managecompany/getAllCompanies', 'Managecompany::getAllCompanies'); // fetch all companies (JSON)
$routes->post('managecompany/companylistjson', 'Managecompany::companylistjson'); // datatable AJAX company list



$routes->get('rolemanagement/create', 'Rolemanagement::create');
$routes->post('rolemanagement/store', 'Rolemanagement::store');
$routes->get('rolemanagement/rolelist', 'Rolemanagement::rolelist');
$routes->post('rolemanagement/rolelistajax', 'Rolemanagement::rolelistajax');
$routes->get('rolemanagement/edit/(:num)', 'Rolemanagement::edit/$1');
$routes->post('rolemanagement/update/(:num)', 'Rolemanagement::update/$1');
$routes->post('rolemanagement/delete', 'Rolemanagement::delete');





// $routes->get('add_estimate', 'Estimate::add_estimate'); 
// $routes->post('estimate/save', 'Estimate::save'); 
// $routes->post('estimate/estimatelistajax', 'Estimate::estimatelistajax');
// $routes->post('estimate/delete', 'Estimate::delete');
// $routes->get('estimatelist', 'Estimate::estimatelist');
// $routes->get('estimate/edit/(:num)', 'Estimate::edit/$1');
// $routes->get('estimate/generateEstimate/(:num)', 'Estimate::generateEstimate/$1');
// $routes->post('save', 'Estimate::saveEstimate');
// $routes->get('estimate/generateEstimate/(:segment)', 'Estimate::generateEstimate/$1');



// $routes->get('invoicelist', 'Invoice::invoicelist');

$routes->post('customer/create', 'Customer::create');
$routes->post('customer/get-address', 'Customer::get_address');
$routes->get('customer/search', 'Customer::search');

$routes->get('expense', 'Expense::index'); 
// $routes->get('addexpenselist', 'Expense::index'); 
$routes->get('addexpense', 'Expense::create');              
$routes->get('addexpense/(:num)', 'Expense::create/$1');
$routes->post('expense/store', 'Expense::store');
$routes->post('expense/list', 'Expense::expenselistajax');
$routes->post('expense/delete/(:num)', 'Expense::delete/$1');
$routes->post('expense/delete', 'Expense::delete'); 
$routes->post('expense/getExpensesAjax', 'Expense::getExpensesAjax');
$routes->get('expense/report', 'Expense::report');



// dashboard
$routes->post('dashboard/getTodayExpenseTotal', 'Dashboard::getTodayExpenseTotal');
$routes->post('dashboard/getMonthlyExpenseTotal', 'Dashboard::getMonthlyExpenseTotal');
$routes->get('estimate/recentEstimates', 'Estimate::recentEstimates');
$routes->get('dashboard/getMonthlyRevenueTotal', 'Dashboard::getMonthlyRevenueTotal');
$routes->get('dashboard/getTodayRevenueTotal', 'Dashboard::getTodayRevenueTotal');





//for report
$routes->get('expense/report', 'Expense::report');
$routes->post('expense/getExpenseReportAjax', 'Expense::getExpenseReportAjax');
$routes->get('companyledger', 'CompanyLedger::index');
$routes->post('companyledger/save', 'CompanyLedger::save');
$routes->post('companyledger/getPaidInvoices', 'CompanyLedger::getPaidInvoices');





$routes->get('customer/list', 'Customer::list');
$routes->post('customer/fetch', 'Customer::fetch');
$routes->post('customer/create', 'Customer::create');
$routes->post('customer/delete', 'Customer::delete');
$routes->post('customer/get_address', 'Customer::get_address');
$routes->get('customer/edit/(:num)', 'Customer::edit/$1'); 
$routes->get('customer/getCustomer/(:num)', 'Customer::getCustomer/$1');
$routes->get('estimate/customer/(:num)', 'Estimate::viewByCustomer/$1');
$routes->get('customer', 'Customer::index'); 
$routes->get('customer/get_discount/(:num)', 'Customer::get_discount/$1');


$routes->get('customerreport', 'CustomerReport::index');
// In app/Config/Routes.php
$routes->post('customerreport/getReport', 'CustomerReport::getReport');


$routes->get('add_estimate', 'Estimate::add_estimate'); 
$routes->post('estimate/save', 'Estimate::save'); 
$routes->post('estimate/estimatelistajax', 'Estimate::estimatelistajax');
$routes->post('estimate/delete', 'Estimate::delete');
$routes->get('estimatelist', 'Estimate::estimatelist');
$routes->get('estimate/edit/(:num)', 'Estimate::edit/$1');
$routes->get('estimate/generateEstimate/(:num)', 'Estimate::generateEstimate/$1');
$routes->post('save', 'Estimate::saveEstimate');
$routes->get('estimate/generateEstimate/(:segment)', 'Estimate::generateEstimate/$1');


// enquiry

$routes->get('enquiry/list', 'Supplier::list');
$routes->post('enquiry/fetch', 'Supplier::fetch');
$routes->get('add_enquiry', 'Supplier::add_enquiry'); 
$routes->post('enquiry/saveEnquiry', 'Supplier::saveEnquiry');
$routes->post('enquiry/delete', 'Supplier::delete');
// $routes->post('supplier/get_address', 'Supplier::get_address');
$routes->post('customer/get-address-phone', 'Customer::get_address_phone');
$routes->get('enquiry/edit/(:num)', 'Supplier::edit/$1');

// $routes->get('supplier/getSupplier/(:num)', 'Supplier::getSupplier/$1');
// $routes->get('supplier', 'Supplier::index'); 
$routes->post('enquiry/markConverted', 'Supplier::markConverted');
$routes->get('enquiry/convertToEstimate/(:num)', 'Supplier::convertToEstimate/$1');
$routes->get('estimate/add_estimate', 'Estimate::add_estimate');



// job order
$routes->get('orderlist', 'JobOrder::list');
$routes->get('order/add', 'JobOrder::add');
$routes->get('order/add/(:num)', 'JobOrder::add/$1');
// $routes->get('orderlist', 'Invoice::list');
// $routes->get('invoice/add', 'Invoice::add');
$routes->post('invoice/save', 'Invoice::save');
$routes->get('invoice/print/(:segment)', 'Invoice::print/$1');
$routes->post('invoice/invoicelistajax', 'Invoice::invoicelistajax');
$routes->get('invoice/edit/(:segment)', 'Invoice::edit/$1');      
$routes->post('invoice/delete/(:segment)', 'Invoice::delete/$1');
$routes->get('invoice/edit/(:num)', 'Invoice::edit/$1');
$routes->post('invoice/save', 'Invoice::save'); 
// $routes->get('invoice/add/(:num)', 'Invoice::add/$1');
$routes->get('invoice/convertFromEstimate/(:num)', 'Invoice::convertFromEstimate/$1');
$routes->get('invoice/delivery_note/(:num)', 'Invoice::delivery_note/$1');
$routes->post('invoice/update_status', 'Invoice::update_status');
$routes->post('invoice/update_partial_payment', 'Invoice::update_partial_payment');
$routes->post('invoice/getSalesReportAjax', 'Invoice::getSalesReportAjax');
$routes->get('invoice/report', 'Invoice::report');
$routes->get('invoice/print/(:num)', 'InvoiceController::printInvoice/$1');


$routes->get('receiptvoucher/(:num)', 'ReceiptVoucher::index/$1');
$routes->get('paymentvoucher/(:num)', 'PaymentVoucher::index/$1');

$routes->get('cashlist', 'CashReceipt::index');
$routes->get('print_receipt', 'ReceiptVoucher::index');
$routes->post('cashreceipt/ajaxListJson', 'CashReceipt::ajaxListJson');
$routes->post('cashreceipt/delete', 'CashReceipt::delete');

$routes->get('/payment_voucher', 'PaymentVoucher::index');
$routes->get('/print_receipt', 'ReceiptVoucher::index');
$routes->get('receiptvoucher/print/(:num)', 'ReceiptVoucher::index/$1');
$routes->get('paymentvoucher/print/(:num)', 'PaymentVoucher::index/$1');


 



// Api


// user login
$routes->post('user/login', 'Api\Login::login');
$routes->post('user/logout', 'Api\Login::logout');

// Enquiries
$routes->post('enquiry/save', 'Api\Enquiry::saveEnquiry');
$routes->get('enquiry/getAll', 'Api\Enquiry::getAllEnquiries');
$routes->get('enquiry/get/(:num)', 'Api\Enquiry::getEnquiryById/$1');
$routes->delete('enquiry/delete/(:num)', 'Api\Enquiry::deleteEnquiry/$1');
$routes->delete('enquiry/deleteItem/(:num)', 'Api\Enquiry::deleteItem/$1');

// Estimates
$routes->post('enquiry/convertToEstimate/(:num)', 'Api\Estimate::convertToEstimate/$1');
$routes->get('estimate/getAll', 'Api\Estimate::getAllEstimates');
$routes->get('estimate/get/(:num)', 'Api\Estimate::getEstimateById/$1');
$routes->delete('estimate/delete/(:num)', 'Api\Estimate::deleteEstimate/$1');
$routes->get('estimate/settings', 'Api\Estimate::getSettingsData');

// Job Orders
$routes->post('estimate/convertToOrder/(:num)', 'Api\JobOrder::convertToJobOrder/$1');
$routes->get('order/getAll', 'Api\JobOrder::getAllJobOrders');
$routes->post('joborder/update-progress', 'Api\JobOrder::updateJobOrderProgress');


//Delivery 

$routes->post('joborder/convertToDelivery/(:num)', 'Api\Delivery::convertToDelivery/$1');
$routes->post('delivery/markAsDelivered/(:num)', 'Api\Delivery::markAsDelivered/$1');
$routes->get('delivery/getAll', 'Api\Delivery::getAllDeliveries');
$routes->post('upload-image', 'Api\Enquiry::uploadImage');


$routes->get('estimate/generateEstimate/(:num)', 'Estimate::generateEstimate/$1');