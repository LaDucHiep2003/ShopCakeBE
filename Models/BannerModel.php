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
}