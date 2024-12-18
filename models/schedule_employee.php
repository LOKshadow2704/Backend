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
            $query = "SELECT  ln.Ngay, c.desc , ln.GhiChu
                        FROM lichlamviec_nhanvien as ln 
                        LEFT JOIN calamviec as c ON  ln.Ca = c.id 
                        WHERE IDNhanVien = ?";
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
            $query = "SELECT * FROM lichlamviec_nhanvien as sch 
                      LEFT JOIN (SELECT a.TenDangNhap, a.SDT , e.ID , a.HoTen FROM taikhoan as a LEFT JOIN nhanvien as e ON a.TenDangNhap = e.TenDangNhap) 
                      as nv ON sch.IDNhanVien = nv.ID"; 
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
            $query = "INSERT INTO lichlamviec_nhanvien (`IDNhanVien`, `Ngay`, `Ca`, `GhiChu` ) VALUES (?, ?, ?, ?)";
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