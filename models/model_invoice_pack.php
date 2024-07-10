<?php
require_once("connect_db.php");
class Model_invoice_pack{
    private $db;
    private $IDHoaDon;
    private $IDGoiTap;
    private $IDKhachHang;
    private $TrangThaiThanhToan;
    private $NgayDangKy;
    private $NgayHetHan;

    public function __construct($IDGoiTap = null,$IDKhachHang = null, $ThoiHan = null, $TrangThaiThanhToan = null){
        $this->db = new Database;
        $this->IDHoaDon = null;
        $this->IDGoiTap=$IDGoiTap;
        $this->IDKhachHang=$IDKhachHang;
        if($TrangThaiThanhToan){
            $this->TrangThaiThanhToan = $TrangThaiThanhToan;
        }else{
            $this->TrangThaiThanhToan="Chưa thanh toán";
        }
        $this->NgayDangKy= date("Y-m-d");
        $ThoiHan = (int)$ThoiHan;
        $this->NgayHetHan=(new DateTime())->modify("+$ThoiHan days")->format("Y-m-d");
    }

    private function get_Pack_byKhachHang($userID){ 
        $connect = $this->db->connect_db();
        if($connect){
            $query = "SELECT * FROM hoadonthuegoitap WHERE IDKhachHang =?";
            $stmt = $connect->prepare($query);
            $stmt->execute([$userID]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        }
    }

    private function Exe_add_Invoice(){
        $connect = $this->db->connect_db();
        if($connect){
            $query = "INSERT INTO hoadonthuegoitap VALUES (NULL, ?, ?, ?, ?, ?)";
            $stmt = $connect->prepare($query);
            $result=$stmt->execute([
                $this->IDKhachHang,
                $this->IDGoiTap,
                $this->TrangThaiThanhToan,
                $this->NgayDangKy,
                $this->NgayHetHan
            ]);
            if($result){
                return $connect->lastInsertId();
            }else{
                return false;
            }
        }
    }

    private function ExeUpdateInvoiceStatus($IDHoaDon){
        $connect = $this->db->connect_db();
        if($connect){
            $query = "UPDATE hoadonthuegoitap SET TrangThaiThanhToan = 'Đã Thanh Toán' WHERE IDHoaDon = ?";
            $stmt = $connect->prepare($query);
            $result = $stmt->execute([$IDHoaDon]);
            if($result){
                $this->db->disconnect_db($connect);
                return $result;
            }else{
                $this->db->disconnect_db($connect);
                return false;
            }
        }
    }

    public function getIDInvoice(){

    }

    public function Exe_get_Pack_byKhachHang($userID){
        return $this->get_Pack_byKhachHang($userID);
    }

    public function add_Invoice(){
        return $this->Exe_add_Invoice();
    }

    public function updateInvoiceStatus($IDHoaDon){
        return $this->ExeUpdateInvoiceStatus($IDHoaDon);
    }
}