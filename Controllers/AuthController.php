<?php
include_once __DIR__ . '/../Models/AuthModel.php';
class AuthController
{
    private $AuthModel;

    private $table;
    public function __construct()
    {
         $this->table = 'accounts';
         $this->AuthModel = new AuthModel();
    }

    public function register(){
        $data = json_decode(file_get_contents("php://input"),true);
        $this->AuthModel->register($data);
    }

    public function login(){
        $data = json_decode(file_get_contents("php://input"),true);
        $this->AuthModel->login($data);
    }

    public function getUserFromToken()
    {
        $headers = getallheaders(); // Lấy tất cả headers từ request
        $this->AuthModel->getUserFromToken($headers);
    }
}