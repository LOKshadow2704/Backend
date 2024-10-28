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
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        // Kiểm tra xem có token theo agent
        $sqlCheck = "SELECT COUNT(*) FROM refresh_tokens WHERE TenDangNhap = ? AND agent = ?";
        $stmtCheck = $this->connect->prepare($sqlCheck);
        $stmtCheck->execute([$username, $agent]);
        $tokenExists = $stmtCheck->fetchColumn();
        $createdAt = date('Y-m-d H:i:s');
        if ($tokenExists >= 1) {
            // Nếu token đã tồn tại, cập nhật với refresh token mới
            $sqlUpdate = "UPDATE refresh_tokens 
                          SET refresh_token = :refresh_token, 
                          created_at = :created_at,
                          expires_at = :expires_at 
                          WHERE TenDangNhap = :username AND agent = :agent";
            $stmtUpdate = $this->connect->prepare($sqlUpdate);
            return $stmtUpdate->execute([
                ':username' => $username,
                ':refresh_token' => $refreshToken,
                ':expires_at' => $expiresAt,
                ':created_at' => $createdAt,
                ':agent' => $agent
            ]);
        } else {
            // Nếu không tồn tại, thêm mới token vào cơ sở dữ liệu
            $sqlInsert = "INSERT INTO refresh_tokens (TenDangNhap, refresh_token , agent, created_at ,expires_at) 
                          VALUES (?, ?, ?, ? , ?)";
            $stmtInsert = $this->connect->prepare($sqlInsert);
            $stmtInsert->execute([$username, $refreshToken, $agent, $createdAt, $expiresAt]);
            return;

        }
    }



    public function getToken($username, $agent)
    {
        $sql = "SELECT * FROM refresh_tokens WHERE TenDangNhap = :username AND agent = :agent";
        $stmt = $this->connect->prepare($sql);
        $stmt->execute([':username' => $username, 'agent' => $agent]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getTokenByToken($refreshToken, $username)
    {
        $sql = "SELECT * FROM refresh_tokens WHERE refresh_token = ? AND expires_at > NOW() AND TenDangNhap = ?";
        $stmt = $this->connect->prepare($sql);
        $stmt->execute([$refreshToken, $username]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result : null;
    }

}
