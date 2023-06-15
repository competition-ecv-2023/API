<?php
define('ENVIRONMENT', 'development');
// define('ENVIRONMENT', 'production');
// $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT']."/patperdue";
if (isset($_GET['page']) && $_GET['page'] == "swagger") {
    require("v1/swagger/index.html");
} else {
    require("v1/index.php");
}