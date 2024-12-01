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
                $result = $this->model_invoice->get_invoiceByCustomer($cusID);  // Lấy thông tin PT theo tên đăng nhập
                if ($result) {
                    http_response_code(200);
                    echo json_encode($result);
                } else {
                    http_response_code(403);
                    echo json_encode(['error' => 'Không có thông tin thuê PT']);
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
                    $this->model_invoice->updateInvoiceStatus($payment_data["data"]["orderCode"]);
                } elseif ($result["status"] == "CANCELLED") {
                    $this->model_invoice->delete_invoice($payment_data["data"]["orderCode"]);
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

    public function pt_schedule()
    {
        if ($_SERVER['REQUEST_METHOD'] === "GET") {
            $auth = $this->authenticate_user();
            if ($auth) {
                $username = $this->jwt->getUserName();
                $id = $this->modelAuth->get_IDHLV($username);
                $result = $this->model_invoice->get_invoiceByPT($id);  // Lấy thông tin PT theo tên đăng nhập
                if ($result) {
                    http_response_code(200);
                    echo json_encode($result);
                } else {
                    http_response_code(403);
                    echo json_encode(['error' => 'Không có lịch dạy']);
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
