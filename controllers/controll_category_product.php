<?php
require (__DIR__ . "/../models/model_category_product.php");
require_once(__DIR__ . '/../middlewares/JWT_Middleware.php');
class controll_category_product{
 public static function controll_getAll_category_products(){
        if($_SERVER['REQUEST_METHOD'] === "GET"){
           $products = new model_category_product();
           $result = $products->get_All_Category_Products();
           if($result){
            http_response_code(200);
            echo json_encode($result);
           }else{
            http_response_code(404);
            echo json_encode(['error' => 'Không có sản phẩm']);
           }
        }else{
            http_response_code(404);
            echo json_encode(['error'=> 'Đường dẫn không tồn tại']);
        }
    }
	
 public static function controll_update_category_product() {
    if ($_SERVER['REQUEST_METHOD'] === "PUT") {
        $jwt = trim(str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']));
        $data = json_decode(file_get_contents("php://input"), true);
        $Auth = new JWT;
        $verify = $Auth->JWT_verify($jwt);
        if ($verify) {
            if (isset($data["data"]) && !empty($data["data"])) {
                $loaiSanPham = new model_category_product();
                $result = $loaiSanPham->update_Category($data["data"], $data["IDLoaiSanPham"]);
                if ($result) {
                    http_response_code(200);
                    echo json_encode(['success' => 'Thay đổi thành công']);
                } else {
                    http_response_code(403);
                    echo json_encode(['error' => 'Không thực hiện được hành động']);
                }
            } else {
                http_response_code(403);
                echo json_encode(['error' => 'Không có thay đổi']);
            }
        } else {
            http_response_code(403);
            echo json_encode(['error' => 'Lỗi xác thực']);
        }
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Đường dẫn không tồn tại']);
    }
}

 public static function controll_delete_category_product(){
    if($_SERVER['REQUEST_METHOD'] === "DELETE"){
        $jwt = trim(str_replace('Bearer ','', $_SERVER['HTTP_AUTHORIZATION']));
        $data = json_decode(file_get_contents("php://input"), true);
        $Auth =  new JWT;
        $verify = $Auth->JWT_verify($jwt);
        if($verify){
            $category = new model_category_product();
            $result = $category->delete_Category($data["IDLoaiSanPham"]);
            if($result){
                http_response_code(200);
                echo json_encode(['success' => 'Xóa loại sản phẩm thành công']);
            }else{
                http_response_code(404);
                echo json_encode(['error' => 'Xóa loại sản phẩm không thành công']);
            }
        }else{
            http_response_code(403);
            echo json_encode(['error'=> 'Lỗi xác thực']);
        }
    }else{
        http_response_code(404);
        echo json_encode(['error'=> 'Đường dẫn không tồn tại']);
    }
}

 public static function controll_add_category_product(){
    if($_SERVER['REQUEST_METHOD'] === "POST"){
        $jwt = trim(str_replace('Bearer ','', $_SERVER['HTTP_AUTHORIZATION']));
        $data = json_decode(file_get_contents("php://input"), true);
        $Auth = new JWT;
        $verify = $Auth->JWT_verify($jwt);
        if($verify){
            $category = new model_category_product();
            $result = $category->add_Category($data["TenLoaiSanPham"]);
            if($result){
                http_response_code(200);
                echo json_encode(['success' => 'Thêm loại sản phẩm thành công']);
            }else{
                http_response_code(404);
                echo json_encode(['error' => 'Thêm loại sản phẩm không thành công']);
            }
        }else{
            http_response_code(403);
            echo json_encode(['error'=> 'Lỗi xác thực']);
        }
    }else{
        http_response_code(404);
        echo json_encode(['error'=> 'Đường dẫn không tồn tại']);
    }
}

}