<?php
    require_once(__DIR__ . "/../models/model_checkin.php");
    require_once(__DIR__ . "/../middlewares/JWT_Middleware.php");
    class controll_checkin{
        public static function get_statistical(){
            if($_SERVER['REQUEST_METHOD'] === "GET"){
                $jwt = trim(str_replace('Bearer ','', $_SERVER['HTTP_AUTHORIZATION']));
                $Auth =  new JWT;
                $verify = $Auth->JWT_verify($jwt);
                if($verify){
                    $checkin = new model_checkin();
                    $result = $checkin->statistical();
                    if($result){
                        http_response_code(200);
                        echo json_encode(['success'=> $result]);
                    }else{
                        http_response_code(403);
                        echo json_encode(['error'=> 'Không thực hiện được hành động']);
                    }
                }else{
                    http_response_code(403);
                    echo json_encode(['error'=> 'Lỗi xác thực']);
                }
            }
        }

    }