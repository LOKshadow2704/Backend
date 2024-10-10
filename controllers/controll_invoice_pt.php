<?php
require_once(__DIR__ . "/../models/model_PT.php");
require_once(__DIR__ . "/../models/model_invoice_pt.php");
require_once(__DIR__ . "/../models/model_auth.php");

class controll_invoice_pt {
    public static function control_get_ptByUser() {
        if ($_SERVER['REQUEST_METHOD'] === "GET") {
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt = trim(str_replace('Bearer ', '', $jwt));

            // Xác thực JWT
            $Auth = new JWT();
            $verify = $Auth->JWT_verify($jwt);
            if ($verify) {
                $user = new model_auth();
                $username = $Auth->getUserName($jwt);
                $cusID = $user->getIDKhachhang($username);

                // Kiểm tra tồn tại thuê PT của user
                $invoice_pt = new model_invoice_pt();
                $result = $invoice_pt->Exe_get_pt_details_byKhachHang($username);  // Lấy thông tin PT theo tên đăng nhập

                if ($result) {
                    http_response_code(200);
                    echo json_encode($result);
                } else {
                    http_response_code(403);
                    echo json_encode(['error' => 'Không có thông tin thuê PT']);
                }
            } else {
                http_response_code(403);
                echo json_encode(['error' => 'Lỗi xác thực']);
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Đường dẫn không tồn tại']);
        }
    }
	
	//Lấy thông tin dựa trên role nma dg lỗi(K set role thì lấy data bth)
	public static function control_get_pt() {
    if ($_SERVER['REQUEST_METHOD'] === "GET") {
        $invoice_pt = new model_invoice_pt();
        $result = $invoice_pt->Exe_get_pt_details();

        if ($result) {
            http_response_code(200);
            echo json_encode($result);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Không có thông tin thuê PT']);
        }
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Phương thức không hợp lệ']);
    }
}



}
