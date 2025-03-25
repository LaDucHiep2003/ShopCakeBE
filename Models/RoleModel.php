<?php

include_once __DIR__ . '/../Models/BaseModel.php';
class RoleModel extends BaseModel
{
    protected $table;
    protected $RoleModel;
    protected $conn;

    public function __construct()
    {
        $this->table = 'role';
        $this->conn = ConnectionDB::GetConnect();
        $this->RoleModel = new BaseModel($this->table);
    }
    public function index()
    {
        return $this->RoleModel->index();
    }

    public function create($data)
    {
        return $this->RoleModel->create($data);
    }
    public function detail($id)
    {
        return $this->RoleModel->read($id);
    }
    public function edit($data, $id)
    {
        return $this->RoleModel->update($data, $id);
    }
    public function delete($id)
    {
        return $this->RoleModel->delete($id);
    }
    public function updatePermissions($roles)
    {
        try {
            $this->conn->beginTransaction();
            $query = $this->conn->prepare("UPDATE role SET permissions = :permissions WHERE id = :id");

            foreach ($roles as $role) {
                $permissions = implode(",", $role['permission']);
                if (!isset($role['id'], $role['permission'])) {
                    continue; // Bỏ qua dữ liệu không hợp lệ
                }
                $query->execute([
                    'permissions' => $permissions,
                    'id' => $role['id']
                ]);
            }

            $this->conn->commit();
            return true;
        } catch (Throwable $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function getRolebyUser($id)
    {
        $query = $this->conn->prepare("SELECT * FROM role inner join accounts on role.id = accounts.id WHERE accounts.id = :id");
        $query->execute(['id' => $id]);
        return $query->fetch();
    }
}