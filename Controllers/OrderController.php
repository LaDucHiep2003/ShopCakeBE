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
        // Đọc dữ liệu từ request
        $data = json_decode(file_get_contents("php://input"), true);
        $orderId = $this->OrderModel->checkout($data);
        if ($orderId) {
            echo json_encode([
                "message" => "success",
                "order_id" => $orderId,
                "detail" => "Đơn hàng đã được tạo thành công."
            ]);
        } else {
            // Trả về thông báo lỗi
            echo json_encode([
                "message" => "error",
                "detail" => "Có lỗi xảy ra khi tạo đơn hàng."
            ]);
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
    public function createVnpayUrl()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $orderId = $data['orderId'];
        $amount = $data['amount'];
        $orderInfo = "Thanh toán đơn hàng #$orderId";
        $result = $this->OrderModel->createVnpayUrl($orderId, $amount, $orderInfo);
        echo json_encode(["payUrl" => $result]);
    }
    public function getHistoryOrder($id)
    {
        $result = $this->OrderModel->getHistoryOrder($id);
        echo json_encode(['orders' => $result]);
    }
}