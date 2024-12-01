<?php
require_once("connect_db.php");
class Model_invoice_pack
{
    private $db;
    private $IDHoaDon;
    private $IDGoiTap;
    private $IDKhachHang;
    private $TrangThaiThanhToan;
    private $NgayDangKy;
    private $NgayHetHan;

    public function __construct($IDGoiTap = null, $IDKhachHang = null, $ThoiHan = null, $TrangThaiThanhToan = null)
    {
        $this->db = new Database;
        $this->IDHoaDon = null;
        $this->IDGoiTap = $IDGoiTap;
        $this->IDKhachHang = $IDKhachHang;
        if ($TrangThaiThanhToan) {
            $this->TrangThaiThanhToan = $TrangThaiThanhToan;
        } else {
            $this->TrangThaiThanhToan = "Chưa thanh toán";
        }
        $this->NgayDangKy = date("Y-m-d");
        $ThoiHan = (int) $ThoiHan;
        $this->NgayHetHan = (new DateTime())->modify("+$ThoiHan days")->format("Y-m-d");
    }

    public function get_PackofCustomer($userID)
    {
        $connect = $this->db->connect_db();
        if ($connect) {
            $query = "SELECT * FROM hoadonthuegoitap WHERE IDKhachHang =?";
            $stmt = $connect->prepare($query);
            $stmt->execute([$userID]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        }
    }

    public function add_Invoice()
    {
        $connect = $this->db->connect_db();
        if ($connect) {
            $query = "INSERT INTO hoadonthuegoitap VALUES (NULL, ?, ?, ?, ?, ?)";
            $stmt = $connect->prepare($query);
            $result = $stmt->execute([
                $this->IDKhachHang,
                $this->IDGoiTap,
                $this->TrangThaiThanhToan,
                $this->NgayDangKy,
                $this->NgayHetHan
            ]);
            if ($result) {
                return intval($connect->lastInsertId());
            } else {
                return false;
            }
        }
    }

    public function updateInvoiceStatus($IDHoaDon)
    {
        $connect = $this->db->connect_db();
        if ($connect) {
            $query = "UPDATE hoadonthuegoitap SET TrangThaiThanhToan = 'Đã Thanh Toán' WHERE IDHoaDon = ?";
            $stmt = $connect->prepare($query);
            $result = $stmt->execute([$IDHoaDon]);
            if ($result) {
                $this->db->disconnect_db($connect);
                return $result;
            } else {
                $this->db->disconnect_db($connect);
                return false;
            }
        }
    }

    public function delete_invoice_packgym($IDHoaDon)
    {
        $connect = $this->db->connect_db();
        if ($connect) {
            $query = 'DELETE FROM hoadonthuegoitap WHERE IDHoaDon = ?';
            $stmt = $connect->prepare($query);
            $stmt->execute([$IDHoaDon]);

            if ($stmt->rowCount()) {
                $this->db->disconnect_db($connect);
                return true;
            } else {
                $this->db->disconnect_db($connect);
                return false;
            }
        }
        return false;
    }

    public function getIDInvoice()
    {

    }


}