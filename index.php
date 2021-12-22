<?php
//require __DIR__ . "/inc/bootstrap.php";
 
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode( '/', $uri );

header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE, PUT');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
header('Content-Type: application/json');

header('Access-Control-Max-Age: 1728000');
header('Content-Length: 0');
header('Content-Type: text/plain');

if ((isset($uri[2]) && $uri[2] != 'manga' && $uri[2] != 'usuario') || !isset($uri[3])) {
    header("HTTP/1.1 404 Not Found");
    exit();
}
 
require "MangaController.php";
$objFeedController = new MangaController();
$strMethodName = $uri[3] . 'Action';
$objFeedController->{$strMethodName}();
?>