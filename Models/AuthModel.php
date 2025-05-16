<?php

include_once __DIR__ . "/../Connection/Connection.php";
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
require 'vendor/autoload.php';

class AuthModel{
    protected $table;
    protected $conn;

    public function __construct()
    {
        $this->table = 'accounts';
        $this->conn = ConnectionDB::GetConnect();
    }

    public function register($data)
    {
        $fullName = $data['fullName'];
        $email = $data['email'];
        $phone = $data['phone'];
        $pass = md5($data['password']);
        $role = $data['roleId'];
        try {
            $this->conn->beginTransaction();
            $query = $this->conn->prepare("select id from $this->table where email=:email");
            $query->execute(['email' => $email]);
            if ($query->rowCount() > 0) {
                echo json_encode(['message' => 'Email đã tồn tại']);
            } else {
                $query2 = $this->conn->prepare("insert into $this->table (fullName,email,password,phone,roleId) values (:fullName,:email,:password,:phone,:roleId)");
                $query2->execute(['fullName' => $fullName, 'email' => $email, 'password' => $pass, 'phone' => $phone,'roleId' => $role]);
                echo json_encode(['message' => 'Đăng ký tài khoản thành công']);
            }
            $this->conn->commit();
        } catch (Throwable $e) {
            $this->conn->rollBack();
            echo json_encode(['message' => $e]);
        }
    }

    public function login($data)
    {
        $key = getenv('Key');
        try {
            $email = $data['email'];
            $pass = md5($data['password']);
            $query = $this->conn->prepare("
                    SELECT a.*, r.title 
                    FROM accounts a
                    JOIN role r ON a.roleId = r.id
                    WHERE a.email = :email AND a.password = :password");
            $query->execute(['email' => $email, 'password' => $pass]);
            $user = $query->fetch(PDO::FETCH_ASSOC);
            if ($query->rowCount() > 0) {
                $timeCreate = time();
                $timeExpire = time() + 86400;
                $payload = [
                    'iat' => $timeCreate,
                    'exp' => $timeExpire,
                    'data' => [
                        'id' => $user['id'],
                        'email' => $user['email'],
                        'fullName' => $user['fullName'],
                        'phone' => $user['phone'],
                        'avatar' => 'avatar',
                        'role' => $user['title'],
                    ]
                ];
                $jwt = JWT::encode($payload, $key, 'HS256');
                echo json_encode([
                    'message' => 'success',
                    'jwt' => $jwt,
                ]);
            } else {
                echo json_encode(['message' => 'error']);
            }
        } catch (Throwable $e) {
            echo json_encode(['message' => "Có lỗi xảy ra " . $e]);
        }
    }

    public function getUserFromToken($headers)
    {
        $key = getenv('Key'); // Khóa bí mật JWT
        try {
            // Kiểm tra Header Authorization
            if (!isset($headers['Authorization'])) {
                echo json_encode(['message' => 'Authorization header không tồn tại']);
                http_response_code(401);
                return;
            }

            $authHeader = $headers['Authorization'];
            if (strpos($authHeader, 'Bearer ') !== 0) {
                echo json_encode(['message' => 'Authorization header không hợp lệ']);
                http_response_code(401);
                return;
            }

            // Lấy token từ Header
            $jwt = str_replace('Bearer ', '', $authHeader);

            // Giải mã token
            $decoded = JWT::decode($jwt, new Key($key, 'HS256'));

            // Trích xuất thông tin người dùng từ payload
            $userData = (array) $decoded->data;
            echo json_encode([
                'message' => 'Token hợp lệ',
                'user' => $userData
            ]);
            http_response_code(200);
        } catch (Throwable $e) {
            echo json_encode([
                'message' => 'Token không hợp lệ hoặc đã hết hạn',
                'error' => $e->getMessage()
            ]);
            http_response_code(401);
        }
    }
}