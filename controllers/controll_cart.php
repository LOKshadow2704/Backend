<?php
require_once(__DIR__ . '/../middlewares/JWT_Middleware.php');
require_once(__DIR__ . '/../models/model_cart.php');
require_once(__DIR__ . '/../models/model_auth.php');
class controll_cart{
    public static function controll_get_All_cart(){
        if($_SERVER['REQUEST_METHOD'] === "GET"){
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ','', $jwt));
            $Auth = new JWT();
            $verify = $Auth->JWT_verify($jwt);
            if($verify){
                $username = $Auth->getUserName($jwt);
                $user = new model_auth();
                $userId = $user->getIDKhachhang($username);
                $model_cart = new model_cart($userId);
                $result = $model_cart->get_All_cart();
                http_response_code(200);
                echo json_encode($result);
            }else{
                http_response_code(400);
                echo json_encode(['error'=> 'Lỗi xác thực 2']);
            }
        }else{
            http_response_code(404);
            echo json_encode(['error'=> 'Đường dẫn không tồn tại']);
        }
            
        }

    public static function controll_AddtoCart(){
        if($_SERVER['REQUEST_METHOD'] === "POST"){
            $data = json_decode(file_get_contents('php://input'), true);
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ','', $jwt));
            $Auth = new JWT();
            $verify = $Auth->JWT_verify($jwt);
            $IDSaNPham = $data['IDSanPham'];
            if($verify){
                $username = $Auth->getUserName($jwt);
                $user = new model_auth();
                $userId = $user->getIDKhachhang($username);
                $model_cart = new model_cart($userId);
                $result = $model_cart->AddtoCart($IDSaNPham);
                if($result){
                    http_response_code(200);
                    echo json_encode(['message'=> 'Thêm vào giỏ hàng thành công']);
                }else{
                    http_response_code(501);
                    echo json_encode(['error'=> 'Không thể thêm sản phẩm này']);
                }   
            }else{
                http_response_code(401);
                echo json_encode(['error'=> 'Lỗi xác thực 2']);
            }
        }else{
            http_response_code(404);
            echo json_encode(['error'=> 'Đường dẫn không tồn tại']);
        }
    }

    public static function controll_PlusCart(){
        if($_SERVER['REQUEST_METHOD'] === "PUT"){
            $data = json_decode(file_get_contents('php://input'), true);
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ','', $jwt));
            $Auth = new JWT();
            $verify = $Auth->JWT_verify($jwt);
            $IDSaNPham = $data['IDSanPham'];
            if($verify){
                $username = $Auth->getUserName($jwt);
                $user = new model_auth();
                $userId = $user->getIDKhachhang($username);
                $model_cart = new model_cart($userId);
                $result = $model_cart->PlusCart($IDSaNPham , $userId);
                if($result){
                    http_response_code(200);
                }else{
                    http_response_code(501);
                }   
            }else{
                http_response_code(401);
                echo json_encode(['error'=> 'Lỗi xác thực 2']);
            }
        }else{
            http_response_code(404);
            echo json_encode(['error'=> 'Đường dẫn không tồn tại']);
        }
    }

    public static function controll_MinusCart(){
        if($_SERVER['REQUEST_METHOD'] === "PUT"){
            $data = json_decode(file_get_contents('php://input'), true);
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ','', $jwt));
            $Auth = new JWT();
            $verify = $Auth->JWT_verify($jwt);
            $IDSaNPham = $data['IDSanPham'];
            if($verify){
                $username = $Auth->getUserName($jwt);
                $user = new model_auth();
                $userId = $user->getIDKhachhang($username);
                $model_cart = new model_cart($userId);
                $result = $model_cart->MinusCart($IDSaNPham , $userId);
                if($result){
                    http_response_code(200);
                }else{
                    http_response_code(501);
                }   
            }else{
                http_response_code(401);
                echo json_encode(['error'=> 'Lỗi xác thực 2']);
            }
        }else{
            http_response_code(404);
            echo json_encode(['error'=> 'Đường dẫn không tồn tại']);
        }
    }

    public static function controll_DeleteCart(){
        if($_SERVER['REQUEST_METHOD'] === "DELETE"){
            $data = json_decode(file_get_contents('php://input'), true);
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ','', $jwt));
            $Auth = new JWT();
            $verify = $Auth->JWT_verify($jwt);
            $IDSaNPham = $data['IDSanPham'];
            if($verify){
                $username = $Auth->getUserName($jwt);
                $user = new model_auth();
                $userId = $user->getIDKhachhang($username);
                $model_cart = new model_cart($userId);
                $result = $model_cart->deleteItem($IDSaNPham , $userId);
                if($result){
                    http_response_code(200);
                }else{
                    http_response_code(501);
                }   
            }else{
                http_response_code(401);
                echo json_encode(['error'=> 'Lỗi xác thực 2']);
            }
        }else{
            http_response_code(404);
            echo json_encode(['error'=> 'Đường dẫn không tồn tại']);
        }
    }

}


