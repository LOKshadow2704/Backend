<?php
require_once('connect_db.php');
class model_category_product{
    private $db1;
    public function __construct(){
        $this->db1 = new Database;
    }

    public function get_All_Category_Products(){
        $connect =$this ->db1 -> connect_db();
        if($connect){
            $query = 'select IDLoaiSanPham, TenLoaiSanPham from loaisanpham';
            $stmt = $connect ->prepare($query);
            $stmt -> execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if($result){
                $this->db1->disconnect_db( $connect );
                return $result;
            }else{
                $this->db1->disconnect_db( $connect );
                return false;
            }
        }else{
            return  false;
        }
    }
	
	public function update_Category($data, $IDLoaiSanPham) {
    $connect = $this->db1->connect_db();
    if ($connect) {
        if (isset($data['TenLoaiSanPham'])) {
            $TenLoaiSanPham = $data['TenLoaiSanPham'];
            $query = "UPDATE loaisanpham SET TenLoaiSanPham = ? WHERE IDLoaiSanPham = ?";
            $stmt = $connect->prepare($query);
            try {
                $result = $stmt->execute([$TenLoaiSanPham, $IDLoaiSanPham]);
                if ($result) {
                    return true;
                } else {
                    return false;
                }
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
                return false;
            }
        } else {
            echo "Dữ liệu không hợp lệ.";
            return false;
        }
    } else {
        return false;
    }
}

	public function delete_Category($IDLoaiSanPham){
    $connect = $this->db1->connect_db();
    if($connect){
        $query = 'DELETE FROM loaisanpham WHERE IDLoaiSanPham = ?';
        $stmt = $connect->prepare($query);
        $result = $stmt->execute([$IDLoaiSanPham]);
        if($result){
            $this->db1->disconnect_db($connect);
            return $result;
        }else{
            $this->db1->disconnect_db($connect);
            return false;
        }
    }else{
        return false;
    }
}

	public function add_Category($TenLoaiSanPham){
    $connect = $this->db1->connect_db();
    if($connect){
        try {
            // Chèn dữ liệu vào bảng LoaiSanPham
            $query = 'INSERT INTO loaisanpham (TenLoaiSanPham) VALUES (?)';
            $stmt = $connect->prepare($query);
            $stmt->execute([$TenLoaiSanPham]);
            $lastInsertedId = $connect->lastInsertId();
            $this->db1->disconnect_db($connect);
            return $lastInsertedId;
        } catch (PDOException $e) {
            echo "Lỗi: " . $e->getMessage();
            return false;
        }
    }else{
        return false;
    }
}

}