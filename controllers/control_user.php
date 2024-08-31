<?php

require_once(__DIR__ . '/../models/model_auth.php');
require_once(__DIR__ . '/../middlewares/JWT_Middleware.php');

// Thực hiện cài đặt, cập nhật tài khoản và đăng ký tài khoản
class UserController
{
    public static function getAccountInfo()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ', '', $jwt));
            //Xác thực
            $Auth = new JWT;
            $verify = $Auth->verifyJWT($jwt);
            if ($verify) {
                $username = $Auth->getUserName($jwt);
                $user = new model_auth();
                $dataUser = $user->AccountInfo($username);
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

    public static function Update_User()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ', '', $jwt));
            //Xác thực
            $Auth = new JWT;
            $verify = $Auth->verifyJWT($jwt);
            if ($verify) {
                $data = json_decode(file_get_contents("php://input"), true);
                $username = $Auth->getUserName($jwt);
                $user = new model_auth();
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
                $result = $user->updateUserInfo($update_data, $username);
                if ($result) {
                    http_response_code(200);
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

    public static function Update_Password()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ', '', $jwt));
            //Xác thực
            $Auth = new JWT;
            $verify = $Auth->verifyJWT($jwt);
            if ($verify) {
                $data = json_decode(file_get_contents("php://input"), true);
                $username = $Auth->getUserName($jwt);
                $user = new model_auth();
                if (isset($data['currentPW']) && isset($data['newPW']) && !empty($data['currentPW']) && !empty($data['newPW'])) {
                    $result = $user->updatePassword($data['currentPW'], $data['newPW'], $username);
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
                    echo json_encode(["message" => "Server không nhận được dữ liệu"]);
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

    public static function Update_Avt()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ', '', $jwt));
            //Xác thực
            $Auth = new JWT;
            $verify = $Auth->verifyJWT($jwt);
            if ($verify) {
                $data = json_decode(file_get_contents("php://input"), true);
                $username = $Auth->getUserName($jwt);
                $user = new model_auth();
                //Kiểm tra null

                $result = $user->updateUserAvt($data["newavt"], $username);
                if ($result) {
                    http_response_code(200);
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

    public static function signup()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);
            $user = new model_auth();
            $result = $user->Signup($data);
            if ($result) {
                http_response_code(200);
                echo json_encode(['success' => 'Đăng ký thành công']);
            } else {
                http_response_code(403);
                echo json_encode(['error' => 'Tên đăng nhập đã tồn tại']);
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
            $verify = $Auth->verifyJWT($jwt);
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
            $verify = $Auth->verifyJWT($jwt);
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