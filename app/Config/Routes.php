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
$routes->post('admin/manage_enquiry/List', 'admin\Enquiry::ajaxList');



$routes->get('admin/profile', 'admin\Profile::index');



//roles
$routes->get('admin/roles', 'admin\Roles::index');
$routes->post('admin/roles/List', 'Admin\Roles::ajaxList');
$routes->get('admin/roles/add', 'Admin\Roles::addRoles');
$routes->get('admin/roles/edit/(:num)', 'Admin\Roles::addRoles/$1');
$routes->post('admin/roles/save', 'Admin\Roles::saveRoles');
$routes->post('admin/roles/status', 'Admin\Roles::changeStatus');
$routes->post('admin/roles/delete/(:any)', 'Admin\Roles::deleteRoles/$1');


?>