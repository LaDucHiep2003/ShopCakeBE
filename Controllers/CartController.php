<?php
include_once __DIR__ . '/../Models/CartModel.php';
class CartController
{
    private $CartModel;

    private $table;

    public function __construct()
    {
        $this->table = 'carts';
        $this->CartModel = new CartModel();
    }
    public function addCart(){
        $result = $this->CartModel->addCart();
        echo json_encode(['cart' => $result]);
    }

    public function getCart()
    {
        // Lấy dữ liệu từ request
        $data = json_decode(file_get_contents("php://input"), true);
        // Gọi model để lấy dữ liệu
        $cartItems = $this->CartModel->getCart($data['cart_id']);
        if ($cartItems) {
            echo json_encode($cartItems);
        } else {
            echo json_encode(["message" => "Giỏ hàng trống hoặc không tồn tại"]);
        }
    }
    public function addProduct()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $result = $this->CartModel->addProducts($data);
        if ($result) {
            echo json_encode(["message" => "success", "Thêm thành công sản phẩm vào giỏ hàng"]);
        } else {
            echo json_encode(["message" => "Lỗi"]);
        }
    }

    public function changeQuantity()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $result = $this->CartModel->changeQuantity($data);
        if ($result) {
            echo json_encode(["message" => "success", "Thay đổi thành công số lượng sản phẩm"]);
        } else {
            echo json_encode(["message" => "Lỗi"]);
        }
    }
    public function deleteProduct()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $result = $this->CartModel->deleteProduct($data);
        if ($result) {
            echo json_encode(["message" => "success","Xóa thành công sản phẩm"]);
        } else {
            echo json_encode(["message" => "Lỗi"]);
        }
    }
    public function UpdateCard()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $result = $this->CartModel->updateCard($data);
        echo json_encode($result);
    }
}