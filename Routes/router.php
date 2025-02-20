<?php

include_once __DIR__ . '/../Controllers/ProductController.php';
include_once __DIR__ . '/../Controllers/AuthController.php';
include_once __DIR__ . '/../Controllers/ProductCategoryController.php';
include_once  __DIR__ . '/../Controllers/RoleController.php';
include_once  __DIR__ . '/../Controllers/AccountsController.php';

include_once __DIR__ . '/../Routes/handleRouter.php';


$ProductController = new ProductController();
$AuthController = new AuthController();
$ProductCategoryController = new ProductCategoryController();
$RoleController = new RoleController();
$AccountController = new AccountController();

$methodRequest = $_SERVER['REQUEST_METHOD'];
$UriRequest = $_SERVER['REQUEST_URI'];
// lấy URI chính
$UriRequest = strtok($UriRequest, '?');

// định tuyến router cho API
$routers = [
    'GET' =>[
        '/products' => function () use ($ProductController) {
            $ProductController->index();
        },
        '/products/(\d+)' => function ($id) use ($ProductController) {
            $ProductController->detail($id);
        },
        '/getUser' =>function () use ($AuthController) {
            $AuthController->getUserFromToken();
        },
        '/getProductCategory' =>function () use ($ProductCategoryController) {
            $ProductCategoryController->index();
        },
        '/category' => function () use ($ProductCategoryController) {
            $ProductCategoryController->index();
        },
        '/category/(\d+)' => function ($id) use ($ProductCategoryController) {
            $ProductCategoryController->detail($id);
        },
        '/roles' => function () use ($RoleController) {
            $RoleController->index();
        },
        '/roles/(\d+)' => function ($id) use ($RoleController) {
            $RoleController->detail($id);
        },
        '/accounts' => function () use ($AccountController) {
            $AccountController->index();
        },
        '/accounts/(\d+)' => function ($id) use ($AccountController) {
            $AccountController->detail($id);
        },
    ],
    'POST' =>[
        '/register' => function () use ($AuthController) {
            $AuthController->register();
        },
        '/login' => function () use ($AuthController) {
            $AuthController->login();
        },
        '/create/product' => function () use ($ProductController) {
            $ProductController->create();
        },
        '/create/category' => function () use ($ProductCategoryController) {
            $ProductCategoryController->create();
        },
        '/edit/product/(\d+)' => function ($id) use ($ProductController) {
            $ProductController->edit($id);
        },
        '/create/role' => function () use ($RoleController) {
            $RoleController->create();
        },
        '/create/account' => function () use ($AccountController) {
            $AccountController->create();
        },
    ],
    'PATCH' =>[
        '/delete/product/(\d+)' => function ($id) use ($ProductController) {
            $ProductController->delete($id);
        },
        '/delete/category/(\d+)' => function ($id) use ($ProductCategoryController) {
            $ProductCategoryController->delete($id);
        },
        '/edit/category/(\d+)' => function ($id) use ($ProductCategoryController) {
            $ProductCategoryController->edit($id);
        },
        '/edit/role/(\d+)' => function ($id) use ($RoleController) {
            $RoleController->edit($id);
        },
        '/delete/role/(\d+)' => function ($id) use ($RoleController) {
            $RoleController->delete($id);
        },
        '/delete/account/(\d+)' => function ($id) use ($AccountController) {
            $AccountController->delete($id);
        },
        '/edit/account/(\d+)' => function ($id) use ($AccountController) {
            $AccountController->edit($id);
        },
        '/role/permissions' => function () use ($RoleController) {
            $RoleController->updatePermissions();
        },
    ],
    // khi xảy ra CORS trình duyệt sẽ gửi OPTIONS (preflight request) trước khi yêu cầu thực tế đến máy chủ. Mục đích kiếm tra xem máy chủ có hỗ trợ method mà web gửi lên không
    'OPTIONS' => function () {
        http_response_code(204); // No Content
        exit();
    }
];
// gọi hàm route để định tuyến request đến các controller
HandleRoute::handleroute($routers, $methodRequest, $UriRequest);