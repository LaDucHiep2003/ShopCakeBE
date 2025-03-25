<?php

include_once __DIR__ . '/../Models/BaseModel.php';
class OrderModel extends BaseModel
{
    protected $table;
    protected BaseModel $OrderModel;
    protected $conn;

    public function __construct()
    {
        $this->table = 'orders';
        $this->conn = ConnectionDB::GetConnect();
        $this->OrderModel = new BaseModel($this->table);
    }

    public function checkout($data) : int
    {
        $cart_id = $data['cart_id'];
        $first_name = $data['delivery']['first_name'];
        $last_name = $data['delivery']['last_name'];
        $company = $data['delivery']['company'];
        $address = $data['delivery']['address'];
        $city = $data['delivery']['city'];
        $phone = $data['delivery']['phone'];
        $totalQuantity = $data['quantity'];
        $totalPrice = $data['totalPrice'];

        $products = $data['products'];

        try {
            // Bắt đầu transaction để đảm bảo tính toàn vẹn dữ liệu
            $this->conn->beginTransaction();

            // Chèn dữ liệu vào bảng `orders`
            $queryOrder = $this->conn->prepare("INSERT INTO orders (cart_id, first_name, last_name, company, city, phone, address,totalPrice,totalQuantity) 
            VALUES (:cart_id, :first_name, :last_name, :company, :city, :phone, :address, :totalPrice, :totalQuantity)");

            if ($queryOrder->execute([
                "cart_id" => $cart_id,
                "first_name" => $first_name,
                "last_name" => $last_name,
                "company" => $company,
                "city" => $city,
                "phone" => $phone,
                "address" => $address,
                "totalPrice" => $totalPrice,
                "totalQuantity" => $totalQuantity
            ])) {
                // Lấy ID đơn hàng vừa tạo
                $orderId = $this->conn->lastInsertId();
                // Thêm từng sản phẩm vào `order_items`
                $queryItem = $this->conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) 
                VALUES (:order_id, :product_id, :quantity, :price)");

                foreach ($products as $product) {
                    $queryItem->execute([
                        "order_id" => $orderId,
                        "product_id" => $product["id"],
                        "quantity" => $product["quantity"],
                        "price" => $product["price"]
                    ]);
                }
                // Xóa giỏ hàng sau khi đặt hàng thành công
                $queryClearCart = $this->conn->prepare("DELETE FROM cart_items WHERE cart_id = :id");
                $queryClearCart->execute(["id" => $cart_id]);
                // Commit transaction nếu tất cả các bước đều thành công
                $this->conn->commit();
                return $orderId;
            } else {
                // Nếu chèn đơn hàng thất bại, rollback dữ liệu
                $this->conn->rollBack();
                return false;
            }
        } catch (Throwable $e) {
            // Rollback nếu có bất kỳ lỗi nào
            $this->conn->rollBack();
            return false;
        }
    }

    public function getCheckout($id)
    {
        try {
            // Lấy thông tin cá nhân từ bảng orders
            $orderQuery = $this->conn->prepare("
            SELECT id,first_name, last_name, phone, address 
            FROM orders 
            WHERE id = :id");
            $orderQuery->execute(["id" => $id]);
            $orderInfo = $orderQuery->fetch(PDO::FETCH_ASSOC); // Lấy thông tin cá nhân

            // Lấy danh sách sản phẩm trong đơn hàng
            $itemsQuery = $this->conn->prepare("
            SELECT p.title,p.image, oi.price, oi.quantity
            FROM order_items oi
            INNER JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = :id");
            $itemsQuery->execute(["id" => $id]);
            $orderItems = $itemsQuery->fetchAll(PDO::FETCH_ASSOC); // Lấy danh sách sản phẩm

            // Trả về dữ liệu dưới dạng mảng có 2 phần
            return [
                "order_info" => $orderInfo,
                "order_items" => $orderItems
            ];
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage()); // Ghi log lỗi
            return null;
        }
    }

    public function getOrderList(): array
    {
        try {
            $query = $this->conn->prepare("Select * from orders where confirm = false and deleted = false");
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        }catch (Throwable $e) {
            $this->conn->rollBack();
            return false;
        }
    }
    public function delete ($id) : bool
    {
        return $this->OrderModel->delete($id);
    }
    public function ConfirmOrder($id) : bool
    {
        try {
            $query = $this->conn->prepare("Update orders $this->table SET confirm = true WHERE id = :id");
            $query->execute(["id" => $id]);
            return true;
        }catch (Throwable $e) {
            $this->conn->rollBack();
            return false;
        }
    }
    function confirmedOrder()
    {
        try {
            $query = $this->conn->prepare("Select * from $this->table where confirm = true and deleted = false");
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        }catch (Throwable $e) {
            $this->conn->rollBack();
            return false;
        }
    }
    function createVnpayUrl($orderId, $amount, $orderInfo): string
    {
        $vnp_TmnCode = "NPSEKRDY"; // Mã website của bạn
        $vnp_HashSecret = "P94EUOWAYSQVXTL2PS7J83FFG8B7JQEG"; // Chuỗi bí mật

        $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html"; // URL sandbox, đổi thành URL thật khi live
        $vnp_Returnurl = "http://localhost:5173/checkout"; // URL nhận kết quả

        $vnp_TxnRef = $orderId;
        $vnp_OrderInfo = $orderInfo;
        $vnp_OrderType = "other";
        $vnp_Amount = $amount * 100; // Số tiền tính theo VND x100
        $vnp_Locale = "vn";
        $vnp_BankCode = ""; // Nếu để trống, sẽ cho phép chọn ngân hàng

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $_SERVER['REMOTE_ADDR'],
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef
        );

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash =   hash_hmac('sha512', $hashdata, $vnp_HashSecret); //
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        return $vnp_Url;
    }

    public function getHistoryOrder($id)
    {
        $query = $this->conn->prepare("Select orders.created_at, order_items.*, products.title, products.image, products.id as productId from carts
            inner join orders on carts.id = orders.cart_id
            inner join order_items on orders.id = order_items.order_id
            inner join products on order_items.product_id = products.id
            where carts.id = :id order by orders.created_at desc");
        $query->execute(['id' => $id]);
        return $query->fetchAll();
    }
}