<?php
    require_once('connect_db.php');
    class model_checkin{
        private $db;
        public function __construct(){
            $this->db  = new Database;
        }
        public function statistical(){
            $connect = $this -> db ->connect_db();
            if($connect){
                $query = "SELECT ThoiGian, CheckOut FROM checkin ORDER BY `ThoiGian` DESC" ;
                $stmt = $connect->prepare(  $query );
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
    }