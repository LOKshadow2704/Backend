<?php
require_once(__DIR__ . "/../models/model_gympack.php");
require_once(__DIR__ . "/../models/model_invoice_pack.php");
require_once(__DIR__ . '/control.php');

class controll_gympack extends Control
{
    private $model_gympack;

    public function __construct()
    {
        $this->model_gympack = new model_gympack();
        parent::__construct($_SERVER['HTTP_AUTHORIZATION'] ?? null);
    }

    public function controll_get_All_gympack()
    {
        if ($_SERVER['REQUEST_METHOD'] === "GET") {
            $result = $this->model_gympack->get_All_gympack();
            if ($result) {
                $this->sendResponse(200, $result);
                return;
            } else {
                $this->sendResponse(404, ['error' => 'Không thể lấy dữ liệu']);
                return;
            }
        } else {
            $this->sendResponse(404, ['error' => 'Đường dẫn không tồn tại']);
            return;
        }
    }

    public function Register()
    {
        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $auth = $this->authenticate_user();
            if ($auth) {
                $data = json_decode(file_get_contents('php://input'), true);
                $agent = $_SERVER['HTTP_USER_AGENT'] == "MOBILE_GOATFITNESS" ? "MOBILE_GOATFITNESS" : "WEB";
                $username = $this->jwt->getUserName($this->jwt->getJWT());

                $cusID = $this->modelAuth->getIDKhachhang($username);
                $invoice_pack = new Model_invoice_pack();
                $check = $invoice_pack->get_PackofCustomer($cusID);
                $user = $this->modelAuth->AccountInfo($username);
                $pack_register_info = $this->model_gympack->get_Info_Pack($data['IDGoiTap']);
                $amount = $pack_register_info['Gia'];

                if (count($check) == 1) {
                    $this->sendResponse(403, ['error' => "Đã tồn tại gói tập"]);
                    return;
                } elseif (count($check) == 0) {
                    if ($data["HinhThucThanhToan"] == 1) {
                        $new_Invoice = new Model_invoice_pack($data["IDGoiTap"], $cusID, $pack_register_info["ThoiHan"]);
                        $result = $new_Invoice->add_Invoice();
                        if ($result) {
                            $this->sendResponse(200, ['message' => "Đăng ký thành công! Vui lòng tới chi nhánh gần nhất để thanh toán"]);
                            return;
                        } else {
                            $this->sendResponse(403, ['error' => "Đăng ký không thành công!"]);
                            return;
                        }
                    } elseif ($data["HinhThucThanhToan"] == 2) {
                        $new_Invoice = new Model_invoice_pack($data["IDGoiTap"], $cusID, $pack_register_info["ThoiHan"]);
                        $result = $new_Invoice->add_Invoice();
                        if ($result) {
                            $payment_data = [
                                'ID' => $result,
                                'amount' => $amount,
                                'name' => $user['HoTen'],
                                'phone' => $user['SDT']
                            ];
                            $payment = new Controll_payment();
                            $ExePayment = $payment->create($payment_data, $agent, "gympack");
                            if ($ExePayment) {
                                $this->sendResponse(200, ['success' => $ExePayment['checkoutUrl']]);
                                return;
                            } else {
                                $this->sendResponse(403, ['error' => 'Không thể thanh toán']);
                                return;
                            }
                        } else {
                            $this->sendResponse(403, ['error' => "Đăng ký không thành công!"]);
                            return;
                        }
                    }
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

    public function get_UserPack()
    {
        if ($_SERVER['REQUEST_METHOD'] === "GET") {
            $auth = $this->authenticate_user();
            if ($auth) {
                $username = $this->jwt->getUserName($this->jwt->getJWT());
                $cusID = $this->modelAuth->getIDKhachhang($username);

                $invoice_pack = new Model_invoice_pack();
                $check = $invoice_pack->get_PackofCustomer($cusID);
                if (count($check) == 1) {
                    $info = $this->model_gympack->get_Info_Pack($check[0]["IDGoiTap"]);
                    if ($info) {
                        $check[0]["info"] = $info;
                        $check = $check[0];
                        $this->sendResponse(200, $check);
                        return;
                    } else {
                        $this->sendResponse(403, ['error' => 'Không thể lấy thông tin']);
                        return;
                    }
                } else {
                    $this->sendResponse(403, ['error' => 'Chưa đăng ký gói tập']);
                    return;
                }
            }
        } else {
            $this->sendResponse(404, ['error' => 'Đường dẫn không tồn tại']);
            return;
        }
    }

    public function register_packByEmployee()
    {
        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $auth = $this->authenticate_employee();
            if ($auth) {
                $data = json_decode(file_get_contents('php://input'), true);

                $username = $this->modelAuth->UserNamebyPhoneN($data["SDT"]);
                if ($username) {
                    $cusID = $this->modelAuth->getIDKhachhang($username);
                    if ($cusID) {
        
                        $invoice_pack = new Model_invoice_pack();
                        $check = $invoice_pack->get_PackofCustomer($cusID);
                        if (count($check) == 1) {
                            $this->sendResponse(403, ['error' => "Đã tồn tại gói tập"]);
                            return;
                        } elseif (count($check) == 0) {
                            $pack = new model_gympack();
                            $timeline = $pack->get_Info_Pack($data["IDGoiTap"])['ThoiHan'];
                            $new_Invoice = new Model_invoice_pack($data["IDGoiTap"], $cusID, $timeline, "Đã Thanh Toán");
                            $result = $new_Invoice->add_Invoice();
                            if ($result) {
                                $this->sendResponse(200, ['message' => "Đăng ký thành công!"]);
                                return;
                            } else {
                                $this->sendResponse(403, ['error' => "Hãy kiểm tra kỹ số điện thoại !"]);
                                return;
                            }
                        }
                    }
                } else {
                    $this->sendResponse(403, ['error' => "Hãy kiểm tra kỹ số điện thoại!"]);
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

    public function update_price()
    {
        if ($_SERVER['REQUEST_METHOD'] === "PUT") {
            $auth = $this->authenticate_employee();
            if ($auth) {
                $data = json_decode(file_get_contents('php://input'), true);
                $role = $this->jwt->getRole();

                if ($role == 2) {
                    $result = $this->model_gympack->update_price($data["Gia"], $data["IDGoiTap"]);
                    if ($result) {
                        $this->sendResponse(200, ['success' => "Cập nhật thành công"]);
                        return;
                    } else {
                        $this->sendResponse(403, ['error' => 'Cập nhật không thành công']);
                        return;
                    }
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
