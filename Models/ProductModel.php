<?php

include_once __DIR__ . '/../Models/BaseModel.php';
class ProductModel extends BaseModel
{
    protected $table;
    protected $ProductModel;
    protected $conn;

    public function __construct()
    {
        $this->table = 'products';
        $this->conn = ConnectionDB::GetConnect();
        $this->ProductModel = new BaseModel($this->table);
    }

    public function index()
    {
        return $this->ProductModel->index();
    }

    public function create($data)
    {
        return $this->ProductModel->create($data);
    }

    public function detail($id)
    {
        return $this->ProductModel->read($id);
    }
    public function edit($data, $id)
    {
        return $this->ProductModel->update($data, $id);
    }
    public function delete($id)
    {
        return $this->ProductModel->delete($id);
    }
}