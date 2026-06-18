<?php
chdir(__DIR__);

require "../config/configs.php";
require "../function/functions.php";

$limit = 100;
$success_count = 0;
$fail_count = 0;

$forward_all = $pdo->prepare("SELECT * FROM `forward_all` WHERE `status` = 0 LIMIT :limit");
$forward_all->bindValue(':limit', $limit, PDO::PARAM_INT);
$forward_all->execute();

if ($forward_all->rowCount() == 0) {
    die("هیچ پیامی برای فوروارد وجود ندارد.");
}
$delete_stmt = $pdo->prepare("DELETE FROM `forward_all` WHERE `id` = :id");

while ($row = $forward_all->fetch()) {

    $from_id = $row->from_id;
    $message_id = $row->message_id;
    $user_id = $row->user_id;
    $id = $row->id;

    $response = forwardMessage($user_id, $from_id, $message_id);

    if ($response->ok) {
        $success_count++;
    } else {
        $fail_count++;
    }
    $delete_stmt->execute(['id' => $id]);
}

echo "پیام شما ارسال گردید!\n";
echo "✅ موفق: $success_count\n";
echo "❌ ناموفق: $fail_count\n";

die;
