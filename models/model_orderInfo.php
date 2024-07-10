<?php
require_once("connect_db.php");
class model_orderInfo{
    private $db;
    private $IDDonHang;
    private $IDSanPham ;
    private $SoLuong ;
    public function __construct($IDDonHang = null, $IDSanPham = null, $SoLuong = null) {
        $this->db = new Database();
        $this->IDDonHang = $IDDonHang;
        $this->IDSanPham = $IDSanPham;
        $this->SoLuong = $SoLuong;
    }

    private function ExeOrderInfo(){
        $connect = $this->db->connect_db();
        if($connect){
            $query = "INSERT into chitietdonhang values (?,?,?)";
            $stmt = $connect->prepare($query);
            $result = $stmt->execute([
                $this->IDSanPham ,
                $this->IDDonHang ,
                $this->SoLuong 
            ]
            );
            return $result;
        }
    }

   private function Exe_get_OrderInfo($IDDonHang){
        $connect = $this->db->connect_db();
        if($connect){
            $query = "SELECT p.TenSP, i.IDSanPham , i.SoLuong , p.DonGia , p.IMG FROM ChiTietDonHang AS i INNER JOIN SanPham AS p ON i.IDSanPham = p.IDSanPham  WHERE IDDonHang = ?";
            $stmt = $connect->prepare($query);
            $stmt->execute([$IDDonHang]);
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

    public function Order(){
        return $this->ExeOrderInfo();
    }

    public function get_OrderInfo($IDDonHang){
        return $this->Exe_get_OrderInfo($IDDonHang);
    }

}