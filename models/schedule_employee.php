<?php
require_once("connect_db.php");
class Schedule_employee
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function schedule_employee($id)
    {
        $connect = $this->db->connect_db();
        if ($connect) {
            $query = "SELECT  `Ngay`, `Ca`, `GhiChu` FROM lichlamvien_nhanvien WHERE IDNhanVien = ?";
            $stmt = $connect->prepare($query);
            $stmt->execute([$id]);
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

    public function get_all_schedules()
    {
        $connect = $this->db->connect_db();
        if ($connect) {
            $query = "SELECT * FROM lichlamvien_nhanvien"; 
            $stmt = $connect->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->db->disconnect_db($connect);
            return $result;
        }
        return false;
    }


    public function insert_schedule($id, $date, $shift, $note)
    {
        $connect = $this->db->connect_db();
        if ($connect) {
            $query = "INSERT INTO lichlamvien_nhanvien (`IDNhanVien`, `Ngay`, `Ca`, `GhiChu` ) VALUES (?, ?, ?, ?)";
            $stmt = $connect->prepare($query);
            $result = $stmt->execute([$id, $date, $shift, $note]);
            if ($result) {
                $this->db->disconnect_db($connect);
                return true;
            } else {
                $this->db->disconnect_db($connect);
                return false;
            }
        }

    }

}