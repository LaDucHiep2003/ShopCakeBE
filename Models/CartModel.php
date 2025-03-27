<?php

include_once __DIR__ . '/../Models/BaseModel.php';
class CartModel extends BaseModel
{
    protected $table;
    protected $CartModel;
    protected $conn;

    public function __construct()
    {
        $this->table = 'carts';
        $this->conn = ConnectionDB::GetConnect();
        $this->CartModel = new BaseModel($this->table);
    }

    public function addCart(){
        $query = $this->conn->prepare("INSERT INTO carts(user_id) VALUES(null)");
        if($query->execute()){
            $lastInsertId = $this->conn->lastInsertId();
            $query = $this->conn->prepare("SELECT * FROM carts WHERE id=:id");
            $query->execute(["id" => $lastInsertId]);
            return $query->fetch();
        }
        return false;
    }
    public function getCart($id): array
    {
        $query = $this->conn->prepare("SELECT products.id, products.title, products.price, products.image, products.thumbnail, cart_items.quantity 
            FROM products
            INNER JOIN cart_items ON products.id = cart_items.product_id
            INNER JOIN carts ON cart_items.cart_id = carts.id
            WHERE carts.id = :cart_id");

        $query->execute(["cart_id" => $id]);
        $results = $query->fetchAll(PDO::FETCH_ASSOC); // Lấy toàn bộ dữ liệu giỏ hàng

        if (count($results) > 0) { // Kiểm tra nếu có sản phẩm trong giỏ hàng
            $product_array = [
                'data' => [],
                'quantity' => 0,
                'totalPrice' => 0,
            ];

            foreach ($results as $row) {
                $product_array['quantity'] += $row['quantity']; // Tính tổng số lượng sản phẩm
                $product_array['totalPrice'] += $row['price'] * $row['quantity']; // Tính tổng tiền
                $product_item = [
                    "id" => $row['id'],
                    "title" => $row['title'],
                    "price" => $row['price'],
                    "image" => $row['image'],
                    "thumbnail" => $row['thumbnail'],
                    "quantity" => $row['quantity'],
                    "totalPrice" => $row['price'] * $row['quantity'],
                ];
                array_push($product_array['data'], $product_item);
            }
            return $product_array;
        } else {
            return [
                'data' => [],
                'quantity' => 0,
                'totalPrice' => 0
            ];
        }
    }


    public function addProducts($data): bool
    {
        $cartId = $data["cartId"];
        $productId = $data["product_id"];
        $quantity = $data["quantity"];
        $query = $this->conn->prepare("SELECT * FROM cart_items WHERE cart_id=:cart_id AND product_id=:product_id");
        $query->execute(["cart_id" => $cartId, "product_id" => $productId]);
        if($query->rowCount() > 0){
            $query = $this->conn->prepare("UPDATE cart_items SET quantity=:quantity WHERE cart_id=:cart_id AND product_id=:product_id");
        }
        else{
            $query = $this->conn->prepare("INSERT INTO cart_items(cart_id, product_id, quantity) VALUES (:cart_id, :product_id, :quantity)");
        }
        if($query->execute(["cart_id" => $cartId, "product_id" => $productId, "quantity" => $quantity])){
            return true;
        }
        return false;
    }

    public function changeQuantity($data): bool
    {
        $productId = $data["product_id"];
        $quantity = $data["quantity"];
        $cartId = $data["cartId"];

        $query = $this->conn->prepare("UPDATE cart_items set quantity=:quantity WHERE product_id=:product_id AND cart_id=:cart_id");
        if($query->execute(["quantity" => $quantity, "product_id" => $productId, "cart_id" => $cartId])){
            return true;
        }
        return false;
    }

    public function deleteProduct($data) : bool
    {
        $product_id = $data["product_id"];
        $query = $this->conn->prepare("DELETE FROM cart_items WHERE product_id=:product_id");
        if($query->execute(["product_id" => $product_id])){
            return true;
        }
        return false;
    }

    public function updateCard($data): int
    {
        $user_id = $data["user_id"];
        $id = $data["id"];

        // Kiểm tra user_id hiện tại của cart
        $query = $this->conn->prepare("SELECT user_id FROM $this->table WHERE id = :id");
        $query->execute(["id" => $id]);
        $cart = $query->fetch(PDO::FETCH_ASSOC);

        if (!$cart) {
            return false; // Không tìm thấy cart
        }
        $cartQuery = $this->conn->prepare("SELECT id FROM $this->table WHERE user_id = :user_id LIMIT 1");
        $cartQuery->execute(["user_id" => $user_id]);
        $existingCart = $cartQuery->fetch(PDO::FETCH_ASSOC);

        if ($existingCart) {
            // Nếu user đã có cart, trả về cartId đã có để tránh xung đột
            return $existingCart["id"];
        }

        if ($cart["user_id"] === null) {
            // Nếu user_id là NULL, cập nhật user_id mới
            $updateQuery = $this->conn->prepare("UPDATE $this->table SET user_id = :user_id WHERE id = :id");
            if ($updateQuery->execute(["user_id" => $user_id, "id" => $id])) {
                return $id;
            }
            return false;
        }
        return $id;
    }
}