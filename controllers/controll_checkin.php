<?php
    require_once(__DIR__ . "/../models/model_checkin.php");
    require_once(__DIR__ . "/../middlewares/JWT_Middleware.php");
    require_once(__DIR__ . '/control.php');
    require_once(__DIR__ . "/controll_orderProduct.php");
    class controll_checkin extends Control{
        private $model_checkin;
        public function __construct(){
            parent::__construct($_SERVER['HTTP_AUTHORIZATION'] ?? null);
            $this->model_checkin =  new model_checkin();
        }
        public  function get_statistical(){
            if($_SERVER['REQUEST_METHOD'] === "GET"){
                $jwt = trim(str_replace('Bearer ','', $_SERVER['HTTP_AUTHORIZATION']));
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
                    $result_order = $control_order -> get_statistical();
                    if($result_checkin && $result_order){
                        $response = [
                            'checkin' => $result_checkin,
                            'orders' => $result_order
                        ];
                        http_response_code(200);
                        echo json_encode( $response);
                        return;
                    }else{
                        http_response_code(403);
                        echo json_encode(['error'=> 'Không thực hiện được hành động']);
                        return;
                    }
                }else{
                    http_response_code(403);
                    echo json_encode(['error'=> 'Lỗi xác thực']);
                    return;
                }
            }else{
                http_response_code(404);
                echo json_encode(['error'=> 'Đường dẫn không tồn tại']);
                return;
            }
        }

    }