<?php
echo "Step A: start\n";

include "config/configs.php";
include "config/updates.php";
include "config/keyboards.php";
include "function/functions.php";
include "function/jdf.php";

echo "Step B: includes done\n";
echo "text=" . ($text ?? 'UNSET') . " | type=" . ($type ?? 'UNSET') . " | from_id=" . ($from_id ?? 'UNSET') . " | chat_type=" . ($chat_type ?? 'UNSET') . "\n";

$text = '/start';
$from_id = 692466131;
$chat_id = 692466131;
$type = 'private';
$chat_type = 'private';
$first_name = 'Test';
$user_name = 'testuser';
$message_id = 1;

echo "Step C: forced fake values set\n";

if (isset($text) && $type == 'group') {
    echo "WOULD DIE HERE (group check)\n";
}

$query = "SELECT * FROM `users` WHERE `chat_id` = ?";
$stmt  = $pdo->prepare($query);
$stmt->execute([$from_id]);
$currentUser = $stmt->fetch();
echo "Step D: currentUser query done. Found=" . ($currentUser ? 'YES' : 'NO') . "\n";

if ($currentUser && $currentUser->is_banned && !in_array($from_id, $adminsList)) {
    echo "WOULD DIE HERE (banned check)\n";
}

echo "Step E: about to include start_command.php\n";

try {
    include "commands/start_command.php";
    echo "Step F: start_command.php finished WITHOUT die\n";
} catch (Throwable $e) {
    echo "ERROR in start_command.php: " . $e->getMessage() . " on line " . $e->getLine() . "\n";
}

echo "Step G: TEST COMPLETE\n";
