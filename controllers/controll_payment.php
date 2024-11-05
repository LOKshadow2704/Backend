<?php
require_once(__DIR__ . "/../models/model_order.php");
require_once(__DIR__ . "/../models/model_invoice_pack.php");
require_once(__DIR__ . "/../models/model_invoice_pt.php");
use PayOS\PayOS;
class Controll_payment extends PayOS
{
    public function __construct()
    {
        parent::__construct(getenv('Client_ID'), getenv('Api_Key'), getenv('Checksum_Key'));
    }
    public function create($info, $agent, $type)
    {
        $callback_url = "";
        if ($agent == "MOBILE_GOATFITNESS") {
            $YOUR_DOMAIN = getenv('host_order_mobile');
            switch ($type) {
                case "personal_trainer":
                    $callback_url = $YOUR_DOMAIN . "/Home/tabs/PersonalTrainer/Payment";
                case "gympack":
                    $callback_url = $YOUR_DOMAIN . "/Home/tabs/PackGym/Payment";
                case "product":
                    $callback_url = $YOUR_DOMAIN . "/Home/tabs/Products/Payment";
            }
        } else {
            $YOUR_DOMAIN = getenv('host_order');
            switch ($type) {
                case "product":
                    $success_url = $YOUR_DOMAIN . "/OrderPayment";
                    $cancle_url = $YOUR_DOMAIN . "/PaymentCancle";
                case "personal_trainer":
                    $success_url = $YOUR_DOMAIN . "/OrderPayment";
                    $cancle_url = $YOUR_DOMAIN . "/PaymentCancle";
                case "gympack":
                    $success_url = $YOUR_DOMAIN . "/OrderPayment";
                    $cancle_url = $YOUR_DOMAIN . "/PaymentCancle";
            }
        }
        $data = [
            "orderCode" => $info['ID'],
            "amount" => $info['amount'],
            "description" => "Thanh toán GOAT FITNESS",
            'buyerName' => $info['name'],
            'buyerPhone' => $info['phone'],
            "returnUrl" => $callback_url,
            "cancelUrl" => $callback_url,
        ];
        $response = $this->createPaymentLink($data);
        return $response;
    }

}