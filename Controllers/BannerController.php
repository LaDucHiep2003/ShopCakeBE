<?php
include_once __DIR__ . '/../Models/BannerModel.php';
class BannerController
{
    private $BannerModel;
    private $table;
    public function __construct()
    {
        $this->table = 'banners';
        $this->BannerModel = new BannerModel();
    }

    public function index()
    {
        $result = $this->BannerModel->index();
        echo json_encode(['data' => $result]);
    }
}