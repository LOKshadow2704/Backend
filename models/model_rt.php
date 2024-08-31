<?php
class RefreshTokenModel
{
    private $connect;

    public function __construct()
    {
        $db = new Database();
        $this->connect = $db->connect_db();
    }


    public function saveToken($username, $refreshToken, $expiresAt, $agent)
    {
        // Kiểm tra xem có token theo agent
        $sqlCheck = "SELECT COUNT(*) FROM refresh_tokens WHERE TenDangNhap = ? AND agent = ?";
        $stmtCheck = $this->connect->prepare($sqlCheck);
        $stmtCheck->execute([$username, $agent]);
        $tokenExists = $stmtCheck->fetchColumn();
        if ($tokenExists >= 1) {
            // Nếu token đã tồn tại, cập nhật với refresh token mới
            $sqlUpdate = "UPDATE refresh_tokens 
                          SET refresh_token = :refresh_token, expires_at = :expires_at 
                          WHERE TenDangNhap = :username AND agent = :agent";
            $stmtUpdate = $this->connect->prepare($sqlUpdate);
            return $stmtUpdate->execute([
                ':username' => $username,
                ':refresh_token' => $refreshToken,
                ':expires_at' => $expiresAt,
                ':agent' => $agent
            ]);
        } else {
            // Nếu không tồn tại, thêm mới token vào cơ sở dữ liệu
            $sqlInsert = "INSERT INTO refresh_tokens (TenDangNhap, refresh_token , agent, expires_at) 
                          VALUES (?, ?, ?, ?)";
            $stmtInsert = $this->connect->prepare($sqlInsert);
            $stmtInsert->execute([$username, $refreshToken, $agent, $expiresAt]);
            return;

        }
    }



    public function getTokenByUsername($username)
    {
        $sql = "SELECT * FROM refresh_tokens WHERE TenDangNhap = :username AND revoked = FALSE AND expires_at > NOW()";
        $stmt = $this->connect->prepare($sql);
        $stmt->execute([':username' => $username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getTokenByToken($refreshToken)
    {
        $sql = "SELECT * FROM refresh_tokens WHERE refresh_token = :refresh_token AND revoked = FALSE AND expires_at > NOW()";
        $stmt = $this->connect->prepare($sql);
        $stmt->execute([':refresh_token' => $refreshToken]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

}
