<?php
require_once(__DIR__ . "/../models/model_category_product.php");
require_once(__DIR__ . '/control.php');

class controll_category_product extends Control
{
    private $model_category;

    public function __construct()
    {
        parent::__construct($_SERVER['HTTP_AUTHORIZATION'] ?? null);
        $this->model_category = new model_category_product();
    }

    public function getAll()
    {
        if ($_SERVER['REQUEST_METHOD'] === "GET") {
            $result = $this->model_category->get_All_Category_Products();
            if ($result) {
                $this->sendResponse(200, $result);
                return;
            } else {
                $this->sendResponse(404, ['error' => 'Không có sản phẩm']);
                return;
            }
        } else {
            $this->sendResponse(404, ['error' => 'Đường dẫn không tồn tại']);
            return;
        }
    }

    public function employee_update()
    {
        if ($_SERVER['REQUEST_METHOD'] === "PUT") {
            $auth = $this->authenticate_employee();
            if ($auth) {
                $data = json_decode(file_get_contents('php://input'), true);
                if (isset($data["TenLoaiSanPham"]) && !empty($data["TenLoaiSanPham"])) {
                    $result = $this->model_category->update($data["TenLoaiSanPham"], $data["IDLoaiSanPham"]);
                    if ($result) {
                        $this->sendResponse(200, ['success' => 'Thay đổi thành công']);
                        return;
                    } else {
                        $this->sendResponse(403, ['error' => 'Không thực hiện được hành động']);
                        return;
                    }
                } else {
                    $this->sendResponse(403, ['error' => 'Không có thay đổi']);
                    return;
                }
            } else {
                $this->sendResponse(403, ['error' => 'Lỗi xác thực']);
                return;
            }
        } else {
            $this->sendResponse(404, ['error' => 'Đường dẫn không tồn tại']);
            return;
        }
    }

    public function employee_delete()
    {
        if ($_SERVER['REQUEST_METHOD'] === "DELETE") {
            $auth = $this->authenticate_employee();
            if ($auth) {
                $result = $this->model_category->delete($_GET["id"]);
                if ($result) {
                    $this->sendResponse(200, ['success' => 'Xóa loại sản phẩm thành công']);
                    return;
                } else {
                    $this->sendResponse(404, ['error' => 'Xóa loại sản phẩm không thành công']);
                    return;
                }
            } else {
                $this->sendResponse(403, ['error' => 'Lỗi xác thực']);
                return;
            }
        } else {
            $this->sendResponse(404, ['error' => 'Đường dẫn không tồn tại']);
            return;
        }
    }

    public function employee_add()
    {
        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $auth = $this->authenticate_employee();
            if ($auth) {
                $data = json_decode(file_get_contents('php://input'), true);
                $result = $this->model_category->add($data["TenLoaiSanPham"]);
                if ($result) {
                    $this->sendResponse(200, ['success' => 'Thêm loại sản phẩm thành công']);
                    return;
                } else {
                    $this->sendResponse(404, ['error' => 'Thêm loại sản phẩm không thành công']);
                    return;
                }
            } else {
                $this->sendResponse(403, ['error' => 'Lỗi xác thực']);
                return;
            }
        } else {
            $this->sendResponse(404, ['error' => 'Đường dẫn không tồn tại']);
            return;
        }
    }

}
