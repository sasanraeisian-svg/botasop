<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('Asia/Tehran');

const API_KEY = '1214289316:IOxAQjveWKEG-vGTi-uWBJSZf_6utZtRPxQ'; // توکن ربات
$adminsList   = [692466131]; // آیدی عددی ادمین ها
$botUserName  = 'Havijbkbot'; // نام کاربری ربات بدون @

$hostName = 'mysql.railway.internal';
$userName = 'root';
$password = 'KxgDFtVbarLSmxDhlIfsJvjbfktCWdSa';
$dbName   = 'railway';

try {
    $pdo = new PDO("mysql:host=$hostName;dbname=$dbName", $userName, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_general_ci"
    ]);
} catch (PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}
