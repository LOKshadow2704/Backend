<?php
require_once('connect_db.php');
class model_product
{
    private $db1;
    public function __construct()
    {
        $this->db1 = new Database;
    }

    public function get_All_Products()
    {
        $connect = $this->db1->connect_db();
        if ($connect) {
            $query = 'select p.IDSanPham,c.TenLoaiSanPham , p.TenSP , p.Mota , p.DonGia , p.IMG   from sanpham as p join loaisanpham as c on p.IDLoaiSanPham = c.IDLoaiSanPham';
            $stmt = $connect->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($result) {
                $this->db1->disconnect_db($connect);
                return $result;
            } else {
                $this->db1->disconnect_db($connect);
                return false;
            }
        } else {
            return false;
        }
    }

    public function get_All_Products_byManege()
    {
        $connect = $this->db1->connect_db();
        if ($connect) {
            $query = 'select p.IDSanPham,c.TenLoaiSanPham , p.TenSP , p.Mota , p.DonGia , p.IMG,k.SoLuong ,p.IDLoaiSanPham from 
            sanpham as p join loaisanpham as c on p.IDLoaiSanPham = c.IDLoaiSanPham  LEFT join kho as k on k.IDSanPham = p.IDSanPham';
            $stmt = $connect->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($result) {
                $this->db1->disconnect_db($connect);
                return $result;
            } else {
                $this->db1->disconnect_db($connect);
                return false;
            }
        } else {
            return false;
        }
    }

    public function get_One_Products($productID)
    {
        $connect = $this->db1->connect_db();
        if ($connect) {
            $query = "select p.IDSanPham,c.TenLoaiSanPham , p.TenSP , p.Mota , p.DonGia , p.IMG , k.SoLuong  from sanpham as p join loaisanpham as c on p.IDLoaiSanPham = c.IDLoaiSanPham  LEFT join kho as k on k.IDSanPham = p.IDSanPham where p.IDSanPham = ?";
            $stmt = $connect->prepare($query);
            $stmt->execute([$productID]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($result) {
                $this->db1->disconnect_db($connect);
                return $result[0];
            } else {
                $this->db1->disconnect_db($connect);
                return false;
            }
        } else {
            return false;
        }
    }

    public function get_Random_Products()
    {
        $connect = $this->db1->connect_db();
        if ($connect) {
            $query = 'SELECT p.IDSanPham,c.TenLoaiSanPham , p.TenSP , p.Mota , p.DonGia , p.IMG   FROM sanpham AS p JOIN loaisanpham AS c ON p.IDLoaiSanPham = c.IDLoaiSanPham ORDER BY RAND() LIMIT 4';
            $stmt = $connect->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($result) {
                $this->db1->disconnect_db($connect);
                return $result;
            } else {
                $this->db1->disconnect_db($connect);
                return false;
            }
        } else {
            return false;
        }
    }

    public function updateProduct($data, $IDSanPham)
    {
        $connect = $this->db1->connect_db();
        if ($connect) {
            $query = 'UPDATE SanPham SET ';
            $query_value = array();
            $SoLuong = null;
            $update_quantity = null;
            $result1 = true; // Khởi tạo mặc định là true để tránh lỗi cảnh báo

            foreach ($data as $key => $value) {
                if ($key == "SoLuong") {
                    $SoLuong = $value;
                    $update_quantity = "UPDATE Kho SET SoLuong = ? WHERE IDSanPham = ?";
                    continue; // Bỏ qua việc thêm vào câu truy vấn `SanPham`
                }
                $query .= " $key = ?,";
                $query_value[] = $value;
            }

            // Cập nhật số lượng trong bảng `Kho` nếu có
            if (isset($SoLuong)) {
                try {
                    $stmt2 = $connect->prepare($update_quantity);
                    $result1 = $stmt2->execute([$SoLuong, $IDSanPham]);
                } catch (PDOException $e) {
                    echo "Error updating quantity: " . $e->getMessage();
                    return false;
                }
            }

            // Kiểm tra nếu có dữ liệu để cập nhật trong bảng `SanPham`
            if (!empty($query_value)) {
                $query = rtrim($query, ','); // Bỏ dấu phẩy cuối cùng
                $query .= " WHERE IDSanPham = ?";
                $query_value[] = $IDSanPham;

                try {
                    $stmt = $connect->prepare($query);
                    $result = $stmt->execute($query_value);

                    // Trả về true nếu cả hai cập nhật đều thành công hoặc chỉ có cập nhật bảng SanPham
                    return $result && $result1;
                } catch (PDOException $e) {
                    echo "Error updating product: " . $e->getMessage();
                    return false;
                }
            }

            // Trường hợp chỉ có cập nhật bảng `Kho`
            return $result1;
        } else {
            return false;
        }
    }


    public function get_All_Category()
    {
        $connect = $this->db1->connect_db();
        if ($connect) {
            $query = 'SELECT * FROM LoaiSanPham';
            $stmt = $connect->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($result) {
                $this->db1->disconnect_db($connect);
                return $result;
            } else {
                $this->db1->disconnect_db($connect);
                return false;
            }
        } else {
            return false;
        }
    }

    public function delete_Product($IDSanPham)
    {
        $connect = $this->db1->connect_db();
        if ($connect) {
            $query = 'DELETE FROM SanPham WHERE IDSanPham = ?';
            $stmt = $connect->prepare($query);
            $result = $stmt->execute([$IDSanPham]);
            if ($result) {

                if ($stmt->rowCount() > 0) {
                    $this->db1->disconnect_db($connect);
                    return true;
                } else {
                    $this->db1->disconnect_db($connect);
                    return false;
                }
            } else {
                $this->db1->disconnect_db($connect);
                return false;
            }
        } else {
            return false;
        }
    }

    public function add_Product($data, $quantity)
    {
        $connect = $this->db1->connect_db();
        if ($connect) {
            try {
                // Chèn dữ liệu vào bảng SanPham
                $query = 'INSERT INTO SanPham  VALUES (null,?, ?, ?, ?, ?, ? , ?)';
                $stmt = $connect->prepare($query);
                $stmt->execute([$data["IDLoaiSanPham"], $data["TenSP"], $data["Mota"], $data["DonGia"], $data["IMG"], $data["Discount"], $data["DaBan"]]);
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
        } else {
            return false;
        }
    }

}