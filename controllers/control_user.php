<?php
require_once(__DIR__ . '/control.php');
// Thực hiện cài đặt, cập nhật tài khoản và đăng ký tài khoản
class UserController extends Control
{
    public function getAccountInfo()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ', '', $jwt));
            //Xác thực
            $agent = "";
            if ($_SERVER['HTTP_USER_AGENT'] == "MOBILE_GOATFITNESS") {
                $agent = "MOBILE_GOATFITNESS";
            } else {
                $agent = "WEB";
            }
            $verify = $this->jwt->verifyJWT($jwt, $agent);
            if ($verify) {
                $username = $this->jwt->getUserName($jwt);
                $dataUser = $this->modelAuth->AccountInfo($username);
                if ($dataUser) {
                    http_response_code(200);
                    echo json_encode($dataUser);
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

    public function Update_User()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ', '', $jwt));
            //Xác thực
            $agent = "";
            if ($_SERVER['HTTP_USER_AGENT'] == "MOBILE_GOATFITNESS") {
                $agent = "MOBILE_GOATFITNESS";
            } else {
                $agent = "WEB";
            }
            $verify = $this->jwt->verifyJWT($jwt, $agent);
            if ($verify) {
                $data = json_decode(file_get_contents("php://input"), true);
                $username = $this->jwt->getUserName($jwt);
                //Kiểm tra null
                $update_data = array();
                if (isset($data['name']) && !empty($data['name'])) {
                    $update_data['HoTen'] = $data['name'];
                }

                if (isset($data['email']) && !empty($data['email'])) {
                    $update_data['Email'] = $data['email'];
                }

                if (isset($data['address']) && !empty($data['address'])) {
                    $update_data['DiaChi'] = $data['address'];
                }

                if (isset($data['phoneNum']) && !empty($data['phoneNum'])) {
                    $update_data['SDT'] = $data['phoneNum'];
                }
                $result = $this->modelAuth->UpdateUserInfo($update_data, $username);
                if ($result) {
                    http_response_code(200);
                    echo json_encode(['success' => 'Update thành công']);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Update thất bại']);
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

    public function Update_Password()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
                if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
                    http_response_code(401);
                    echo json_encode(['error' => 'Không tìm thấy token xác thực']);
                    return;
                }
                $jwt = $_SERVER['HTTP_AUTHORIZATION'];
                $jwt = trim(str_replace('Bearer ', '', $jwt));
                $agent = ($_SERVER['HTTP_USER_AGENT'] === "MOBILE_GOATFITNESS") ? "MOBILE_GOATFITNESS" : "WEB";
                $verify = $this->jwt->verifyJWT($jwt, $agent);
                if ($verify) {
                    $data = json_decode(file_get_contents("php://input"), true);
                    if (isset($data['currentPW']) && isset($data['newPW']) && !empty($data['currentPW']) && !empty($data['newPW'])) {
                        $username = $this->jwt->getUserName($jwt);
                        $result = $this->modelAuth->UpdatePassword($data['currentPW'], $data['newPW'], $username);
                        switch ($result) {
                            case "Mật khẩu hiện tại không khớp":
                                http_response_code(400);
                                echo json_encode(["message" => "Mật khẩu hiện tại không khớp"]);
                                break;
                            case "Đổi mật khẩu không thành công":
                                http_response_code(500);
                                echo json_encode(["message" => "Đổi mật khẩu không thành công"]);
                                break;
                            case "Đổi mật khẩu thành công":
                                http_response_code(200);
                                echo json_encode(["message" => "Đổi mật khẩu thành công"]);
                                break;
                            default:
                                http_response_code(500);
                                echo json_encode(["error" => "Lỗi không xác định"]);
                                break;
                        }
                    } else {
                        http_response_code(400);
                        echo json_encode(["message" => "Thiếu dữ liệu: currentPW hoặc newPW"]);
                    }
                } else {

                    http_response_code(403);
                    echo json_encode(['error' => 'Lỗi xác thực JWT']);
                }
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Đường dẫn không tồn tại']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Lỗi hệ thống: ' . $e->getMessage()]);
        }
    }



    public function Update_Avt()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ', '', $jwt));
            //Xác thực
            $agent = ($_SERVER['HTTP_USER_AGENT'] === "MOBILE_GOATFITNESS") ? "MOBILE_GOATFITNESS" : "WEB";
            $verify = $this->jwt->verifyJWT($jwt, $agent);
            if ($verify) {
                $data = json_decode(file_get_contents("php://input"), true);
                $username = $this->jwt->getUserName($jwt);
                $result = $this->modelAuth->updateUserAvt($data["newavt"], $username);
                if ($result) {
                    http_response_code(200);
                    echo json_encode(['success' => 'Cập nhật thành công']);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Cập nhật không thành công']);
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

    public function signup()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = json_decode(file_get_contents("php://input"), true);
                $result = $this->modelAuth->Signup($data);
                if ($result) {
                    http_response_code(200);
                    echo json_encode(['success' => 'Đăng ký thành công']);
                }
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode(['error' => $e->getMessage()]);
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Đường dẫn không tồn tại']);
        }
    }


    public static function get_user_training()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $user = new model_auth();
            $result = $user->user_training();
            if ($result) {
                http_response_code(200);
                echo json_encode(['success' => $result]);
            } else {
                http_response_code(200);
                echo json_encode(['warning' => 'Chưa có người tập hôm nay']);
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Đường dẫn không tồn tại']);
        }
    }

    public static function get_Employee_Working()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $user = new model_auth();
            $result = $user->Employee_Working();
            if ($result) {
                http_response_code(200);
                echo json_encode(['success' => $result]);
            } else {
                http_response_code(200);
                echo json_encode(['warning' => 'Không thực hiện được hành động']);
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Đường dẫn không tồn tại']);
        }
    }

    public static function get_Account()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ', '', $jwt));
            //Xác thực
            $Auth = new JWT;
            $verify = $Auth->verifyJWT($jwt, $_SERVER['HTTP_USER_AGENT']);
            if ($verify) {
                $user = new model_auth();
                $result = $user->All_Account();
                if ($result) {
                    http_response_code(200);
                    echo json_encode($result);
                } else {
                    http_response_code(400);
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

    public static function Update_Account_ByAdmin()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ', '', $jwt));
            $data = json_decode(file_get_contents("php://input"), true);
            //Xác thực
            $Auth = new JWT;
            $verify = $Auth->verifyJWT($jwt, $_SERVER['HTTP_USER_AGENT']);
            if ($verify) {
                $user = new model_auth();
                $result = $user->Admin_Update_Account($data);
                if ($result) {
                    http_response_code(200);
                    echo json_encode($result);
                } else {
                    http_response_code(403);
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