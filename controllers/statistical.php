<?php
require_once(__DIR__ . '/control.php');
require_once(__DIR__ . "/../models/model_auth.php");
require_once(__DIR__ . "/../models/model_order.php");
require_once(__DIR__ . "/../models/model_invoice_pt.php");

class Statistical extends Control
{
    public function __construct(){
        parent::__construct($_SERVER['HTTP_AUTHORIZATION'] ?? null);
    }
    public function dashboard_data()
    {
        // Xác thực người dùng một lần trong phương thức này
        $auth = $this->authenticate_admin();
        if (!$auth) {
            http_response_code(403);
            echo json_encode(['error' => 'Lỗi xác thực']);
            return;
        }

        // Nếu xác thực thành công, tiếp tục lấy dữ liệu
        try {
            $userData = $this->user_data();       // Gọi user_data
            $orderData = $this->order_data();     // Gọi order_data
            $gympackData = $this->gympack_data(); // Gọi gympack_data

            // Chuẩn bị dữ liệu trả về
            $response = [
                'user_data' => $userData,
                'order_data' => $orderData,
                'gympack_data' => $gympackData,
            ];

            // Trả về phản hồi JSON
            echo json_encode($response);
            return;

        } catch (Exception $e) {
            // Nếu có lỗi trong quá trình lấy dữ liệu
            echo json_encode(['error' => $e->getMessage()]);
            return;
        }
    }

    public function user_data()
    {
        $user = new model_auth();
        $result = $user->dashboard_userdata();
        return $result ?: []; // Trả về mảng rỗng nếu không có kết quả
    }

    public function order_data()
    {
        $order = new model_order();
        $result = $order->dashboard_orderdata();
        return $result ?: [];
    }

    public function gympack_data()
    {
        $order = new model_invoice_pt();
        $result = $order->dashboard_ptdata();
        return $result ?: [];
    }
}
