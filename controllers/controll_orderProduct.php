<?php
require_once(__DIR__ . "/../models/model_products.php");
require_once("controll_payment.php");
require_once(__DIR__ . "/../models/model_order.php");
require_once(__DIR__ . "/../models/model_orderInfo.php");
require_once(__DIR__ . "/../models/model_warehouse.php");
require_once(__DIR__ . "/../models/model_cart.php");
require_once(__DIR__ . '/control.php');
class controll_Order extends Control
{

    protected $model_product;
    protected $model_order;
    protected $model_order_info;

    public function __construct()
    {
        $this->model_product = new model_product();
        $this->model_order = new model_order();
        $this->model_order_info = new model_orderInfo();
        parent::__construct($_SERVER['HTTP_AUTHORIZATION'] ?? null);
    }

    public function check_conditions($product)
    {
        $amount = 0;
        $product_check = $this->model_product->get_One_Products($product["IDSanPham"]);
        if (is_array($product_check)) {
            if ($product["SoLuong"] > $product_check["SoLuong"]) {
                return ['error' => 'Sản phẩm không đủ số lượng'];
            } else {
                $amount += $product["SoLuong"] * $product_check["DonGia"];
            }
        } else {
            return ['error' => 'Sản phẩm không tồn tại'];
        }

        return $amount;
    }
    public function Order()
    {
        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ', '', $jwt));
            $data = json_decode(file_get_contents('php://input'), true);
            if ($_SERVER['HTTP_USER_AGENT'] == "MOBILE_GOATFITNESS") {
                $agent = "MOBILE_GOATFITNESS";
            } else {
                $agent = "WEB";
            }
            //Xác thực
            $Auth = new JWT();
            $verify = $Auth->verifyJWT($jwt, $agent);
            if ($verify) {
                $amount = 0;
                foreach ($data["products"] as $index => $item) {
                    $check_quantity = $this->check_conditions($item);
                    if (is_array($check_quantity) && isset($check_quantity['error'])) {
                        http_response_code(403);
                        echo json_encode($check_quantity);
                        return;
                    } else {
                        if (is_numeric($check_quantity)) {
                            $amount += $check_quantity;
                        } else {
                            http_response_code(500);
                            echo json_encode(['error' => 'Đã xảy ra lỗi không xác định.']);
                            return;
                        }
                    }
                }
                $username = $this->jwt->getUserName($jwt);
                $cusID = $this->modelAuth->getIDKhachhang($username);
                $data_user = $this->modelAuth->AccountInfo($username);
                $oder = new model_order("", $cusID, $data["HinhThucThanhToan"], $data_user["DiaChi"], $amount);
                //Thêm đơn hàng
                $ExeOrder = $oder->Order();
                //Thêm chi tiết đơn hàng
                foreach ($data["products"] as $item) {
                    $oderinfo = new model_orderInfo($ExeOrder, $item["IDSanPham"], $item["SoLuong"]);
                    $oderinfo->Order();
                    $updateQuantity = new Model_warehouse($item["IDSanPham"]);
                    $updateQuantity->updateQuantity($item["SoLuong"]);
                    if (!$oderinfo || !$updateQuantity) {
                        http_response_code(403);
                        echo json_encode(['error' => 'Không thể mua sản phẩm']);
                        return;
                    }
                }
                //Thao tác thanh toán
                if ($ExeOrder && $data["HinhThucThanhToan"] == 2) {
                    $payment_data = [];
                    $payment_data['ID'] = $ExeOrder;
                    $payment_data['amount'] = $amount;
                    $payment_data['name'] = $data_user['HoTen'];
                    $payment_data['phone'] = $data_user['SDT'];
                    //Tạo link thanh toán
                    $payment = new Controll_payment();
                    $ExePayment = $payment->create($payment_data, $agent, "product");
                    if ($ExePayment) {
                        http_response_code(200);
                        echo json_encode(["success" => $ExePayment['checkoutUrl']]);
                        return;
                    } else {
                        http_response_code(403);
                        echo json_encode(['error' => 'Không thể thanh toán ' . $ExePayment]);
                        return;
                    }
                } elseif ($ExeOrder && $data["HinhThucThanhToan"] == 1) {
                    $cart = new model_cart();
                    foreach ($data["products"] as $item) {
                        $cart->deleteItem($item['IDSanPham'], $cusID);
                    }
                    http_response_code(200);
                    echo json_encode(['message' => 'Mua sản phẩm thành công']);
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

    public function getPurchaseOrder()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ', '', $jwt));
            $agent = "";
            if ($_SERVER['HTTP_USER_AGENT'] == "MOBILE_GOATFITNESS") {
                $agent = "MOBILE_GOATFITNESS";
            } else {
                $agent = "WEB";
            }
            $verify = $this->jwt->verifyJWT($jwt, $agent);
            if ($verify) {
                $username = $this->jwt->getUserName($jwt);
                $userId = $this->modelAuth->getIDKhachhang($username);
                $result_Purchase = $this->model_order->get_All_Purchase($userId);
                if (empty($result_Purchase)) {
                    http_response_code(200);
                    echo json_encode(["orders" => "Không có đơn hàng mới"]);
                    return;
                }
                $groupedOrders = [];
                foreach ($result_Purchase as $item) {
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
                    $result_orderInfo = $this->model_order_info->get_OrderInfo($item["IDDonHang"]);
                    $orderDetails["orderInfo"] = $result_orderInfo;
                    $groupedOrders[$item["IDDonHang"]] = $orderDetails;
                }
                http_response_code(200);
                echo json_encode(["orders" => array_values($groupedOrders)]);
                return;
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Lỗi xác thực']);
                return;
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Đường dẫn không tồn tại']);
            return;
        }
    }

    public function PurchaseOrder_unconfimred()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ', '', $jwt));
            if ($_SERVER['HTTP_USER_AGENT'] == "MOBILE_GOATFITNESS") {
                $agent = "MOBILE_GOATFITNESS";
            } else {
                $agent = "WEB";
            }
            $verify = $this->jwt->verifyJWT($jwt, $agent);
            $role = $this->jwt->getRole();
            if ($verify && $role == "2") {
                $result_Purchase = $this->model_order->get_Purchase_unconfimred();
                if (empty($result_Purchase)) {
                    http_response_code(200);
                    echo json_encode(["orders" => "Không có đơn hàng mới"]);
                    return;
                }
                $groupedOrders = [];
                foreach ($result_Purchase as $item) {
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
                    $result_orderInfo = $this->model_order_info->get_OrderInfo($item["IDDonHang"]);
                    $orderDetails["orderInfo"] = $result_orderInfo;
                    $groupedOrders[$item["IDDonHang"]] = $orderDetails;
                }
                http_response_code(200);
                echo json_encode(["orders" => array_values($groupedOrders)]);
                return;
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Lỗi xác thực']);
                return;
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Đường dẫn không tồn tại']);
            return;
        }
    }

    public static function Control_PurchaseOrder_confirm()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ', '', $jwt));
            $data = json_decode(file_get_contents('php://input'), true);
            $Auth = new JWT();
            $verify = $Auth->JWT_verify($jwt);
            if ($verify) {
                $order = new model_order();
                $result_Purchase = $order->Purchase_confirm($data["IDDonHang"]);
                if ($result_Purchase) {
                    http_response_code(200);
                    exit();
                } else {
                    http_response_code(403);
                    var_dump($result_Purchase);
                    echo json_encode(['error' => 'Cập nhật không thành công']);
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

    public function payment_check()
    {
        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ', '', $jwt));
            $agent = "";
            $data = json_decode(file_get_contents('php://input'), true);
            if ($_SERVER['HTTP_USER_AGENT'] == "MOBILE_GOATFITNESS") {
                $agent = "MOBILE_GOATFITNESS";
            } else {
                $agent = "WEB";
            }
            $verify = $this->jwt->verifyJWT($jwt, $agent);
            if ($verify) {
                $payment = new Controll_payment();
                $payment_data = $payment->getPaymentLinkInformation($data['orderCode']);
                $result = $payment->verifyPaymentWebhookData($payment_data);
                if ($result["status"] == "PAID") {
                    $this->model_order->UpdatePaymentStatus($payment_data["data"]["orderCode"]);
                    $cart = new model_cart();
                    $username = $this->jwt->getUserName($jwt);
                    $cusID = $this->modelAuth->getIDKhachhang($username);
                    foreach ($data["products"] as $item) {
                        $cart->deleteItem($item['IDSanPham'], $cusID);
                    }
                } elseif ($result["status"] == "CANCELLED") {
                    $this->model_order->delete_order($payment_data["data"]["orderCode"]);
                }
                http_response_code(200);
                echo json_encode(['status' => $result["status"]]);
                return;
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