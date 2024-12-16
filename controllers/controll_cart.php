<?php
require_once(__DIR__ . '/../models/model_cart.php');
require_once(__DIR__ . '/control.php');

class controll_cart extends Control
{
    protected $model_cart;

    public function __construct()
    {
        parent::__construct($_SERVER['HTTP_AUTHORIZATION'] ?? null);
        $this->model_cart = new model_cart();
    }

    public function controll_get_All_cart()
    {
        if ($_SERVER['REQUEST_METHOD'] === "GET") {
            $auth = $this->authenticate_user();
            if ($auth) {
                $username = $this->jwt->getUserName();
                $userId = $this->modelAuth->getIDKhachhang($username);
                $this->model_cart->userID = $userId;
                $result = $this->model_cart->get_All_cart();
                $this->sendResponse(200, $result);
            } else {
                $this->sendResponse(401, ['error' => 'Lỗi xác thực']);
            }
            return;
        } else {
            $this->sendResponse(404, ['error' => 'Đường dẫn không tồn tại']);
            return;
        }
    }

    public function controll_AddtoCart()
    {
        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $data = json_decode(file_get_contents('php://input'), true);
            $auth = $this->authenticate_user();
            if ($auth) {
                $IDSaNPham = $data['IDSanPham'];
                $username = $this->jwt->getUserName();
                $userId = $this->modelAuth->getIDKhachhang($username);
                $this->model_cart->setUserId($userId);
                $result = $this->model_cart->AddtoCart($IDSaNPham);
                if ($result) {
                    $this->sendResponse(200, ['message' => 'Thêm vào giỏ hàng thành công']);
                } else {
                    $this->sendResponse(501, ['error' => 'Không thể thêm sản phẩm này']);
                }
            } else {
                $this->sendResponse(401, ['error' => 'Lỗi xác thực']);
            }
            return;
        } else {
            $this->sendResponse(404, ['error' => 'Đường dẫn không tồn tại']);
            return;
        }
    }

    public function updateQuantity()
    {
        if ($_SERVER['REQUEST_METHOD'] === "PUT") {
            $data = json_decode(file_get_contents('php://input'), true);
            $auth = $this->authenticate_user();
            if ($auth) {
                $username = $this->jwt->getUserName();
                $userId = $this->modelAuth->getIDKhachhang($username);
                $this->model_cart->setUserId($userId);
                $cartItem = $this->model_cart->getCartItem($userId, $data['IDSanPham']);
                if (!$cartItem) {
                    $this->sendResponse(400, ['error' => 'Sản phẩm không tồn tại trong giỏ hàng.']);
                    return;
                }
                if ($cartItem['SoLuong'] <= 1 && $data['Quantity'] < 0) {
                    $this->sendResponse(400, ['error' => 'Số lượng phải lớn hơn 0']);
                    return;
                }
                $newQuantity = $data['Quantity'] + $cartItem['SoLuong'];
                $result = $this->model_cart->updateQuantity($data['IDSanPham'], $userId, $newQuantity);
                if ($result) {
                    $this->sendResponse(200, ['Success' => 'Cập nhật thành công']);
                } else {
                    $this->sendResponse(501, ['error' => 'Cập nhật thất bại']);
                }
            } else {
                $this->sendResponse(401, ['error' => 'Lỗi xác thực']);
            }
            return;
        } else {
            $this->sendResponse(404, ['error' => 'Đường dẫn không tồn tại']);
            return;
        }
    }

    public function controll_DeleteCart()
    {
        if ($_SERVER['REQUEST_METHOD'] === "DELETE") {
            $data = json_decode(file_get_contents('php://input'), true);
            $auth = $this->authenticate_user();
            if ($auth) {
                $IDSaNPham = $data['IDSanPham'];
                $username = $this->jwt->getUserName();
                $userId = $this->modelAuth->getIDKhachhang($username);
                $model_cart = new model_cart($userId);
                $result = $model_cart->deleteItem($IDSaNPham, $userId);
                if ($result) {
                    $this->sendResponse(200, ['success' => 'Xóa thành công']);
                } else {
                    $this->sendResponse(501, ['error' => 'Xóa không thành công']);
                }
            } else {
                $this->sendResponse(401, ['error' => 'Lỗi xác thực']);
            }
            return;
        } else {
            $this->sendResponse(404, ['error' => 'Đường dẫn không tồn tại']);
            return;
        }
    }
}
