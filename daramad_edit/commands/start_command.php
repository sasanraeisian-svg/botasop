<?php

if (strpos($text, '/start') === 0 && count(explode(' ', $text)) > 1) {
    $parts = explode(' ', $text);
    $referrerId = isset($parts[1]) && is_numeric($parts[1]) ? $parts[1] : null;

    $query = "SELECT * FROM `users` WHERE `chat_id` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$from_id]);

    if ($stmt->rowCount() == 0) {
        $query = "INSERT INTO `users` (`chat_id`) VALUES (?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$from_id]);

        if ($referrerId && $referrerId != $from_id) {
            $query = "SELECT * FROM `users` WHERE `chat_id` = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$referrerId]);

            if ($stmt->rowCount() > 0) {
                $query = "INSERT INTO `referrals` (`referrer_id`, `referee_id`) VALUES (?, ?)";
                $stmt = $pdo->prepare($query);
                $stmt->execute([$referrerId, $from_id]);
                sendMessage($referrerId, "🎉 تبریک! یک کاربر جدید با شناسه *$from_id* از طریق لینک شما به ربات وارد شد. \n\n💎 توجه داشته باشید که در صورتی که این زیرمجموعه در کانال‌های اسپانسری عضو شود، هدیه دعوت آن به حساب شما افزوده خواهد شد. 🙏");
            }
        }
    }
}

if ((!empty($text) || !empty($data)) && $chat_type != "group" && $chat_type != "channel" && !in_array($from_id, $adminsList)) {
    $query = "SELECT `channel_username` FROM `force_channels`";
    $stmt = $pdo->query($query);
    $channels = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($channels)) {
        $notJoinedChannels = [];
        $inlineButtons = [];

        foreach ($channels as $index => $channelUsername) {
            $check = getChatMember('@' . $channelUsername, $from_id);
            $status = $check->result->status ?: null;

            if (!in_array($status, ['member', 'administrator', 'creator'])) {
                $notJoinedChannels[] = "کانال: @$channelUsername";
                $inlineButtons[] = [['text' => "عضویت در کانال $channelUsername", 'url' => "https://ble.ir/$channelUsername"]];
            }
        }

        if (!empty($notJoinedChannels)) {
            $responseText = "*🔔 برای ادامه، ابتدا در کانال‌های زیر عضو شوید:*\n\n";
            $responseText .= implode("\n", $notJoinedChannels);

            $inlineButtons[] = [['text' => 'بررسی عضویت', 'callback_data' => 'joined_all']];

            sendMessage($from_id, $responseText, json_encode([
                'inline_keyboard' => $inlineButtons
            ]));
            die;
        }
    }
}

if (strpos($text, '/start') === 0 || $text == '🔙 بازگشت به منو اصلی' || $data == 'joined_all') {
    $query = "SELECT * FROM `users` WHERE `chat_id` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$from_id]);

    if ($stmt->rowCount() == 0) {
        $query = "INSERT INTO `users` (`chat_id`) VALUES (?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$from_id]);
    }

    $query = "SELECT * FROM `referrals` WHERE `referee_id` = ? AND `is_verified` = 0";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$from_id]);

    if ($stmt->rowCount() > 0) {
        $query = "UPDATE `referrals` SET `is_verified` = 1 WHERE `referee_id` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$from_id]);

        $query = "SELECT `referrer_id` FROM `referrals` WHERE `referee_id` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$from_id]);
        $referrer = $stmt->fetch();

        if ($referrer) {
            $inviteBonus = settings('inviteBonus') ?: 200;

            $query = "UPDATE `users` SET `balance` = `balance` + ? WHERE `chat_id` = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$inviteBonus, $referrer->referrer_id]);

            $responseText = "🎉 تبریک! زیرمجموعه شما با موفقیت احراز هویت شد.\n\n💰 به حساب شما *$inviteBonus* تومان اضافه گردید.";
            sendMessage($referrer->referrer_id, $responseText);
        }
    } else {
        $query = "SELECT * FROM `referrals` WHERE `referee_id` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$from_id]);

        if ($stmt->rowCount() == 0) {
            $query = "INSERT INTO `referrals` (`referrer_id`, `referee_id`) VALUES (0, ?)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$from_id]);
        }
    }

    $responseText = settings('startText')  ?: 'تنظیم نشده';
    sendMessage($from_id, $responseText, $userKeyboard);
    setStep($from_id, 'home');
    die;
}
