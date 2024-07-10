<?php
    require_once(__DIR__ . "/../models/model_products.php");
    require_once(__DIR__ . "/../models/model_PT.php");
    class Controll_HomeContent{
        public static function HomeContent(){
            if($_SERVER['REQUEST_METHOD'] === "GET"){
                $shop = new model_product();
                $pt = new model_pt();
                $result_product = $pt->get_Random_pt();
                $result_pt = $shop->get_Random_Products();
                if($result_product && $result_pt){
                    http_response_code(200);
                    echo json_encode([$result_product , $result_pt]);
                   }else{
                    http_response_code(404);
                    echo json_encode(['error' => 'Không thực hiện được yêu cầu']);
                   }
            }
        }
    }