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
    public function verifyCaptcha($captchaToken) {
        $secretKey = '6LdCyEArAAAAAFMkL5M6idEDfnz_t-AKcI4VVpRM';
        $response = file_get_contents(
            "https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$captchaToken"
        );
        $result = json_decode($response, true);
        return $result['success'];
    }

    public function login($data)
    {
        $key = getenv('Key');
        try {
            $email = $data['email'];
            $pass = md5($data['password']);
            $captchaToken = $data['captchaToken'];
            if (!$this->verifyCaptcha($captchaToken)) {
                echo json_encode(['message' => 'Captcha invalid']);
                http_response_code(401);
                return;
            }
            $stmt = $this->conn->prepare("SELECT * FROM accounts WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $account = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$account) {
                echo json_encode(['message' => 'Tài khoản không tồn tại']);
                http_response_code(401);
                return;
            }

            if ($account['status'] === 'inactive') {
                $now = new DateTime();
                $lockedUntil = new DateTime($account['locked_until']);

                if ($now < $lockedUntil) {
                    $remaining = $lockedUntil->getTimestamp() - $now->getTimestamp();
                    echo json_encode(['message' => 'Tài khoản bị khóa. Vui lòng thử lại sau ' . ceil($remaining / 60) . ' phút.']);
                    http_response_code(403);
                    return;
                } else {
                    $this->conn->prepare("UPDATE accounts SET status = 'active', failed_attempts = 0, locked_until = NULL WHERE id = :id")
                        ->execute(['id' => $account['id']]);
                    $account['status'] = 'active';
                    $account['failed_attempts'] = 0;
                }
            }

            // Kiểm tra mật khẩu
            if ($account['password'] === $pass) {
                $this->conn->prepare("UPDATE accounts SET failed_attempts = 0, locked_until = NULL WHERE id = :id")
                    ->execute(['id' => $account['id']]);
                $query = $this->conn->prepare("
                SELECT a.*, r.title 
                FROM accounts a
                JOIN role r ON a.roleId = r.id
                WHERE a.id = :id");
                $query->execute(['id' => $account['id']]);
                $user = $query->fetch(PDO::FETCH_ASSOC);

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
                $failed = $account['failed_attempts'] + 1;
                if ($failed >= 5) {
                    $lockedUntil = (new DateTime())->modify('+30 minutes')->format('Y-m-d H:i:s');

                    $this->conn->prepare("UPDATE accounts SET failed_attempts = :fail, status = 'inactive', locked_until = :locked WHERE id = :id")
                        ->execute([
                            'fail' => $failed,
                            'locked' => $lockedUntil,
                            'id' => $account['id']
                        ]);
                    echo json_encode(['message' => 'Tài khoản bị khóa do đăng nhập sai quá 5 lần. Vui lòng liên hệ admin để mở khóa.']);
                    http_response_code(403);
                } else {
                    $this->conn->prepare("UPDATE accounts SET failed_attempts = :fail WHERE id = :id")
                        ->execute([
                            'fail' => $failed,
                            'id' => $account['id']
                        ]);
                    echo json_encode(['message' => 'Sai mật khẩu. Bạn còn ' . (5 - $failed) . ' lần thử.']);
                    http_response_code(401);
                }
            }

        } catch (Throwable $e) {
            echo json_encode(['message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
            http_response_code(500);
        }
    }


    public function getUserFromToken($headers)
    {
        $key = getenv('Key'); // Khóa bí mật JWT
        try {
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