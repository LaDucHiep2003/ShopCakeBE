<?php

include_once __DIR__ . '/../Models/BaseModel.php';
class ProductModel extends BaseModel
{
    protected $table;
    protected $ProductModel;
    protected $conn;

    public function __construct()
    {
        $this->table = 'products';
        $this->conn = ConnectionDB::GetConnect();
        $this->ProductModel = new BaseModel($this->table);
    }

    public function index()
    {
        return $this->ProductModel->index();
    }

    public function create($data)
    {
        return $this->ProductModel->create($data);
    }

    public function detail($id)
    {
        return $this->ProductModel->read($id);
    }
    public function edit($data, $id)
    {
        return $this->ProductModel->update($data, $id);
    }
    public function delete($id)
    {
        return $this->ProductModel->delete($id);
    }
    public function randomProducts($parentId)
    {
        $query = $this->conn->prepare("Select * from products
            inner join category on products.parentId = category.id
            where products.parentId = :parentId and products.deleted = false ORDER BY RAND() LIMIT 4");
        $query->execute(['parentId' => $parentId]);
        $data = $query->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }
    public function getPopularProducts()
    {
        $query = $this->conn->prepare("SELECT * FROM products where deleted = false and popular = true");
        $query->execute();
        $data = $query->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }
    public function getProductsOfCategory()
    {
        $page = (isset($_GET['page']) && $_GET['page'] !== '' && $_GET['page'] !== 'undefined') ? $_GET['page'] : 1;
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $name = isset($_GET['title']) ? trim($_GET['title']) : null;
        $id = isset($_GET['category']) ? trim($_GET['category']) : null;
        $min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : null;
        $max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : null;

        $where = "deleted = false";
        $params = [];

        if ($id) {
            $idArray = explode(',', $id);
            $placeholders = implode(',', array_fill(0, count($idArray), '?'));
            $where .= " AND parentId IN ($placeholders)";
            $params = array_merge($params, $idArray);
        }

        if ($name) {
            $where .= " AND title LIKE ?";
            $params[] = "%$name%";
        }

        if (!is_null($min_price)) {
            $where .= " AND price >= ?";
            $params[] = $min_price;
        }

        if (!is_null($max_price)) {
            $where .= " AND price <= ?";
            $params[] = $max_price;
        }

        $count_query = $this->conn->prepare("SELECT COUNT(*) as total FROM $this->table WHERE $where");
        $count_query->execute($params);
        $record_total = $count_query->fetch(PDO::FETCH_ASSOC)['total'];
        $page_total = ceil($record_total / $limit);

        // Lấy danh sách sản phẩm
        $query = $this->conn->prepare("SELECT * FROM products WHERE $where LIMIT $limit OFFSET $offset");
        $query->execute($params);

        return [
            'data' => $query->fetchAll(),
            'limit' => $limit,
            'current_page' => $page,
            'total_page' => $page_total,
            'record_total' => $record_total
        ];
    }
}