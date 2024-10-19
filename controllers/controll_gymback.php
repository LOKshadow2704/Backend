<?php
require_once(__DIR__ . "/../models/model_gympack.php");
require_once(__DIR__ . "/../models/model_invoice_pack.php");
require_once(__DIR__ . "/../models/payment.php");
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

    public static function controll_Register()
    {
        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ', '', $jwt));
            $data = json_decode(file_get_contents('php://input'), true);
            //Xác thực
            $Auth = new JWT();
            $verify = $Auth->JWT_verify($jwt);
            if ($verify) {
                $user = new model_auth();
                $username = $Auth->getUserName($jwt);
                $cusID = $user->getIDKhachhang($username);
                //Kiểm tra tồn tại gói tập của user chưa
                $invoice_pack = new Model_invoice_pack();
                $check = $invoice_pack->Exe_get_Pack_byKhachHang($cusID);
                if (count($check) == 1) {
                    http_response_code(403);
                    echo json_encode(['error' => "Đã tồn tại gói tập"]);
                } elseif (count($check) == 0) {
                    if ($data["HinhThucThanhToan"] == 1) {
                        $new_Invoice = new Model_invoice_pack($data["IDGoiTap"], $cusID, $data["ThoiHan"]);
                        $result = $new_Invoice->add_Invoice();
                        if ($result) {
                            http_response_code(200);
                            echo json_encode(['message' => "Đăng ký thành công! Vui lòng tới chi nhánh gần nhất để thanh toán"]);
                        } else {
                            http_response_code(403);
                            echo json_encode(['error' => "Đăng ký không thành công!"]);
                        }
                    } elseif ($data["HinhThucThanhToan"] == 2) {
                        $new_Invoice = new Model_invoice_pack($data["IDGoiTap"], $cusID, $data["ThoiHan"]);
                        $result = $new_Invoice->add_Invoice();
                        if ($result) {
                            $payment = new Payment();
                            $link = "gympack";
                            $ExePayment = $payment->create($data["amount"], $result, $link);
                            if ($ExePayment) {
                                http_response_code(200);
                                echo json_encode(['success' => $ExePayment]);
                            } else {
                                http_response_code(403);
                                echo json_encode(['error' => 'Không thể thanh toán']);
                            }
                        } else {
                            http_response_code(403);
                            echo json_encode(['error' => "Đăng ký không thành công!"]);
                        }
                    }
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

    public static function control_get_PackByUser()
    {
        if ($_SERVER['REQUEST_METHOD'] === "GET") {
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ', '', $jwt));
            //Xác thực
            $Auth = new JWT();
            $verify = $Auth->JWT_verify($jwt);
            if ($verify) {
                $user = new model_auth();
                $username = $Auth->getUserName($jwt);
                $cusID = $user->getIDKhachhang($username);
                //Kiểm tra tồn tại gói tập của user chưa
                $invoice_pack = new Model_invoice_pack();
                $check = $invoice_pack->Exe_get_Pack_byKhachHang($cusID);
                if (count($check) == 1) {
                    $pack = new model_gympack();
                    $info = $pack->get_Info_Pack($check[0]["IDGoiTap"]);
                    if ($info) {
                        $check[0]["info"] = $info[0];
                        $check = $check[0];
                        http_response_code(200);
                        echo json_encode($check);
                    } else {
                        http_response_code(403);
                        echo json_encode(['error' => 'Không thể lấy thông tin']);
                    }

                } else {
                    http_response_code(403);
                    echo json_encode(['error' => 'Chưa đăng ký gói tập']);
                }
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Đường dẫn không tồn tại']);
        }
    }

    public static function control_Register_PackByEmployee()
    {
        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ', '', $jwt));
            $data = json_decode(file_get_contents('php://input'), true);
            //Xác thực
            $Auth = new JWT();
            $verify = $Auth->JWT_verify($jwt);
            if ($verify) {
                $user = new model_auth();
                $username = $user->getUserNamebyPhoneN($data["SDT"]);

                if ($username) {
                    $cusID = $user->getIDKhachhang($username);
                    if ($cusID) {
                        //Kiểm tra tồn tại gói tập của user chưa
                        $invoice_pack = new Model_invoice_pack();
                        $check = $invoice_pack->Exe_get_Pack_byKhachHang($cusID);
                        if (count($check) == 1) {
                            http_response_code(403);
                            echo json_encode(['error' => "Đã tồn tại gói tập"]);
                        } elseif (count($check) == 0) {
                            $new_Invoice = new Model_invoice_pack($data["IDGoiTap"], $cusID, $data["ThoiHan"], "Đã Thanh Toán");
                            $result = $new_Invoice->add_Invoice();
                            if ($result) {
                                http_response_code(200);
                                echo json_encode(['message' => "Đăng ký thành công!"]);
                            } else {
                                http_response_code(403);
                                echo json_encode(['error' => "Hãy kiểm tra kỹ số điện thoại !"]);
                            }
                        }
                    }

                } else {
                    http_response_code(403);
                    echo json_encode(['error' => "Hãy kiểm tra kỹ số điện thoại!"]);
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

    public function controll_update_gympack()
    {
        if ($_SERVER['REQUEST_METHOD'] === "PUT") {
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ', '', $jwt));
            $data = json_decode(file_get_contents('php://input'), true);
            //Xác thực
            $agent = "";
            if ($_SERVER['HTTP_USER_AGENT'] == "MOBILE_GOATFITNESS") {
                $agent = "MOBILE_GOATFITNESS";
            } else {
                $agent = "WEB";
            }
            $verify = $this->jwt->verifyJWT($jwt , $agent);
            if ($verify) {
                $pack = new model_gympack();
                $result = $pack->Update_Pack($data["Gia"], $data["IDGoiTap"]);
                if ($result) {
                    http_response_code(200);
                    echo json_encode(['success' => "Cập nhật thành công"]);
                } else {
                    http_response_code(403);
                    echo json_encode(['error' => 'Cập nhật không thành công']);
                }
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Đường dẫn không tồn tại']);
        }
    }

    public static function controll_add_gympack()
    {
        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ', '', $jwt));
            $data = json_decode(file_get_contents('php://input'), true);

            // Xác thực
            $Auth = new JWT();
            $verify = $Auth->JWT_verify($jwt);

            if ($verify) {
                // Kiểm tra dữ liệu đầu vào
                if (isset($data['TenGoiTap']) && isset($data['ThoiHan']) && isset($data['Gia'])) {
                    $gympack = new model_gympack();
                    $result = $gympack->add_Pack($data);

                    if ($result) {
                        http_response_code(200); // Created
                        echo json_encode(['success' => 'Gói tập đã được thêm thành công!']);
                    } else {
                        http_response_code(500); // Internal Server Error
                        echo json_encode(['error' => 'Không thể thêm gói tập.']);
                    }
                } else {
                    http_response_code(400); // Bad Request
                    echo json_encode(['error' => 'Dữ liệu đầu vào không hợp lệ.']);
                }
            } else {
                http_response_code(403); // Forbidden
                echo json_encode(['error' => 'Lỗi xác thực.']);
            }
        } else {
            http_response_code(404); // Not Found
            echo json_encode(['error' => 'Đường dẫn không tồn tại.']);
        }
    }

    public static function controll_delete_gympack()
    {
        if ($_SERVER['REQUEST_METHOD'] === "DELETE") {
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ', '', $jwt));

            // Xác thực
            $Auth = new JWT();
            $verify = $Auth->JWT_verify($jwt);

            if ($verify) {
                // Lấy ID gói tập từ request
                $data = json_decode(file_get_contents('php://input'), true);
                if (isset($data['IDGoiTap'])) {
                    $gympack = new model_gympack();
                    $result = $gympack->delete_Pack($data['IDGoiTap']);

                    if ($result) {
                        http_response_code(200); // OK
                        echo json_encode(['success' => 'Gói tập đã được xóa thành công!']);
                    } else {
                        http_response_code(500); // Internal Server Error
                        echo json_encode(['error' => 'Không thể xóa gói tập.']);
                    }
                } else {
                    http_response_code(400); // Bad Request
                    echo json_encode(['error' => 'ID gói tập không hợp lệ.']);
                }
            } else {
                http_response_code(403); // Forbidden
                echo json_encode(['error' => 'Lỗi xác thực.']);
            }
        } else {
            http_response_code(404); // Not Found
            echo json_encode(['error' => 'Đường dẫn không tồn tại.']);
        }
    }

}