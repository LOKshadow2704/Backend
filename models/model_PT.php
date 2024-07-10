<?php
    require_once('connect_db.php');
    class model_pt{
        private $db;
        public function __construct(){
            $this->db  = new Database;
        }
        public function get_All_pt(){
            $connect = $this -> db ->connect_db();
            if($connect){
                $query = 'SELECT p.IDHLV, c.HoTen, c.DiaChi, c.Email , c.SDT,c.avt, p.DichVu , p.GiaThue , k.IDKhachHang FROM khachhang as k left join hlv as p on p.IDHLV = k.IDHLV left join taikhoan as c on c.TenDangNhap = k.TenDangNhap WHERE k.IDHLV IS NOT NULL' ;
                $stmt = $connect->prepare(  $query );
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

        public function get_One_personalTrainer($ptID){
            $connect = $this -> db ->connect_db();
            if($connect){
                $query = 'SELECT p.IDHLV, c.HoTen, c.DiaChi, c.Email , c.SDT,c.avt, p.DichVu , p.GiaThue , k.IDKhachHang , p.DanhGia , p.ChungChi FROM khachhang as k left join hlv as p on p.IDHLV = k.IDHLV left join taikhoan as c on c.TenDangNhap = k.TenDangNhap WHERE k.IDHLV = ?';
                $stmt = $connect->prepare(  $query );
                $stmt->execute([$ptID]);
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

        public function get_Random_pt(){
            $connect = $this -> db ->connect_db();
            if($connect){
                $query = 'SELECT p.IDHLV, k.IDKhachHang , c.HoTen, c.DiaChi, c.Email , c.SDT,c.avt, p.DichVu , p.GiaThue , k.IDKhachHang , p.ChungChi FROM khachhang as k left join hlv as p on p.IDHLV = k.IDHLV left join taikhoan as c on c.TenDangNhap = k.TenDangNhap WHERE k.IDHLV IS NOT NULL ORDER BY RAND() LIMIT 4' ;
                $stmt = $connect->prepare(  $query );
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
    }