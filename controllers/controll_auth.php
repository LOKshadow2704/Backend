<?php
require_once(__DIR__ . '/control.php');

//  Xử lý các logic xác thực và phân quyền và tài khoản, thực hiện quản lý phiên làm việc với tài khoản

class AuthController extends Control
{

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
            //Kiểm tra phiên làm việc
            $existingSession = $this->modelAuth->checkPHPSESSID($username);
            if (!empty($existingSession) && !is_null($existingSession[0]['phpsessid'])) {
                if (session_status() === PHP_SESSION_ACTIVE) {
                    session_write_close();
                }
                session_id($existingSession[0]['phpsessid']);
            } else {
                // Trường hợp không có PHPSESSID trong cơ sở dữ liệu
                if (session_status() === PHP_SESSION_ACTIVE) {
                    session_destroy();
                }
                session_start();
                session_regenerate_id(true);
                $this->modelAuth->savePHPSESSID($username, session_id());
            }
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            if ($_SERVER['HTTP_USER_AGENT'] === 'MOBILE_GOATFITNESS') {
                $csrf = $this->jwt->generateCSRFToken();
                if (!isset($_SESSION["csrf_token"])) {
                    $_SESSION["csrf_token"] = [];
                }
                if (!isset($_SESSION["csrf_token"][$username]) || !is_array($_SESSION["csrf_token"][$username])) {
                    $_SESSION["csrf_token"][$username] = [];
                }
                $_SESSION["csrf_token"][$username][$_SERVER['HTTP_USER_AGENT']] = $csrf;
                $payload = $this->createPayload($username);
                $token = $this->jwt->generateJWT($payload, $_SERVER['HTTP_USER_AGENT']);
                $refreshToken = $this->jwt->createRefreshToken($username, 'MOBILE');
                $this->sendResponse(200, [
                    'message' => 'Đăng nhập thành công',
                    'access_token' => $token,
                    'refresh_token' => $refreshToken,
                    'phpsessid' => session_id(),
                    'user' => $this->modelAuth->AccountInfo($username),
                ]);
            } else {
                $this->createSession($username);
                $this->sendResponse(200, [
                    'message' => 'Đăng nhập thành công',
                    'user' => $this->modelAuth->AccountInfo($username)
                ]);
                // MOBILE
            }
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
        $agent = "";
        if ($_SERVER['HTTP_USER_AGENT'] == "MOBILE_GOATFITNESS") {
            //Đăng nhập MOBILE
            $agent = "MOBILE_GOATFITNESS";
            $RT_request = json_decode(file_get_contents('php://input'), true);
            $refreshToken = $RT_request['refresh_token'];
            if (empty($refreshToken)) {
                $this->sendResponse(400, ['error' => 'Refresh Token không được cung cấp']);
                return;
            }
            $stmt = $this->jwt->VerifiRefreshToken($refreshToken, $data['username']);
            if (!$stmt) {
                $this->sendResponse(401, ['error' => 'Refresh Token không hợp lệ']);
                return;
            }
            $csrf = $this->jwt->generateCSRFToken();
                if (!isset($_SESSION["csrf_token"])) {
                    $_SESSION["csrf_token"] = [];
                }
                if (!isset($_SESSION["csrf_token"][$data['username']]) || !is_array($_SESSION["csrf_token"][$data['username']])) {
                    $_SESSION["csrf_token"][$data['username']] = [];
                }
                $_SESSION["csrf_token"][$data['username']][$_SERVER['HTTP_USER_AGENT']] = $csrf;
            $payload = $this->createPayload($data['username']);
            $token = $this->jwt->generateJWT($payload, $_SERVER['HTTP_USER_AGENT']);
            $refreshToken = $this->jwt->createRefreshToken($data['username'], 'MOBILE');
            $this->sendResponse(200, [
                'message' => 'Đăng nhập thành công',
                'access_token' => $token,
                'refresh_token' => $refreshToken,
                'phpsessid' => session_id(),
                'user' => $this->modelAuth->AccountInfo($data['username']),
            ]);
            return;
            //Đăng nhập WEB
        } else {
            $agent = "WEB";
            $refreshToken = isset($_COOKIE['refresh_token']) ? $_COOKIE['refresh_token'] : null;
            if (empty($refreshToken)) {
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
    }

    public function logout()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendResponse(404, ['error' => 'Đường dẫn không tồn tại']);
            return;
        }
        $jwt = $this->getJWTFromRequest();
        if (!$jwt) {
            $this->sendResponse(400, ['error' => ' Yêu cầu không hợp lệ.']);
            return;
        }
        $agent = "";
        if ($_SERVER['HTTP_USER_AGENT'] == "MOBILE_GOATFITNESS") {
            $agent = "MOBILE_GOATFITNESS";
        } else {
            $agent = "WEB";
        }
        if ($jwt && $this->jwt->verifyJWT($jwt, $agent)) {
            $username = $this->jwt->getUserName($jwt);
            $this->clearSession($username);
            $this->sendResponse(200, ['message' => 'Đăng xuất thành công']);
        } else {
            $this->sendResponse(403, ['error' => 'Lỗi xác thực']);
        }
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
}
