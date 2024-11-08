<?php
require_once(__DIR__ . "/../models/model_invoice_pack.php");
require_once(__DIR__ . '/control.php');
class controll_invoicePackgym extends Control
{
    private $invoice;
    public function __construct(){
        parent::__construct($_SERVER['HTTP_AUTHORIZATION'] ?? null);
        $this->invoice = new model_invoice_pack();
    }
    public  function controll_get_All_invoice_packgym()
    {
        if ($_SERVER['REQUEST_METHOD'] === "GET") {
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ', '', $jwt));
            $agent = "";
            if ($_SERVER['HTTP_USER_AGENT'] == "MOBILE_GOATFITNESS") {
                $agent = "MOBILE_GOATFITNESS";
            } else {
                $agent = "WEB";
            }
            $verify = $this->jwt->verifyJWT($jwt , $agent);
            $role = $this->jwt->getRole();
            if ($verify && $role == 2) {
                $result = $this->invoice->get_All_invoice_packgym();
                http_response_code(200);
                echo json_encode($result);
                return;
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Không thể lấy dữ liệu']);
                return;
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Đường dẫn không tồn tại']);
            return;
        }
    }

    public static function controll_update_invoice_status()
    {
        // Kiểm tra phương thức yêu cầu là PUT
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            // Nhận JWT từ header Authorization
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ', '', $jwt));

            // Đọc dữ liệu từ PUT request (php://input)
            $data = json_decode(file_get_contents('php://input'), true);

            // Xác thực JWT
            $Auth = new JWT(); // Giả định bạn đã có class JWT để xác thực
            $verify = $Auth->JWT_verify($jwt);

            if ($verify) {
                // Kiểm tra xem dữ liệu có đủ IDHoaDon và TrangThaiThanhToan không
                if (isset($data['IDHoaDon']) && isset($data['TrangThaiThanhToan'])) {
                    $IDHoaDon = $data['IDHoaDon'];
                    $TrangThaiThanhToan = $data['TrangThaiThanhToan'];

                    // Tạo instance của model
                    $invoice_pack = new Model_invoice_pack();

                    // Kiểm tra nếu trạng thái là "Đã Thanh Toán"
                    if ($TrangThaiThanhToan === "Đã Thanh Toán") {
                        // Gọi hàm updateInvoiceStatus để cập nhật
                        $result = $invoice_pack->updateInvoiceStatus($IDHoaDon);

                        if ($result) {
                            // Thành công
                            http_response_code(200);
                            echo json_encode(['success' => 'Cập nhật trạng thái thanh toán thành công']);
                        } else {
                            // Lỗi khi cập nhật
                            http_response_code(403);
                            echo json_encode(['error' => 'Cập nhật trạng thái thanh toán không thành công']);
                        }
                    } else {
                        // Trường hợp trạng thái không hợp lệ
                        http_response_code(400);
                        echo json_encode(['error' => 'Trạng thái thanh toán không hợp lệ']);
                    }
                } else {
                    // Trường hợp thiếu dữ liệu
                    http_response_code(400);
                    echo json_encode(['error' => 'Thiếu dữ liệu IDHoaDon hoặc TrangThaiThanhToan']);
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

    public static function controll_delete_invoice_packgym()
    {
        if ($_SERVER['REQUEST_METHOD'] === "DELETE") {
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ', '', $jwt));
            $Auth = new JWT();
            $verify = $Auth->JWT_verify($jwt);
            if ($verify) {
                $data = json_decode(file_get_contents('php://input'), true);
                if (isset($data['IDHoaDon'])) {
                    $gympack = new model_invoice_pack();
                    $result = $gympack->delete_invoice_packgym($data['IDHoaDon']);

                    if ($result) {
                        http_response_code(200); 
                        echo json_encode(['success' => 'Hóa đơn thuê gói tập đã được xóa thành công!']);
                        return;
                    } else {
                        http_response_code(500); 
                        echo json_encode(['error' => 'Không thể xóa hóa đơn thuê gói tập.']);
                        return;
                    }
                } else {
                    http_response_code(400); 
                    echo json_encode(['error' => 'ID hóa đơn không hợp lệ.']);
                    return;
                }
            } else {
                http_response_code(403); 
                echo json_encode(['error' => 'Lỗi xác thực.']);
                return;
            }
        } else {
            http_response_code(404); 
            echo json_encode(['error' => 'Đường dẫn không tồn tại.']);
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
