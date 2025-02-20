<?php
include_once __DIR__ . '/../Models/ProductCategory.php';
class ProductCategoryController
{
    private $ProductCategoryModel;

    private $table;
    public function __construct()
    {
        $this->table = 'category';
        $this->ProductCategoryModel = new ProductCategoryModel();
    }

    public function index()
    {
        $result = $this->ProductCategoryModel->index();
        echo json_encode(['data' => $result]);
    }
    public function detail($id)
    {
        $result = $this->ProductCategoryModel->detail($id);
        echo json_encode(['detail' => $result]);
    }
    public function create()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if ($this->ProductCategoryModel->create($data) == false) {
            echo json_encode(['message' => "Có lỗi xảy ra !"]);
        } else {
            echo json_encode(['message' => "Tạo mới sản phẩm thành công !"]);
        }
    }
    public function edit($id)
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if ($this->ProductCategoryModel->edit($data, $id) == false) {
            echo json_encode(['message' => "Có lỗi xảy ra !"]);
        } else {
            echo json_encode(['message' => "Sửa danh mục sản phẩm thành công !"]);
        }
    }
    public function delete($id)
    {
        if (!$this->ProductCategoryModel->delete($id)) {
            echo json_encode(['message' => "Có lỗi xảy ra !"]);
        } else {
            echo json_encode(['message' => "Xóa sản phẩm thành công!"]);
        }
    }
}