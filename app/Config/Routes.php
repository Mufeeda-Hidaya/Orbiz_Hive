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
$routes->get('admin/manage_enquiry', 'admin\Enquiry::index');
$routes->post('admin/manage_enquiry/orderListAjax', 'admin\Enquiry::orderListAjax');
$routes->get('admin/manage_enquiry/view_enquiry/(:num)', 'admin\Enquiry::view/$1');

// $routes->get('admin/profile', 'admin\Profile::index');

//roles
$routes->get('admin/manage_roles', 'admin\Roles::index');
$routes->post('admin/manage_roles/rolelistAjax', 'admin\Roles::roleListAjax');
$routes->get('admin/add_role', 'Admin\Roles::addRoles');
$routes->post('admin/manage_roles/save', 'Admin\Roles::saveRoles');
$routes->post('admin/manage_roles/delete', 'admin\Roles::deleteRole/$1');
$routes->get('admin/manage_roles/edit/(:num)', 'admin\Roles::addRoles/$1');
$routes->post('admin/manage_roles/status', 'admin\Roles::changeStatus');





// $routes->get('admin/add_role/edit/(:num)', 'admin\ManageRole::edit/$1');
// $routes->post('admin/manage_role/update/(:num)', 'admin\ManageRole::update/$1');


//manage users
$routes->get('admin/manage_user', 'admin\User::index');
$routes->post('admin/manage_user/userListAjax', 'admin\User::userListAjax');
$routes->get('admin/add_user', 'admin\User::addUser');
$routes->post('admin/save/user', 'admin\User::saveUser');
$routes->get('admin/add_user/edit/(:num)', 'admin\User::edit/$1');
$routes->post('admin/manage_user/delete', 'admin\User::deleteUser');
$routes->post('admin/manage_user/status', 'admin\User::changeStatus');



?>