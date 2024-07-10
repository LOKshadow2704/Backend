<?php
    require_once(__DIR__ ."/../models/model_order.php");
    require_once(__DIR__ ."/../models/model_invoice_pack.php");
    require_once(__DIR__ ."/../models/model_invoice_pt.php");
    class Controll_payment{
        public static function returnPayment(){
            if($_SERVER['REQUEST_METHOD'] === "GET"){
                $vnp_HashSecret = "TALPOXXNXPJYNOGRZMWFZGAWWZUGOFRX";
                $vnp_SecureHash = $_GET['vnp_SecureHash'];
                $inputData = array();
                foreach ($_GET as $key => $value) {
                    if (substr($key, 0, 4) == "vnp_") {
                        $inputData[$key] = $value;
                    }
                }
                
                unset($inputData['vnp_SecureHash']);
                ksort($inputData);
                $i = 0;
                $hashData = "";
                foreach ($inputData as $key => $value) {
                    if ($i == 1) {
                        $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
                    } else {
                        $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                        $i = 1;
                    }
                }
        
                $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
                //Xử lý chuyển hướng
                if ($secureHash == $vnp_SecureHash) {
                    switch($_GET["link"]){
                        //Đặt hàng
                        case "order_product":
                            if ($_GET['vnp_ResponseCode'] == '00') {
                                $order = new model_order();
                                $update = $order->updatePaymentStatus($_GET["vnp_TxnRef"]);
                                if($update){
                                    header("Location: http://localhost:3000/Order?message=successfully");
                                    exit();
                                }else{
                                    header("Location: http://localhost:3000/Order?message=unsuccessfully");
                                    exit();
                                }
                            } 
                            else {
                                header("Location: http://localhost:3000/Order?message=unsuccessfully");
                                exit();
                            }
                        //Đăng ký gói tập
                        case "gympack":
                            if ($_GET['vnp_ResponseCode'] == '00') {
                                $order = new Model_invoice_pack();
                                $update = $order->updateInvoiceStatus($_GET["vnp_TxnRef"]);
                                if($update){
                                    header("Location: http://localhost:3000/GymPack?message=successfully");
                                    exit();
                                }else{
                                    header("Location: http://localhost:3000/GymPack?message=unsuccessfully");
                                    exit();
                                }
                            } 
                            else {
                                header("Location: http://localhost:3000/GymPack?message=unsuccessfully");
                                exit();
                            }
                        //Thuê PT
                        case "pt":
                            if ($_GET['vnp_ResponseCode'] == '00') {
                                $order = new model_invoice_pt();
                                $update = $order->updateInvoiceStatus($_GET["vnp_TxnRef"]);
                                if($update){
                                    header("Location: http://localhost:3000/PT?message=successfully");
                                    exit();
                                }else{
                                    header("Location:http://localhost:3000/PT?message=unsuccessfully");
                                    exit();
                                }
                            } 
                            else {
                                header("Location:http://localhost:3000/PT?message=unsuccessfully");
                                exit();
                            }

                    }
                } else {
                    switch($_GET["link"]){
                        case "order_product":
                            header("Location: http://localhost:3000/Order?message=unsuccessfully");
                            exit();
                        case "gympack":
                            header("Location: http://localhost:3000/GymPack?message=unsuccessfully");
                            exit();

                    }
                }
            }
        
        }
    }