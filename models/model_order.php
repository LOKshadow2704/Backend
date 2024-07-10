<?php
require_once("connect_db.php");
class model_order{
    private $db;
    private $IDDonHang;
    private $IDKhachHang ;
    private $IDHinhThuc ;
    private $NgayDat;
    private $NgayGiaoDuKien;
    private $TrangThaiThanhToan;
    private $DiaChi;
    private $ThanhTien;
    public function __construct($IDDonHang = null, $IDKhachHang = null, $IDHinhThuc = null, $DiaChi = null ,$ThanhTien = null) {
        $this->db = new Database();
        $this->IDDonHang = $IDDonHang;
        $this->IDKhachHang = $IDKhachHang;
        $this->IDHinhThuc = $IDHinhThuc;
        $this->NgayDat = date("Y-m-d");
        $this->NgayGiaoDuKien = (new DateTime())->modify('+3 days')->format("Y-m-d");
        $this->TrangThaiThanhToan = "Chưa thanh toán";
        $this->DiaChi = $DiaChi;
        $this->ThanhTien = $ThanhTien;
    }

    private function ExeOrder(){
        $connect = $this->db->connect_db();
        if($connect){
            $query = "INSERT into DonHang values (?,?,?,?,?,?,?,?,'Chưa xác nhận')";
            $stmt = $connect->prepare($query);
            $result = $stmt->execute([
                $this->IDDonHang,
                $this->IDKhachHang,
                $this->IDHinhThuc,
                $this->NgayDat,
                $this->NgayGiaoDuKien,
                $this->TrangThaiThanhToan,
                $this->DiaChi,
                $this->ThanhTien
            ]
            );
            if($result){
                return $connect->lastInsertId();
            }else{
                return false;
            }
        }
    }

    private function Exe_get_All_Purchase($IDKhachHang){
        $connect = $this->db->connect_db();
        if($connect){
            $query = "SELECT * FROM DonHang WHERE IDKhachHang = ?";
            $stmt = $connect->prepare($query);
            $stmt->execute([$IDKhachHang]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if($result){
                $this->db->disconnect_db($connect);
                return $result;
            }else{
                $this->db->disconnect_db($connect);
                return false;
            }
        }
    }

    private function ExeUpdatePaymentStatus($IDDonHang){
        $connect = $this->db->connect_db();
        if($connect){
            $query = "UPDATE DonHang SET TrangThaiThanhToan = 'Đã Thanh Toán' WHERE IDDonHang = ?";
            $stmt = $connect->prepare($query);
            $result = $stmt->execute([$IDDonHang]);
            if($result){
                $this->db->disconnect_db($connect);
                return $result;
            }else{
                $this->db->disconnect_db($connect);
                return false;
            }
        }
    }

    public function get_All_Purchase_unconfimred(){
        $connect = $this->db->connect_db();
        if($connect){
            $query = "SELECT * FROM DonHang WHERE TrangThai LIKE 'Chưa xác nhận'";
            $stmt = $connect->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if($result){
                $this->db->disconnect_db($connect);
                return $result;
            }else{
                $this->db->disconnect_db($connect);
                return false;
            }
        }
    }

    public function Purchase_confirm($IDDonHang){
        $connect = $this->db->connect_db();
        if($connect){
            $query = "UPDATE DonHang SET TrangThai = 'Đã xác nhận' WHERE IDDonHang = ?";
            $stmt = $connect->prepare($query);
            $result = $stmt->execute([$IDDonHang]);
            if($result){
                $this->db->disconnect_db($connect);
                return $result;
            }else{
                $this->db->disconnect_db($connect);
                return false;
            }
        }
    }

    public function Order(){
        return $this->ExeOrder();
    }

    public function get_All_Purchase($IDKhachHang){
        return $this->Exe_get_All_Purchase($IDKhachHang);
    }

    public function updatePaymentStatus($IDDonHang){
        return $this->ExeUpdatePaymentStatus($IDDonHang);
    }
}