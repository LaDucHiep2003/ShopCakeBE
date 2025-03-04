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
}