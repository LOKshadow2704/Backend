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
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ', '', $jwt));
            $data = json_decode(file_get_contents('php://input'), true);
            $agent = "";

            if ($_SERVER['HTTP_USER_AGENT'] == "MOBILE_GOATFITNESS") {
                $agent = "MOBILE_GOATFITNESS";
            } else {
                $agent = "WEB";
            }

            $verify = $this->jwt->verifyJWT($jwt, $agent);
            if ($verify) {
                $username = $this->jwt->getUserName($jwt);
                $customer = $this->modelAuth->KhachHang($username);
                $user = $this->modelAuth->AccountInfo($this->jwt->getUsername($jwt));

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
                                $ExePayment = $payment->create($payment_data, $agent, "personal_trainer");
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
                        exit();
                    }
                } else {
                    http_response_code(403);
                    echo json_encode(['error' => 'Đây là tài khoản của bạn!']);
                }
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Đường dẫn không tồn tại']);
        }
    }



}