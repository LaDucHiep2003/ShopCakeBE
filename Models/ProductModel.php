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
    public function randomProducts($parentId)
    {
        $query = $this->conn->prepare("Select * from products
            inner join category on products.parentId = category.id
            where products.parentId = :parentId and products.deleted = false ORDER BY RAND() LIMIT 4");
        $query->execute(['parentId' => $parentId]);
        $data = $query->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }
    public function getPopularProducts()
    {
        $query = $this->conn->prepare("SELECT * FROM products where deleted = false and popular = true");
        $query->execute();
        $data = $query->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }
}