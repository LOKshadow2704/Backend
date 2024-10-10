<?php
// if (session_status() == PHP_SESSION_NONE) {
//     // Kiểm tra xem HTTP_PHPSESSID có được cung cấp không
//     if (isset($_SERVER["HTTP_PHPSESSID"]) && !empty($_SERVER["HTTP_PHPSESSID"])) {
//         session_id($_SERVER["HTTP_PHPSESSID"]); // Thiết lập session ID từ HTTP_PHPSESSID
//     } else {
//         // Nếu HTTP_PHPSESSID không được cung cấp hoặc là null, khởi tạo session với ID mới
//         session_start(); // Bắt đầu session mới với ID mới
//     }
//     // Bắt đầu phiên
//     session_start();
// } else {
//     // Phiên đã bắt đầu, kiểm tra nếu session_id không khớp
//     if (isset($_SERVER["HTTP_PHPSESSID"]) && session_id() !== $_SERVER["HTTP_PHPSESSID"]) {
//         session_write_close();
//         session_id($_SERVER["HTTP_PHPSESSID"]);
//         session_start();
//     }
// }

require_once(__DIR__.'/../models/model_rt.php');
class JWT
{
    private $model_rt;
    private $jwt;

    public function __construct($accessToken = null){
        $this->model_rt = new RefreshTokenModel();
        $this->jwt = $accessToken;
    }

    public function generateJWT(array $payload , $agent): string //Access Token JWT
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $encodedHeader = $this->base64UrlEncode($header);
        $encodedPayload = $this->base64UrlEncode(json_encode($payload));
        $signature = hash_hmac('sha256', "$encodedHeader.$encodedPayload", $_SESSION["csrf_token"][$payload['username']]["$agent"], true);
        $encodedSignature = $this->base64UrlEncode($signature);
        return "$encodedHeader.$encodedPayload.$encodedSignature";
    }

    public function verifyJWT(string $jwt , $agent): bool
    {
        list($encodedHeader, $encodedPayload, $encodedSignature) = explode('.', $jwt);
        $header = base64_decode($encodedHeader);
        $payload = json_decode(base64_decode($encodedPayload) ,true);
        $expectedSignature = $this->base64UrlEncode(hash_hmac('sha256', "$encodedHeader.$encodedPayload", $_SESSION["csrf_token"][$payload['username']]["$agent"], true));
        if (!hash_equals($encodedSignature, $expectedSignature)) {
            return false;
        }
        return true;
    }

    public function getRole(){
        list($encodedHeader, $encodedPayload) = explode('.', $this->jwt);
        $payload = base64_decode($encodedPayload);
        $payloadArray = json_decode($payload, true);
        return $payloadArray['role'] ?? null;
    }

    public function createRefreshToken(string $username , $agent): ?string
    {
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $randomString = bin2hex(random_bytes(32));
        $refreshToken = hash('sha256', $randomString);
        $newExpiryDate = date('Y-m-d H:i:s', time() + 3600 * 24 * 30);
        $this->model_rt->saveToken($username, $refreshToken, $newExpiryDate , $agent);
        return $refreshToken;
    }


    public function VerifiRefreshToken($refreshToken, $username)
    {

    }

    public function getUsername(string $jwt): ?string
    {
        list($encodedHeader, $encodedPayload) = explode('.', $jwt);
        $payload = base64_decode($encodedPayload);
        $payloadArray = json_decode($payload, true);
        return $payloadArray['username'] ?? null;
    }

    public function generateCSRFToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    protected function base64UrlEncode(string $data): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    protected function validateCSRFToken(?string $username, ?string $csrfToken): bool
    {
        return isset($username) && isset($csrfToken) && isset($_SESSION["csrf_token"][$username]) && $csrfToken === $_SESSION["csrf_token"][$username];
    }

    

}