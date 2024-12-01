<?php
require_once("connect_db.php");
class Model_warehouse
{
    private $db;
    private $IDSanPham;
    private $SoLuong;

    public function __construct($IDSanPham, $SoLuong = 0)
    {
        $this->db = new Database();
        $this->IDSanPham = $IDSanPham;
        $this->SoLuong = $SoLuong;
    }


    public function updateQuantity($newQuantity)
    {
        $connect = $this->db->connect_db();
        if ($connect) {
            $query = "UPDATE kho SET SoLuong = SoLuong - ? WHERE IDSanPham = ?";
            $stmt = $connect->prepare($query);
            $result = $stmt->execute([$newQuantity, $this->IDSanPham]);
            return $result;
        } else {
            return false;
        }
    }

}