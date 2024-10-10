<?php
require_once(__DIR__ . "/../models/model_PT.php");
require_once(__DIR__ . "/../models/model_invoice_pt.php");
class controll_PT{
    private $pt;

    public function __construct(){
        $this->pt = new model_pt();
    }
    public  function controll_getAll_PT() {
        if($_SERVER['REQUEST_METHOD']==="GET"){
            $result = $this->pt->get_All_pt();
            if($result){
                http_response_code(200);
                echo json_encode($result);
            }else{
                http_response_code(404);
                echo json_encode(['error'=> 'Không tìm thấy dữ liệu']);
            }
        }else{
            http_response_code(404);
            echo json_encode(['error'=> 'Đường dẫn không tồn tại']);
        }
    }

    public  function controll_getOne_personalTrainer(){
        if($_SERVER['REQUEST_METHOD'] === 'GET'){
            
            $ptID = $_GET['IDHLV'];
            $result = $this->pt->get_One_personalTrainer($ptID);
            if($result){
                http_response_code(200);
                echo json_encode($result);
            }else{
                http_response_code(403);
                echo json_encode(['error'=> 'Không truy cập được dữ liệu']);
            }
        }else{
            http_response_code(404);
            echo json_encode(['error'=> 'Đường dẫn không tồn tại']);
        }
    }

    public static function controll_Register_PT(){
        if($_SERVER['REQUEST_METHOD'] === "POST"){
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ','', $jwt));
            $data = json_decode(file_get_contents('php://input'),true);
            //Xác thực
            $Auth =  new JWT();
            $verify = $Auth->JWT_verify($jwt);
            if($verify){
                $user = new model_auth();
                $username = $Auth->getUserName($jwt);
                $customer = $user->getKhachHang($username);
                if($customer && $customer["IDHLV"]!=$data["IDHLV"]){
                    //Kiểm tra trùng lặp giờ
                    $checkTime = new model_invoice_pt();
                    if(count($checkTime->checkTime($data["StartDate"],$data["EndDate"]))==0){
                        if($data["HinhThucThanhToan"]==1){
                            $newInvoi = new model_invoice_pt(null ,$customer["IDKhachHang"],$data["IDHLV"] , $data["StartDate"] , $data["EndDate"]);
                            $exeAdd = $newInvoi->add_Invoice();
                            if($exeAdd){
                                http_response_code(200);
                                echo json_encode(['message'=>'Đăng ký thành công, Thanh toán sau khi tập!']);
                            }else{
                                http_response_code(403);
                                echo json_encode(['error'=>'Không thực hiện được hành động']);
                            }
                        }elseif($data["HinhThucThanhToan"]==2){
                            $newInvoi = new model_invoice_pt(null ,$customer["IDKhachHang"],$data["IDHLV"] , $data["StartDate"] , $data["EndDate"]);
                            $exeAdd = $newInvoi->add_Invoice();
                            if($exeAdd){
                                $payment = new  Payment();
                                $link = "pt";
                                $ExePayment = $payment->create($data["amount"] , $exeAdd , $link);
                                if($ExePayment){
                                    http_response_code(200);
                                    echo json_encode(['success' => $ExePayment]);
                                }else{
                                    http_response_code(403);
                                    echo json_encode(['error' => 'Không thể thanh toán']);
                                }
                            }
                        }
                    }else{
                        http_response_code(403);
                        echo json_encode(['error'=>'HLV này đã có lịch tại thời điểm bạn đăng ký']);
                        exit();
                    }
                }else{
                    http_response_code(403);
                    echo json_encode(['error'=>'Không thực hiện được hành động']);
                }
            }
        }else{
            http_response_code(404);
            echo json_encode(['error'=> 'Đường dẫn không tồn tại']);
        }
    }
}