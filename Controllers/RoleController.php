<?php
include_once __DIR__ . '/../Models/RoleModel.php';
class RoleController
{
    private $RoleModel;

    private $table;

    public function __construct()
    {
        $this->table = 'role';
        $this->RoleModel = new RoleModel();
    }

    public function index()
    {
        $result = $this->RoleModel->index();
        echo json_encode(['data' => $result]);
    }

    public function create()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if ($this->RoleModel->create($data) == false) {
            echo json_encode(['message' => "Có lỗi xảy ra !"]);
        } else {
            echo json_encode(['message' => "Thêm nhóm quyền thành công !"]);
        }
    }
    public function detail($id)
    {
        $result = $this->RoleModel->detail($id);
        echo json_encode(['detail' => $result]);
    }
    public function edit($id)
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if ($this->RoleModel->edit($data, $id) == false) {
            echo json_encode(['message' => "Có lỗi xảy ra !"]);
        } else {
            echo json_encode(['message' => "Sửa nhóm quyền thành công !"]);
        }
    }
    public function delete($id)
    {
        if (!$this->RoleModel->delete($id)) {
            echo json_encode(['message' => "Có lỗi xảy ra !"]);
        } else {
            echo json_encode(['message' => "Xóa nhóm quyền thành công!"]);
        }
    }
    public function updatePermissions()
    {
        // Lấy dữ liệu từ request body (JSON)
        $data = json_decode(file_get_contents("php://input"), true);

        // Kiểm tra dữ liệu có hợp lệ không
        if (!is_array($data) || empty($data)) {
            echo json_encode(['status' => 'error', 'message' => 'Dữ liệu không hợp lệ']);
            return;
        }

        // Gọi model để cập nhật quyền
        $updated = $this->RoleModel->updatePermissions($data);

        if ($updated) {
            echo json_encode(['status' => 'success', 'message' => 'Cập nhật quyền thành công']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Cập nhật quyền thất bại']);
        }
    }

}