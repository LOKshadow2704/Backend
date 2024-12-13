<?php
require(__DIR__."/vendor/autoload.php");
header("content-Type:application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: http://localhost:3000");
header('Access-Control-Allow-Credentials: true');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept,X-CSRF-Token,Authorization,PHPSESSID");

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Xử lý yêu cầu OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Methods: GET, POST , PUT, OPTIONS ,DELETE");
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept,X-CSRF-Token, Authorization,PHPSESSID");
    header("Access-Control-Allow-Origin: http://localhost:3000");
    header("Access-Control-Max-Age: 3600");
    header("Content-Length: 0");
    header("Content-Type: text/plain");
    die();
}

define('ROOT_PATH', __DIR__);
define('ROUTES_PATH', ROOT_PATH . '/routes');

require_once ROUTES_PATH . '/routes.php';

$requestUrl = $_SERVER['REQUEST_URI'];
handleRequest($requestUrl);
