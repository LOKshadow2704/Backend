<?php

// Include controllers
require_once(__DIR__ . '/../controllers/controll_auth.php');
require_once(__DIR__ . '/../controllers/controll_product.php');
require_once(__DIR__ . '/../controllers/controll_PT.php');
require_once(__DIR__ . '/../controllers/controll_gymback.php');
require_once(__DIR__ . '/../controllers/controll_cart.php');
require_once(__DIR__ . '/../controllers/controll_orderProduct.php');
require_once(__DIR__ . '/../controllers/controll_payment.php');
require_once(__DIR__ . '/../controllers/controll_homecontent.php');
require_once(__DIR__ . '/../controllers/controll_checkin.php');
require_once(__DIR__ . '/../controllers/control_user.php');

$routes = [
    // Account Routes
      // Account Routes
      'account' => [
        '/Backend/signup' => function () { //---------------Đã chỉnh sửa
            $userController = new UserController();
            $userController->signup();
        },
        '/Backend/login' => function () { //---------------Đã chỉnh sửa
            $authController = new AuthController();
            $authController->login();
        },
        '/Backend/login/refresh-token' => function () {
            $authController = new AuthController();
            $authController->login(); // Chưa sửa
        },
        '/Backend/logout' => function () { //---------------Đã chỉnh sửa
            $authController = new AuthController();
            $authController->logout();
        },
    ],

    // User Routes (Tách từ Account Routes)
    'user' => [
        '/Backend/user/update' => function () {
            $userController = new UserController();
            $userController->Update_User();
        },
        '/Backend/user/updatePassword' => function () {
            $userController = new UserController();
            $userController->Update_Password();
        },
        '/Backend/user/updateAvt' => function () {
            $userController = new UserController();
            $userController->Update_Avt();
        },
        '/Backend/getAccountInfo' => function () {
            $userController = new UserController();
            $userController->getAccountInfo();
        },
        '/Backend/user/checkin' => function () {
            $userController = new UserController();
            $userController->get_user_training();
        },
        '/Backend/admin/getAllAccount' => function () {
            $userController = new UserController();
            $userController->get_Account();
        },
        '/Backend/admin/update' => function () {
            $authController = new AuthController();
            $authController->Update_Account_ByAdmin();
        },
        '/Backend/employee/working' => function () {
            $authController = new AuthController();
            $authController->get_Employee_Working();
        },
    ],

    // Product Routes
    'product' => [
        '/Backend/products' => function () { //---------------Đã chỉnh sửa
            $productController = new controll_product();
            $productController->controll_getAll_products();
        },
        '/Backend/products/manager' => function () { //---------------Đã chỉnh sửa
            $productController = new controll_product();
            $productController->controll_getAll_products_byManeger();
        },
        '/Backend/products/info' => function () { //---------------Đã chỉnh sửa
            $productController = new controll_product();
            $productController->controll_getOne_products();
        },
        '/Backend/product/update' => function () {
            $productController = new controll_product();
            $productController->controll_update_Product();
        },
        '/Backend/product/add' => function () {
            $productController = new controll_product();
            $productController->controll_add_Product();
        },
        '/Backend/product/get_All_Category' => function () {
            $productController = new controll_product();
            $productController->controll_get_All_Category();
        },
        '/Backend/product/delete' => function () {
            $productController = new controll_product();
            $productController->controll_delete_products();
        },
        //Thêm sản phẩm liên quan
    ],

    // PT (Personal Trainer) Routes
    'pt' => [
        '/Backend/PT' => function () {
            $ptController = new controll_PT();
            $ptController->controll_getAll_PT();
        },
        '/Backend/personalTrainer/Info' => function () {
            $ptController = new controll_PT();
            $ptController->controll_getOne_personalTrainer();
        },
        '/Backend/PT/Register' => function () {
            $ptController = new controll_PT();
            $ptController->controll_Register_PT();
        },
    ],

    // Gym Package Routes
    'gympack' => [
        '/Backend/gympack' => function () {
            $gympackController = new controll_gympack();
            $gympackController->controll_get_All_gympack();
        },
        '/Backend/gympack/update' => function () {
            $gympackController = new controll_gympack();
            $gympackController->controll_update_gympack();
        },
        '/Backend/order-gympack' => function () {
            $gympackController = new controll_gympack();
            $gympackController->controll_Register();
        },
        '/Backend/PackageGym/UserInfo' => function () {
            $gympackController = new controll_gympack();
            $gympackController->control_get_PackByUser();
        },
        '/Backend/gympack/registerByEmployee' => function () {
            $gympackController = new controll_gympack();
            $gympackController->control_Register_PackByEmployee();
        },
    ],

    // Cart Routes
    'cart' => [
        '/Backend/cart' => function () { //---------------Đã chỉnh sửa
            $cartController = new controll_cart();
            $cartController->controll_get_All_cart();
        },
        '/Backend/cart/add' => function () { //---------------Đã chỉnh sửa
            $cartController = new controll_cart();
            $cartController->controll_AddtoCart();
        },
        '/Backend/cart/updateQuanPlus' => function () { 
            $cartController = new controll_cart();
            $cartController->controll_PlusCart();
        },
        '/Backend/cart/updateQuanMinus' => function () {
            $cartController = new controll_cart();
            $cartController->controll_MinusCart();
        },
        '/Backend/cart/delete' => function () { //---------------Đã chỉnh sửa
            $cartController = new controll_cart();
            $cartController->controll_DeleteCart();
        },
    ],

    // Order Product Routes
    'order' => [
        '/Backend/order' => function () { //---------------Đã chỉnh sửa
            $orderController = new controll_Order();
            $orderController->controll_ExeOrder();
        },
        '/Backend/PurchaseOrder' => function () {
            $orderController = new controll_Order();
            $orderController->getPurchaseOrder();
        },
        '/Backend/PurchaseOrder/unconfirmed' => function () {
            $orderController = new controll_Order();
            $orderController->getPurchaseOrder_unconfimred();
        },
        '/Backend/PurchaseOrder/confirm' => function () {
            $orderController = new controll_Order();
            $orderController->Control_PurchaseOrder_confirm();
        },
    ],

    // Payment Routes
    'payment' => [
        '/Backend/returnPayment' => function () {
            $paymentController = new Controll_payment();
            $paymentController->returnPayment();
        },
    ],

    // Home Content Routes
    'home' => [
        '/Backend/HomeContent' => function () {
            $homeContentController = new Controll_HomeContent();
            $homeContentController->HomeContent();
        },
    ],

    // Employee Routes
    'employee' => [
        '/Backend/employee/statistical' => function () {
            $checkinController = new controll_checkin();
            $checkinController->get_statistical();
        },
    ],
];

function handleRequest($url)
{
    global $routes;
    $parts = explode('?', $url);
    $route = $parts[0];

    if (isset($parts[1])) {
        handleRequestWithParams($route, $parts[1]);
    } else {
        foreach ($routes as $group => $groupRoutes) {
            if (isset($groupRoutes[$route])) {
                $groupRoutes[$route]();
                return;
            }
        }
        echo $route;
        echo "Route not found!";
    }
}

function handleRequestWithParams($route, $queryParams)
{
    global $routes;
    parse_str($queryParams, $params);

    foreach ($routes as $group => $groupRoutes) {
        if (isset($groupRoutes[$route])) {
            $groupRoutes[$route]($params);
            return;
        }
    }

    echo $route;
    echo "Route not found!";
}