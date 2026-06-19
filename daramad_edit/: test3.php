<?php
echo "=== TEST START ===\n";

include "config/configs.php";
include "config/updates.php";
include "config/keyboards.php";
include "function/functions.php";
include "function/jdf.php";

echo "Includes loaded OK\n";
echo "REMOTE_ADDR=" . ($_SERVER['REMOTE_ADDR'] ?? 'NONE') . "\n";

$fake_text = '/start';
$fake_from_id = 692466131;
$fake_type = 'private';

echo "Simulating query...\n";
$query = "SELECT * FROM `users` WHERE `chat_id` = ?";
$stmt  = $pdo->prepare($query);
$stmt->execute([$fake_from_id]);
$currentUser = $stmt->fetch();
echo "Query executed OK. currentUser = " . ($currentUser ? "FOUND" : "NOT FOUND (null)") . "\n";

echo "Testing sendMessage function exists: " . (function_exists('sendMessage') ? 'YES' : 'NO') . "\n";

echo "=== TEST END ===\n";
