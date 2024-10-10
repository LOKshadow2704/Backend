<?php

// include_once("./controllers/controll_KhachHang.php");
require_once(__DIR__ . '/../controllers/controll_auth.php');
require_once(__DIR__ . '/../controllers/controll_product.php');
require_once(__DIR__ . '/../controllers/controll_PT.php');
require_once(__DIR__ . '/../controllers/controll_gymback.php');
require_once(__DIR__ . '/../controllers/controll_cart.php');
require_once(__DIR__ . '/../controllers/controll_orderProduct.php');
require_once(__DIR__ . '/../controllers/controll_payment.php');
require_once(__DIR__ . '/../controllers/controll_homecontent.php');
require_once(__DIR__ . '/../controllers/controll_checkin.php');
require_once(__DIR__ . '/../controllers/controll_invoicePackgym.php');
require_once(__DIR__ . '/../controllers/controll_category_product.php');
require_once(__DIR__ . '/../controllers/controll_employee.php');
require_once(__DIR__ . '/../controllers/controll_invoice_pt.php');

$routes = [
    //Account
    '/Backend/signup'=> function(){ controll_auth::controll_Sigup(); },
    '/Backend/login/'=> function(){ controll_auth::controll_Login(); },
    '/Backend/logout/'=> function(){ controll_auth::controll_Logout(); },
    '/Backend/updateUser'=> function(){ controll_auth::controll_Update_User(); }, //
    '/Backend/updatePassword'=> function(){ controll_auth::controll_Update_Password(); },//
    '/Backend/updateAvt'=> function(){ controll_auth::controll_Update_Avt(); },//
    '/Backend/getAccountInfo'=> function(){controll_auth::controll_getAccountInfo(); },//
    '/Backend/user/checkin'=>function(){ controll_auth::get_user_training();},
    '/Backend/admin/getAllAccount'=>function(){ controll_auth::get_Account();},//
    '/Backend/admin/update'=>function(){ controll_auth::Update_Account_ByAdmin();},
	'/Backend/admin/delete'=>function(){ controll_auth::Delete_Account_ByAdmin();},
    //product
    '/Backend/shop'=> function(){ controll_product::controll_getAll_products(); },
    '/Backend/shop/manege/'=> function(){ controll_product::controll_getAll_products_byManeger(); },
    '/Backend/product'=> function(){ controll_product::controll_getOne_products(); },
    '/Backend/product/update'=>function(){ controll_product::controll_update_Product(); },
    '/Backend/product/add'=>function(){ controll_product::controll_add_Product(); },
    '/Backend/product/get_All_Category'=>function(){ controll_product::controll_get_All_Category(); },
    '/Backend/product/delete'=> function(){ controll_product::controll_delete_products(); },//
    //PT
    '/Backend/PT/'=> function(){controll_PT::controll_getAll_PT();},
    '/Backend/personalTrainer/Info'=> function(){ controll_PT::controll_getOne_personalTrainer(); },
    '/Backend/PT/Register'=> function(){controll_PT::controll_Register_PT();},
    //Gym Package
    '/Backend/gympack/'=> function(){controll_gympack::controll_get_All_gympack();},
    '/Backend/gympack/update'=> function(){controll_gympack::controll_update_gympack();},
    '/Backend/order-gympack'=> function(){ controll_gympack::controll_Register(); },
    '/Backend/PackageGym/UserInfo'=> function(){ controll_gympack::control_get_PackByUser(); }, //
    '/Backend/gympack/registerByEmployee'=> function(){ controll_gympack::control_Register_PackByEmployee(); },
	'/Backend/gympack/add'=>function(){ controll_gympack::controll_add_gympack(); },
	'/Backend/gympack/delete'=> function(){ controll_gympack::controll_delete_gympack(); },
    //Cart
    '/Backend/cart/'=> function(){controll_cart::controll_get_All_cart();},//
    '/Backend/cart/add'=> function(){controll_cart::controll_AddtoCart();},//
    '/Backend/cart/updateQuanPlus' =>function(){controll_cart::controll_PlusCart();},//
    '/Backend/cart/updateQuanMinus' =>function(){controll_cart::controll_MinusCart();},//
    '/Backend/cart/delete' =>function(){controll_cart::controll_DeleteCart();},//
    //Order-Product-Purchase
    '/Backend/order'=> function(){ controll_orderProduct::controll_ExeOrder(); },
    '/Backend/PurchaseOrder'=>function(){ controll_orderProduct::getPurchaseOrder();},//
    '/Backend/PurchaseOrder/unconfimred'=>function(){ controll_orderProduct::getPurchaseOrder_unconfimred();},//
    '/Backend/PurchaseOrder/confirm'=>function(){ controll_orderProduct::Control_PurchaseOrder_confirm();},
    //Payment
    '/Backend/returnPayment'=>function(){ Controll_payment::returnPayment();},
    //Home
    '/Backend/HomeContent'=>function(){ Controll_HomeContent::HomeContent();},
    //Employee
    '/Backend/employee/working'=>function(){ controll_auth::get_Employee_Working();},
    '/Backend/employee/statistical'=>function(){ controll_checkin::get_statistical();},//
	//Order-GymPack
	'/Backend/order-gympack/'=> function(){controll_invoicePackgym::controll_get_All_invoice_packgym();},
	'/Backend/order-gympack/update'=> function(){controll_invoicePackgym::controll_update_invoice_status();},
    '/Backend/order-gympack/delete'=> function(){controll_invoicePackgym::controll_delete_invoice_packgym();},
    //Category_Products
	'/Backend/category_product/'=> function(){ controll_category_product::controll_getAll_category_products(); },
	'/Backend/category_product/update'=> function(){ controll_category_product::controll_update_category_product(); },
	'/Backend/category_product/delete'=> function(){ controll_category_product::controll_delete_category_product(); },
	'/Backend/category_product/add'=> function(){ controll_category_product::controll_add_category_product(); },
	//Employee
	'/Backend/employee/'=> function(){ controll_employee::controll_get_all_employee_with_roles(); },
	'/Backend/employee/update'=> function(){ controll_employee::controll_update_employee_info(); },
	'/Backend/employee/delete'=> function(){ controll_employee::controll_delete_employee_info(); },
    //Invoice_PT
    '/Backend/Invoice_PT/UserInfo'=> function(){ controll_invoice_pt::control_get_ptByUser(); },
	'/Backend/Invoice_PT/Owner'=> function(){ controll_invoice_pt::control_get_pt(); }, 
];

// function handleRequest($url) {
//     global $routes;
//     if (isset($routes[$url])) {
//         $routes[$url]();
//     } else {
//         echo $url;
//         echo "Route not found!";
//     }
// }   
// $requestUrl = $_SERVER['REQUEST_URI'];
// handleRequest($requestUrl);
function handleRequest($url) {
    global $routes;
    $parts = explode('?', $url);
    $route = $parts[0];
    if (isset($parts[1])) {
        handleRequestWithParams($route, $parts[1]);
    } else {
        if (isset($routes[$route])) {
            $routes[$route]();
        } else {
            echo $route;
            echo "Route not found!";
        }
    }
}

function handleRequestWithParams($route, $queryParams) {
    global $routes;
    parse_str($queryParams, $params);
    if (isset($routes[$route])) {
        $routes[$route]($params);
    } else {
        echo $route;
        echo "Route not found!";
    }
}
