<?php

include_once __DIR__ . '/../Models/BaseModel.php';
class DiscountsModel extends BaseModel
{
    protected $table;
    protected BaseModel $DiscountsModel;
    protected $conn;

    public function __construct()
    {
        $this->table = 'discounts';
        $this->conn = ConnectionDB::GetConnect();
        $this->DiscountsModel = new BaseModel($this->table);
    }
    public function index()
    {
        return $this->DiscountsModel->index();
    }

    public function detail($id)
    {
        try {
            $query = $this->conn->prepare("Select discounts.*, discount_category.category_id from discounts
                inner join discount_category on discounts.id = discount_category.discount_id
                where discounts.id = :id");
            $query->execute(['id' => $id]);
        } catch (Throwable $e) {
            return null;
        }
        return $query->fetch();
    }

    public function create($data)
    {
        $code = $data['code'];
        $type = $data['type'];
        $value = $data['value'];
        $start_date = $data['start_date'];
        $end_date = $data['end_date'];
        $max_uses = $data['max_uses'];
        $min_order_amount = $data['min_order_amount'];
        $category_id = $data['category_id'];

        try{
            $stmt = $this->conn->prepare("INSERT INTO discounts (code, type, value, start_date, end_date, max_uses, min_order_amount) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$code, $type, $value, $start_date, $end_date, $max_uses, $min_order_amount]);

            $discountCodeId = $this->conn->lastInsertId();

            $stmtCategory = $this->conn->prepare("INSERT INTO discount_category (discount_id, category_id) VALUES (?, ?)");

            $stmtCategory->execute([$discountCodeId, $category_id]);

            return true;
        }catch (PDOException $e){
            $this->conn->rollBack();
            return false;
        }
    }
    public function edit($data, $id)
    {
        $code = $data['code'];
        $type = $data['type'];
        $value = $data['value'];
        $start_date = $data['start_date'];
        $end_date = $data['end_date'];
        $max_uses = $data['max_uses'];
        $min_order_amount = $data['min_order_amount'];
        $category_id = $data['category_id'];

        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare("
            UPDATE discounts 
            SET code = ?, type = ?, value = ?, start_date = ?, end_date = ?, max_uses = ?, min_order_amount = ?
            WHERE id = ?
        ");
            $stmt->execute([$code, $type, $value, $start_date, $end_date, $max_uses, $min_order_amount, $id]);

            $stmtCheck = $this->conn->prepare("SELECT id FROM discount_category WHERE discount_id = ?");
            $stmtCheck->execute([$id]);

            if ($stmtCheck->rowCount() > 0) {
                $stmtCategory = $this->conn->prepare("
                UPDATE discount_category SET category_id = ? WHERE discount_id = ?
            ");
                $stmtCategory->execute([$category_id, $id]);
            } else {
                $stmtCategory = $this->conn->prepare("
                INSERT INTO discount_category (discount_id, category_id) VALUES (?, ?)
            ");
                $stmtCategory->execute([$id, $category_id]);
            }

            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return false;
        }
    }
    public function delete($id)
    {
        return $this->DiscountsModel->delete($id);
    }

    public function getDiscountOfCategory()
    {
        try {
            $query = $this->conn->prepare("Select discount_category.id,discounts.code, discounts.is_active, category.title, discounts.value, discounts.type 
                from discounts
                inner join discount_category on discounts.id = discount_category.discount_id
                inner join category on discount_category.category_id = category.id where discount_category.deleted = false");
            $query->execute();
            return $query->fetchAll();
        }catch (PDOException $e){
            $this->conn->rollBack();
        }
    }

    public function deleteDiscountCategory($id)
    {
        try {
            $this->conn->beginTransaction();
            $query = $this->conn->prepare("UPDATE discount_category SET deleted = true WHERE id = :id");
            $query->execute(['id' => $id]);
            $this->conn->commit();
            return true;
        } catch (Throwable $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function applyDiscountCode($code, $orderTotal, $userId)
    {
        try {
            $stmt = $this->conn->prepare("
            SELECT d.*, 
                   COUNT(ud.id) AS used_count 
            FROM discounts d
            LEFT JOIN used_discounts ud ON d.id = ud.discount_id
            WHERE d.code = ?
            GROUP BY d.id
        ");
            $stmt->execute([$code]);
            $discount = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$discount) {
                return ['success' => false, 'message' => 'Mã giảm giá không tồn tại'];
            }

            $now = date('Y-m-d H:i:s');

            // Kiểm tra thời gian
            if ($now < $discount['start_date'] || $now > $discount['end_date']) {
                return ['success' => false, 'message' => 'Mã giảm giá không còn hiệu lực'];
            }

            // Kiểm tra số lượt sử dụng
            if ($discount['used_count'] >= $discount['max_uses']) {
                return ['success' => false, 'message' => 'Mã giảm giá đã hết lượt sử dụng'];
            }

            $checkUserStmt = $this->conn->prepare("
                SELECT 1 FROM used_discounts 
                WHERE discount_id = ? AND user_id = ?
            ");
            $checkUserStmt->execute([$discount['id'], $userId]);

            if ($checkUserStmt->fetch()) {
                return ['success' => false, 'message' => 'Bạn đã sử dụng mã giảm giá này rồi'];
            }

            // Kiểm tra giá trị tối thiểu đơn hàng
            if ($orderTotal < $discount['min_order_amount']) {
                return ['success' => false, 'message' => 'Đơn hàng chưa đủ điều kiện sử dụng mã giảm giá'];
            }

            // Tính giá trị giảm
            $discountValue = 0;
            if ($discount['type'] == 'percent') {
                $discountValue = $orderTotal * ($discount['value'] / 100);
            } elseif ($discount['type'] == 'fixed') {
                $discountValue = $discount['value'];
            }
            return [
                'success' => true,
                'message' => 'Mã giảm giá áp dụng thành công',
                'discount_value' => min($discountValue, $orderTotal),
                'final_total' => $orderTotal - min($discountValue, $orderTotal),
                'discount_id' => $discount['id']
            ];

        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Lỗi khi xử lý mã giảm giá'];
        }
    }


}