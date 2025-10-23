<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Managecompany_Model;
use CodeIgniter\Exceptions\PageNotFoundException;

class Managecompany extends BaseController
{
	protected $companyModel; // instance of Managecompany_Model

	public function __construct()
	{
		$this->companyModel = new Managecompany_Model(); // initialize company model
		$session = \Config\Services::session();
		if (!$session->get('logged_in')) {  // check if user is logged in
			header('Location: ' . base_url('/')); // redirect to login/home
			exit;
		}
	}

	public function index()
	{
		return view('addcompany'); // load company add/edit main page
	}

	  // Load add or edit company page with optional $id for edit
	public function add($id = null)
	{
		$data = [];

		if ($id) {
			$company = $this->companyModel->find($id); // get company data by ID

			if (!$company) {
				throw PageNotFoundException::forPageNotFound('Company Not Found.');
			}
			$data['selectedCompany'] = $company;
		}
		return view('addcompany', $data);  // pass company data to view
	}

	// Handle saving company data (add or edit)
	public function save()
	{
		helper(['form']);
		$validation = \Config\Services::validation();  // validation service

		$uid = $this->request->getPost('uid');  // company ID (for edit)
		$logoFile = $this->request->getFile('company_logo'); // uploaded logo file
 		 
		// validation rules
		
		 $rules = [
			'company_name' => 'required',
			'address' => 'permit_empty',
			'tax_number' => 'permit_empty',
			'email' => 'permit_empty|valid_email',
			'phone' => 'required',
		];
      
		// logo validation for new/add vs edit
		if (!$uid) {
			$rules['company_logo'] = 'uploaded[company_logo]|is_image[company_logo]|mime_in[company_logo,image/jpg,image/jpeg,image/png,image/gif]';
		} else { // editing existing
			if ($logoFile && $logoFile->isValid() && !$logoFile->hasMoved()) {
				$rules['company_logo'] = 'is_image[company_logo]|mime_in[company_logo,image/jpg,image/jpeg,image/png,image/gif]';
			}
		}
        
		// validate input
		if (!$this->validate($rules)) {
			return $this->response->setJSON([
				'status' => 'error',
				'message' => $this->validator->getErrors()
			]);
		}

		$logoName = null;  // initialize logo file name
		if ($logoFile && $logoFile->isValid() && !$logoFile->hasMoved()) {
			$logoName = $logoFile->getRandomName(); // generate random file name
			$logoFile->move(ROOTPATH . 'public/uploads', $logoName); // move file to uploads
		}
        
		// prepare data array for DB
		$rawAddress = trim($this->request->getPost('address'));
		$newData = [
			'company_name' => $this->request->getPost('company_name'),
			'address' => $rawAddress !== '-N/A-' ? $rawAddress : '',
			'billing_address' => $this->request->getPost('billing_address'),
			'tax_number' => $this->request->getPost('tax_number'),
			'email' => $this->request->getPost('email'),
			'phone' => $this->request->getPost('phone'),
		];

        
		if ($uid) { // update existing company
			$existing = $this->companyModel->find($uid);
			if (!$existing) {
				return $this->response->setJSON([
					'status' => 'error',
					'message' => 'Company Not Found For Update.'
				]);
			}

            // check duplicate company name and tax number
			$duplicate = $this->companyModel
				->where('company_name', $newData['company_name'])
				->where('company_id !=', $uid);

			if (!empty($newData['tax_number'])) {
				$duplicate = $duplicate->where('tax_number', $newData['tax_number']);
			} else {
				$duplicate = $duplicate->where('tax_number IS NULL');
			}

			$duplicate = $duplicate->first();

			if ($duplicate) {
				return $this->response->setJSON(['success' => false, 'message' => 'Another company with same name exists.']);
			}

			$this->companyModel->update($uid, $newData);

			if (!preg_match('/^[0-9+\-\s]{7,20}$/', $newData['phone'])) {
				return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid Phone Number']);
			}

			// if logo uploaded, replace existing logo
			if ($logoName) {
				$newData['company_logo'] = $logoName;
			} else {
				$newData['company_logo'] = $existing['company_logo'];
			}

			// check if any changes made
			$hasChanges = (
				$newData['company_name'] !== $existing['company_name'] ||
				$newData['address'] !== $existing['address'] ||
				$newData['billing_address'] !== $existing['billing_address'] ||
				$newData['tax_number'] !== $existing['tax_number'] ||
				$newData['email'] !== $existing['email'] ||
				$newData['phone'] !== $existing['phone'] ||
				$newData['company_logo'] !== $existing['company_logo']
			);

			if (!$hasChanges) {
				return $this->response->setJSON([
					'status' => 'warning',
					'message' => 'No Changes Detected To Update.'
				]);
			}

			// delete old logo file
			if ($logoName && !empty($existing['company_logo']) && file_exists(ROOTPATH . 'public/uploads/' . $existing['company_logo'])) {
				unlink(ROOTPATH . 'public/uploads/' . $existing['company_logo']);
			}

			$this->companyModel->update($uid, $newData);
			return $this->response->setJSON(['status' => 'success', 'message' => 'Company Updated Successfully']);
		} else {  // insert new company


			$existing = $this->companyModel
				 ->where('company_name', $newData['company_name'])
    			 ->where('company_status !=', 3);

				if (!empty($newData['tax_number'])) {
					$existing = $existing->where('tax_number', $newData['tax_number']);
				} else {
					$existing = $existing->where('tax_number IS NULL');
				}
			$existing = $existing->first();

			if ($existing) {
				return $this->response->setJSON(['success' => false, 'message' => 'Company with same name already exists.']);
			}

			if ($logoName) {
				$newData['company_logo'] = $logoName;
			}

			$this->companyModel->insert($newData);
			return $this->response->setJSON([
				'status' => 'success',
				'message' => 'Company Added Successfully'
			]);
		}
	}


