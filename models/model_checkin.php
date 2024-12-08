<?php
require_once('connect_db.php');
class model_checkin
{
    private $db;
    public function __construct()
    {
        $this->db = new Database;
    }
    public function statistical()
    {
        $connect = $this->db->connect_db();
        if ($connect) {
            $query = "SELECT ThoiGian, CheckOut FROM checkin ORDER BY `ThoiGian` DESC";
            $stmt = $connect->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($result) {
                $this->db->disconnect_db($connect);
                return $result;
            } else {
                $this->db->disconnect_db($connect);
                return false;
            }
        }
    }

    public function employee_checkin($username, $time)
    {
        $connect = $this->db->connect_db();
        if ($connect) {
            $query = "INSERT INTO checkin VALUE(null, ? , ? , 0 , null)";
            $stmt = $connect->prepare($query);
            $result = $stmt->execute([$username, $time]);
            if ($result) {
                $this->db->disconnect_db($connect);
                return $result;
            } else {
                $this->db->disconnect_db($connect);
                return false;
            }
        }
    }

    public function checkined($username)
    {
        $connect = $this->db->connect_db();
        if ($connect) {
            $query = "SELECT 1 
                  FROM checkin
                  WHERE CheckOut = 0 
                  AND DATE(ThoiGian) = CURDATE() 
                  AND TenDangNhap = ? 
                  LIMIT 1;";
            $stmt = $connect->prepare($query);
            $stmt->execute([$username]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->db->disconnect_db($connect);

            return $result ? true : false;
        }

        return false;
    }

    public function checkout($username)
    {
        $connect = $this->db->connect_db();

        if ($connect) {

            $query = "SELECT id, ThoiGian FROM checkin WHERE TenDangNhap = ? AND CheckOut = 0 LIMIT 1";
            $stmt = $connect->prepare($query);
            $stmt->execute([$username]);
            $checkinData = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$checkinData) {
                $this->db->disconnect_db($connect);
                return false;
            }

            $checkoutTime = new DateTime("now", new DateTimeZone("Asia/Ho_Chi_Minh"));
            $checkInTime = new DateTime($checkinData['ThoiGian'], new DateTimeZone("Asia/Ho_Chi_Minh"));
            $interval = $checkoutTime->diff($checkInTime);
            $updateQuery = "UPDATE CheckIn SET CheckOut = 1, checkout_time = NOW() WHERE id = ?";
            $stmt = $connect->prepare($updateQuery);
            $stmt->execute([$checkinData['id']]);
            $affectedRows = $stmt->rowCount();
            $this->db->disconnect_db($connect);
            if ($affectedRows === 1) {
                $hours = $interval->h;
                $minutes = $interval->i;
                $seconds = $interval->s;
                $totalHours = $hours + ($minutes / 60) + ($seconds / 3600);
                return round($totalHours, 2);
            } else {
                return false;
            }
        }

        return false;
    }





}