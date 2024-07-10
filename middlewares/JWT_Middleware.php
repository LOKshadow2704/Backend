<?php
if (session_status() == PHP_SESSION_NONE) {
    // Kiểm tra xem HTTP_PHPSESSID có được cung cấp không
    if (isset($_SERVER["HTTP_PHPSESSID"])) {
        session_id($_SERVER["HTTP_PHPSESSID"]);
    }
    // Bắt đầu phiên
    session_start();
} else {
    // Phiên đã bắt đầu, kiểm tra nếu session_id không khớp
    if (isset($_SERVER["HTTP_PHPSESSID"]) && session_id() !== $_SERVER["HTTP_PHPSESSID"]) {
        session_write_close(); 
        session_id($_SERVER["HTTP_PHPSESSID"]); 
        session_start(); 
    }
}
    class JWT{
        private function ExEgenerateCSRFToken() {
            $token = bin2hex(random_bytes(32)); // Sử 
            return $token;
        }
        private function generateJWT($payload){
            $header = json_encode(['typ'=>'JWT','alg'=>'HS256']);
            $encode_Header_base64Url = str_replace(['+', '/', '='], ['-', '_', ''],base64_encode($header));
            $encode_Username_base64Url = str_replace(['+', '/', '='], ['-', '_', ''],base64_encode(json_encode($payload)));
            //Tạo chữ ký!!!!!!!
            $signature = hash_hmac('sha256',"$encode_Header_base64Url.$encode_Username_base64Url",'20042101nguyenthanhloc',true);
            $endcode_Signature_base64Url = str_replace(['+', '/', '='], ['-', '_', ''],base64_encode($signature));
            return "$encode_Header_base64Url.$encode_Username_base64Url.$endcode_Signature_base64Url";
        } 

        private function verifyJWT($jwt) {
            list($header64, $payload64, $signature64) = explode('.', $jwt);
            $header = base64_decode($header64);
            $payload = base64_decode($payload64);
            $username = $this->Username($jwt);
            $csrf = $this->get_CSRF($jwt);
            //Header
            $encode_Header_base64Url = str_replace(['+', '/', '='], ['-', '_', ''],base64_encode($header));
            //Payload
            $encode_Username_base64Url = str_replace(['+', '/', '='], ['-', '_', ''],base64_encode($payload));
            //Tạo chữ ký từ header và payload nhận được
            $expectedSignature = hash_hmac('sha256',"$encode_Header_base64Url.$encode_Username_base64Url",'20042101nguyenthanhloc',true);
            //Get chữ ký để so sánh
            $endcode_Signature_base64Url = str_replace(['+', '/', '='], ['-', '_', ''],base64_encode($expectedSignature));
            if (!hash_equals($signature64, $endcode_Signature_base64Url)) {
                return  false; // Chữ ký không hợp lệ
            }
            if ($csrf !== $_SESSION["csrf_token"][$username] ) {
                echo $csrf ."|". $_SESSION["csrf_token"][$username];
                return false;
            }// CSRF token không khớp
            return true ;//hash_equals($signature, $expectedSignature);
        }


        private function get_CSRF($jwt){
            list($header64, $payload64, $signature64) = explode('.', $jwt);
            $payload = base64_decode($payload64);
            $payloadArray = json_decode($payload, true); 
            return $payloadArray['jti'];
        }

        private function Username($jwt){
            list($header64, $payload64, $signature64) = explode('.', $jwt);
            $payload = base64_decode($payload64);
            $payloadArray = json_decode($payload, true); 
            return $payloadArray['username'];
        }

        public function JWT_key($payload){
            return $this->generateJWT($payload);
        }

        public function JWT_verify($jwt ){
            return $this->verifyJWT($jwt);
            
        }

        public function getUserName($jwt){
            return $this->Username($jwt);
        }

        public function generateCSRFToken(){
            return $this->ExEgenerateCSRFToken();
        }

    }