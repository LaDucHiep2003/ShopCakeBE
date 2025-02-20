<?php

include_once __DIR__ . '/../Models/BaseModel.php';
class AccountCategoryModel extends BaseModel
{
    protected $table;
    protected $AccountCategoryModel;
    protected $conn;

    public function __construct()
    {
        $this->table = 'accounts';
        $this->conn = ConnectionDB::GetConnect();
        $this->AccountCategoryModel = new BaseModel($this->table);
    }

    public function index()
    {
        return $this->AccountCategoryModel->index();
    }

    public function detail($id)
    {
        return $this->AccountCategoryModel->read($id);
    }

    public function create($data)
    {
        return $this->AccountCategoryModel->create($data);
    }
    public function edit($data, $id)
    {
        return $this->AccountCategoryModel->update($data, $id);
    }
    public function delete($id)
    {
        return $this->AccountCategoryModel->delete($id);
    }
}