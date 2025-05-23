<?php
include_once __DIR__ . '/../Models/BaseModel.php';
class BannerModel
{
    protected $table;
    protected $BannerModel;
    protected $conn;

    public function __construct()
    {
        $this->table = 'banners';
        $this->conn = ConnectionDB::GetConnect();
        $this->BannerModel = new BaseModel($this->table);
    }
    public function index()
    {
        return $this->BannerModel->index();
    }
    public function create($data)
    {
        return $this->BannerModel->create($data);
    }
    public function delete($id)
    {
        return $this->BannerModel->delete($id);
    }
}