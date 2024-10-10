<?php
    require_once(__DIR__ . "/../models/model_employee.php");
    class controll_employee{
		public static function controll_get_all_employee_with_roles() {
    		if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $model_employee = new model_employee();
        $result = $model_employee->get_all_employee_with_roles();
       		if ($result) {
            http_response_code(200);
            echo json_encode($result);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Không thể lấy dữ liệu']);
        }
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Đường dẫn không tồn tại']);
    }
}

		public static function controll_update_employee_info() {
    if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        $jwt = $_SERVER['HTTP_AUTHORIZATION'];
        $jwt = trim(str_replace('Bearer ', '', $jwt));
        $data = json_decode(file_get_contents('php://input'), true);
        $Auth = new JWT();
        $verify = $Auth->JWT_verify($jwt);
        if ($verify) {
            if (isset($data['TenDangNhap'], $data['HoTen'], $data['Email'], $data['SDT'], $data['DichVu'])) {
                $TenDangNhap = $data['TenDangNhap'];
                $HoTen = $data['HoTen'];
                $Email = $data['Email'];
                $SDT = $data['SDT'];
                $DichVu = $data['DichVu'];

                $employee_model = new Model_employee();
                $result = $employee_model->update_employee_info($TenDangNhap, $HoTen, $Email, $SDT, $DichVu);

                // Kiểm tra xem kết quả trả về có 'error' hay không
                if (isset($result['error'])) {
                    http_response_code(400);
                } else {
                    http_response_code(200);
                }

                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Thiếu dữ liệu TenDangNhap, HoTen, Email, SDT hoặc DichVu']);
            }
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Xác thực không thành công']);
        }
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Phương thức không được hỗ trợ']);
    }
}

		public static function controll_delete_employee_info() {
    if ($_SERVER['REQUEST_METHOD'] === "DELETE") {
        // Lấy JWT từ request để xác thực
        $jwt = $_SERVER['HTTP_AUTHORIZATION'];
        $jwt = trim(str_replace('Bearer ', '', $jwt));

        // Xác thực JWT
        $Auth = new JWT();
        $verify = $Auth->JWT_verify($jwt);

        if ($verify) {
            // Lấy dữ liệu TenDangNhap từ request
            $data = json_decode(file_get_contents('php://input'), true);
            if (isset($data['TenDangNhap'])) {
                $employee = new model_employee();
                $result = $employee->delete_employee_info($data['TenDangNhap']);
                
                if ($result) {
                    http_response_code(200); // OK
                    echo json_encode(['success' => 'Nhân viên đã được xóa thành công!']);
                } else {
                    http_response_code(500); // Internal Server Error
                    echo json_encode(['error' => 'Không thể xóa nhân viên.']);
                }
            } else {
                http_response_code(400); // Bad Request
                echo json_encode(['error' => 'Tên đăng nhập không hợp lệ.']);
            }
        } else {
            http_response_code(403); // Forbidden
            echo json_encode(['error' => 'Lỗi xác thực.']);
        }
    } else {
        http_response_code(404); // Not Found
        echo json_encode(['error' => 'Đường dẫn không tồn tại.']);
    }
}

	}