	public function companyList()
	{
		$data['companies'] = $this->companyModel->findAll(); // get all companies
		return view('companylist', $data);
	}

	public function getCompany($id)
	{
		$company = $this->companyModel->find($id);
		return $this->response->setJSON($company); // fetch company by ID
	}

	public function delete()
	{
		$id = $this->request->getPost('id'); // company ID

		if (!$id) {
			return $this->response->setJSON([
				'status' => 'error',
				'message' => 'No ID provided.'
			]);
		}

		$companyModel = new Managecompany_Model();
		$company = $companyModel->find($id);

		if (!$company) {
			return $this->response->setJSON([
				'status' => 'error',
				'message' => 'Company Not Found.'
			]);
		}

		// Soft delete: set company_status = 3
		$update = $companyModel->update($id, ['company_status' => 3]);

		if ($update) {
			return $this->response->setJSON([
				'status' => 'success',
				'message' => 'Company Deleted Successfully.'
			]);
		}

		return $this->response->setJSON([
			'status' => 'error',
			'message' => 'Failed To Delete Company.'
		]);
	}

	public function companylistjson()
	{
		$draw = $this->request->getPost('draw') ?? 1;
		$fromstart = $this->request->getPost('start') ?? 0;
		$tolimit = $this->request->getPost('length') ?? 10;
		$order = $this->request->getPost('order')[0]['dir'] ?? 'desc';
		$columnIndex = $this->request->getPost('order')[0]['column'] ?? 1;
		$search = $this->request->getPost('search')['value'] ?? '';

		$slno = $fromstart + 1;

		$columnMap = [
			0 => 'company_id',
			1 => 'company_name',
			2 => 'address',
			3 => 'tax_number',
			4 => 'email',
			5 => 'phone',
			6 => 'company_logo',
			7 => 'company_id'
		];
		$orderColumn = $columnMap[$columnIndex] ?? 'company_id';

		$companyModel = new Managecompany_Model();

		// Fetch filtered records based on updated search logic
		$companies = $companyModel->getAllFilteredRecords($search, $fromstart, $tolimit, $orderColumn, $order);

		$result = [];
		foreach ($companies as $company) {
			$tax = trim($company['tax_number']);
			$company['tax_number'] = ($tax === '0' || $tax === '') ? '-N/A-' : $tax;

			$address = trim($company['address']);
			$company['address'] = $address === '' ? '-N/A-' : $address;

			$result[] = [
				'slno' => $slno++,
				'company_id' => $company['company_id'],
				'company_name' => $company['company_name'],
				'address' => $company['address'],
				'tax_number' => $company['tax_number'],
				'email' => $company['email'],
				'phone' => $company['phone'],
				'company_logo' => $company['company_logo']
			];
		}

		$total = $companyModel->getAllCompanyCount()->totcompanies;
		$filteredTotal = $companyModel->getFilteredCompanyCount($search)->filCompanies;

		return $this->response->setJSON([
			'draw' => intval($draw),
			'recordsTotal' => $total,
			'recordsFiltered' => $filteredTotal,
			'data' => $result
		]);
	}


}
