<?php
require_once("connect_db.php");
    class model_cart{
        private $db;
        public $userID;
        public function __construct($userID = null){
            $this->db = new Database();
            $this->userID = $userID;
        }

        public function setUserId($userId) {
            $this->userID = $userId;
        }

        public function get_All_cart(){
            $connect = $this->db->connect_db();
            if ($connect) {
                try {
                    $query = "SELECT c.IDSanPham, p.TenSP, p.DonGia, p.IMG, c.SoLuong 
                            FROM `giohang` as c 
                            LEFT JOIN khachhang as a ON c.IDKhachHang = a.IDKhachHang 
                            LEFT JOIN sanpham as p ON c.IDSanPham = p.IDSanPham 
                            WHERE a.IDKhachHang = :userID";
                    $stmt = $connect->prepare($query);
                    $stmt->execute(array(':userID' => $this->userID));
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    $this->db->disconnect_db($connect);
                    
                    return $result;
                } catch (PDOException $e) {
                    // Xử lý lỗi nếu có
                    error_log('Lỗi khi truy vấn: ' . $e->getMessage());
                    return false;
                }
            } else {
                return false;
            }
        }


        public function AddtoCart($IDSanPham){
            $connect = $this->db->connect_db();
            if ($connect) {
                try {
                    // Kiểm tra bản ghi đã tồn tại
                    $search_query = "SELECT * FROM giohang WHERE IDKhachHang = :userID AND IDSanPham = :IDSanPham";
                    $search_stmt = $connect->prepare($search_query);
                    $search_stmt->execute(array(':userID' => $this->userID, ':IDSanPham' => $IDSanPham));
        
                    if ($search_stmt->rowCount() > 0) {
                        $update_query = "UPDATE giohang SET SoLuong = SoLuong + 1 WHERE IDKhachHang = :userID AND IDSanPham = :IDSanPham";
                        $update_stmt = $connect->prepare($update_query);
                        $update_result = $update_stmt->execute(array(':userID' => $this->userID, ':IDSanPham' => $IDSanPham));
        
                        $this->db->disconnect_db($connect);
                        return $update_result;
                    } else {
                        $insert_query = "INSERT INTO giohang (IDKhachHang, IDSanPham, SoLuong) VALUES (:userID, :IDSanPham, 1)";
                        $insert_stmt = $connect->prepare($insert_query);
                        $insert_result = $insert_stmt->execute(array(':userID' => $this->userID, ':IDSanPham' => $IDSanPham));
        
                        $this->db->disconnect_db($connect);
                        return $insert_result;
                    }
                } catch (PDOException $e) {
                    error_log('Lỗi khi thực thi truy vấn: ' . $e->getMessage());
                    return false;
                }
            } else {
                return false;
            }
        }

        public function updateQuantity($IDSanPham,$IDKhachHang, $SoLuong){
            $connect = $this->db->connect_db();
            if($connect){
                $query = "update giohang set SoLuong = ? where IDSanPham = ? and IDKhachHang = ?";
                $stmt = $connect ->prepare($query);
                $result= $stmt->execute([$SoLuong , $IDSanPham,$IDKhachHang]);
                if($result){
                    $this->db->disconnect_db($connect);
                    return $result;
                }else{
                    $this->db->disconnect_db($connect);
                    return false;
                }
            }
           

        }


        public function deleteItem($IDSanPham,$IDKhachHang){
            $connect = $this->db->connect_db();
            if($connect){
                $query = "DELETE FROM `giohang` WHERE IDSanPham = ? AND IDKhachHang = ?";
                $stmt = $connect ->prepare($query);
                $result= $stmt->execute([$IDSanPham,$IDKhachHang]);
                if($result){
                    $this->db->disconnect_db($connect);
                    return $result;
                }else{
                    $this->db->disconnect_db($connect);
                    return false;
                }
            }
        }

        public function getCartItem($IDKhachHang , $IDSanPham){
            $connect = $this->db->connect_db();
            if($connect){
                $query = "SELECT * FROM GioHang WHERE IDSanPham = ? AND IDKhachHang = ? ";
                $stmt = $connect ->prepare($query);
                $result= $stmt->execute([$IDSanPham,$IDKhachHang]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
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