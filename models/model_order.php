<?php
require_once("connect_db.php");
class model_order
{
    private $db;
    private $IDDonHang;
    private $IDKhachHang;
    private $IDHinhThuc;
    private $NgayDat;
    private $NgayGiaoDuKien;
    private $TrangThaiThanhToan;
    private $DiaChi;
    private $ThanhTien;
    public function __construct($IDDonHang = null, $IDKhachHang = null, $IDHinhThuc = null, $DiaChi = null, $ThanhTien = null)
    {
        $this->db = new Database();
        $this->IDDonHang = $IDDonHang;
        $this->IDKhachHang = $IDKhachHang;
        $this->IDHinhThuc = $IDHinhThuc;
        $this->NgayDat = date("Y-m-d");
        $this->NgayGiaoDuKien = (new DateTime())->modify('+3 days')->format("Y-m-d");
        $this->TrangThaiThanhToan = "Chưa thanh toán";
        $this->DiaChi = $DiaChi;
        $this->ThanhTien = $ThanhTien;
    }

    public function Order()
    {
        $connect = $this->db->connect_db();
        if ($connect) {
            $query = "INSERT into donhang values (?,?,?,?,?,?,?,?,'Chưa xác nhận')";
            $stmt = $connect->prepare($query);
            $result = $stmt->execute(
                [
                    $this->IDDonHang,
                    $this->IDKhachHang,
                    $this->IDHinhThuc,
                    $this->NgayDat,
                    $this->NgayGiaoDuKien,
                    $this->TrangThaiThanhToan,
                    $this->DiaChi,
                    $this->ThanhTien
                ]
            );
            if ($result) {
                return intval($connect->lastInsertId());
            } else {
                return false;
            }
        }
    }

    public function get_All_Purchase($IDKhachHang)
    {
        $connect = $this->db->connect_db();
        if ($connect) {
            $query = "SELECT * FROM donhang WHERE IDKhachHang = ?";
            $stmt = $connect->prepare($query);
            $stmt->execute([$IDKhachHang]);
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

    public function UpdatePaymentStatus($IDDonHang)
    {
        $connect = $this->db->connect_db();
        if ($connect) {
            $query = "UPDATE donhang SET TrangThaiThanhToan = 'Đã Thanh Toán' WHERE IDDonHang = ?";
            $stmt = $connect->prepare($query);
            $result = $stmt->execute([$IDDonHang]);
            if ($result) {
                $this->db->disconnect_db($connect);
                return $result;
            } else {
                $this->db->disconnect_db($connect);
                return false;
            }
        }
    }

    public function get_Purchase_unconfimred()
    {
        $connect = $this->db->connect_db();
        if ($connect) {
            $query = "SELECT * FROM donhang WHERE TrangThai LIKE 'Chưa xác nhận'";
            $stmt = $connect->prepare($query);
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

    public function Purchase_confirm($IDDonHang)
    {
        $connect = $this->db->connect_db();
        if ($connect) {
            $query = "UPDATE donhang SET TrangThai = 'Đã xác nhận' WHERE IDDonHang = ?";
            $stmt = $connect->prepare($query);
            $result = $stmt->execute([$IDDonHang]);
            if ($result) {
                $this->db->disconnect_db($connect);
                return $result;
            } else {
                $this->db->disconnect_db($connect);
                return false;
            }
        }
    }

    public function delete_order($IDDonHang)
    {
        $connect = $this->db->connect_db();
        if ($connect) {
            $query = "DELETE FROM donhang WHERE IDDonHang =?";
            $stmt = $connect->prepare($query);
            $result = $stmt->execute([$IDDonHang]);
            if ($result) {
                $this->db->disconnect_db($connect);
                return $result;
            } else {
                $this->db->disconnect_db($connect);
                return false;
            }
        }
    }

    public function purchase()
    {
        $connect = $this->db->connect_db();
        if ($connect) {
            $query = "SELECT * FROM DonHang";
            $stmt = $connect->prepare($query);
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

    public function dashboard_orderdata()
    {
        $connect = $this->db->connect_db();
        if ($connect) {
            $query = "SELECT NgayDat, TrangThai, COUNT(*) AS SoLuongDonHang, SUM(ThanhTien) AS DoanhThu
                      FROM donhang GROUP BY NgayDat, TrangThai";
            $stmt = $connect->prepare($query);
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


}