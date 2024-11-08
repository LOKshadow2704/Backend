<?php
class RateLimitMiddleware
{
    private $limit;
    private $interval;

    public function __construct($limit = 4, $interval = 1)
    {
        $this->limit = $limit;
        $this->interval = $interval;
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function handle($route)
    {
        $clientIP = $_SERVER['REMOTE_ADDR'];
        $key = "rate_limit:{$route}:{$clientIP}";
        $currentTime = time();
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [];
        }
        $timestamps = $_SESSION[$key];
        $timestamps = array_filter($timestamps, function ($timestamp) use ($currentTime) {
            return ($currentTime - $timestamp) < $this->interval;
        });
        if (count($timestamps) >= $this->limit) {
            http_response_code(429);
            header('Content-Type: application/json');
            echo json_encode([
                "status" => "error",
                "message" => "Bạn đã vượt quá giới hạn yêu cầu. Vui lòng thử lại sau.",
                "retry_after" => $this->interval
            ]);
            return false;
        }

        $_SESSION[$key][] = $currentTime;

        if (count($_SESSION[$key]) > $this->limit) {
            array_shift($_SESSION[$key]);
        }

        return true;
    }
}

