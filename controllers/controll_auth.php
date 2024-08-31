<?php

require_once(__DIR__ . '/../models/model_auth.php');
require_once(__DIR__ . '/../middlewares/JWT_Middleware.php');

// Tại đây xử lý các logic xác thực và phân quyền và tài khoản, thực hiện quản lý phiên làm việc với tài khoản

class AuthController
{
    private $modelAuth;
    private $jwt;

    public function __construct()
    {
        $this->modelAuth = new model_auth();
        $this->jwt = new JWT();
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendResponse(404, ['error' => 'Đường dẫn không tồn tại']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        $result = $this->modelAuth->login($username, $password);
        if ($result) {
            $this->createSession($username, $_SERVER['HTTP_USER_AGENT']);
            $this->sendResponse(200, [
                'message' => 'Đăng nhập thành công',
                'user' => $this->modelAuth->AccountInfo($username)
            ]);
        } else {
            $this->sendResponse(400, ['error' => 'Kiểm tra lại thông tin']);
        }
    }

    public function loginWithRT()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendResponse(404, ['error' => 'Đường dẫn không tồn tại']);
            return;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $refreshToken = isset($_COOKIE['refresh_token']) ? $_COOKIE['refresh_token'] : null;
        if (!$refreshToken) {
            $this->sendResponse(400, ['error' => 'Refresh Token không được cung cấp']);
            return;
        }
        $stmt = $this->jwt->VerifiRefreshToken($refreshToken, $data['username']);
        if (!$stmt) {
            $this->sendResponse(401, ['error' => 'Refresh Token không hợp lệ']);
            return;
        }
        $this->createSession($data['username'], $_SERVER['HTTP_USER_AGENT']);
        $this->sendResponse(200, ['success' => 'Refresh Token thành công']);
        return;
    }

    public function logout()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendResponse(404, ['error' => 'Đường dẫn không tồn tại']);
            return;
        }

        $jwt = $this->getJWTFromRequest();
        if ($jwt && $this->jwt->verifyJWT($jwt)) {
            $username = $this->jwt->getUserName($jwt);
            $this->clearSession($username);
            $this->sendResponse(200, ['message' => 'Đăng xuất thành công']);
        } else {
            $this->sendResponse(403, ['error' => 'Lỗi xác thực']);
        }
    }


    private function createSession($username, $agent)
    {
        $newRefreshToken = $this->jwt->createRefreshToken($username, "WEB");
        $csrf = $this->jwt->generateCSRFToken();
        $_SESSION["csrf_token"][$username] = $csrf;
        $payload = $this->createPayload($username);
        $token = $this->jwt->generateJWT($payload);
        if ($agent == 'WEB' || $agent == 'PostmanRuntime/7.41.2') {
            setcookie("jwt", $token, time() + 36000, "/"); // Lưu JWT vào cookie
            setcookie("refresh_token", $newRefreshToken, time() + 86400 * 30, "/");
            setcookie("PHPSESSID", session_id(), time() + 36000, "/");
        }
    }


    private function clearSession($username)
    {
        setcookie("jwt", "", time() - 3600, "/");
        unset($_SESSION["csrf_token"][$username]);
        $this->modelAuth->update_Status("Offline", $username);
    }

    private function createPayload($username)
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

    private function getJWTFromRequest()
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        return trim(str_replace('Bearer ', '', $authHeader));
    }

    private function sendResponse($statusCode, $data)
    {
        http_response_code($statusCode);
        echo json_encode($data);
    }

    public function refreshToken()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendResponse(404, ['error' => 'Đường dẫn không tồn tại']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $csrfToken = $data['csrf_token'] ?? '';

        if (!$csrfToken) {
            $this->sendResponse(400, ['error' => 'CSRF token không được cung cấp']);
            return;
        }

        $username = $this->getUserNameFromCSRF($csrfToken);

        if (!$username || !$this->isCSRFTokenValid($username, $csrfToken)) {
            $this->sendResponse(403, ['error' => 'CSRF token không hợp lệ']);
            return;
        }

        // Generate new JWT and CSRF token
        $newCSRFToken = $this->jwt->generateCSRFToken();
        $payload = $this->createPayload($username, $newCSRFToken);
        $newToken = $this->jwt->generateJWT($payload);

        // Set new cookies and update session
        setcookie("jwt", $newToken, time() + 36000, "/");
        $_SESSION["csrf_token"][$username] = $newCSRFToken;

        $this->sendResponse(200, ['message' => 'Token đã được làm mới thành công', 'token' => $newToken]);
    }

    private function getUserNameFromCSRF($csrfToken)
    {
        // Tìm người dùng dựa trên CSRF token từ session
        foreach ($_SESSION["csrf_token"] as $username => $token) {
            if ($token === $csrfToken) {
                return $username;
            }
        }
        return null;
    }

    private function isCSRFTokenValid($username, $csrfToken)
    {
        // Kiểm tra xem CSRF token có hợp lệ không
        return isset($_SESSION["csrf_token"][$username]) && $_SESSION["csrf_token"][$username] === $csrfToken;
    }
}
