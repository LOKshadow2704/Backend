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
            $auth = $this->authenticate_user();
            $data = json_decode(file_get_contents('php://input'), true);
            if ($auth) {
                $amount = 0;
                foreach ($data["products"] as $index => $item) {
                    $check_quantity = $this->check_conditions($item);
                    if (is_array($check_quantity) && isset($check_quantity['error'])) {
                        $this->sendResponse(403, $check_quantity);
                        return;
                    } else {
                        if (is_numeric($check_quantity)) {
                            $amount += $check_quantity;
                        } else {
                            $this->sendResponse(500, ['error' => 'Đã xảy ra lỗi không xác định.']);
                            return;
                        }
                    }
                }

                $username = $this->jwt->getUserName($this->jwt->get_JWT());
                $cusID = $this->modelAuth->getIDKhachhang($username);
                $data_user = $this->modelAuth->AccountInfo($username);
                $oder = new model_order("", $cusID, $data["HinhThucThanhToan"], $data_user["DiaChi"], $amount);
                $ExeOrder = $oder->Order();

                foreach ($data["products"] as $item) {
                    $oderinfo = new model_orderInfo($ExeOrder, $item["IDSanPham"], $item["SoLuong"]);
                    $oderinfo->Order();
                    $updateQuantity = new Model_warehouse($item["IDSanPham"]);
                    $updateQuantity->updateQuantity($item["SoLuong"]);
                    if (!$oderinfo || !$updateQuantity) {
                        $this->sendResponse(403, ['error' => 'Không thể mua sản phẩm']);
                        return;
                    }
                }

                if ($ExeOrder && $data["HinhThucThanhToan"] == 2) {
                    $payment_data = [];
                    $payment_data['ID'] = $ExeOrder;
                    $payment_data['amount'] = $amount;
                    $payment_data['name'] = $data_user['HoTen'];
                    $payment_data['phone'] = $data_user['SDT'];
                    $payment = new Controll_payment();
                    $ExePayment = $payment->create($payment_data, $this->get_agent(), "product");
                    if ($ExePayment) {
                        $this->sendResponse(200, ["success" => $ExePayment['checkoutUrl']]);
                        return;
                    } else {
                        $this->sendResponse(403, ['error' => 'Không thể thanh toán ' . $ExePayment]);
                        return;
                    }
                } elseif ($ExeOrder && $data["HinhThucThanhToan"] == 1) {
                    $cart = new model_cart();
                    foreach ($data["products"] as $item) {
                        $cart->deleteItem($item['IDSanPham'], $cusID);
                    }
                    $this->sendResponse(200, ['message' => 'Mua sản phẩm thành công']);
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

    public function getPurchaseOrder()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $auth = $this->authenticate_user();
            if ($auth) {
                $username = $this->jwt->getUserName();
                $userId = $this->modelAuth->getIDKhachhang($username);
                $result_Purchase = $this->model_order->get_All_Purchase($userId);
                if (empty($result_Purchase)) {
                    $this->sendResponse(200, ["orders" => "Không có đơn hàng mới"]);
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
                $this->sendResponse(200, ["orders" => array_values($groupedOrders)]);
                return;
            } else {
                $this->sendResponse(400, ['error' => 'Lỗi xác thực']);
                return;
            }
        } else {
            $this->sendResponse(404, ['error' => 'Đường dẫn không tồn tại']);
            return;
        }
    }

    public function get_order_unconfimred()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $auth = $this->authenticate_employee();
            if ($auth) {
                $result_Purchase = $this->model_order->get_Purchase_unconfimred();
                if (empty($result_Purchase)) {
                    $this->sendResponse(200, ["orders" => "Không có đơn hàng mới"]);
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
                $this->sendResponse(200, ["orders" => array_values($groupedOrders)]);
                return;
            } else {
                $this->sendResponse(400, ['error' => 'Lỗi xác thực']);
                return;
            }
        } else {
            $this->sendResponse(404, ['error' => 'Đường dẫn không tồn tại']);
            return;
        }
    }

    public function order_confirm()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $auth = $this->authenticate_employee();
            $data = json_decode(file_get_contents('php://input'), true);
            if ($auth) {
                $result_Purchase = $this->model_order->Purchase_confirm($data["IDDonHang"]);
                if ($result_Purchase) {
                    $this->sendResponse(200, []);
                    return;
                } else {
                    $this->sendResponse(403, ['error' => 'Cập nhật không thành công']);
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

    public function payment_check()
    {
        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $auth = $this->authenticate_user();
            $data = json_decode(file_get_contents('php://input'), true);
            if ($auth) {
                $payment = new Controll_payment();
                $payment_data = $payment->getPaymentLinkInformation($data['orderCode']);
                $result = $payment->verifyPaymentWebhookData($payment_data);
                if ($result["status"] == "PAID") {
                    $this->model_order->UpdatePaymentStatus($payment_data["data"]["orderCode"]);
                } elseif ($result["status"] == "CANCELLED") {
                    $this->model_order->delete_order($payment_data["data"]["orderCode"]);
                }
                $this->sendResponse(200, ['status' => $result["status"]]);
                return;
            } else {
                $this->sendResponse(403, ['error' => 'Lỗi xác thực']);
                return;
            }
        } else {
            $this->sendResponse(404, ['error' => 'Đường dẫn không tồn tại']);
            return;
        }
    }

    public function get_statistical()
    {
        $auth = $this->authenticate_employee();
        if ($auth) {
            $result_data = $this->model_order->purchase();
            return $result_data;
        } else {
            return false;
        }
    }
}
