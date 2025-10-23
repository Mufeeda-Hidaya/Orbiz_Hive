<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Models\Managecompany_Model; // Make sure the model path is correct
use App\Models\Manageuser_Model; 

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var list<string>
     */
    protected $helpers = [];

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);
        $this->session = \Config\Services::session();

        // Preload any models, libraries, etc, here.

        $userId = $this->session->get('user_id');
    $companyData = null;

    if ($userId) {
        $userModel = new \App\Models\Manageuser_Model();
        $user = $userModel->find($userId);

        if ($user && isset($user['company_id'])) {
            $companyModel = new \App\Models\Managecompany_Model();
            $companyData = $companyModel->find($user['company_id']);
        }
    }
    $renderer = \Config\Services::renderer();
    $renderer->setVar('company', $companyData);
    }

    
}
