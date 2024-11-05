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
                return intval($connect->lastInsertId());
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

    //Vỹ thêm hàm lấy thông tin PT của KH
    public function Exe_get_pt_details_byKhachHang($username) {
        $connect = $this->db->connect_db();
        if ($connect) {
            // Query để lấy thông tin thuê PT của khách hàng và HLV
            $query = 'SELECT 
                        hd.IDHoaDon, 
                        hd.TrangThaiThanhToan, 
                        hd.NgayDangKy, 
                        hd.NgayHetHan, 
                        hlv.DichVu, 
                        hlv.GiaThue, 
                        tkHLV.HoTen AS TenHLV, 
                        tkHLV.Email, 
                        tkHLV.SDT
                      FROM hoadonthuept hd
                      JOIN khachhang kh ON hd.IDKhachHang = kh.IDKhachHang
                      JOIN hlv ON hd.IDHLV = hlv.IDHLV
                      JOIN khachhang khHLV ON hlv.IDHLV = khHLV.IDHLV
                      JOIN taikhoan tkKH ON kh.TenDangNhap = tkKH.TenDangNhap
                      JOIN taikhoan tkHLV ON khHLV.TenDangNhap = tkHLV.TenDangNhap
                      WHERE tkKH.TenDangNhap = :username';

            $stmt = $connect->prepare($query);
            $stmt->bindParam(':username', $username);
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

	//Vỹ thêm hàm lấy thông tin lịch làm việc nhưng chưa chưa phân role dc
	public function Exe_get_pt_details() {
    $connect = $this->db->connect_db();
    if ($connect) {
        $query = 'SELECT 
                    tkKH.HoTen AS TenKhachHang,
                    hd.NgayDangKy,
                    hd.NgayHetHan,
                    tkHLV.HoTen AS TenHLV,
                    hlv.DichVu,
                    hlv.GiaThue,
                    tkHLV.SDT AS SdtHLV
                  FROM hoadonthuept hd
                  JOIN khachhang kh ON hd.IDKhachHang = kh.IDKhachHang
                  JOIN hlv ON hd.IDHLV = hlv.IDHLV
                  JOIN khachhang khHLV ON hlv.IDHLV = khHLV.IDHLV
                  JOIN taikhoan tkKH ON kh.TenDangNhap = tkKH.TenDangNhap
                  JOIN taikhoan tkHLV ON khHLV.TenDangNhap = tkHLV.TenDangNhap
                  ORDER BY hd.NgayDangKy DESC
                  LIMIT 0, 25';

        $stmt = $connect->prepare($query);

        try {
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->db->disconnect_db($connect);
            return false;
        }

        $this->db->disconnect_db($connect);
        return $result ? $result : false;
    } else {
        return false; 
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