<?php
require("../vendor/autoload.php");

$openapi = \OpenApi\Generator::scan([$_SERVER['DOCUMENT_ROOT'] . '/Backend/controllers']);

header('Content-Type: application/json');
echo $openapi->toJson();