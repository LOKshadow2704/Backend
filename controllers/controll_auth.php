<?php

require_once(__DIR__ . '/../models/model_auth.php');
require_once(__DIR__ . '/../middlewares/JWT_Middleware.php');
class controll_auth{
    
    /**
     * @OA\Info(title="Goat Fitness API docs", version="0.1")
    */

    public static function controll_Login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $username = $data['username'];
            $password = $data['password'];
            $modelAuth = new model_auth();
            $result = $modelAuth->login($username, $password);
            $user = $modelAuth->AccountInfo($username);
            if ($result) {
                //Tạo token, cookie
                $jwt = new JWT();
                $csrf = $jwt -> generateCSRFToken();
                $payload =array(
                    'iss' => 'goatfitnessServer',                 // Issuer: người phát hành token
                    'aud' => 'goatfitnessClient',                 // Audience: đối tượng mà token hướng đến
                    'iat' => time(),                        // Issued At: thời điểm token được phát hành (Unix timestamp)
                    'exp' => time() + (10 * 60 * 60),        // Expiration Time: thời điểm token hết hạn (Unix timestamp) 2 giờ
                    'nbf' => time(),                        // Not Before: thời điểm token bắt đầu có hiệu lực (Unix timestamp)       
                    'jti' => $csrf,
                    'username'=> $username,
                    'role' => $user["IDVaiTro"]
                );
                $token = $jwt->JWT_key($payload); 
                setcookie("jwt", $token, time() + 36000,"/");
                setcookie("PHPSESSID",session_id(), time() + 36000,"/");
                if (!isset($_SESSION['csrf_token'])) {
                    $_SESSION['csrf_token'] = array();
                }
                if(isset($_SESSION["csrf_token"][$username])){
                    $_SESSION["csrf_token"][$username] = null;
                    unset($_SESSION["csrf_token"][$username]);
                }
                $_SESSION["csrf_token"][$username] = $csrf;
                $result["csrf"] = $_SESSION["csrf_token"][$username];
                $result[0]["TrangThai"] = "Online";
                $modelAuth->update_Status("Online" ,$username);
                // header('Content-Type: application/json');
                header("Authorization: Bearer $token");
                $response = [
                    'message'=> 'Đăng nhập thành công',
                    'user' => $result,
                ];
                echo json_encode($response);
            }else {
                http_response_code(400);
                echo json_encode(['error' => 'Kiểm tra lại thông tin']);
            }
        }else{
            http_response_code(404);
            echo json_encode(['error'=> 'Đường dẫn không tồn tại']);
        }


    }

    public static function controll_Logout(){
        if($_SERVER['REQUEST_METHOD']==='POST'){
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ','', $jwt));
            //Xác thực
            $Auth =  new JWT;
            $username = $Auth->getUserName($jwt);
            $verify = $Auth->JWT_verify($jwt);
            if($verify){
                setcookie("jwt", "", time() - 3600, "/");
                unset($_SESSION["csrf_token"][$username]);
                $modelAuth = new model_auth();
                $modelAuth->update_Status("Offline",$username);
                http_response_code(200);
                echo json_encode(['message'=> 'Đăng xuất thành công']);
            }else{
                http_response_code(403);
                echo json_encode(['error'=> 'Lỗi xác thực 2']);
            }
        }else{
            http_response_code(404);
            echo json_encode(['error'=> 'Đường dẫn không tồn tại']);
        }
    }

    public static function controll_getAccountInfo(){
        if($_SERVER['REQUEST_METHOD']==='GET'){
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ','', $jwt));
            //Xác thực
            $Auth =  new JWT;
            $verify = $Auth->JWT_verify($jwt);
            if($verify){
                $username = $Auth->getUserName($jwt);
                $user = new model_auth();
                $dataUser = $user->AccountInfo($username);
                if($dataUser){
                    http_response_code(200);
                    echo json_encode($dataUser);
                }
            }else{ 
                http_response_code(403);
                echo json_encode(['error'=> 'Lỗi xác thực']);
            }
        }else{
            http_response_code(404);
            echo json_encode(['error'=> 'Đường dẫn không tồn tại']);
        }
    }

    public static function controll_Update_User(){
        if($_SERVER['REQUEST_METHOD']==='PUT'){
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ','', $jwt));
            //Xác thực
            $Auth =  new JWT;
            $verify = $Auth->JWT_verify($jwt);
            if($verify){
                $data = json_decode(file_get_contents("php://input"), true);
                $username = $Auth->getUserName($jwt);
                $user = new model_auth();
                //Kiểm tra null
                $update_data = array();
                if(isset($data['name']) && !empty($data['name'])) {
                    $update_data['HoTen'] = $data['name'];
                }
                
                if(isset($data['email']) && !empty($data['email'])) {
                    $update_data['Email'] = $data['email'];
                }
                
                if(isset($data['address']) && !empty($data['address'])) {
                    $update_data['DiaChi'] = $data['address'];
                }
                
                if(isset($data['phoneNum']) && !empty($data['phoneNum'])) {
                    $update_data['SDT'] = $data['phoneNum'];
                }
                $result = $user->updateUserInfo($update_data, $username);
                if($result ){
                    http_response_code(200);
                }else{
                    http_response_code(400);
                }
            }else{ 
                http_response_code(403);
                echo json_encode(['error'=> 'Lỗi xác thực']);
            }
        }else{
            http_response_code(404);
            echo json_encode(['error'=> 'Đường dẫn không tồn tại']);
        }
    }

    public static function controll_Update_Password(){
        if($_SERVER['REQUEST_METHOD']==='PUT'){
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ','', $jwt));
            //Xác thực
            $Auth =  new JWT;
            $verify = $Auth->JWT_verify($jwt);
            if($verify){
                $data = json_decode(file_get_contents("php://input"), true);
                $username = $Auth->getUserName($jwt);
                $user = new model_auth();
                if(isset($data['currentPW']) && isset($data['newPW']) && !empty($data['currentPW'])&& !empty($data['newPW'])){
                    $result = $user->updatePassword($data['currentPW'], $data['newPW'],$username);
                    switch($result){
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
                }else{
                    http_response_code(400);
                    echo json_encode(["message" => "Server không nhận được dữ liệu"]);
                }
            }else{ 
                http_response_code(403);
                echo json_encode(['error'=> 'Lỗi xác thực']);
            }
        }else{
            http_response_code(404);
            echo json_encode(['error'=> 'Đường dẫn không tồn tại']);
        }
    }

    public static function controll_Update_Avt(){
        if($_SERVER['REQUEST_METHOD']==='PUT'){
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ','', $jwt));
            //Xác thực
            $Auth =  new JWT;
            $verify = $Auth->JWT_verify($jwt);
            if($verify){
                $data = json_decode(file_get_contents("php://input"), true);
                $username = $Auth->getUserName($jwt);
                $user = new model_auth();
                //Kiểm tra null
                
                $result = $user->updateUserAvt($data["newavt"], $username);
                if($result ){
                    http_response_code(200);
                }else{
                    http_response_code(400);
                }
            }else{ 
                http_response_code(403);
                echo json_encode(['error'=> 'Lỗi xác thực']);
            }
        }else{
            http_response_code(404);
            echo json_encode(['error'=> 'Đường dẫn không tồn tại']);
        }
    }

    public static function controll_Sigup(){
        if($_SERVER['REQUEST_METHOD']==='POST'){
            $data = json_decode(file_get_contents("php://input"), true);
            $user = new model_auth();
            $result = $user->Signup($data);
            if($result){
                http_response_code(200);
                echo json_encode(['success'=> 'Đăng ký thành công']);
            }else{
                http_response_code(403);
                echo json_encode(['error'=> 'Tên đăng nhập đã tồn tại']);
            }
        }else{
            http_response_code(404);
            echo json_encode(['error'=> 'Đường dẫn không tồn tại']);
        }
    }

    public static function get_user_training(){
        if($_SERVER['REQUEST_METHOD']==='GET'){
            $user = new model_auth();
            $result = $user->user_training();
            if($result){
                http_response_code(200);
                echo json_encode(['success'=> $result]);
            }else{
                http_response_code(200);
                echo json_encode(['warning'=> 'Chưa có người tập hôm nay']);
            }
        }else{
            http_response_code(404);
            echo json_encode(['error'=> 'Đường dẫn không tồn tại']);
        }
    }

    public static function get_Employee_Working(){
        if($_SERVER['REQUEST_METHOD']==='GET'){
            $user = new model_auth();
            $result = $user->Employee_Working();
            if($result){
                http_response_code(200);
                echo json_encode(['success'=> $result]);
            }else{
                http_response_code(200);
                echo json_encode(['warning'=> 'Không thực hiện được hành động']);
            }
        }else{
            http_response_code(404);
            echo json_encode(['error'=> 'Đường dẫn không tồn tại']);
        }
    }

    public static function get_Account(){
        if($_SERVER['REQUEST_METHOD']==='GET'){
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ','', $jwt));
            //Xác thực
            $Auth =  new JWT;
            $verify = $Auth->JWT_verify($jwt);
            if($verify){
                $user = new model_auth();
                $result = $user->All_Account();
                if($result ){
                    http_response_code(200);
                    echo json_encode($result);
                }else{
                    http_response_code(400);
                }
            }else{ 
                http_response_code(403);
                echo json_encode(['error'=> 'Lỗi xác thực']);
            }
        }else{
            http_response_code(404);
            echo json_encode(['error'=> 'Đường dẫn không tồn tại']);
        }
    }

    public static function Update_Account_ByAdmin(){
        if($_SERVER['REQUEST_METHOD']==='PUT'){
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ','', $jwt));
            $data = json_decode(file_get_contents("php://input"), true);
            //Xác thực
            $Auth =  new JWT;
            $verify = $Auth->JWT_verify($jwt);
            if($verify){
                $user = new model_auth();
                $result = $user->Admin_Update_Account($data);
                if($result){
                    http_response_code(200);
                    echo json_encode($result);
                }else{
                    http_response_code(403);
                }
            }else{ 
                http_response_code(403);
                echo json_encode(['error'=> 'Lỗi xác thực']);
            }
        }else{
            http_response_code(404);
            echo json_encode(['error'=> 'Đường dẫn không tồn tại']);
        }
    }

}