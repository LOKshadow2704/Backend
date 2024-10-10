<?php
require_once("connect_db.php");

class Model_employee {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function get_all_employee_with_roles() {
    $connect = $this->db->connect_db();
    if ($connect) {
        $query = 'SELECT 
                     tk.HoTen, 
                     tk.Email, 
                     tk.SDT,
					 tk.TrangThai,
					 tk.TenDangNhap,
                     CASE 
                        WHEN tk.IDVaiTro = 2 THEN "Lễ tân" 
                        WHEN tk.IDVaiTro = 3 THEN hlv.DichVu 
                     END AS DichVu
                  FROM taikhoan tk
                  LEFT JOIN khachhang kh ON tk.TenDangNhap = kh.TenDangNhap
                  LEFT JOIN hlv ON kh.IDHLV = hlv.IDHLV
                  WHERE (tk.IDVaiTro = 2) 
                     OR (tk.IDVaiTro = 3 AND kh.IDHLV IS NOT NULL)'; // Chỉ lấy khi có IDHLV

        $stmt = $connect->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->db->disconnect_db($connect);
        if ($result) {
            return $result;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

	public function get_employee_info($TenDangNhap) {
    $connect = $this->db->connect_db();
    if ($connect) {
        $query = "
            SELECT 
                tk.HoTen, 
                tk.Email, 
                tk.SDT, 
                h.DichVu, 
                h.GiaThue 
            FROM 
                taikhoan AS tk
            LEFT JOIN 
                khachhang AS kh ON tk.TenDangNhap = kh.TenDangNhap
            LEFT JOIN 
                hlv AS h ON kh.IDHLV = h.IDHLV 
            WHERE 
                tk.IDVaiTro IN (2, 3) 
                AND (tk.IDVaiTro != 3 OR (tk.IDVaiTro = 3 AND kh.IDHLV IS NOT NULL))
                AND tk.TenDangNhap = ?
        ";
        
        $stmt = $connect->prepare($query);
        $stmt->execute([$TenDangNhap]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->db->disconnect_db($connect);
        return $result;
    }
    return false;
}

	public function update_employee_info($TenDangNhap, $HoTen, $Email, $SDT, $DichVu) {
    $connect = $this->db->connect_db();
    if ($connect) {
        $query_check = "SELECT IDVaiTro FROM taikhoan WHERE TenDangNhap = ?";
        $stmt_check = $connect->prepare($query_check);
        $stmt_check->execute([$TenDangNhap]);
        $employee = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if ($employee) {
            $IDVaiTro = $employee['IDVaiTro'];
            $query_update = "UPDATE taikhoan SET HoTen = ?, Email = ?, SDT = ? WHERE TenDangNhap = ?";
            $stmt_update = $connect->prepare($query_update);
            $result_update = $stmt_update->execute([$HoTen, $Email, $SDT, $TenDangNhap]);
            if ($IDVaiTro == 3) {
                $query_hlv = "SELECT IDHLV FROM khachhang WHERE TenDangNhap = ?";
                $stmt_hlv = $connect->prepare($query_hlv);
                $stmt_hlv->execute([$TenDangNhap]);
                $khachhang = $stmt_hlv->fetch(PDO::FETCH_ASSOC);
                if ($khachhang && $khachhang['IDHLV']) {
                    $IDHLV = $khachhang['IDHLV'];
                    $query_service_update = "UPDATE hlv SET DichVu = ? WHERE IDHLV = ?";
                    $stmt_service_update = $connect->prepare($query_service_update);
                    $result_service_update = $stmt_service_update->execute([$DichVu, $IDHLV]);
                }
            } elseif ($IDVaiTro == 2) {
                $DichVu = "Lễ tân";
            }
            if ($result_update) {
                $this->db->disconnect_db($connect);
                return ['success' => 'Cập nhật thông tin nhân viên thành công.'];
            } else {
                $this->db->disconnect_db($connect);
                return ['error' => 'Cập nhật thông tin nhân viên không thành công.'];
            }
        } else {
            $this->db->disconnect_db($connect);
            return ['error' => 'Không tìm thấy nhân viên với TenDangNhap này.'];
        }
    }
    return ['error' => 'Kết nối cơ sở dữ liệu không thành công.'];
}

	public function delete_employee_info($TenDangNhap) {
    $connect = $this->db->connect_db();
    if ($connect) {
        try {
            $connect->beginTransaction();

            // Xóa từ bảng khachhang dựa trên TenDangNhap
            $query1 = 'DELETE FROM khachhang WHERE TenDangNhap = ?';
            $stmt1 = $connect->prepare($query1);
            $stmt1->execute([$TenDangNhap]);

            // Xóa từ bảng hlv dựa trên IDHLV (nếu cần xóa huấn luyện viên liên quan)
            $query2 = 'DELETE FROM hlv WHERE IDHLV = (SELECT IDHLV FROM khachhang WHERE TenDangNhap = ?)';
            $stmt2 = $connect->prepare($query2);
            $stmt2->execute([$TenDangNhap]);

            // Xóa từ bảng taikhoan
            $query3 = 'DELETE FROM taikhoan WHERE TenDangNhap = ?';
            $stmt3 = $connect->prepare($query3);
            $stmt3->execute([$TenDangNhap]);

            // Commit transaction sau khi tất cả truy vấn xóa thành công
            $connect->commit();
            $this->db->disconnect_db($connect);
            return true;

        } catch (Exception $e) {
            // Rollback nếu có lỗi
            $connect->rollBack();
            $this->db->disconnect_db($connect);
            return false;
        }
    }
    return false;
}

}
