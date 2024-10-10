<?php
require_once('connect_db.php');
class model_product{
    private $db1;
    public function __construct(){
        $this->db1 = new Database;
    }

    public function get_All_Products(){
        $connect =$this ->db1 -> connect_db();
        if($connect){
            $query = 'select p.IDSanPham,c.TenLoaiSanPham , p.TenSP , p.Mota , p.DonGia , p.IMG   from sanpham as p join loaisanpham as c on p.IDLoaiSanPham = c.IDLoaiSanPham';
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

    public function get_All_Products_byManege(){
        $connect =$this ->db1 -> connect_db();
        if($connect){
            $query = 'select p.IDSanPham,c.TenLoaiSanPham , p.TenSP , p.Mota , p.DonGia , p.IMG,k.SoLuong ,p.IDLoaiSanPham from 
            sanpham as p join loaisanpham as c on p.IDLoaiSanPham = c.IDLoaiSanPham  LEFT join kho as k on k.IDSanPham = p.IDSanPham';
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

    public function get_One_Products( $productID){
        $connect =$this ->db1 -> connect_db();
        if($connect){
            $query = "select p.IDSanPham,c.TenLoaiSanPham , p.TenSP , p.Mota , p.DonGia , p.IMG , k.SoLuong  from sanpham as p join loaisanpham as c on p.IDLoaiSanPham = c.IDLoaiSanPham  LEFT join kho as k on k.IDSanPham = p.IDSanPham where p.IDSanPham = ?";
            $stmt = $connect ->prepare($query);
            $stmt -> execute([$productID]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if($result){
                $this->db1->disconnect_db( $connect );
                return $result[0];
            }else{
                $this->db1->disconnect_db( $connect );
                return false;
            }
        }else{
            return  false;
        }
    }

    public function get_Random_Products(){
        $connect =$this ->db1 -> connect_db();
        if($connect){
            $query = 'SELECT p.IDSanPham,c.TenLoaiSanPham , p.TenSP , p.Mota , p.DonGia , p.IMG   FROM sanpham AS p JOIN loaisanpham AS c ON p.IDLoaiSanPham = c.IDLoaiSanPham ORDER BY RAND() LIMIT 4';
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

    public function updateProduct($data, $IDSanPham) {
        $connect = $this->db1->connect_db();
        if ($connect) {
            $query = 'UPDATE SanPham SET ';
            $query_value = array();
            $SoLuong = null; 
            $update_quantity = null;
            foreach ($data as $key => $value) {
                if ($key == "SoLuong") {
                    $SoLuong = $value;
                    $update_quantity = "UPDATE Kho SET SoLuong = ? WHERE IDSanPham = ?";
                    continue;
                }
                $query .= " $key = ?,";
                $query_value[] = $value;
            }
            if (isset($SoLuong) && isset($update_quantity)) {
                try {
                    $stmt2 = $connect->prepare($update_quantity);
                    $result1 = $stmt2->execute([$SoLuong, $IDSanPham]);
                } catch (PDOException $e) {
                    // Xử lý lỗi khi thực thi truy vấn
                    echo "Error: " . $e->getMessage();
                    return false;
                }
            }
            if(empty($query_value) && $result1){
                return true;
            }elseif(empty($query_value) && !$result1 && isset($SoLuong)){
                return false;
            }elseif((!empty($query_value) && $result1) || (!empty($query_value) && empty($result1))){
                $query = rtrim($query, ',');
                $query .= " WHERE IDSanPham = ?";
                $stmt = $connect->prepare($query);
                $query_value[] = $IDSanPham;
                echo($query);
                try {
                    $result = $stmt->execute($query_value);
                    return $result;
                } catch (PDOException $e) {
                    // Xử lý lỗi khi thực thi truy vấn
                    echo "Error: " . $e->getMessage();
                    return false;
                }
            }
            
        } else {
            return false;
        }
    }
    

    public function get_All_Category(){
        $connect = $this ->db1 -> connect_db();
        if($connect){
            $query = 'SELECT * FROM LoaiSanPham';
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

    public function delete_Product($IDSanPham){
        $connect = $this ->db1 -> connect_db();
        if($connect){
            $query = 'DELETE FROM SanPham WHERE IDSanPham = ? ';
            $stmt = $connect ->prepare($query);
            $result = $stmt -> execute([$IDSanPham]);
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

    public function add_Product($data, $quantity){
        $connect = $this->db1->connect_db();
        if($connect){
            try {
                // Chèn dữ liệu vào bảng SanPham
                $query = 'INSERT INTO SanPham  VALUES (null,?, ?, ?, ?, ?)';
                $stmt = $connect->prepare($query);
                $stmt->execute([$data["IDLoaiSanPham"] ,$data["TenSP"] ,$data["Mota"] ,$data["DonGia"] ,$data["IMG"]  ]);
                $lastInsertedId = $connect->lastInsertId();
                // Chèn dữ liệu vào bảng Kho
                $query_quantity = "INSERT INTO Kho VALUES (?, ?)";
                $stmt_quantity = $connect->prepare($query_quantity);
                $stmt_quantity->execute([$lastInsertedId, $quantity]);
                $this->db1->disconnect_db($connect);
                return $lastInsertedId;
            } catch (PDOException $e) {
                echo "Lỗi: " . $e->getMessage();
                return false;
            }
        }else{
            return  false;
        }
    }

}