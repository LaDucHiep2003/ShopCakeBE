<?php
include_once __DIR__ . '/../Models/DiscountsModel.php';
class DiscountsController
{
    private $DiscountModel;

    private $table;

    public function __construct()
    {
        $this->table = 'discounts';
        $this->DiscountModel = new DiscountsModel();
    }

    public function index()
    {
        $result = $this->DiscountModel->index();
        echo json_encode(['data' => $result]);
    }

    public function create()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!$this->DiscountModel->create($data)) {
            echo json_encode(['message' => "Có lỗi xảy ra !"]);
        } else {
            echo json_encode(['message' => "Thêm mã giảm giá thành công !"]);
        }
    }

    public function detail($id)
    {
        $result = $this->DiscountModel->detail($id);
        echo json_encode(['detail' => $result]);
    }

    public function edit($id)
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!$this->DiscountModel->edit($data, $id)) {
            echo json_encode(['message' => "Có lỗi xảy ra !"]);
        } else {
            echo json_encode(['message' => "Sửa mã giảm giá thành công !"]);
        }
    }

    public function delete($id)
    {
        if (!$this->DiscountModel->delete($id)) {
            echo json_encode(['message' => "Có lỗi xảy ra !"]);
        } else {
            echo json_encode(['message' => "Xóa mã giảm giá thành công!"]);
        }
    }

    public function deleteDiscountCategory($id)
    {
        if (!$this->DiscountModel->deleteDiscountCategory($id)) {
            echo json_encode(['message' => "Có lỗi xảy ra !"]);
        } else {
            echo json_encode(['message' => "Xóa mã giảm giá thành công!"]);
        }
    }

    public function getDiscountOfCategory()
    {
        $result = $this->DiscountModel->getDiscountOfCategory();
        echo json_encode(['discounts' => $result]);
    }

    public function applyDiscountCode()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $code = $data['code'];
        $orderTotal = $data['orderTotal'];
        $userId = $data['userId'];
        $result = $this->DiscountModel->applyDiscountCode($code, $orderTotal, $userId);
        echo json_encode($result);
    }
}