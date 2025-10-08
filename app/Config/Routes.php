<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('admin/dashboard', 'admin\Dashboard::index');
$routes->get('admin/profile', 'admin\Profile::index');
$routes->get('admin/roles', 'admin\Roles::index');
?>