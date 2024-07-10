<?php
require_once("connect_db.php");
class model_auth{
    private $db;
    public function __construct(){
        $this->db = new Database();
    }

    private function Account($username){
        $connect = $this->db->connect_db(); 
        if($connect){
            $query  = "SELECT a.TenDangNhap , r.IDVaiTro , a.HoTen , a.DiaChi , a.Email , a.SDT , a.TrangThai , a.avt , r.TenVaiTro FROM TaiKhoan as a JOIN role as r ON a.IDVaiTro = r.IDVaiTro WHERE TenDangNhap LIKE ?";
            $stmt = $connect->prepare($query);
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if($user){
                return $user;
            }
        }
    }

    private function UserNamebyPhoneN($phonenumber){
        $connect = $this->db->connect_db(); 
        if($connect){
            $query  = "SELECT a.TenDangNhap , r.IDVaiTro , a.HoTen , a.DiaChi , a.Email , a.SDT , a.TrangThai , a.avt , r.TenVaiTro FROM TaiKhoan as a JOIN role as r ON a.IDVaiTro = r.IDVaiTro WHERE a.SDT = ?";
            $stmt = $connect->prepare($query);
            $stmt->execute([$phonenumber]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if($user){
                return $user["TenDangNhap"];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    
    

    private function KhachHang($username){
        $connect  = $this->db->connect_db(); 
        if($connect){
            // Sử dụng prepared statement để tránh SQL Injection
            $query  = "SELECT * FROM khachhang WHERE TenDangNhap LIKE ?";
            $stmt = $connect->prepare($query);
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if($user){
                return $user;
            }
        }
    }
    
    private function ExeLogin($username, $password){
        $connect  = $this -> db -> connect_db(); 
        $sha_key = getenv('SHA_KEY');
        $hashed_password = hash_hmac('sha256', $password, $sha_key);
        if($connect){
            $query = 'select t.HoTen, t.DiaChi , t.Email, t.SDT , r.TenVaiTro , t.avt , t.TrangThai from taikhoan as t inner join role as r on t.IDVaiTro = r.IDVaiTro  where TenDangNhap = :username and  MatKhau = :password';
            $params = array(':username' => $username, ':password' => $hashed_password);
            $stmt = $connect->prepare($query);
            $stmt->execute($params);
            $user = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if($user){
                $this->db -> disconnect_db($connect);
                return $user;
            }else{
                $this->db->disconnect_db($connect);
                return false;
            }
        }
    }

    private function ExeUpdateUserInfo($update_data, $username){
        $connect = $this -> db ->connect_db();
        if($connect){
            $query = "UPDATE TaiKhoan SET";
            $query_value = array();
            foreach($update_data as $key => $value ){
                $query .= " $key = ?,";
                $query_value[]  = $value;
            }
            $query = rtrim($query, ',');
            // Loại bỏ dấu phẩy cuối cùng trong câu truy vấn
            $query .= " WHERE TenDangNhap = ?";
            $query_value[] = $username;
            $stmt = $connect->prepare($query);
            $result = $stmt->execute($query_value);
            return $result;
        }
    }

    private function ExeUpdatePassword($currentPW, $newPW ,$username){
        $connect = $this -> db ->connect_db();
        $sha_key = getenv('SHA_KEY');
        $hash_currentPW = hash_hmac('sha256', $currentPW, $sha_key);
        $hash_newPW = hash_hmac('sha256', $newPW, $sha_key);
        if($connect){
            //Kiểm tra mật khẩu cũ
            $checkPW_query = "SELECT MatKhau FROM TaiKhoan WHERE TenDangNhap =?";
            $checkPW_stmt = $connect->prepare($checkPW_query);
            $checkPW_stmt->execute([$username]);
            $checkPW = $checkPW_stmt->fetch(PDO::FETCH_ASSOC);
            if($hash_currentPW == $checkPW["MatKhau"]){
                //Thực hiện thay đổi
                $query = "UPDATE TaiKhoan SET MatKhau = ? WHERE TenDangNhap = ?";
                $stmt = $connect->prepare($query);
                $result = $stmt->execute([$hash_newPW , $username]);
                if($result){
                    return "Đổi mật khẩu thành công";
                }else{
                    return "Đổi mật khẩu không thành công";
                }
            }else{
                return "Mật khẩu hiện tại không khớp";
            }
        }
    }

    private function ExeupdateUserAvt($link, $username){
        $connect = $this -> db ->connect_db();
        if($connect){
            $query = "UPDATE TaiKhoan SET avt = ? WHERE TenDangNhap = ?";
            $stmt = $connect->prepare($query);
            $result = $stmt->execute([$link , $username]);
            return $result;
        }
    }

    private function ExeSignup($data){
        $connect = $this -> db ->connect_db();
        $connect->beginTransaction();
        if($connect){
            $checkUser = "SELECT * FROM TaiKhoan WHERE TenDangNhap = ?";
            $stmt = $connect->prepare($checkUser);
            $stmt->execute([$data["username"]]);
            $check = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($check !== false) { 
                if(count($check) != 0){
                    return false;
                }
            }
            $sha_key = getenv('SHA_KEY');
            $hash_PW = hash_hmac('sha256', $data["password"], $sha_key);
            $query = "INSERT INTO TaiKhoan VALUE(? ,? , 3 , ? ,? ,? ,? , 0 ,'https://i.imgur.com/2MUWzRp.jpg')";
            $stmt = $connect->prepare($query);
            $result = $stmt->execute([$data["username"] ,$hash_PW , $data["fullname"] , $data["address"] , $data["email"] , $data["phone"]]);
            if(!$result){
                return false;
            }
            $query_cus = "INSERT INTO khachhang VALUE( null , ?, null)";
            $stmt_cus = $connect->prepare($query_cus);
            $result2 = $stmt_cus->execute([$data["username"]]);
            if(!$result2){
                return false;
            }
            $connect->commit();
            return $result;
        }
    }

    public function update_Status($status , $username){
        $connect = $this -> db ->connect_db();
        if($connect){
            $query = "UPDATE TaiKhoan SET TrangThai =? WHERE TenDangNhap =?";
            $query = rtrim($query, ',');
            $stmt = $connect->prepare($query);
            $result = $stmt->execute([$status ,$username]);
            return $result;
        }
    }

    public function user_training(){
        $connect = $this -> db ->connect_db();
        if($connect){
            $query =  "SELECT * FROM checkin WHERE ThoiGian = CURDATE() AND CheckOut = 0";
            $stmt = $connect->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        }
    }

    public function Employee_Working(){
        $connect = $this -> db ->connect_db();
        if($connect){
            $query =  "SELECT a.TenDangNhap , r.IDVaiTro , a.HoTen , a.DiaChi , a.Email , a.SDT , a.TrangThai , a.avt , r.TenVaiTro FROM TaiKhoan as a JOIN role as r ON a.IDVaiTro = r.IDVaiTro WHERE a.TrangThai LIKE 'Online' AND a.IDVaiTro = 2";
            $stmt = $connect->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        }
    }

    public function All_Account(){
        $connect = $this -> db ->connect_db();
        if($connect){
            $query =  "SELECT a.TenDangNhap , r.IDVaiTro , r.TenVaiTro FROM TaiKhoan as a JOIN role as r ON a.IDVaiTro = r.IDVaiTro";
            $stmt = $connect->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        }
    }

    public function Admin_Update_Account($data){
        $connect = $this -> db ->connect_db();
        if($connect){
            $query =  "UPDATE TaiKhoan SET IDVaiTro = ? WHERE TenDangNhap =?";
            $stmt = $connect->prepare($query);
            $result = $stmt->execute([$data["IDVaiTro"] , $data["TenDangNhap"]]);
            return $result;
        }
        
    }

    public function login($username, $password){
        return $this->ExeLogin($username, $password);
    }

    public function getIDKhachhang($username){
        $id =  $this->KhachHang($username);
        return $id['IDKhachHang'];
    }
    

    public function getKhachHang($username){
        return $this->KhachHang($username);
    }

    public function AccountInfo($username){
        return $this->Account($username);
    }

    public function updatePassword($currentPW, $newPW,$username){
        return $this->ExeUpdatePassword($currentPW, $newPW,$username);
    }

    public function updateUserInfo($update_data, $username){
        return $this->ExeUpdateUserInfo($update_data, $username,);
    }
    
    public function updateUserAvt($link, $username){
        return $this->ExeupdateUserAvt($link, $username);
    }
    
    public function Signup($data){
        return $this->ExeSignup($data);
    }

    public function getUserNamebyPhoneN($phonenumber){
        return $this->UserNamebyPhoneN($phonenumber);
    }

}