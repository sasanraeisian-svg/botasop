<?php

if ($text == '🏠 حساب کاربری') {

    $userChantId  = $from_id;
    $userJoinTime = jdate('H:i - Y/m/d', strtotime($currentUser->created_at));
    $userBalance  = $currentUser->balance;

    $query = "SELECT COUNT(*) FROM `referrals` WHERE `referrer_id` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$from_id]);
    $totalReferrals = $stmt->fetchColumn();

    $queryVerified = "SELECT COUNT(*) FROM `referrals` WHERE `referrer_id` = ? AND `is_verified` = 1";
    $stmtVerified = $pdo->prepare($queryVerified);
    $stmtVerified->execute([$from_id]);
    $verifiedReferrals = $stmtVerified->fetchColumn();

    $queryUnverified = "SELECT COUNT(*) FROM `referrals` WHERE `referrer_id` = ? AND `is_verified` = 0";
    $stmtUnverified = $pdo->prepare($queryUnverified);
    $stmtUnverified->execute([$from_id]);
    $unverifiedReferrals = $stmtUnverified->fetchColumn();

    $responseText = "جزئیات حساب کاربری ($from_id) 👇

💼 موجودی کیف پول شما: $userBalance تومان
🗓 تاریخ ثبت‌نام: $userJoinTime

👥 تعداد زیرمجموعه‌های شما: $totalReferrals
✅ تعداد زیرمجموعه‌های احراز شده: $verifiedReferrals
❌ تعداد زیرمجموعه‌های احراز نشده: $unverifiedReferrals
    ";

    sendMessage($from_id, $responseText, json_encode([
        'inline_keyboard' => [
            [['text' => 'کپی کردن شناسه عددی', 'copy_text' => ['text' => (string) $from_id]]]
        ]
    ]));
    die;
}
