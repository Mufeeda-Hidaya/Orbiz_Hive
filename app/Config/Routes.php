<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('admin/dashboard', 'admin\Dashboard::index');

//admin login

$routes->get('admin', 'admin\Login::index');
$routes->post('admin/login', 'admin\Login::login');
$routes->get('admin/logout', 'admin\Login::logout');


//enquiries
$routes->get('admin/enquiries', 'admin\Enquiry::index');
$routes->post('admin/manage_enquiry/orderListAjax', 'admin\Enquiry::orderListAjax');
$routes->get('admin/manage_enquiry/view_enquiry/(:num)', 'admin\Enquiry::view/$1');

// $routes->get('admin/profile', 'admin\Profile::index');

//roles
$routes->get('admin/roles', 'admin\Roles::index');
$routes->post('admin/manage_roles/rolelistAjax', 'admin\Roles::roleListAjax');
$routes->get('admin/add_role', 'admin\Roles::addRoles');
// $routes->get('admin/roles/edit/(:num)', 'admin\Roles::addRoles/$1');
// $routes->post('admin/roles/save', 'admin\Roles::saveRoles');
// $routes->post('admin/roles/status', 'admin\Roles::changeStatus');
// $routes->post('admin/roles/delete/(:any)', 'admin\Roles::deleteRoles/$1');


?>