<?php
require_once(__DIR__ . "/../models/model_checkin.php");
require_once(__DIR__ . "/../middlewares/JWT_Middleware.php");
require_once(__DIR__ . '/control.php');
require_once(__DIR__ . "/controll_orderProduct.php");
class controll_checkin extends Control
{
    private $model_checkin;
    public function __construct()
    {
        parent::__construct($_SERVER['HTTP_AUTHORIZATION'] ?? null);
        $this->model_checkin = new model_checkin();
    }
    public function get_statistical()
    {
        if ($_SERVER['REQUEST_METHOD'] === "GET") {
            $jwt = trim(str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']));
            $agent = "";
            if ($_SERVER['HTTP_USER_AGENT'] == "MOBILE_GOATFITNESS") {
                $agent = "MOBILE_GOATFITNESS";
            } else {
                $agent = "WEB";
            }
            $verify = $this->jwt->verifyJWT($jwt, $agent);
            $role = $this->jwt->getRole();
            if ($verify && $role == 2) {
                $result_checkin = $this->model_checkin->statistical();
                $control_order = new controll_Order();
                $result_order = $control_order->get_statistical();
                if ($result_checkin && $result_order) {
                    $response = [
                        'checkin' => $result_checkin,
                        'orders' => $result_order
                    ];
                    http_response_code(200);
                    echo json_encode($response);
                    return;
                } else {
                    http_response_code(403);
                    echo json_encode(['error' => 'Không thực hiện được hành động']);
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

    public function checkin()
    {
        if ($_SERVER['REQUEST_METHOD'] === "GET") {
            $auth = $this->authenticate_employee();
            $data = json_decode(file_get_contents('php://input'), true);
            $dateTime = new DateTime();
            $checkin_time = $dateTime->format('Y-m-d H:i:s');
            if ($auth) {
                if (!$this->modelAuth->check_devices($data["id_device"], $data["username"])) {
                    $this->sendResponse(400, ['error' => 'Phát hiện gian lận']);
                    return;
                }
                if ($this->model_checkin->employee_checkin($data["username"], $checkin_time)) {
                    $this->sendResponse(200, ['success' => 'Check-in thành công']);
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
            http_response_code(404);
            echo json_encode(['error' => 'Đường dẫn không tồn tại']);
            return;
        }
    }

}