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
                if (isset($data['IDHoaDon'])) {
                    $IDHoaDon = $data['IDHoaDon'];
                    $result = $this->invoice->updateInvoiceStatus($IDHoaDon);

                    if ($result) {
                        $this->sendResponse(200, ['success' => 'Cập nhật trạng thái thanh toán thành công']);
                        return;
                    } else {
                        $this->sendResponse(403, ['error' => 'Cập nhật trạng thái thanh toán không thành công']);
                        return;
                    }
                } else {
                    $this->sendResponse(400, ['error' => 'Dữ liệu không hợp lệ']);
                    return;
                }
            } else {
                $this->sendResponse(401, ['error' => 'Xác thực không thành công']);
                return;
            }
        } else {
            $this->sendResponse(404, ['error' => 'Phương thức không được hỗ trợ']);
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
                    $this->invoice->updateInvoiceStatus($payment_data["data"]["orderCode"]);
                } elseif ($result["status"] == "CANCELLED") {
                    $this->invoice->delete_invoice_packgym($payment_data["data"]["orderCode"]);
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
}
