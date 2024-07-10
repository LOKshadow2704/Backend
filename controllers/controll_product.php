<?php
require (__DIR__ . "/../models/model_products.php");
require(__DIR__ . "/../models/payment.php");
require_once(__DIR__ . '/../middlewares/JWT_Middleware.php');
class controll_product{

    /**
     * @OA\Get(
     *     path="/Backend/shop",
     *     description="Lấy tất cả thông tin public của sản phẩm",
     *     operationId="endpoint",
     *     tags={"endpoints"},
     *     @OA\Response(response="200", description="Lấy sản phẩm thành công"),
     *     @OA\Response(response="404", description="Không có sản phẩm")
     * )
     */
    public static function controll_getAll_products(){
        if($_SERVER['REQUEST_METHOD'] === "GET"){
           $products = new model_product();
           $result = $products->get_All_Products();
           if($result){
            http_response_code(200);
            echo json_encode($result);
           }else{
            http_response_code(404);
            echo json_encode(['error' => 'Không có sản phẩm']);
           }
        }
    }

    public static function controll_getAll_products_byManeger(){
        if($_SERVER['REQUEST_METHOD'] === "GET"){
           $products = new model_product();
           $result = $products->get_All_Products_byManege();
           if($result){
            http_response_code(200);
            echo json_encode($result);
           }else{
            http_response_code(404);
            echo json_encode(['error' => 'Không có sản phẩm']);
           }
        }
    }

    public static function controll_getOne_products(){
        if($_SERVER['REQUEST_METHOD'] === "GET"){
            $productID = $_GET['IDSanPham'] ?? null;
            $products = new model_product();
            $result = $products->get_One_Products($productID);
            if($result){
            http_response_code(200);
            echo json_encode($result);
           }else{
            http_response_code(404);
            echo json_encode(['error' => 'Không có sản phẩm']);
           }
        }
    }

    public static function controll_update_Product(){
        if($_SERVER['REQUEST_METHOD'] === "PUT"){
            $jwt = trim(str_replace('Bearer ','', $_SERVER['HTTP_AUTHORIZATION']));
            $data = json_decode(file_get_contents("php://input"), true);
            $Auth =  new JWT;
            $verify = $Auth->JWT_verify($jwt);
            if($verify){
                if(isset($data["data"]) && !empty($data["data"])){
                    $products = new model_product();
                    $result = $products->updateProduct($data["data"] , $data["IDSanPham"]);
                    if($result){
                        http_response_code(200);
                        echo json_encode(['success'=> 'Thay đổi thành công']);
                    }else{
                        http_response_code(403);
                        echo json_encode(['error'=> 'Không thực hiện được hành động']);
                    }
                }else{
                    http_response_code(403);
                    echo json_encode(['error'=> 'Không có thay đổi']);
                }
            }else{
                http_response_code(403);
                echo json_encode(['error'=> 'Lỗi xác thực']);
            }
        }
    }

    public static function controll_get_All_Category(){
        if($_SERVER['REQUEST_METHOD'] === "GET"){
           $products = new model_product();
           $result = $products->get_All_Category();
           if($result){
            http_response_code(200);
            echo json_encode($result);
           }else{
            http_response_code(404);
            echo json_encode(['error' => 'Không có sản phẩm']);
           }
        }
    }

    public static function controll_delete_products(){
        if($_SERVER['REQUEST_METHOD'] === "DELETE"){
            $jwt = trim(str_replace('Bearer ','', $_SERVER['HTTP_AUTHORIZATION']));
            $data = json_decode(file_get_contents("php://input"), true);
            $Auth =  new JWT;
            $verify = $Auth->JWT_verify($jwt);
            if($verify){
                $products = new model_product();
                $result = $products->delete_Product($data["IDSanPham"]);
                if($result){
                    http_response_code(200);
                    echo json_encode(['success' => 'Xóa thành công']);
                    
                }else{
                    http_response_code(404);
                    echo json_encode(['error' => 'Xóa không thành công']);
                }
            }else{
                http_response_code(403);
                echo json_encode(['error'=> 'Lỗi xác thực']);
            }
        }
    }

    public static function controll_add_Product(){
        if($_SERVER['REQUEST_METHOD'] === "POST"){
            $jwt = trim(str_replace('Bearer ','', $_SERVER['HTTP_AUTHORIZATION']));
            $data = json_decode(file_get_contents("php://input"), true);
            $Auth =  new JWT;
            $verify = $Auth->JWT_verify($jwt);
            if($verify){
                $products = new model_product();
                $result = $products->add_Product($data["data"], $data["SoLuong"]);
                if($result){
                    http_response_code(200);
                    echo json_encode(['success' => 'Thêm thành công']);
                    
                }else{
                    http_response_code(404);
                    echo json_encode(['error' => 'Thêm không thành công']);
                }
            }else{
                http_response_code(403);
                echo json_encode(['error'=> 'Lỗi xác thực']);
            }
        }
    }
    
}