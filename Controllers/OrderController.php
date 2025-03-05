<?php
include_once __DIR__ . '/../Models/OrderModel.php';
class OrderController
{
    private $OrderModel;

    private $table;

    public function __construct()
    {
        $this->table = 'orders';
        $this->OrderModel = new OrderModel();
    }

    public function checkout()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $result = $this->OrderModel->checkout($data);
        if ($result) {
            echo json_encode(["message" => "success", "Thêm thành công sản phẩm vào giỏ hàng"]);
        } else {
            echo json_encode(["message" => "Lỗi"]);
        }
    }

    public function getCheckout($id)
    {
        $result = $this->OrderModel->getCheckout($id);
        echo json_encode(['orders' => $result]);
    }

    public function getOrderList()
    {
        $result = $this->OrderModel->getOrderList();
        echo json_encode(['data' => $result]);
    }
    public function delete($id)
    {
        $result = $this->OrderModel->delete($id);
        if ($result) {
            echo json_encode(["message" => "success", "Xóa thành công"]);
        } else {
            echo json_encode(["message" => "Lỗi"]);
        }
    }
    public function ConfirmOrder()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'];
        $result = $this->OrderModel->ConfirmOrder($id);
        if ($result) {
            echo json_encode(["message" => "success", "Xác nhận thành công đơn hàng"]);
        } else {
            echo json_encode(["message" => "Lỗi"]);
        }
    }
    public function confirmedOrder()
    {
        $result = $this->OrderModel->confirmedOrder();
        echo json_encode(['orders' => $result]);
    }
}