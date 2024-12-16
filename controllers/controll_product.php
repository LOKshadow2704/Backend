<?php
require_once(__DIR__ . "/../models/model_products.php");
require_once(__DIR__ . '/control.php');

class controll_product extends Control
{
    protected $model_products;

    public function __construct()
    {
        parent::__construct($_SERVER['HTTP_AUTHORIZATION'] ?? null);
        $this->model_products = new model_product();
    }

    private function sanitizeData($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeData'], $data);
        }
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        $data = strip_tags($data);
        return $data;
    }


    public function getAll_products()
    {
        if ($_SERVER['REQUEST_METHOD'] === "GET") {
            $result = $this->model_products->get_All_Products();
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

    public function getOne_product()
    {
        if ($_SERVER['REQUEST_METHOD'] === "GET") {
            $productID = $_GET['IDSanPham'] ?? null;
            $result = $this->model_products->get_One_Products($productID);
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

    // public function employee_update()
    // {
    //     if ($_SERVER['REQUEST_METHOD'] === "PUT") {
    //         $data = json_decode(file_get_contents("php://input"), true);
    //         $auth = $this->authenticate_employee();
    //         if ($auth) {
    //             if (isset($data["data"]) && !empty($data["data"])) {
    //                 $result = $this->model_products->updateProduct($data["data"], $data["IDSanPham"]);
    //                 if ($result) {
    //                     $this->sendResponse(200, ['success' => 'Thay đổi thành công']);
    //                     return;
    //                 } else {
    //                     $this->sendResponse(403, ['error' => 'Không thực hiện được hành động']);
    //                     return;
    //                 }
    //             } else {
    //                 $this->sendResponse(403, ['error' => 'Không có thay đổi']);
    //                 return;
    //             }
    //         } else {
    //             $this->sendResponse(403, ['error' => 'Lỗi xác thực']);
    //             return;
    //         }
    //     } else {
    //         $this->sendResponse(404, ['error' => 'Đường dẫn không tồn tại']);
    //         return;
    //     }
    // }

    public function employee_update()
{
    if ($_SERVER['REQUEST_METHOD'] === "PUT") {
        $data = json_decode(file_get_contents("php://input"), true);
        $auth = $this->authenticate_employee();

        if ($auth) {
            if (isset($data["data"]) && !empty($data["data"])) {
                // Lọc và xử lý dữ liệu đầu vào để ngăn ngừa XSS
                $filteredData = $this->sanitizeData($data["data"]);

                // Cập nhật sản phẩm với dữ liệu đã được lọc
                $result = $this->model_products->updateProduct($filteredData, $data["IDSanPham"]);
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
            $data = json_decode(file_get_contents("php://input"), true);
            $auth = $this->authenticate_employee();
            if ($auth) {
                $result = $this->model_products->delete_Product($data["IDSanPham"]);
                if ($result) {
                    $this->sendResponse(200, ['success' => 'Xóa thành công']);
                    return;
                } else {
                    $this->sendResponse(404, ['error' => 'Xóa không thành công']);
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

    // public function employee_add()
    // {
    //     if ($_SERVER['REQUEST_METHOD'] === "POST") {
    //         $data = json_decode(file_get_contents("php://input"), true);
    //         $auth = $this->authenticate_employee();
    //         if ($auth) {
    //             $result = $this->model_products->add_Product($data["data"], $data["SoLuong"]);
    //             if ($result) {
    //                 $this->sendResponse(200, ['success' => 'Thêm thành công']);
    //                 return;
    //             } else {
    //                 $this->sendResponse(404, ['error' => 'Thêm không thành công']);
    //                 return;
    //             }
    //         } else {
    //             $this->sendResponse(403, ['error' => 'Lỗi xác thực']);
    //             return;
    //         }
    //     } else {
    //         $this->sendResponse(404, ['error' => 'Đường dẫn không tồn tại']);
    //         return;
    //     }
    // }
    
    public function employee_add()
{
    if ($_SERVER['REQUEST_METHOD'] === "POST") {
        $data = json_decode(file_get_contents("php://input"), true);
        $auth = $this->authenticate_employee();
        
        if ($auth) {
            // Lọc và mã hóa dữ liệu đầu vào để ngăn ngừa XSS
            $filteredData = $this->sanitizeData($data["data"]);
            
            // Thêm sản phẩm với dữ liệu đã được lọc
            $result = $this->model_products->add_Product($filteredData, $data["SoLuong"]);
            
            if ($result) {
                $this->sendResponse(200, ['success' => 'Thêm thành công']);
                return;
            } else {
                $this->sendResponse(404, ['error' => 'Thêm không thành công']);
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
