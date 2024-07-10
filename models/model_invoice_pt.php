<?php
require_once("connect_db.php");
class model_invoice_pt{
    private $db;
    private $IDHoaDon;
    private $IDKhachHang ;
    private $IDHLV  ;
    private $TrangThaiThanhToan;
    private $NgayDangKy;
    private $NgayHetHan;
    public function __construct($IDHoaDon = null ,$IDKhachHang = null , $IDHLV = null  ,$NgayDangKy = null ,$NgayHetHan = null ) {
        $this->db = new Database;
        $this->IDHoaDon = $IDHoaDon;
        $this->IDKhachHang  = $IDKhachHang ;
        $this->IDHLV = $IDHLV;
        $this->TrangThaiThanhToan = "Chưa thanh toán";
        $this->NgayDangKy = $NgayDangKy;
        $this->NgayHetHan = $NgayHetHan;
    }

    private function get_PT_byKhachHang($userID){ 
        $connect = $this->db->connect_db();
        if($connect){
            $query = "SELECT * FROM hoadonthuept WHERE IDKhachHang =?";
            $stmt = $connect->prepare($query);
            $stmt->execute([$userID]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        }
    }

    private function Exe_add_Invoice(){
        $connect = $this->db->connect_db();
        if($connect){
            $query = "INSERT INTO hoadonthuept VALUES (NULL, ?, ?, ?, ?, ?)";
            $stmt = $connect->prepare($query);
            $result=$stmt->execute([
                $this->IDKhachHang ,
                $this->IDHLV  ,
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
            $query = "UPDATE hoadonthuept SET TrangThaiThanhToan = 'Đã Thanh Toán' WHERE IDHoaDon = ?";
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

    public function checkTime($starttime , $endtime){
        $connect = $this->db->connect_db();
        if($connect){
            $query = "SELECT * FROM hoadonthuept WHERE (( NgayDangKy BETWEEN ? AND ?) OR (NgayHetHan BETWEEN ? AND ?))";
            $stmt = $connect->prepare($query);
            $stmt->execute([$starttime , $endtime ,$starttime , $endtime]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        }
    }

    public function getIDInvoice(){

    }

    public function Exe_get_PT_byKhachHang($userID){
        return $this->get_PT_byKhachHang($userID);
    }

    public function add_Invoice(){
        return $this->Exe_add_Invoice();
    }

    public function updateInvoiceStatus($IDHoaDon){
        return $this->ExeUpdateInvoiceStatus($IDHoaDon);
    }
}