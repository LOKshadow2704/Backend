<?php
require_once(__DIR__ . "/../models/model_PT.php");
require_once(__DIR__ . "/../models/model_invoice_pt.php");
require_once(__DIR__ . '/control.php');

class controll_PT extends Control
{
    private $pt;
    private $invoice_pt;

    public function __construct()
    {
        $this->pt = new model_pt();
        $this->invoice_pt = new model_invoice_pt();
        parent::__construct($_SERVER['HTTP_AUTHORIZATION'] ?? null);
    }
    public function getAll_personalTrainer()
    {
        if ($_SERVER['REQUEST_METHOD'] === "GET") {
            $result = $this->pt->get_All_pt();
            if ($result) {
                http_response_code(200);
                echo json_encode($result);
                return;
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Không tìm thấy dữ liệu']);
                return;
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Đường dẫn không tồn tại']);
            return;
        }
    }

    public function getOne_personalTrainer()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $ptID = $_GET['IDHLV'];
            $result = $this->pt->get_One_personalTrainer($ptID);
            if ($result) {
                http_response_code(200);
                echo json_encode($result);
                return;
            } else {
                http_response_code(403);
                echo json_encode(['error' => 'Không truy cập được dữ liệu']);
                return;
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Đường dẫn không tồn tại']);
            return;
        }
    }

