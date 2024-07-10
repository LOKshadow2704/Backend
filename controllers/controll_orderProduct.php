<?php
require_once(__DIR__ . "/../models/model_products.php");
require_once(__DIR__ . "/../models/payment.php");
require_once(__DIR__ . "/../models/model_order.php");
require_once(__DIR__ . '/../middlewares/JWT_Middleware.php');
require_once(__DIR__ . "/../models/model_auth.php");
require_once(__DIR__ . "/../models/model_orderInfo.php");
require_once(__DIR__ . "/../models/model_warehouse.php");
require_once(__DIR__ . "/../models/model_cart.php");
class controll_Order{
    public static function controll_ExeOrder(){
        if($_SERVER['REQUEST_METHOD'] === "POST"){
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ','', $jwt));
            $data = json_decode(file_get_contents('php://input'),true);
            //Xác thực
            $Auth =  new JWT();
            $verify = $Auth->JWT_verify($jwt);
            if($verify){
                $shop = new model_product();
                foreach ($data["products"] as $item){
                    $products = $shop->get_One_Products($item["IDSanPham"]);
                    if($item["SoLuong"] > $products[0]["SoLuong"]){
                        http_response_code(403);
                        echo json_encode(['error' => 'Sản phẩm không đủ số lượng']);
                        exit(); 
                    }
                }
                $user = new model_auth();
                $username = $Auth->getUserName($jwt);
                $cusID = $user->getIDKhachhang($username); 
                $oder = new model_order("", $cusID , $data["HinhThucThanhToan"], $data["DiaChi"],$data["amount"]);
                //Thêm đơn hàng
                $ExeOrder =  $oder->Order();
                //Thêm chi tiết đơn hàng
                foreach ($data["products"] as $item){
                    $oderinfo = new model_orderInfo($ExeOrder, $item["IDSanPham"], $item["SoLuong"]);
                    $oderinfo->Order();
                    $updateQuantity = new Model_warehouse($item["IDSanPham"]);
                    $updateQuantity->updateQuantity($item["SoLuong"]);
                    if(!$oderinfo){
                        http_response_code(403);
                        echo json_encode(['error' => 'Không thể mua sản phẩm']);
                        exit(); 
                    }
                }
                if($ExeOrder && $data["HinhThucThanhToan"]==2){
                    //Tạo link thanh toán
                    $payment = new  Payment();
                    $link = "order_product";
                    $ExePayment = $payment->create($data["amount"] , $ExeOrder , $link);
                    if($ExePayment){
                        http_response_code(200);
                        echo json_encode(['success' => $ExePayment]);
                    }else{
                        http_response_code(403);
                        echo json_encode(['error' => 'Không thể thanh toán']);
                    }
                }elseif($ExeOrder && $data["HinhThucThanhToan"]==1){
                    $cart = new model_cart();
                    foreach ($data["products"] as $item){
                        $cart->deleteItem($item['IDSanPham'],$cusID);
                    }
                    http_response_code(200);
                    echo json_encode(['message' => 'Mua sản phẩm thành công']);
                }
            }else{
                http_response_code(403);
                echo json_encode(['error'=> 'Lỗi xác thực']);
            }
        }else{
            http_response_code(403);
            echo json_encode(['error' => 'Lỗi không xác định']);
        }
    }

    public static function getPurchaseOrder(){
        if($_SERVER['REQUEST_METHOD']==='GET'){
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ','', $jwt));
            $Auth = new JWT();
            $verify = $Auth->JWT_verify($jwt);
            if($verify){
                $username = $Auth->getUserName($jwt);
                $user = new model_auth();
                $userId = $user->getIDKhachhang($username);
                $order = new model_order();
                $result_Purchase = $order->get_All_Purchase($userId);
                $orderInfo = new model_orderInfo();
                $groupedOrders = [];
                foreach($result_Purchase as $item){
                    $orderDetails = [
                        "IDDonHang" => $item["IDDonHang"],
                        "IDKhachHang" => $item["IDKhachHang"],
                        "IDHinhThuc" => $item["IDHinhThuc"],
                        "NgayDat" => $item["NgayDat"],
                        "NgayGiaoDuKien" => $item["NgayGiaoDuKien"],
                        "TrangThaiThanhToan" => $item["TrangThaiThanhToan"],
                        "DiaChi" => $item["DiaChi"],
                        "ThanhTien" => $item["ThanhTien"]
                    ];
                    $result_orderInfo = $orderInfo->get_OrderInfo($item["IDDonHang"]);
                    $orderDetails["orderInfo"] = $result_orderInfo;
                    $groupedOrders[$item["IDDonHang"]] = $orderDetails;
                }
                http_response_code(200);
                echo json_encode(["orders" => array_values($groupedOrders)]);
            } else {
                http_response_code(400);
                echo json_encode(['error'=> 'Lỗi xác thực']);
            }
        }else{
            http_response_code(403);
            echo json_encode(['error' => 'Lỗi không xác định']);
        }
    }

    public static function getPurchaseOrder_unconfimred(){
        if($_SERVER['REQUEST_METHOD']==='PUT'){
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ','', $jwt));
            $Auth = new JWT();
            $verify = $Auth->JWT_verify($jwt);
            if($verify){
                $order = new model_order();
                $result_Purchase = $order->get_All_Purchase_unconfimred();
                $orderInfo = new model_orderInfo();
                $groupedOrders = [];
                foreach($result_Purchase as $item){
                    $orderDetails = [
                        "IDDonHang" => $item["IDDonHang"],
                        "IDKhachHang" => $item["IDKhachHang"],
                        "IDHinhThuc" => $item["IDHinhThuc"],
                        "NgayDat" => $item["NgayDat"],
                        "NgayGiaoDuKien" => $item["NgayGiaoDuKien"],
                        "TrangThaiThanhToan" => $item["TrangThaiThanhToan"],
                        "DiaChi" => $item["DiaChi"],
                        "ThanhTien" => $item["ThanhTien"]
                    ];
                    $result_orderInfo = $orderInfo->get_OrderInfo($item["IDDonHang"]);
                    $orderDetails["orderInfo"] = $result_orderInfo;
                    $groupedOrders[$item["IDDonHang"]] = $orderDetails;
                }
                http_response_code(200);
                echo json_encode(["orders" => array_values($groupedOrders)]);
            } else {
                http_response_code(400);
                echo json_encode(['error'=> 'Lỗi xác thực']);
            }
        }else{
            http_response_code(403);
            echo json_encode(['error' => 'Lỗi không xác định']);
        }
    }

    public static function Control_PurchaseOrder_confirm(){
        if($_SERVER['REQUEST_METHOD']==='PUT'){
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ','', $jwt));
            $data = json_decode(file_get_contents('php://input'),true);
            $Auth = new JWT();
            $verify = $Auth->JWT_verify($jwt);
            if($verify){
                $order = new model_order();
                $result_Purchase = $order->Purchase_confirm($data["IDDonHang"]);
                if($result_Purchase){
                    http_response_code(200);
                    exit();
                }else{
                    http_response_code(403);
                    var_dump($result_Purchase);
                    echo json_encode(['error'=> 'Cập nhật không thành công']);
                }
            } else {
                http_response_code(403);
                echo json_encode(['error'=> 'Lỗi xác thực']);
            }
        }else{
            http_response_code(403);
            echo json_encode(['error' => 'Lỗi không xác định']);
        }
    }
}