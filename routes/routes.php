<?php

// Include controllers
require_once(__DIR__ . '/../controllers/controll_auth.php');
require_once(__DIR__ . '/../controllers/controll_product.php');
require_once(__DIR__ . '/../controllers/controll_PT.php');
require_once(__DIR__ . '/../controllers/controll_gymback.php');
require_once(__DIR__ . '/../controllers/controll_cart.php');
require_once(__DIR__ . '/../controllers/controll_orderProduct.php');
require_once(__DIR__ . '/../controllers/controll_payment.php');
require_once(__DIR__ . '/../controllers/statistical.php');
require_once(__DIR__ . '/../controllers/controll_checkin.php');
require_once(__DIR__ . '/../controllers/control_user.php');
require_once(__DIR__ . '/../controllers/controll_invoice_pt.php');
require_once(__DIR__ . '/../controllers/controll_invoicePackgym.php');
require_once(__DIR__ . '/../middlewares/limit_Request.php');
require_once(__DIR__ . '/../controllers/controll_category_product.php');
require_once(__DIR__ . '/../controllers/schedule_employee.php');

$routes = [
    // Account Routes
    // Account Routes
    'account' => [
        '/Backend/signup' => function () {
            $userController = new UserController();
            $userController->signup();
        },
        '/Backend/login' => function () {
            $authController = new AuthController();
            $authController->login();
        },
        '/Backend/login/refresh-token' => function () {
            $authController = new AuthController();
            $authController->loginWithRT();
        },
        '/Backend/logout' => function () {
            $authController = new AuthController();
            $authController->logout();
        },
    ],

    // User Routes
    'user' => [
        '/Backend/user/update' => function () {
            $userController = new UserController();
            $userController->Update_User();
        },
        '/Backend/user/updatePW' => function () {
            $userController = new UserController();
            $userController->Update_Password();
        },
        '/Backend/user/updateAvt' => function () {
            $userController = new UserController();
            $userController->Update_Avt();
        },
        '/Backend/user/Info' => function () {
            $userController = new UserController();
            $userController->getAccountInfo();
        },
        '/Backend/user/training' => function () {
            $userController = new UserController();
            $userController->get_user_training();
        },
        '/Backend/user/register_pt' => function () {
            $userController = new controll_PT();
            $userController->applyPT();
        },
        '/Backend/user/register_pt/see' => function () {
            $userController = new controll_PT();
            $userController->user_request();
        }


    ],
    'employee' => [
        '/Backend/employee/dashboard' => function () {
            $checkinController = new controll_checkin();
            $checkinController->get_statistical();
        },
        '/Backend/employee/user/gympack' => function () {
            $productController = new UserController();
            $productController->gympack_customer();
        },
        '/Backend/employee/gympack/register' => function () {
            $gympackController = new controll_gympack();
            $gympackController->register_packByEmployee();
        },
        '/Backend/employee/gympack/payment/confirm' => function () {
            $gympackController = new controll_invoicePackgym();
            $gympackController->update_invoice_status();
        },
        '/Backend/employee/gympack/price/update' => function () {
            $gympackController = new controll_gympack();
            $gympackController->update_price();
        },
        '/Backend/employee/products/add' => function () {
            $gympackController = new controll_product();
            $gympackController->employee_add();
        },
        '/Backend/employee/products/update' => function () {
            $gympackController = new controll_product();
            $gympackController->employee_update();
        },
        '/Backend/employee/products/delete' => function () {
            $gympackController = new controll_product();
            $gympackController->employee_delete();
        },
        '/Backend/employee/category/add' => function () {
            $gympackController = new controll_category_product();
            $gympackController->employee_add();
        },
        '/Backend/employee/category/update' => function () {
            $gympackController = new controll_category_product();
            $gympackController->employee_update();
        },
        '/Backend/employee/category/delete' => function () {
            $gympackController = new controll_category_product();
            $gympackController->employee_delete();
        },
        '/Backend/employee/order/unconfirm/get' => function () {
            $gympackController = new controll_Order();
            $gympackController->get_order_unconfimred();
        },
        '/Backend/employee/order/confirm' => function () {
            $gympackController = new controll_Order();
            $gympackController->order_confirm();
        },
        '/Backend/employee/checkin' => function () { //----Chưa test
            $gympackController = new controll_checkin();
            $gympackController->checkin();
        },
        '/Backend/employee/schedule' => function () {
            $gympackController = new Schedule_employee_Controller();
            $gympackController->get_employee_schedule();
        },

    ],
    'admin' => [
        '/Backend/admin/dashboard' => function () {
            $dashboard = new Statistical();
            $dashboard->dashboard_data();
        },
        '/Backend/admin/account/all' => function () {
            $userController = new UserController();
            $userController->get_Account();
        },
        '/Backend/admin/account/add' => function () {
            $userController = new UserController();
            $userController->admin_create_user();
        },
        '/Backend/admin/account/delete' => function () {
            $userController = new UserController();
            $userController->admin_delete_account();
        },
        '/Backend/admin/role/update' => function () {//Loại bỏ
            $authController = new UserController();
            $authController->update_Role();
        },
        '/Backend/admin/personalTrainer/request' => function () {
            $authController = new controll_PT();
            $authController->get_request();
        },
        '/Backend/admin/personalTrainer/request/accept' => function () {
            $authController = new controll_PT();
            $authController->accept_request();
        },
        '/Backend/admin/personalTrainer/request/reject' => function () {
            $authController = new controll_PT();
            $authController->reject_request();
        },
        '/Backend/admin/employee/all' => function () {
            $authController = new UserController();
            $authController->admin_get_employee();
        },
        '/Backend/admin/employee/schedule/add' => function () {
            $authController = new Schedule_employee_Controller();
            $authController->add();
        },
        '/Backend/admin/employee/schedule/all' => function () {
            $gympackController = new Schedule_employee_Controller();
            $gympackController->get_all_schedules();
        },


    ],
    // Product Routes
    'product' => [
        '/Backend/products' => function () {
            $productController = new controll_product();
            $productController->getAll_products();
        },
        '/Backend/products/info' => function () {
            $productController = new controll_product();
            $productController->getOne_product();
        },
        '/Backend/categories' => function () {
            $productController = new controll_category_product();
            $productController->getAll();
        },
    ],

    // PT (Personal Trainer) Routes
    'pt' => [
        '/Backend/personalTrainer/all' => function () {
            $ptController = new controll_PT();
            $ptController->getAll_personalTrainer();
        },
        '/Backend/personalTrainer/Info' => function () {
            $ptController = new controll_PT();
            $ptController->getOne_personalTrainer();
        },
        '/Backend/personalTrainer/Register' => function () {
            $ptController = new controll_PT();
            $ptController->Register_PT();
        },
        '/Backend/personalTrainer/practiceSchedule' => function () {
            // Khách hàng lấy lịch tập
            $ptController = new controll_invoice_pt();
            $ptController->get_practiceSchedule();
        },
        '/Backend/personalTrainer/payment' => function () {
            $ptController = new controll_invoice_pt();
            $ptController->payment_check();
        },
        '/Backend/personalTrainer/schedule' => function () {
            $ptController = new controll_invoice_pt();
            $ptController->pt_schedule();
        },
    ],

    // Gym Package Routes
    'gympack' => [
        '/Backend/gympack' => function () {
            $gympackController = new controll_gympack();
            $gympackController->controll_get_All_gympack();
        },
        '/Backend/gympack/register' => function () {
            $gympackController = new controll_gympack();
            $gympackController->Register();
        },
        '/Backend/gympack/user' => function () {
            $gympackController = new controll_gympack();
            $gympackController->get_UserPack();
        },
        '/Backend/gympack/payment' => function () {
            $gympackController = new controll_invoicePackgym();
            $gympackController->payment_check();
        },

    ],

    // Cart Routes
    'cart' => [
        '/Backend/cart' => function () {
            $cartController = new controll_cart();
            $cartController->controll_get_All_cart();
        },
        '/Backend/cart/add' => function () {
            $cartController = new controll_cart();
            $cartController->controll_AddtoCart();
        },
        '/Backend/cart/updateQuan' => function () {
            $cartController = new controll_cart();
            $cartController->updateQuantity();
        },
        '/Backend/cart/delete' => function () {
            $cartController = new controll_cart();
            $cartController->controll_DeleteCart();
        },
    ],

    // Order Product Routes
    'order' => [
        '/Backend/order' => function () {
            $orderController = new controll_Order();
            $orderController->Order();
        },
        '/Backend/order/purchase' => function () {
            $orderController = new controll_Order();
            $orderController->getPurchaseOrder();
        },
        '/Backend/order/payment' => function () {
            $orderController = new controll_Order();
            $orderController->payment_check();
        },
    ],

    // Home Content Routes
    'home' => [
        '/Backend/home' => function () {
            $homeContentController = new Controll_HomeContent();
            $homeContentController->HomeContent();
        },
    ],

];

function handleRequest($url)
{
    global $routes;
    $parts = explode('?', $url);
    $route = $parts[0];
    // Khởi tạo middleware với giới hạn 10 lần mỗi giây
    $rateLimitMiddleware = new RateLimitMiddleware(10, 1);
    if (!$rateLimitMiddleware->handle($route)) {
        return;
    }
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
    $rateLimitMiddleware = new RateLimitMiddleware(10, 1);
    if (!$rateLimitMiddleware->handle($route)) {
        return;
    }
    foreach ($routes as $group => $groupRoutes) {
        if (isset($groupRoutes[$route])) {
            $groupRoutes[$route]($params);
            return;
        }
    }

    echo $route;
    echo "Route not found!";
}