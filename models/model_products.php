<?php
require_once('connect_db.php');

class model_product
{
    private $db1;

    public function __construct()
    {
        $this->db1 = new Database;
    }

    // Hàm kết nối cơ sở dữ liệu, để đơn giản hoá việc tái sử dụng
    private function connectDB()
    {
        return $this->db1->connect_db();
    }

    // Lấy tất cả sản phẩm
    public function get_All_Products()
    {
        $connect = $this->connectDB();
        if ($connect) {
            $query = 'SELECT p.IDSanPham, c.TenLoaiSanPham, p.TenSP, p.Mota, p.DonGia, p.IMG, k.SoLuong, p.IDLoaiSanPham, p.Discount, p.DaBan
                      FROM sanpham AS p
                      JOIN loaisanpham AS c ON p.IDLoaiSanPham = c.IDLoaiSanPham
                      LEFT JOIN kho AS k ON k.IDSanPham = p.IDSanPham';
            $stmt = $connect->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->db1->disconnect_db($connect);
            return $result ?: [];
        }
        return false;
    }

    // Lấy thông tin một sản phẩm dựa trên ID
    public function get_One_Products($productID)
    {
        $connect = $this->connectDB();
        if ($connect) {
            $query = 'SELECT p.IDSanPham, c.TenLoaiSanPham, p.TenSP, p.Mota, p.DonGia, p.IMG, k.SoLuong, p.IDLoaiSanPham, p.Discount, p.DaBan
                      FROM sanpham AS p
                      JOIN loaisanpham AS c ON p.IDLoaiSanPham = c.IDLoaiSanPham
                      LEFT JOIN kho AS k ON k.IDSanPham = p.IDSanPham
                      WHERE p.IDSanPham = ?';
            $stmt = $connect->prepare($query);
            $stmt->execute([$productID]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->db1->disconnect_db($connect);
            return $result ?: false;
        }
        return false;
    }

    // Lấy 4 sản phẩm ngẫu nhiên
    public function get_Random_Products()
    {
        $connect = $this->connectDB();
        if ($connect) {
            $query = 'SELECT p.IDSanPham, c.TenLoaiSanPham, p.TenSP, p.Mota, p.DonGia, p.IMG
                      FROM sanpham AS p
                      JOIN loaisanpham AS c ON p.IDLoaiSanPham = c.IDLoaiSanPham
                      ORDER BY RAND() LIMIT 4';
            $stmt = $connect->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->db1->disconnect_db($connect);
            return $result ?: [];
        }
        return false;
    }

    // Cập nhật sản phẩm
    public function updateProduct($data, $IDSanPham)
    {
        $connect = $this->connectDB();
        if (!$connect)
            return false;

        try {
            $connect->beginTransaction();

            // Cập nhật bảng Kho nếu có số lượng
            if (isset($data['SoLuong'])) {
                $stmt2 = $connect->prepare("UPDATE kho SET SoLuong = ? WHERE IDSanPham = ?");
                $stmt2->execute([$data['SoLuong'], $IDSanPham]);
                unset($data['SoLuong']);
            }

            // Chuẩn bị câu truy vấn cho bảng SanPham
            if (!empty($data)) {
                $fields = array_map(fn($key) => "$key = ?", array_keys($data));
                $query = 'UPDATE SanPham SET ' . implode(', ', $fields) . ' WHERE IDSanPham = ?';
                $stmt = $connect->prepare($query);
                $stmt->execute(array_merge(array_values($data), [$IDSanPham]));
            }

            $connect->commit();
            return true;
        } catch (PDOException $e) {
            $connect->rollBack();
            error_log("Error updating product: " . $e->getMessage());
            return false;
        }
    }

    // Xóa sản phẩm
    public function delete_Product($IDSanPham)
    {
        $connect = $this->connectDB();
        if ($connect) {
            $query = 'DELETE FROM SanPham WHERE IDSanPham = ?';
            $stmt = $connect->prepare($query);
            $result = $stmt->execute([$IDSanPham]);

            $this->db1->disconnect_db($connect);
            return ($stmt->rowCount() > 0);
        }
        return false;
    }

    // Thêm sản phẩm mới
    public function add_Product($data, $quantity)
    {
        $connect = $this->connectDB();
        if (!$connect)
            return false;

        try {
            $connect->beginTransaction();

            // Thêm vào bảng SanPham
            $query = 'INSERT INTO SanPham (IDLoaiSanPham, TenSP, Mota, DonGia, IMG, Discount, DaBan) VALUES (?, ?, ?, ?, ?, 0, 0)';
            $stmt = $connect->prepare($query);
            $stmt->execute([$data["IDLoaiSanPham"], $data["TenSP"], $data["Mota"], $data["DonGia"], $data["IMG"]]);
            $lastInsertedId = $connect->lastInsertId();

            // Thêm vào bảng Kho
            $query_quantity = "INSERT INTO kho (IDSanPham, SoLuong) VALUES (?, ?)";
            $stmt_quantity = $connect->prepare($query_quantity);
            $stmt_quantity->execute([$lastInsertedId, $quantity]);

            $connect->commit();
            return $lastInsertedId;
        } catch (PDOException $e) {
            $connect->rollBack();
            error_log("Error adding product: " . $e->getMessage());
            return false;
        }
    }

    public function minute_one()
    {
        $connect = $this->db1->connect_db();
        if ($connect) {
            $stmt = $connect->prepare("UPDATE kho SET SoLuong = SoLuong - 1 WHERE IDSanPham = ?");
            $result = $stmt->execute();
            if ($result) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

}
