<?php
namespace App\Controllers\admin;
use App\Controllers\BaseController;
use App\Models\admin\UserModel;
use App\Models\admin\RolesModel;
 
class User extends BaseController
{
     
    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->input = \Config\Services::request();
        $this->userModel = new UserModel();
        $this->rolesModel = new RolesModel();

        if (!$this->session->has('user_id')) {
            header('Location: ' . base_url('admin'));
            exit();
        }
    }
    public function index()
    {
        
        $template = view('admin/common/header');
        $template.= view('admin/common/left_menu');
        $template.= view('admin/manage_user');
        $template.= view('admin/common/footer');
        $template.= view('admin/page_scripts/userjs');
        return $template;
   
    }
    public function addUser()
    {
        // $data['roles'] = $this->userModel->getAllRoles();
        $template = view('admin/common/header');
        $template.= view('admin/common/left_menu');
        $template.= view('admin/add_user',$data);
        $template.= view('admin/common/footer');
        $template.= view('admin/page_scripts/userjs');
        return $template;
    }
   public function userListAjax()
{
    $draw      = $this->request->getPost('draw') ?? 1;
    $start     = $this->request->getPost('start') ?? 0;
    $length    = $this->request->getPost('length') ?? 10;
    $searchVal = $this->request->getPost('search')['value'] ?? '';

    $columns = [
        0 => 'u.user_id',
        1 => 'u.user_name',
        2 => 'u.email',
        3 => 'r.role_Name',
        4 => 'u.status',
        5 => 'u.user_id'
    ];

    $orderColumnIndex = $this->request->getPost('order')[0]['column'] ?? 0;
    $orderDir = $this->request->getPost('order')[0]['dir'] ?? 'desc';
    $orderBy = $columns[$orderColumnIndex] ?? 'u.user_id';

    $users = $this->userModel->getAllFilteredRecords($searchVal, $start, $length, $orderBy, $orderDir);

    $result = [];
    $slno = $start + 1;

    foreach ($users as $user) {
        $statusBadge = '<span class="badge badge-sm ' 
            . ($user->status == 1 ? 'bg-gradient-success' : 'bg-gradient-secondary')
            . ' status-toggle" data-id="' . $user->user_id . '" style="cursor:pointer">'
            . ($user->status == 1 ? 'Active' : 'Inactive')
            . '</span>';

        $result[] = [
            'slno'          => $slno++,
            'user_name'     => $user->user_name,
            'email'         => $user->email,
            'role_Name'     => $user->role_Name ?? 'N/A',
            'status_switch' => $statusBadge,
            'user_id'       => $user->user_id
        ];
    }

    $totalCount    = $this->userModel->getAllUserCount();
    $filteredCount = $this->userModel->getFilterUserCount($searchVal);

    return $this->response->setJSON([
        "draw" => intval($draw),
        "recordsTotal" => intval($totalCount),
        "recordsFiltered" => intval($filteredCount),
        "data" => $result
    ]);
}


}