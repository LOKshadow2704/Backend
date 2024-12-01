<?php
require_once('connect_db.php');
class model_pt
{
    private $db;
    public function __construct()
    {
        $this->db = new Database;
    }
    public function get_All_pt()
    {
        $connect = $this->db->connect_db();
        if ($connect) {
            $query = 'SELECT p.IDHLV, c.HoTen, c.DiaChi, c.Email , c.SDT,c.avt, p.DichVu , p.GiaThue , k.IDKhachHang , p.ChungChi	FROM khachhang as k inner join hlv as p on p.IDHLV = k.IDHLV left join taikhoan as c on c.TenDangNhap = k.TenDangNhap WHERE k.IDHLV IS NOT NULL';
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

    public function get_One_personalTrainer($ptID)
    {
        $connect = $this->db->connect_db();
        if ($connect) {
            $query = 'SELECT p.IDHLV, c.HoTen, c.DiaChi, c.Email , c.SDT,c.avt, p.DichVu , p.GiaThue , k.IDKhachHang  , p.ChungChi FROM khachhang as k left join hlv as p on p.IDHLV = k.IDHLV left join taikhoan as c on c.TenDangNhap = k.TenDangNhap WHERE k.IDHLV = ?';
            $stmt = $connect->prepare($query);
            $stmt->execute([$ptID]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($result) {
                $this->db->disconnect_db($connect);
                return $result[0];
            } else {
                $this->db->disconnect_db($connect);
                return false;
            }
        }
    }

    public function get_Random_pt()
    {
        $connect = $this->db->connect_db();
        if ($connect) {
            $query = 'SELECT p.IDHLV, k.IDKhachHang , c.HoTen, c.DiaChi, c.Email , c.SDT,c.avt, p.DichVu , p.GiaThue , k.IDKhachHang , p.ChungChi FROM khachhang as k left join hlv as p on p.IDHLV = k.IDHLV left join taikhoan as c on c.TenDangNhap = k.TenDangNhap WHERE k.IDHLV IS NOT NULL ORDER BY RAND() LIMIT 4';
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

    public function user_apply($dichvu, $giathue, $chungchi)
    {
        $connect = $this->db->connect_db();
        if ($connect) {
            $query = 'INSERT INTO hlv VALUE( null , ?, ?, ?, 0, 0)';
            $stmt = $connect->prepare($query);
            $result = $stmt->execute([$dichvu, $giathue, $chungchi]);
            if ($result) {
                $this->db->disconnect_db($connect);
                return $connect->lastInsertId();
            } else {
                $this->db->disconnect_db($connect);
                return false;
            }
        }
    }

    public function accept_pt($id)
    {
        $connect = $this->db->connect_db();
        if ($connect) {
            $query = "UPDATE hlv SET XacNhan = 1 WHERE IDHLV = ?";
            $stmt = $connect->prepare($query);
            $stmt->execute([$id]);
            $rowsAffected = $stmt->rowCount();
            $this->db->disconnect_db($connect);
            if ($rowsAffected === 0) {
                return false;
            }
            return true;
        }
        return false;
    }

    public function reject_pt($id)
    {
        $connect = $this->db->connect_db();
        if ($connect) {
            $query = "DELETE FROM hlv WHERE IDHLV = ?";
            $stmt = $connect->prepare($query);
            $stmt->execute([$id]);
            $rowsAffected = $stmt->rowCount();
            $this->db->disconnect_db($connect);
            if ($rowsAffected === 0) {
                return false;
            }
            return true;
        }
        return false;
    }

    public function request_pt()
    {
        $connect = $this->db->connect_db();
        if ($connect) {
            $query = 'SELECT p.IDHLV, c.HoTen, c.DiaChi, c.Email , c.SDT,c.avt, p.DichVu , p.GiaThue , k.IDKhachHang , p.ChungChi	FROM khachhang as k inner join hlv as p on p.IDHLV = k.IDHLV left join taikhoan as c on c.TenDangNhap = k.TenDangNhap WHERE k.IDHLV IS NOT NULL AND p.XacNhan = 0';
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

    public function user_request_pt($username){
        $connect = $this->db->connect_db();
        if ($connect) {
            $query = 'SELECT p.IDHLV, c.HoTen, c.DiaChi, c.Email , c.SDT,c.avt, p.DichVu , p.GiaThue , k.IDKhachHang , p.ChungChi	
                      FROM khachhang as k 
                      inner join hlv as p on p.IDHLV = k.IDHLV 
                      left join taikhoan as c on c.TenDangNhap = k.TenDangNhap 
                      WHERE k.IDHLV IS NOT NULL AND p.XacNhan = 0 AND c.TenDangNhap like ?';
            $stmt = $connect->prepare($query);
            $stmt->execute([$username]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                $this->db->disconnect_db($connect);
                return $result;
            } else {
                $this->db->disconnect_db($connect);
                return false;
            }
        }
    }

}