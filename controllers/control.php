<?php
require_once(__DIR__ . '/../models/model_auth.php');
require_once(__DIR__ . '/../middlewares/JWT_Middleware.php');
class Control
{
    protected $modelAuth;
    protected $jwt;

    public function __construct($accessToken = null)
    {
        $this->modelAuth = new model_auth();
        $this->jwt = new JWT($accessToken);
    }
    protected function createSession($username)
    {
        $newRefreshToken = $this->jwt->createRefreshToken($username, "WEB");
        $csrf = $this->jwt->generateCSRFToken();
        if (!isset($_SESSION["csrf_token"])) {
            $_SESSION["csrf_token"] = [];
        }
        if (!isset($_SESSION["csrf_token"][$username]) || !is_array($_SESSION["csrf_token"][$username])) {
            $_SESSION["csrf_token"][$username] = []; // Khởi tạo mảng cho từng username
        }
        $_SESSION["csrf_token"][$username]["WEB"] = $csrf;
        $payload = $this->createPayload($username);
        $token = $this->jwt->generateJWT($payload, "WEB");
        // setcookie("jwt", $token, time() + 36000, "/");
        // setcookie("refresh_token", $newRefreshToken, time() + 86400 * 30, "/");
        // setcookie("PHPSESSID", session_id(), time() + 36000, "/");
    }

    protected function clearSession($username)
    {
        setcookie("jwt", "", time() - 3600, "/");
        setcookie("refresh_token", "", time() - 3600, "/");
        unset($_SESSION["csrf_token"][$username]);
        $this->modelAuth->update_Status("Offline", $username);
    }

    protected function createPayload($username)
    {
        $user = $this->modelAuth->AccountInfo($username);
        return [
            'iss' => 'goatfitnessServer',
            'aud' => 'goatfitnessClient',
            'iat' => time(),
            'exp' => time() + (10 * 60 * 60),
            'nbf' => time(),
            'username' => $username,
            'role' => $user["IDVaiTro"]
        ];
    }

    protected function getJWTFromRequest()
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        $jwt = trim(str_replace('Bearer ', '', $authHeader));
        if (empty($jwt)) {
            return false;
        } else {
            return $jwt;
        }

    }

    protected function sendResponse($statusCode, $data)
    {
        http_response_code($statusCode);
        echo json_encode($data);
    }

    protected function getUserNameFromCSRF($csrfToken)
    {
        // Tìm người dùng dựa trên CSRF token từ session
        foreach ($_SESSION["csrf_token"] as $username => $token) {
            if ($token === $csrfToken) {
                return $username;
            }
        }
        return null;
    }

    public function authenticate_employee($requiredRole = 2)
    {
        $jwt = trim(str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION'] ?? ''));
        $agent = $this->get_agent();
        if ($this->jwt->verifyJWT($jwt, $agent) && $this->jwt->getRole() == $requiredRole) {
            return true;
        }
        return false;
    }

    public function authenticate_admin($requiredRole = 1)
    {
        $jwt = trim(str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION'] ?? ''));
        $agent = $this->get_agent();
        if ($this->jwt->verifyJWT($jwt, $agent) && $this->jwt->getRole() == $requiredRole) {
            return true;
        }
        return false;
    }

    public function authenticate_user()
    {
        $jwt = trim(str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION'] ?? ''));
        $agent = $this->get_agent();
        if ($this->jwt->verifyJWT($jwt, $agent)) {
            return true;
        }
        return false;
    }


    public function get_agent(){
        $agent = $_SERVER['HTTP_USER_AGENT'] === "MOBILE_GOATFITNESS" ? "MOBILE_GOATFITNESS" : "WEB";
        return $agent;
    }

}