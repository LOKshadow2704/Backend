<?php
require_once(__DIR__ . '/../models/model_cart.php');
require_once(__DIR__ . '/control.php');
class controll_cart extends Control
{
    protected $model_cart;

    public function __construct()
    {
        parent::__construct();
        $this->model_cart = new model_cart();
    }
    public function controll_get_All_cart()
    {
        if ($_SERVER['REQUEST_METHOD'] === "GET") {
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ', '', $jwt));
            $agent = "";
            if ($_SERVER['HTTP_USER_AGENT'] == "MOBILE_GOATFITNESS") {
                $agent = "MOBILE_GOATFITNESS";
            } else {
                $agent = "WEB";
            }
            $refresh_token = $_SERVER['HTTP_REFRESH_TOKEN'] ?? '';
            $verify = $this->jwt->verifyJWT($jwt, $agent , $refresh_token);
            if ($verify) {
                $username = $this->jwt->getUserName($jwt);
                $userId = $this->modelAuth->getIDKhachhang($username);
                $this->model_cart->userID = $userId;
                $result = $this->model_cart->get_All_cart();
                http_response_code(200);
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Lỗi xác thực 2']);
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Đường dẫn không tồn tại']);
        }

    }

    public function controll_AddtoCart()
    {
        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $data = json_decode(file_get_contents('php://input'), true);
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            if (session_status() === PHP_SESSION_NONE) {
                session_id($_SERVER['HTTP_PHPSESSID']);
                session_start();
            }
            $jwt = trim(str_replace('Bearer ', '', $jwt));
            $agent = "";
            if ($_SERVER['HTTP_USER_AGENT'] == "MOBILE_GOATFITNESS") {
                $agent = "MOBILE_GOATFITNESS";
            } else {
                $agent = "WEB";
            }
            $verify = $this->jwt->verifyJWT($jwt, $agent);
            $IDSaNPham = $data['IDSanPham'];
            if ($verify) {
                $username = $this->jwt->getUserName($jwt);
                $userId = $this->modelAuth->getIDKhachhang($username);
                $model_cart = new model_cart($userId);
                $result = $model_cart->AddtoCart($IDSaNPham);
                if ($result) {
                    http_response_code(200);
                    echo json_encode(['message' => 'Thêm vào giỏ hàng thành công']);
                } else {
                    http_response_code(501);
                    echo json_encode(['error' => 'Không thể thêm sản phẩm này']);
                }
            } else {
                http_response_code(401);
                echo json_encode(['error' => 'Lỗi xác thực 2']);
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Đường dẫn không tồn tại']);
        }
    }

    public static function controll_PlusCart()
    {
        if ($_SERVER['REQUEST_METHOD'] === "PUT") {
            $data = json_decode(file_get_contents('php://input'), true);
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ', '', $jwt));
            $this->jwt = new JWT();
            $verify = $this->jwt->JWT_verify($jwt);
            $IDSaNPham = $data['IDSanPham'];
            if ($verify) {
                $username = $this->jwt->getUserName($jwt);

                $userId = $user->getIDKhachhang($username);
                $model_cart = new model_cart($userId);
                $result = $model_cart->PlusCart($IDSaNPham, $userId);
                if ($result) {
                    http_response_code(200);
                } else {
                    http_response_code(501);
                }
            } else {
                http_response_code(401);
                echo json_encode(['error' => 'Lỗi xác thực 2']);
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Đường dẫn không tồn tại']);
        }
    }

    public static function controll_MinusCart()
    {
        if ($_SERVER['REQUEST_METHOD'] === "PUT") {
            $data = json_decode(file_get_contents('php://input'), true);
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ', '', $jwt));
            $this->jwt = new JWT();
            $verify = $this->jwt->JWT_verify($jwt);
            $IDSaNPham = $data['IDSanPham'];
            if ($verify) {
                $username = $this->jwt->getUserName($jwt);

                $userId = $user->getIDKhachhang($username);
                $model_cart = new model_cart($userId);
                $result = $model_cart->MinusCart($IDSaNPham, $userId);
                if ($result) {
                    http_response_code(200);
                } else {
                    http_response_code(501);
                }
            } else {
                http_response_code(401);
                echo json_encode(['error' => 'Lỗi xác thực 2']);
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Đường dẫn không tồn tại']);
        }
    }

    public function controll_DeleteCart()
    {
        if ($_SERVER['REQUEST_METHOD'] === "DELETE") {
            $data = json_decode(file_get_contents('php://input'), true);
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ', '', $jwt));
            if (session_status() === PHP_SESSION_NONE) {
                session_id($_SERVER['HTTP_PHPSESSID']);
                session_start();
            }
            $verify = $this->jwt->verifyJWT($jwt, $_SERVER['HTTP_USER_AGENT']);
            $IDSaNPham = $data['IDSanPham'];
            if ($verify) {
                $username = $this->jwt->getUserName($jwt);
                $userId = $this->modelAuth->getIDKhachhang($username);
                $model_cart = new model_cart($userId);
                $result = $model_cart->deleteItem($IDSaNPham, $userId);
                if ($result) {
                    http_response_code(200);
                    echo json_encode(['success' => 'Xóa thành công']);
                } else {
                    http_response_code(501);
                    echo json_encode(['success' => 'Xóa không thành công']);
                }
            } else {
                http_response_code(401);
                echo json_encode(['error' => 'Lỗi xác thực 2']);
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Đường dẫn không tồn tại']);
        }
    }

}


