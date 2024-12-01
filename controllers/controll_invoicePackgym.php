<?php
require_once(__DIR__ . "/../models/model_invoice_pack.php");
require_once(__DIR__ . '/control.php');
class controll_invoicePackgym extends Control
{
    private $invoice;
    public function __construct()
    {
        parent::__construct($_SERVER['HTTP_AUTHORIZATION'] ?? null);
        $this->invoice = new model_invoice_pack();
    }


    public function update_invoice_status()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $data = json_decode(file_get_contents('php://input'), true);

            if ($this->authenticate_employee()) {
                // Kiểm tra xem dữ liệu có đủ IDHoaDon và TrangThaiThanhToan không
                if (isset($data['IDHoaDon'])) {
                    $IDHoaDon = $data['IDHoaDon'];
                    $result = $this->invoice->updateInvoiceStatus($IDHoaDon);

                    if ($result) {
                        http_response_code(200);
                        echo json_encode(['success' => 'Cập nhật trạng thái thanh toán thành công']);
                    } else {
                        // Lỗi khi cập nhật
                        http_response_code(403);
                        echo json_encode(['error' => 'Cập nhật trạng thái thanh toán không thành công']);
                    }

                } else {
                    // Trường hợp thiếu dữ liệu
                    http_response_code(400);
                    echo json_encode(['error' => 'Dữ liệu không hợp lệ']);
                }
            } else {
                // Lỗi xác thực JWT
                http_response_code(401);
                echo json_encode(['error' => 'Xác thực không thành công']);
            }
        } else {
            // Trả về lỗi nếu không phải phương thức PUT
            http_response_code(404);
            echo json_encode(['error' => 'Phương thức không được hỗ trợ']);
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
                    $this->invoice->updateInvoiceStatus($payment_data["data"]["orderCode"]);
                } elseif ($result["status"] == "CANCELLED") {
                    $this->invoice->delete_invoice_packgym($payment_data["data"]["orderCode"]);
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
