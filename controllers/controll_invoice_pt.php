<?php
require_once(__DIR__ . "/../models/model_PT.php");
require_once(__DIR__ . "/../models/model_invoice_pt.php");
require_once(__DIR__ . '/control.php');

class controll_invoice_pt extends Control
{
    private $model_invoice;
    
    public function __construct()
    {
        $this->model_invoice = new model_invoice_pt();
        parent::__construct($_SERVER['HTTP_AUTHORIZATION'] ?? null);
    }

    public function get_practiceSchedule()
    {
        if ($_SERVER['REQUEST_METHOD'] === "GET") {
            $auth = $this->authenticate_user();
            if ($auth) {
                $username = $this->jwt->getUserName();
                $cusID = $this->modelAuth->getIDKhachhang($username);
                $result = $this->model_invoice->get_invoiceByCustomer($cusID);
                if ($result) {
                    $this->sendResponse(200, $result);
                } else {
                    $this->sendResponse(403, ['error' => 'Không có thông tin thuê PT']);
                }
            } else {
                $this->sendResponse(403, ['error' => 'Lỗi xác thực']);
            }
            return;
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
                    $this->model_invoice->updateInvoiceStatus($payment_data["data"]["orderCode"]);
                } elseif ($result["status"] == "CANCELLED") {
                    $this->model_invoice->delete_invoice($payment_data["data"]["orderCode"]);
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

    public function pt_schedule()
    {
        if ($_SERVER['REQUEST_METHOD'] === "GET") {
            $auth = $this->authenticate_user();
            if ($auth) {
                $username = $this->jwt->getUserName();
                $id = $this->modelAuth->get_IDHLV($username);
                $result = $this->model_invoice->get_invoiceByPT($id);
                if ($result) {
                    $this->sendResponse(200, $result);
                } else {
                    $this->sendResponse(403, ['error' => 'Không có lịch dạy']);
                }
            } else {
                $this->sendResponse(403, ['error' => 'Lỗi xác thực']);
            }
            return;
        } else {
            $this->sendResponse(404, ['error' => 'Đường dẫn không tồn tại']);
            return;
        }
    }
}
