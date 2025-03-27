<?php

include_once __DIR__ . '/../Models/BaseModel.php';
class ProductCategoryModel extends BaseModel
{
    protected $table;
    protected $ProductCategoryModel;
    protected $conn;

    public function __construct()
    {
        $this->table = 'category';
        $this->conn = ConnectionDB::GetConnect();
        $this->ProductCategoryModel = new BaseModel($this->table);
    }

    public function index()
    {
        return $this->ProductCategoryModel->index();
    }

    public function detail($id)
    {
        return $this->ProductCategoryModel->read($id);
    }

    public function create($data)
    {
        return $this->ProductCategoryModel->create($data);
    }
    public function edit($data, $id)
    {
        return $this->ProductCategoryModel->update($data, $id);
    }
    public function delete($id)
    {
        return $this->ProductCategoryModel->delete($id);
    }
    public function getProductInCategory($id)
    {
        $query = $this->conn->prepare("Select products.* from category
            inner join products on category.id = products.parentId
            where category.id=:id");
        $query->execute(["id" => $id]);
        $data = $query->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }
}