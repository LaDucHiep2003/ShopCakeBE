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
    public function create()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if ($this->BannerModel->create($data) == false) {
            echo json_encode(['message' => "Có lỗi xảy ra !"]);
        } else {
            echo json_encode(['message' => "Thêm banner thành công !"]);
        }
    }
    public function delete($id)
    {
        if (!$this->BannerModel->delete($id)) {
            echo json_encode(['message' => "Có lỗi xảy ra !"]);
        } else {
            echo json_encode(['message' => "Xóa banner thành công!"]);
        }
    }
}