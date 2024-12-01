<?php
require_once(__DIR__ . '/control.php');
// Thực hiện cài đặt, cập nhật tài khoản và đăng ký tài khoản
class UserController extends Control
{
    public function __construct()
    {
        parent::__construct($_SERVER['HTTP_AUTHORIZATION'] ?? null);
    }
    public function getAccountInfo()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $auth = $this->authenticate_user();
            if ($auth) {
                $username = $this->jwt->getUserName();
                $dataUser = $this->modelAuth->AccountInfo($username);
                if ($dataUser) {
                    $this->sendResponse(200, $dataUser);
                } else {
                    $this->sendResponse(404, ['error' => 'Không tìm thấy thông tin người dùng']);
                }
            } else {
                $this->sendResponse(403, ['error' => 'Lỗi xác thực']);
            }
        } else {
            $this->sendResponse(404, ['error' => 'Đường dẫn không tồn tại']);
        }
    }

    public function Update_User()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $auth = $this->authenticate_user();
            if ($auth) {
                $data = json_decode(file_get_contents("php://input"), true);
                $username = $this->jwt->getUserName();
                $update_data = array();

                if (isset($data['HoTen']) && !empty($data['HoTen'])) {
                    $update_data['HoTen'] = $data['HoTen'];
                }

                if (isset($data['Email']) && !empty($data['Email'])) {
                    $update_data['Email'] = $data['Email'];
                }

                if (isset($data['DiaChi']) && !empty($data['DiaChi'])) {
                    $update_data['DiaChi'] = $data['DiaChi'];
                }

                if (isset($data['SDT']) && !empty($data['SDT'])) {
                    $update_data['SDT'] = $data['SDT'];
                }

                $result = $this->modelAuth->UpdateUserInfo($update_data, $username);
                if ($result) {
                    $this->sendResponse(200, ['success' => 'Cập nhật thành công']);
                } else {
                    $this->sendResponse(500, ['error' => 'Cập nhật thất bại']);
                }
            } else {
                $this->sendResponse(403, ['error' => 'Lỗi xác thực']);
            }
        } else {
            $this->sendResponse(404, ['error' => 'Đường dẫn không tồn tại']);
        }
    }

    public function Update_Password()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
                $auth = $this->authenticate_user();
                if ($auth) {
                    $data = json_decode(file_get_contents("php://input"), true);
                    if (isset($data['currentPW']) && isset($data['newPW']) && !empty($data['currentPW']) && !empty($data['newPW'])) {
                        $username = $this->jwt->getUserName();
                        $result = $this->modelAuth->UpdatePassword($data['currentPW'], $data['newPW'], $username);
                        switch ($result) {
                            case "Mật khẩu hiện tại không khớp":
                                $this->sendResponse(400, ["message" => "Mật khẩu hiện tại không khớp"]);
                                break;
                            case "Đổi mật khẩu không thành công":
                                $this->sendResponse(500, ["message" => "Đổi mật khẩu không thành công"]);
                                break;
                            case "Đổi mật khẩu thành công":
                                $this->sendResponse(200, ["message" => "Đổi mật khẩu thành công"]);
                                break;
                            default:
                                $this->sendResponse(500, ["error" => "Lỗi không xác định"]);
                                break;
                        }
                    } else {
                        $this->sendResponse(400, ["message" => "Thiếu dữ liệu: currentPW hoặc newPW"]);
                    }
                } else {
                    $this->sendResponse(403, ['error' => 'Lỗi xác thực JWT']);
                }
            } else {
                $this->sendResponse(404, ['error' => 'Đường dẫn không tồn tại']);
            }
        } catch (PDOException $e) {
            $this->sendResponse(500, ['error' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
        } catch (Exception $e) {
            $this->sendResponse(500, ['error' => 'Lỗi hệ thống: ' . $e->getMessage()]);
        }
    }

    public function Update_Avt()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $auth = $this->authenticate_user();
            if ($auth) {
                $data = json_decode(file_get_contents("php://input"), true);
                $username = $this->jwt->getUserName();
                $result = $this->modelAuth->updateUserAvt($data["newavt"], $username);
                if ($result) {
                    $this->sendResponse(200, ['success' => 'Cập nhật thành công']);
                } else {
                    $this->sendResponse(500, ['error' => 'Cập nhật không thành công']);
                }
            } else {
                $this->sendResponse(403, ['error' => 'Lỗi xác thực']);
            }
        } else {
            $this->sendResponse(404, ['error' => 'Đường dẫn không tồn tại']);
        }
    }

    public function signup()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = json_decode(file_get_contents("php://input"), true);
                $result = $this->modelAuth->Signup($data);
                if ($result) {
                    $this->sendResponse(200, ['success' => 'Đăng ký thành công']);
                }
            } catch (Exception $e) {
                $this->sendResponse(400, ['error' => $e->getMessage()]);
            }
        } else {
            $this->sendResponse(404, ['error' => 'Đường dẫn không tồn tại']);
        }
    }


    public function get_user_training()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ', '', $jwt));
            // Xác thực
            $agent = ($_SERVER['HTTP_USER_AGENT'] == "MOBILE_GOATFITNESS") ? "MOBILE_GOATFITNESS" : "WEB";
            $verify = $this->jwt->verifyJWT($jwt, $agent);

            if (!$verify) {
                $this->sendResponse(401, ['error' => 'Xác thực không thành công']);
                return;
            }
            $role = $this->jwt->getRole();
            if ($role == "1" || $role == "2") {
                $user = new model_auth();
                $result = $user->user_training();
                if ($result) {
                    $this->sendResponse(200, ['success' => $result]);
                } else {
                    $this->sendResponse(200, ['warning' => 'Chưa có người tập hôm nay']);
                }
            } else {
                $this->sendResponse(401, ['error' => 'Không có quyền truy cập']);
            }
        } else {
            $this->sendResponse(404, ['error' => 'Đường dẫn không tồn tại']);
        }
    }

    public function get_Employee_Working()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $user = new model_auth();
            $result = $user->Employee_Working();
            if ($result) {
                $this->sendResponse(200, ['success' => $result]);
            } else {
                $this->sendResponse(200, ['warning' => 'Không thực hiện được hành động']);
            }
        } else {
            $this->sendResponse(404, ['error' => 'Đường dẫn không tồn tại']);
        }
    }

    public function get_Account()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $auth = $this->authenticate_admin();
            if ($auth) {
                $user = new model_auth();
                $result = $user->admin_get_account();
                if ($result) {
                    $this->sendResponse(200, $result);
                } else {
                    $this->sendResponse(400, ['error' => 'Không có tài khoản']);
                }
            } else {
                $this->sendResponse(403, ['error' => 'Lỗi xác thực']);
            }
        } else {
            $this->sendResponse(404, ['error' => 'Đường dẫn không tồn tại']);
        }
    }

    public function update_Role()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $data = json_decode(file_get_contents("php://input"), true);
            $auth = $this->authenticate_admin();
            if ($auth) {
                $user = new model_auth();
                if ($data["IDVaiTro"] == 1) {
                    $this->sendResponse(403, ['error' => 'Bạn không có quyền này!']);
                    return;
                }
                $result = $user->Admin_Update_Role($data["IDVaiTro"], $data["TenDangNhap"]);
                if ($result) {
                    $this->sendResponse(200, ['success' => 'Cập nhật quyền thành công']);
                    return;
                } else {
                    $this->sendResponse(500, ['error' => 'Lỗi hệ thống']);
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

    public function gympack_customer()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $auth = $this->authenticate_employee();
            if ($auth) {
                $result = $this->modelAuth->get_gympack_customer();
                if ($result) {
                    $this->sendResponse(200, $result);
                    return;
                } else {
                    $this->sendResponse(403, ['error' => 'Lỗi hệ thống']);
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

    public function admin_create_user()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);
            $auth = $this->authenticate_admin();
            if ($auth) {
                if ($data["role_id"] == 1) {
                    $this->sendResponse(400, ['error' => 'Không thể thực hiện yêu cầu']);
                    return;
                }
                // Kiểm tra các trường cần thiết
                $required_fields = ["username", "password", "fullname", "email", "phone"];
                foreach ($required_fields as $field) {
                    if (empty($data[$field])) {
                        $this->sendResponse(400, ['error' => "Trường {$field} không được để trống."]);
                        return;
                    }
                }

                // Kiểm tra định dạng email
                if (!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
                    $this->sendResponse(400, ['error' => 'Định dạng email không hợp lệ.']);
                    return;
                }

                // Kiểm tra độ dài mật khẩu
                if (strlen($data["password"]) < 8) {
                    $this->sendResponse(400, ['error' => 'Mật khẩu phải có ít nhất 8 ký tự.']);
                    return;
                }

                try {
                    // Gọi phương thức admin_add_user và bắt lỗi nếu có
                    $result = $this->modelAuth->admin_add_user($data);
                    if ($result) {
                        $this->sendResponse(200, ['success' => 'Tạo tài khoản thành công']);
                        return;
                    } else {
                        $this->sendResponse(403, ['error' => 'Lỗi hệ thống']);
                        return;
                    }
                } catch (Exception $e) {
                    // Bắt lỗi và trả về phản hồi
                    $this->sendResponse(400, ['error' => $e->getMessage()]);
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

    public function admin_delete_account()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            if ($this->authenticate_admin()) {
                $user_delete = $_GET["un"];
                $result = $this->modelAuth->delete_account($user_delete);
                if ($result) {
                    $this->sendResponse(200, ['success' => 'Xóa thành công']);
                    return;
                } else {
                    $this->sendResponse(500, ['error' => 'Lỗi hệ thống']);
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

    public function admin_get_employee()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if ($this->authenticate_admin()) {
                $result = $this->modelAuth->manager_employee();
                if ($result) {
                    $this->sendResponse(200, $result);
                    return;
                } else {
                    $this->sendResponse(500, ['error' => 'Lỗi hệ thống']);
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
}
?>