    public function Register_PT()
    {
        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $auth = $this->authenticate_user();
            $data = json_decode(file_get_contents('php://input'), true);
            if ($auth) {
                $username = $this->jwt->getUserName();
                $customer = $this->modelAuth->KhachHang($username);
                $user = $this->modelAuth->AccountInfo($this->jwt->getUsername());

                // Kiểm tra nếu IDHLV không trùng với ID khách hàng
                if ($customer && $customer["IDHLV"] != $data["IDHLV"]) {

                    // Kiểm tra giờ làm việc (từ 8:00 đến 22:00)
                    $startDate = new DateTime($data['StartDate']);
                    $endDate = new DateTime($data['EndDate']);
                    $currentDate = new DateTime();
                    $currentHour = $currentDate->format('H');

                    // Kiểm tra nếu thời gian bắt đầu không được trễ hơn giờ hiện tại
                    if ($startDate < $currentDate || ($startDate->format('H') < $currentHour && $startDate < $currentDate)) {
                        http_response_code(403);
                        echo json_encode(['error' => 'Thời gian bắt đầu không hợp lệ.']);
                        exit();
                    }

                    // Kiểm tra thời gian kết thúc phải cách thời gian bắt đầu ít nhất 1 giờ
                    $interval = $startDate->diff($endDate);
                    if ($interval->h < 1) {
                        http_response_code(403);
                        echo json_encode(['error' => 'Ngày kết thúc phải cách ngày bắt đầu ít nhất 1 giờ.']);
                        exit();
                    }

                    // Kiểm tra thời gian kết thúc phải trong ngày
                    $endHour = $endDate->format('H');
                    if ($endHour > 22) {
                        http_response_code(403);
                        echo json_encode(['error' => 'Giờ kết thúc phải không quá 22:00.']);
                        exit();
                    }

                    // Kiểm tra giờ làm việc trong khoảng 8:00 - 22:00
                    $startHour = $startDate->format('H');
                    if ($startHour < 8 || $startHour > 22) {
                        echo $startHour;
                        http_response_code(403);
                        echo json_encode(['error' => 'Giờ làm việc phải từ 8:00 đến 22:00.']);
                        exit();
                    }

                    // Kiểm tra trùng lặp giờ
                    if (count($this->invoice_pt->checkTime($data["StartDate"], $data["EndDate"])) == 0) {
                        $pt = $this->pt->get_One_personalTrainer($data["IDHLV"]);
                        $amount = $pt["GiaThue"] * $interval->h;

                        if ($data["HinhThucThanhToan"] == 1) {
                            $newInvoi = new model_invoice_pt(null, $customer["IDKhachHang"], $data["IDHLV"], $data["StartDate"], $data["EndDate"]);
                            $exeAdd = $newInvoi->add_Invoice();
                            if ($exeAdd) {
                                http_response_code(200);
                                echo json_encode(['message' => 'Đăng ký thành công, Thanh toán sau khi tập!']);
                            } else {
                                http_response_code(403);
                                echo json_encode(['error' => 'Không thực hiện được hành động']);
                            }
                        } elseif ($data["HinhThucThanhToan"] == 2) {
                            $newInvoi = new model_invoice_pt(null, $customer["IDKhachHang"], $data["IDHLV"], $data["StartDate"], $data["EndDate"]);
                            $exeAdd = $newInvoi->add_Invoice();
                            if ($exeAdd) {
                                $payment = new Controll_payment();
                                $payment_data = [];
                                $payment_data['ID'] = $exeAdd;
                                $payment_data['amount'] = $amount;
                                $payment_data['name'] = $user['HoTen'];
                                $payment_data['phone'] = $user['SDT'];
                                $ExePayment = $payment->create($payment_data, $this->get_agent(), "personal_trainer");
                                if ($ExePayment) {
                                    http_response_code(200);
                                    echo json_encode(['success' => $ExePayment["checkoutUrl"]]);
                                } else {
                                    http_response_code(403);
                                    echo json_encode(['error' => 'Không thể thanh toán']);
                                }
                            }
                        }
                    } else {
                        http_response_code(403);
                        echo json_encode(['error' => 'HLV này đã có lịch tại thời điểm bạn đăng ký']);
                        return;

                    }
                } else {
                    http_response_code(403);
                    echo json_encode(['error' => 'Đây là tài khoản của bạn!']);
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

    public function applyPT()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $auth = $this->authenticate_user();
            $data = json_decode(file_get_contents('php://input'), true);
            $username = $this->jwt->getUsername();
            if ($auth && $this->jwt->getRole() == 3) {
                $check_pt_user = $this->modelAuth->check_pt($username);
                if ($check_pt_user) {
                    $this->sendResponse(403, ['error' => 'Bạn đã là HVL']);
                    return;
                }
                $id_pt = $this->pt->user_apply($data["DichVu"], $data["GiaThue"], $data["ChungChi"]);
                $result = $this->modelAuth->update_pt($id_pt, $username);
                if ($result) {
                    $this->sendResponse(200, ['success' => 'Đăng ký thành công']);
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

    public function get_request()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $auth = $this->authenticate_admin();
            if ($auth) {
                $result = $this->pt->request_pt();
                if (!$result) {
                    $this->sendResponse(500, ['error' => 'Không có dữ liệu']);
                    return;
                }
                $this->sendResponse(200, $result);
                return;
            } else {
                $this->sendResponse(403, ['error' => 'Lỗi xác thực']);
                return;
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Đường dẫn không tồn tại']);
        }
    }

    public function accept_request()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $auth = $this->authenticate_admin();
            $id = $_GET['id'] ?? null;
            if ($auth) {
                $result = $this->pt->accept_pt($id);
                if ($result) {
                    $this->sendResponse(200, ['success' => 'Xác thực PT mới thành công']);
                    return;
                } else {
                    $this->sendResponse(500, ['error' => 'Lỗi hệ thống']);
                    return;
                }
            } else {

            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Đường dẫn không tồn tại']);
        }
    }

    public function reject_request()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $auth = $this->authenticate_admin();
            $id = $_GET['id'] ?? null;
            if ($auth) {
                $result = $this->pt->reject_pt($id);
                if ($result) {
                    $this->sendResponse(200, ['success' => 'Đã từ chối PT']);
                    return;
                } else {
                    $this->sendResponse(500, ['error' => 'Lỗi hệ thống']);
                    return;
                }
            } else {

            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Đường dẫn không tồn tại']);
        }
    }

    public function user_request()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $auth = $this->authenticate_user();
            if ($auth) {
                $username = $this->jwt->getUsername();
                $result = $this->pt->user_request_pt($username);
                if (!$result) {
                    $this->sendResponse(500, ['error' => 'Không có dữ liệu']);
                    return;
                }
                $this->sendResponse(200, $result);
                return;
            } else {
                $this->sendResponse(403, ['error' => 'Lỗi xác thực']);
                return;
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Đường dẫn không tồn tại']);
        }
    }



}