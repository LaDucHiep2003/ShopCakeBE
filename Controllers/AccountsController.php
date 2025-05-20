<?php
include_once __DIR__ . '/../Models/AccountsModel.php';
class AccountController
{
    private $AccountCategoryModel;

    private $table;
    public function __construct()
    {
        $this->table = 'accounts';
        $this->AccountCategoryModel = new AccountCategoryModel();
    }

    public function index()
    {
        $result = $this->AccountCategoryModel->index();
        echo json_encode(['data' => $result]);
    }
    public function detail($id)
    {
        $result = $this->AccountCategoryModel->detail($id);
        echo json_encode(['detail' => $result]);
    }
    public function create()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if ($this->AccountCategoryModel->create($data) == false) {
            echo json_encode(['message' => "Có lỗi xảy ra !"]);
        } else {
            echo json_encode(['message' => "Tạo mới tài khoản thành công !"]);
        }
    }
    public function edit($id)
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $data['password'] = md5($data['password']);
        if ($this->AccountCategoryModel->edit($data, $id) == false) {
            echo json_encode(['message' => "Có lỗi xảy ra !"]);
        } else {
            echo json_encode(['message' => "Sửa tài khoản thành công !"]);
        }
    }
    public function delete($id)
    {
        if (!$this->AccountCategoryModel->delete($id)) {
            echo json_encode(['message' => "Có lỗi xảy ra !"]);
        } else {
            echo json_encode(['message' => "Xóa tài khoản thành công!"]);
        }
    }
}