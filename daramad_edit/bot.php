<?php

function isValidBaleIP($ip, $ranges)
{
    $ip_dec = (float) sprintf("%u", ip2long($ip));
    foreach ($ranges as $range) {
        $lower_dec = (float) sprintf("%u", ip2long($range['lower']));
        $upper_dec = (float) sprintf("%u", ip2long($range['upper']));
        if ($ip_dec >= $lower_dec && $ip_dec <= $upper_dec) {
            return true;
        }
    }
    return false;
}

$bale_ip_ranges = [
    ['lower' => '185.136.96.111', 'upper' => '185.136.99.111'],
    ['lower' => '185.88.153.138', 'upper' => '185.88.153.138'],
    ['lower' => '2.189.68.126', 'upper' => '2.189.68.126'],
];

if (!isValidBaleIP($_SERVER['REMOTE_ADDR'], $bale_ip_ranges)) {
    die("Access denied.");
}

include "config/configs.php";
include "config/updates.php";
include "config/keyboards.php";
include "function/functions.php";
include "function/jdf.php";

if (isset($text) && $type == 'group') {
    die;
}

$query = "SELECT * FROM `users` WHERE `chat_id` = ?";
$stmt  = $pdo->prepare($query);
$stmt->execute([$from_id]);
$currentUser = $stmt->fetch();

if ($currentUser->is_banned && !in_array($from_id, $adminsList)) {
    $responseText = "⚠️ شما از ربات مسدود شده اید!\n\nبرای رفع مشکل به پشتیبانی مراجعه کنید";
    sendMessage($from_id, $responseText);
    return;
}

include "commands/start_command.php";
include "commands/invite_command.php";
include "commands/user_command.php";
include "commands/ranking_command.php";
include "commands/withdraw_command.php";
include "commands/history_command.php";
include "commands/other_command.php";
include "admin/admin.php";
