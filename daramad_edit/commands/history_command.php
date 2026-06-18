<?php

if ($text == '📊 تاریخچه برداشت') {
    $query = "
        SELECT `amount`, `created_at`, `track_id`, `card`, `status`
        FROM `withdrawal_requests`
        WHERE `chat_id` = ?
        ORDER BY `created_at` DESC
        LIMIT 5
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$from_id]);
    $withdrawals = $stmt->fetchAll();

    $responseText = "📊 *تاریخچه برداشت‌ها*\n\n";

    if (empty($withdrawals)) {
        $responseText .= "❌ هیچ برداشت اخیر یافت نشد.";
    } else {
        foreach ($withdrawals as $withdrawal) {
            $amount = number_format($withdrawal->amount);
            $date = jdate("H:i - Y/m/d", strtotime($withdrawal->created_at));
            $transactionId = $withdrawal->track_id;
            $cardNumber = $withdrawal->card;
            $status = $withdrawal->status;

            switch ($status) {
                case 'confirme':
                    $statusText = "✅ تایید شده";
                    break;
                case 'rejected':
                    $statusText = "❌ رد شده";
                    break;
                case 'pending':
                default:
                    $statusText = "⏳ در صف انتظار";
                    break;
            }

            $responseText .= "🔹 *برداشت*\n";
            $responseText .= "💰 *مبلغ:* $amount تومان\n";
            $responseText .= "📅 *تاریخ:* $date\n";
            $responseText .= "🎟 *کد رهگیری:* $transactionId\n";
            $responseText .= "📌 *شماره کارت یا شناسه کاربری:* $cardNumber\n";
            $responseText .= "📍 *وضعیت:* $statusText\n\n";
        }
    }
    sendMessage($from_id, $responseText);
    die;
}
