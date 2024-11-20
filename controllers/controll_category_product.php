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
                http_response_code(200);
                echo json_encode($result);
                return;
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Không có sản phẩm']);
                return;
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Đường dẫn không tồn tại']);
            return;
        }
    }

    public function employee_update()
    {
        if ($_SERVER['REQUEST_METHOD'] === "PUT") {
            $data = json_decode(file_get_contents('php://input'), true);
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ', '', $jwt));
            $agent = "";
            if ($_SERVER['HTTP_USER_AGENT'] == "MOBILE_GOATFITNESS") {
                $agent = "MOBILE_GOATFITNESS";
            } else {
                $agent = "WEB";
            }
            $verify = $this->jwt->verifyJWT($jwt, $agent);
            $role = $this->jwt->getRole();
            if ($verify && $role == 2) {
                if (isset($data["TenLoaiSanPham"]) && !empty($data["TenLoaiSanPham"])) {
                    $result = $this->model_category->update($data["TenLoaiSanPham"], $data["IDLoaiSanPham"]);
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

    public function employee_delete()
    {
        if ($_SERVER['REQUEST_METHOD'] === "DELETE") {
            $data = json_decode(file_get_contents('php://input'), true);
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ', '', $jwt));
            $agent = "";
            if ($_SERVER['HTTP_USER_AGENT'] == "MOBILE_GOATFITNESS") {
                $agent = "MOBILE_GOATFITNESS";
            } else {
                $agent = "WEB";
            }
            $verify = $this->jwt->verifyJWT($jwt, $agent);
            $role = $this->jwt->getRole();
            if ($verify && $role == 2) {
                $result = $this->model_category->delete($data["IDLoaiSanPham"]);
                if ($result) {
                    http_response_code(200);
                    echo json_encode(['success' => 'Xóa loại sản phẩm thành công']);
                    return;
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Xóa loại sản phẩm không thành công']);
                    return;
                }
            } else {
                http_response_code(403);
                echo json_encode(['error' => 'Lỗi xác thực']);
                return;
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Đường dẫn không tồn tại']);
            return;
        }
    }

    public function employee_add()
    {
        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $data = json_decode(file_get_contents('php://input'), true);
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ', '', $jwt));
            $agent = "";
            if ($_SERVER['HTTP_USER_AGENT'] == "MOBILE_GOATFITNESS") {
                $agent = "MOBILE_GOATFITNESS";
            } else {
                $agent = "WEB";
            }
            $verify = $this->jwt->verifyJWT($jwt, $agent);
            $role = $this->jwt->getRole();
            if ($verify && $role == 2) {
                $result = $this->model_category->add($data["TenLoaiSanPham"]);
                if ($result) {
                    http_response_code(200);
                    echo json_encode(['success' => 'Thêm loại sản phẩm thành công']);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Thêm loại sản phẩm không thành công']);
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

}