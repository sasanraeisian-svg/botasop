<?php
chdir(__DIR__);

require "../config/configs.php";
require "../function/functions.php";

$limit = 100;
$send_all = $pdo->prepare("SELECT * FROM `send_all` WHERE `status` = 0 LIMIT :limit");
$send_all->bindValue(':limit', $limit, PDO::PARAM_INT);
$send_all->execute();

if ($send_all->rowCount() == 0) {
    die("هیچ پیامی برای ارسال وجود ندارد.");
}

$delete_stmt = $pdo->prepare("DELETE FROM `send_all` WHERE `id` = :id");

$success_count = 0;
$fail_count = 0;

while ($row = $send_all->fetch()) {
    $user_id = $row->user_id;
    $text = $row->text;
    $id = $row->id;

    $response = sendMessage($user_id, $text);

    if (isset($response->ok) && $response->ok) {
        $success_count++;
    } else {
        $fail_count++;
    }

    $delete_stmt->execute(['id' => $id]);
}

echo "پیام ها با موفقیت ارسال شدند!\n";
echo "✅ موفق: $success_count\n";
echo "❌ ناموفق: $fail_count\n";

die;
