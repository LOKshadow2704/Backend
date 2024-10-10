<?php
    require_once('connect_db.php');
    class model_gympack{
        private $db;
        private $IDGoiTap;
        private $TenGoiTap;
        private $ThoiHan;
        private $Gia;
        public function __construct($IDGoiTap = null , $TenGoiTap = null, $ThoiHan = null, $Gia = null ){
            $this->db = new Database;
            $this->IDGoiTap = $IDGoiTap;
            $this->TenGoiTap = $TenGoiTap;
            $this->ThoiHan = $ThoiHan;
            $this->Gia = $Gia;
        }
        public function get_All_gympack(){
            $connect = $this->db->connect_db();
            if($connect){
                $query = 'select * from goitap';
                $stmt = $connect->prepare($query);
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

        public function get_Info_Pack($IDPack){
            $connect = $this->db->connect_db();
            if($connect){
                $query = 'select * from goitap WHERE IDGoiTap = ?';
                $stmt = $connect->prepare($query);
                $stmt->execute([$IDPack]);
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

        public function Update_Pack($data){
            $connect = $this->db->connect_db();
            if($connect){
                $IDGoiTap = null;
                $query = 'UPDATE GoiTap SET ';
                $query_value = array();
                foreach ($data as $key => $value) {
                    if ($key == "IDGoiTap") {
                        $IDGoiTap = $value;
                        continue;
                    }
                    $query .= " $key = ?,";
                    $query_value[] = $value;
                }
                $query = rtrim($query, ',');
                $query .= " WHERE IDGoiTap = ?";
                $stmt = $connect->prepare($query);
                $query_value[] = $IDGoiTap;
                $result = $stmt->execute($query_value);
                if($result){
                    echo($query);
                    $this->db->disconnect_db($connect);
                    return $result;
                }else{
                    $this->db->disconnect_db($connect);
                    return false;
                }
            }
        }
		public function add_Pack($data) {
    $connect = $this->db->connect_db();
    if ($connect) {
        $query = 'INSERT INTO goitap (TenGoiTap, ThoiHan, Gia) VALUES (?, ?, ?)';
        $stmt = $connect->prepare($query);
        $result = $stmt->execute([$data['TenGoiTap'], $data['ThoiHan'], $data['Gia']]);
        
        if ($result) {
            $this->db->disconnect_db($connect);
            return true;
        } else {
            $this->db->disconnect_db($connect);
            return false;
        }
    }
    return false; // Nếu không thể kết nối
}
public function delete_Pack($IDGoiTap) {
    $connect = $this->db->connect_db();
    if ($connect) {
        $query = 'DELETE FROM goitap WHERE IDGoiTap = ?';
        $stmt = $connect->prepare($query);
        $result = $stmt->execute([$IDGoiTap]);

        if ($result) {
            $this->db->disconnect_db($connect);
            return true;
        } else {
            $this->db->disconnect_db($connect);
            return false;
        }
    }
    return false; // Nếu không thể kết nối
}


    }