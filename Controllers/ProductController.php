<?php
include_once __DIR__ . '/../Models/ProductModel.php';
class ProductController
{
    private $ProductModel;

    private $table;
    public function __construct()
    {
         $this->table = 'products';
         $this->ProductModel = new ProductModel();
    }

    public function index()
    {
        $result = $this->ProductModel->index();
        echo json_encode(['data' => $result]);
    }
    public function create()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if ($this->ProductModel->create($data) == false) {
            echo json_encode(['message' => "Có lỗi xảy ra !"]);
        } else {
            echo json_encode(['message' => "Thêm sản phẩm thành công !"]);
        }
    }
    public function detail($id)
    {
        $result = $this->ProductModel->detail($id);
        echo json_encode(['detail' => $result]);
    }
    public function edit($id)
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if ($this->ProductModel->edit($data, $id) == false) {
            echo json_encode(['message' => "Có lỗi xảy ra !"]);
        } else {
            echo json_encode(['message' => "Sửa sản phẩm thành công !"]);
        }
    }
    public function delete($id)
    {
        if (!$this->ProductModel->delete($id)) {
            echo json_encode(['message' => "Có lỗi xảy ra !"]);
        } else {
            echo json_encode(['message' => "Xóa sản phẩm thành công!"]);
        }
    }
}