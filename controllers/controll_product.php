<?php
require_once(__DIR__ . "/../models/model_products.php");
require_once(__DIR__ . "/../models/payment.php");
require_once(__DIR__ . '/control.php');
class controll_product extends Control
{
    protected $model_products;
    public function __construct()
    {
        parent::__construct($_SERVER['HTTP_AUTHORIZATION'] ?? null);
        $this->model_products = new model_product();
    }
    public function getAll_products()
    {
        if ($_SERVER['REQUEST_METHOD'] === "GET") {
            $result = $this->model_products->get_All_Products();
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

    public function getOne_product()
    {
        if ($_SERVER['REQUEST_METHOD'] === "GET") {
            $productID = $_GET['IDSanPham'] ?? null;
            $result = $this->model_products->get_One_Products($productID);
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
            $jwt = trim(str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']));
            $data = json_decode(file_get_contents("php://input"), true);
            $agent = "";
            if ($_SERVER['HTTP_USER_AGENT'] == "MOBILE_GOATFITNESS") {
                $agent = "MOBILE_GOATFITNESS";
            } else {
                $agent = "WEB";
            }
            $verify = $this->jwt->verifyJWT($jwt, $agent);
            $role = $this->jwt->getRole();
            if ($verify && ($role == "1" || $role == "2")) {
                if (isset($data["data"]) && !empty($data["data"])) {
                    $result = $this->model_products->updateProduct($data["data"], $data["IDSanPham"]);
                    if ($result) {
                        http_response_code(200);
                        echo json_encode(['success' => 'Thay đổi thành công']);
                        return;
                    } else {
                        http_response_code(403);
                        echo json_encode(['error' => 'Không thực hiện được hành động']);
                        return;
                    }
                } else {
                    http_response_code(403);
                    echo json_encode(['error' => 'Không có thay đổi']);
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
    public function employee_delete()
    {
        if ($_SERVER['REQUEST_METHOD'] === "DELETE") {
            $jwt = trim(str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']));
            $data = json_decode(file_get_contents("php://input"), true);
            $agent = "";
            if ($_SERVER['HTTP_USER_AGENT'] == "MOBILE_GOATFITNESS") {
                $agent = "MOBILE_GOATFITNESS";
            } else {
                $agent = "WEB";
            }
            $verify = $this->jwt->verifyJWT($jwt, $agent);
            $role = $this->jwt->getRole();
            if ($verify && ($role == "1" || $role == "2")) {
                $result = $this->model_products->delete_Product($data["IDSanPham"]);
                if ($result) {
                    http_response_code(200);
                    echo json_encode(['success' => 'Xóa thành công']);
                    return;

                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Xóa không thành công']);
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
            $jwt = trim(str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']));
            $data = json_decode(file_get_contents("php://input"), true);
            $agent = "";
            if ($_SERVER['HTTP_USER_AGENT'] == "MOBILE_GOATFITNESS") {
                $agent = "MOBILE_GOATFITNESS";
            } else {
                $agent = "WEB";
            }
            $verify = $this->jwt->verifyJWT($jwt, $agent);
            $role = $this->jwt->getRole();
            if ($verify && ($role == "1" || $role == "2")) {
                $result = $this->model_products->add_Product($data["data"], $data["SoLuong"]);
                if ($result) {
                    http_response_code(200);
                    echo json_encode(['success' => 'Thêm thành công']);
                    return;

                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Thêm không thành công']);
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

}