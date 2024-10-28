<?php

// Include controllers
require_once(__DIR__ . '/../controllers/controll_auth.php');
require_once(__DIR__ . '/../controllers/controll_product.php');
require_once(__DIR__ . '/../controllers/controll_PT.php');
require_once(__DIR__ . '/../controllers/controll_gymback.php');
require_once(__DIR__ . '/../controllers/controll_cart.php');
require_once(__DIR__ . '/../controllers/controll_orderProduct.php');
require_once(__DIR__ . '/../controllers/controll_payment.php');
// require_once(__DIR__ . '/../controllers/controll_homecontent.php');
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
            $authController->loginWithRT(); // Chưa sửa
        },
        '/Backend/logout' => function () { //---------------Đã chỉnh sửa
            $authController = new AuthController();
            $authController->logout();
        },
    ],

    // User Routes
    'user' => [
        '/Backend/user/update' => function () {//---------------Đã chỉnh sửa
            $userController = new UserController();
            $userController->Update_User();
        },
        '/Backend/user/updatePW' => function () {//---------------Đã chỉnh sửa
            $userController = new UserController();
            $userController->Update_Password();
        },
        '/Backend/user/updateAvt' => function () {//---------------Đã chỉnh sửa
            $userController = new UserController();
            $userController->Update_Avt();
        },
        '/Backend/user/Info' => function () { //---------------Đã chỉnh sửa
            $userController = new UserController();
            $userController->getAccountInfo();
        },
        '/Backend/user/training' => function () { //---------------Đã chỉnh sửa
            $userController = new UserController();
            $userController->get_user_training();
        },
        '/Backend/admin/getAllAccount' => function () {//---------------Đã chỉnh sửa
            $userController = new UserController();
            $userController->get_Account();
        },
        '/Backend/admin/update' => function () {//---------------Đã chỉnh sửa
            $authController = new UserController();
            $authController->Update_Account_ByAdmin();
        },
        '/Backend/employee/working' => function () {
            $authController = new AuthController();
            $authController->get_Employee_Working();
        },
        // checkin
        // checkout

    ],

    // Product Routes
    'product' => [
        '/Backend/products' => function () { //---------------Đã chỉnh sửa
            $productController = new controll_product();
            $productController->getAll_products();
        },
        '/Backend/products/manager' => function () { //---------------Đã chỉnh sửa
            $productController = new controll_product();
            $productController->getAll_products_byManeger();
        },
        '/Backend/products/info' => function () { //---------------Đã chỉnh sửa
            $productController = new controll_product();
            $productController->getOne_products();
        },
        '/Backend/products/product/update' => function () {  //---------------Đã chỉnh sửa
            $productController = new controll_product();
            $productController->update_Product();
        },
        '/Backend/products/product/add' => function () {//---------------Đã chỉnh sửa
            $productController = new controll_product();
            $productController->add_Product();
        },
        '/Backend/products/categories/all' => function () {//---------------Đã chỉnh sửa
            $productController = new controll_product();
            $productController->get_All_Category();
        },
        '/Backend/products/product/delete' => function () {//---------------Đã chỉnh sửa
            $productController = new controll_product();
            $productController->delete_products();
        },
        //Thêm sản phẩm liên quan
    ],

    // PT (Personal Trainer) Routes
    'pt' => [
        '/Backend/personalTrainer/all' => function () {//---------------Đã chỉnh sửa
            $ptController = new controll_PT();
            $ptController->getAll_personalTrainer();
        },
        '/Backend/personalTrainer/Info' => function () {//---------------Đã chỉnh sửa
            $ptController = new controll_PT();
            $ptController->getOne_personalTrainer();
        },
        '/Backend/personalTrainer/Register' => function () {//---------------Đã chỉnh sửa
            $ptController = new controll_PT();
            $ptController->Register_PT();
        },
    ],

    // Gym Package Routes
    'gympack' => [
        '/Backend/gympack' => function () {//---------------Đã chỉnh sửa
            $gympackController = new controll_gympack();
            $gympackController->controll_get_All_gympack();
        },
        '/Backend/gympack/info' => function () {
            $gympackController = new controll_gympack();
            $gympackController->controll_get_All_gympack();
        },
        '/Backend/gympack/update' => function () {//---------------Đã chỉnh sửa
            $gympackController = new controll_gympack();
            $gympackController->controll_update_gympack();
        },
        '/Backend/order-gympack' => function () {
            $gympackController = new controll_gympack();
            $gympackController->controll_Register();
        },
        '/Backend/gympack/user' => function () {
            $gympackController = new controll_gympack();
            $gympackController->control_get_PackByUser();
        },
        '/Backend/gympack/manager/register' => function () {
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
        '/Backend/cart/updateQuan' => function () {
            $cartController = new controll_cart();
            $cartController->updateQuantity();
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
            $orderController->Order();
        },
        '/Backend/order/purchase' => function () { //---------------Đã chỉnh sửa
            $orderController = new controll_Order();
            $orderController->getPurchaseOrder();
        },
        '/Backend/order/purchase/get_unconfirm' => function () {//---------------Đã chỉnh sửa
            $orderController = new controll_Order();
            $orderController->PurchaseOrder_unconfimred();
        },
        '/Backend/order/purchase/confirm' => function () {//---------------Đã chỉnh sửa
            $orderController = new controll_Order();
            $orderController->Control_PurchaseOrder_confirm();
        },
    ],

    // Payment Routes
    'payment' => [
        '/Backend/WEB/products/success' => function () {
            $paymentController = new Controll_payment();
            $paymentController->returnPayment();
        },
        '/Backend/MOBILE_GOATFITNESS/products/success' => function () {
            $paymentController = new Controll_payment();
            $paymentController->returnPayment();
        },
        '/Backend/WEB/products/cancle' => function () {
            $paymentController = new Controll_payment();
            $paymentController->returnPayment();
        },
        '/Backend/MOBILE_GOATFITNESS/products/cancle' => function () {
            $paymentController = new Controll_payment();
            $paymentController->returnPayment();
        },
    ],

    // Home Content Routes
    'home' => [
        '/Backend/HomeContent' => function () { //---------------Đã chỉnh sửa
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