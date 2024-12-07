<?php
require_once(__DIR__ . '/control.php');
require_once(__DIR__ . '/../models/schedule_employee.php');

class Schedule_employee_Controller extends Control
{
    private $model;

    public function __construct()
    {
        $this->model = new Schedule_employee();
        parent::__construct($_SERVER['HTTP_AUTHORIZATION'] ?? null);
    }

    // Thêm lịch làm việc
    public function add()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendResponse(404, ['error' => 'Đường dẫn không tồn tại']);
            return;
        }

        $auth = $this->authenticate_admin();
        if (!$auth) {
            $this->sendResponse(403, ['error' => 'Lỗi xác thực']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $id = $this->modelAuth->get_IDNhanVien($data['employee_username']);
        if (!isset($data['id'], $data['date'], $data['shift'], $data['note'])) {
            $this->sendResponse(400, ['error' => 'Dữ liệu không hợp lệ']);
            return;
        }
        $id = $data['id'];
        $date = $data['date'];
        $shift = $data['shift'];
        $note = $data['note'];
        $formatted_date = DateTime::createFromFormat('d-m-Y', $date);
        $date_for_db = $formatted_date->format('Y-m-d');
        $result = $this->model->insert_schedule($id, $date_for_db, $shift, $note);

        if ($result) {
            $this->sendResponse(200, ['success' => 'Thêm thành công']);
        } else {
            $this->sendResponse(500, ['error' => 'Lỗi hệ thống']);
        }
    }

    // Lấy lịch làm việc cho nhân viên (theo ID)
    public function get_employee_schedule()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendResponse(404, ['error' => 'Đường dẫn không tồn tại']);
            return;
        }

        $auth = $this->authenticate_employee();
        if (!$auth) {
            $this->sendResponse(403, ['error' => 'Lỗi xác thực']);
            return;
        }
        $username = $this->jwt->getUsername();
        $id = $this->modelAuth->get_IDNhanVien($username);
        if (!$id) {
            $this->sendResponse(400, ['error' => 'Thiếu ID nhân viên']);
            return;
        }
        $result = $this->model->schedule_employee($id);

        if ($result) {
            $this->sendResponse(200, $result);
        } else {
            $this->sendResponse(404, ['error' => 'Không tìm thấy dữ liệu']);
        }
    }

    // Lấy tất cả lịch làm việc (dành cho admin)
    public function get_all_schedules()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendResponse(404, ['error' => 'Đường dẫn không tồn tại']);
            return;
        }

        $auth = $this->authenticate_admin();
        if (!$auth) {
            $this->sendResponse(403, ['error' => 'Lỗi xác thực']);
            return;
        }

        $result = $this->model->get_all_schedules();
        if ($result) {
            $this->sendResponse(200, $result);
            return;
        } else {
            $this->sendResponse(404, ['error' => 'Không tìm thấy dữ liệu']);
            return;
        }
    }
}
