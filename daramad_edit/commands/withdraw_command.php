<?php

if ($text == '💸 برداشت از حساب') {

    sendMessage($from_id, "لطفا یکی از روش های برداشت زیر رو انتخاب کنید: ", json_encode([
        'keyboard' => [
            [['text' => '💳 کارت به کارت'], ['text' => '💸 پاکت هدیه']],
            [['text' => '🔙 بازگشت به منو اصلی']]
        ]
    ]));

    setStep($from_id, 'enter-type-withdrawal');
    die;
}

if ($text == '💳 کارت به کارت') {
    $userBalance = $currentUser->balance;
    $responseText = "💸 لطفاً مبلغ مورد نظر برای برداشت را وارد کنید:\n\n💰 موجودی حساب شما: $userBalance تومان";

    sendMessage($from_id, $responseText, $backToUserKeyboard);
    setStep($from_id, 'enter-card-withdrawal-amount');
    die;
}

if ($text == '💸 پاکت هدیه') {
    $userBalance = $currentUser->balance;
    $responseText = "💸 لطفاً مبلغ مورد نظر برای برداشت را وارد کنید:\n\n💰 موجودی حساب شما: $userBalance تومان";

    sendMessage($from_id, $responseText, $backToUserKeyboard);
    setStep($from_id, 'enter-packat-withdrawal-amount');
    die;
}

if ($currentUser->step == 'enter-card-withdrawal-amount') {
    $amount = floatval($text);

    if ($amount <= 0) {
        sendMessage($from_id, "⚠️ مبلغ وارد شده نامعتبر است. لطفاً مبلغی صحیح وارد کنید:");
        die;
    }

    if ($amount > $currentUser->balance) {
        sendMessage($from_id, "⚠️ موجودی کافی برای برداشت مبلغ $amount تومان ندارید. لطفاً مبلغ کمتری وارد کنید.");
        die;
    }

    $minimumWithdrawal = settings('minimumWithdrawal') ?: 1000;
    if ($amount < $minimumWithdrawal) {
        sendMessage($from_id, "⚠️ مبلغ وارد شده کمتر از حداقل برداشت ($minimumWithdrawal تومان) است. لطفاً مبلغی بیشتر از $minimumWithdrawal تومان وارد کنید:");
        die;
    }

    sendMessage($from_id, "💳 لطفاً شماره کارت بانکی معتبر خود را وارد کنید تا درخواست برداشت شما تکمیل شود. شماره کارت باید 16 رقمی باشد:");
    setStep($from_id, "enter-card-$amount");
    die;
}

if ($currentUser->step == 'enter-packat-withdrawal-amount') {
    $amount = floatval($text);

    if ($amount <= 0) {
        sendMessage($from_id, "⚠️ مبلغ وارد شده نامعتبر است. لطفاً مبلغی صحیح وارد کنید:");
        die;
    }

    if ($amount > $currentUser->balance) {
        sendMessage($from_id, "⚠️ موجودی کافی برای برداشت مبلغ $amount تومان ندارید. لطفاً مبلغ کمتری وارد کنید.");
        die;
    }

    $minimumWithdrawal = settings('minimumWithdrawal') ?: 1000;
    if ($amount < $minimumWithdrawal) {
        sendMessage($from_id, "⚠️ مبلغ وارد شده کمتر از حداقل برداشت ($minimumWithdrawal تومان) است. لطفاً مبلغی بیشتر از $minimumWithdrawal تومان وارد کنید:");
        die;
    }

    sendMessage($from_id, "💳 لطفا یوزرنیم شخصی که میخواهید پاکت به آن ارسال شود را همراه با @ وارد کنید:");
    setStep($from_id, "enter-username-$amount");
    die;
}


if (strpos($currentUser->step, 'enter-card-') === 0) {
    $amount = explode('-', $currentUser->step)[2];
    $cardNumber = $text;

    $date = jdate("Y/m/d");
    $time = jdate("H:i:s");

    $responseText = "🔹 *فاکتور درخواست برداشت*
    💰 *مبلغ*: $amount تومان
    🏦 *شماره کارت*: $cardNumber
    ⏰ $date $time
    ";

    $keyboard = json_encode([
        'inline_keyboard' => [
            [
                ['text' => "✅ تایید برداشت", 'callback_data' => "confirm_withdrawal-$amount-$cardNumber"],
                ['text' => "❌ انصراف", 'callback_data' => "cancel_withdrawal"]
            ]
        ]
    ]);
    sendMessage($from_id, $responseText, $keyboard);
    setStep($from_id, "confirm-or-cancel-card");
    die;
}

if (strpos($currentUser->step, 'enter-username-') === 0) {
    $amount = explode('-', $currentUser->step)[2];
    $userName = $text;

    if (!preg_match('/^@[a-zA-Z0-9_]{5,}$/', $userName)) {
        sendMessage($from_id, "⚠️ لطفا یوزرنیم معتبر وارد کنید (شروع با @ و حداقل ۵ کاراکتر).");
        die;
    }

    $date = jdate("Y/m/d");
    $time = jdate("H:i:s");

    $responseText = "🔹 *فاکتور درخواست برداشت*
    💰 *مبلغ*: $amount تومان
    🏦 *شناسه کاربری*: $userName
    ⏰ $date $time
    ";

    $keyboard = json_encode([
        'inline_keyboard' => [
            [
                ['text' => "✅ تایید برداشت", 'callback_data' => "confirm_withdrawal-$amount-$userName"],
                ['text' => "❌ انصراف", 'callback_data' => "cancel_withdrawal"]
            ]
        ]
    ]);
    sendMessage($from_id, $responseText, $keyboard);
    setStep($from_id, "confirm-or-cancel-username");
    die;
}

if ($data == 'cancel_withdrawal' && strpos($currentUser->step, 'confirm-or-cancel-') === 0) {
    editMessage($from_id, $message_id, "❌ درخواست برداشت لغو شد");
    sendMessage($from_id, 'به منو اصلی بازگشتید', $userKeyboard);
    setStep($from_id, 'home');
    die;
}

if ($currentUser->step == "confirm-or-cancel-card" && strpos($data, 'confirm_withdrawal-') === 0) {
    $amount = explode('-', $data)[1];
    $cardNumber = explode('-', $data)[2];

    if ($amount > $currentUser->balance) {
        editMessage($from_id, $message_id, "⚠️ موجودی کافی برای برداشت مبلغ $amount تومان ندارید. لطفاً مبلغ کمتری وارد کنید.");
        die;
    }

    do {
        $random_id = rand(1000000, 9999999);
        $transactionId = $random_id;

        $query = "SELECT COUNT(*) FROM `withdrawal_requests` WHERE `track_id` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$transactionId]);
        $rowCount = $stmt->fetchColumn();
    } while ($rowCount > 0);

    $query = "INSERT INTO `withdrawal_requests` (`chat_id`, `amount`, `card`, `track_id`) VALUES (?,?,?,?)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$from_id, $amount, $cardNumber, $transactionId]);

    $query = "UPDATE `users` SET `balance` = `balance` - ? WHERE `chat_id` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$amount, $from_id]);

    $withdrawChannel = settings('withdrawalChannelId');
    $date = jdate("Y/m/d");
    $time = jdate("H:i:s");

    $adminText = "📤 *گزارش برداشت جدید*\n\n";
    $adminText .= "👤 *کاربر:* $from_id\n";
    $adminText .= "💰 *مبلغ:* " . number_format($amount) . " تومان\n";
    $adminText .= "🎟 *کد پیگیری:* $transactionId\n";
    $adminText .= "📌 *شماره کارت:* $cardNumber\n";
    $adminText .= "⏰ $date $time";

    sendMessage($withdrawChannel, $adminText, json_encode([
        'inline_keyboard' => [
            [
                ['text' => 'تایید برداشت', 'callback_data' => "confirme_withdraw-$transactionId"],
                ['text' => 'رد کردن برداشت', 'callback_data' => "reject_withdraw-$transactionId"]
            ]
        ]
    ]));

    editMessage($from_id, $message_id, "✅ درخواست شما با شماره *$transactionId* با موفقیت ثبت شد و در صف واریز قرار گرفت\n\n⏳ لطفاً توجه داشته باشید که ممکن است واریز تا 24 ساعت طول بکشد");
    sendMessage($from_id, 'به منو اصلی بازگشتید', $userKeyboard);
    setStep($from_id, 'home');
    die;
}

if ($currentUser->step == "confirm-or-cancel-username" && strpos($data, 'confirm_withdrawal-') === 0) {
    $amount = explode('-', $data)[1];
    $userName = explode('-', $data)[2];

    if ($amount > $currentUser->balance) {
        editMessage($from_id, $message_id, "⚠️ موجودی کافی برای برداشت مبلغ $amount تومان ندارید. لطفاً مبلغ کمتری وارد کنید.");
        die;
    }

    do {
        $random_id = rand(1000000, 9999999);
        $transactionId = $random_id;

        $query = "SELECT COUNT(*) FROM `withdrawal_requests` WHERE `track_id` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$transactionId]);
        $rowCount = $stmt->fetchColumn();
    } while ($rowCount > 0);

    $query = "INSERT INTO `withdrawal_requests` (`chat_id`, `amount`, `card`, `track_id`) VALUES (?,?,?,?)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$from_id, $amount, $userName, $transactionId]);

    $query = "UPDATE `users` SET `balance` = `balance` - ? WHERE `chat_id` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$amount, $from_id]);

    $withdrawChannel = settings('withdrawalChannelId');
    $date = jdate("Y/m/d");
    $time = jdate("H:i:s");

    $adminText = "📤 *گزارش برداشت جدید*\n\n";
    $adminText .= "👤 *کاربر:* $from_id\n";
    $adminText .= "💰 *مبلغ:* " . number_format($amount) . " تومان\n";
    $adminText .= "🎟 *کد پیگیری:* $transactionId\n";
    $adminText .= "📌 *شناسه کاربری:* $userName\n";
    $adminText .= "⏰ $date $time";

    sendMessage($withdrawChannel, $adminText, json_encode([
        'inline_keyboard' => [
            [
                ['text' => 'تایید برداشت', 'callback_data' => "confirme_withdraw-$transactionId"],
                ['text' => 'رد کردن برداشت', 'callback_data' => "reject_withdraw-$transactionId"]
            ]
        ]
    ]));

    editMessage($from_id, $message_id, "✅ درخواست شما با شماره *$transactionId* با موفقیت ثبت شد و در صف واریز قرار گرفت\n\n⏳ لطفاً توجه داشته باشید که ممکن است واریز تا 24 ساعت طول بکشد");
    sendMessage($from_id, 'به منو اصلی بازگشتید', $userKeyboard);
    setStep($from_id, 'home');
    die;
}

if (strpos($data, 'confirme_withdraw-') === 0 || strpos($data, 'reject_withdraw-') === 0) {
    $transactionId = explode('-', $data)[1];

    $query = "SELECT * FROM `withdrawal_requests` WHERE `track_id` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$transactionId]);
    $withdrawal = $stmt->fetch();

    $chatId = $withdrawal->chat_id;
    $amount = $withdrawal->amount;
    $status = $withdrawal->status;
    $card   = $withdrawal->card;
    $created_at = $withdrawal->created_at;

    if (strpos($data, 'confirme_withdraw-') === 0) {
        $query = "UPDATE `withdrawal_requests` SET `status` = 'confirme' WHERE `track_id` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$transactionId]);

        $responseText = "✅ درخواست برای برداشت با موفقیت تایید شد.\n\n";
        $responseText .= "🎁 *دریافت‌کننده:* $chatId\n";
        $responseText .= "💰 *مبلغ:* $amount تومان\n";
        $responseText .= "🎟 *کد پیگیری:* $transactionId\n";
        $responseText .= "📌 درخواست شما انجام شد.";
        sendMessage($chatId, $responseText);

        $depositProofChannelId = settings('depositProofChannelId');
        $proofText = "📢 *تراکنش جدید تایید شده:*\n\n";
        $proofText .= "🎁 *دریافت‌کننده:* $chatId\n";
        $proofText .= "💰 *مبلغ:* $amount تومان\n";
        $proofText .= "🎟 *کد پیگیری:* $transactionId\n\n";
        $proofText .= "📌 مبلغ به حساب کاربر واریز گردید";
        sendMessage($depositProofChannelId, $proofText, json_encode([
            'inline_keyboard' => [
                [['text' => '💸 ورود به ربات + کسب درآمد', 'url' => "https://ble.ir/$botUserName"]]
            ]
        ]));

        editMessage($chat_id, $message_id, $responseText, json_encode([
            'inline_keyboard' => [
                [
                    ['text' => '✅ انجام شد', 'callback_data' => '0']
                ]
            ]
        ]));
    } elseif (strpos($data, 'reject_withdraw-') === 0) {
        $query = "UPDATE `withdrawal_requests` SET `status` = 'rejected' WHERE `track_id` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$transactionId]);

        $responseText = "❌ درخواست برای برداشت رد شد.\n\n";
        $responseText .= "🎁 *دریافت‌کننده:* $chatId\n";
        $responseText .= "💰 *مبلغ:* $amount تومان\n";
        $responseText .= "🎟 *کد پیگیری:* $transactionId\n";
        $responseText .= "📌 درخواست شما رد شد.";

        sendMessage($chatId, $responseText);
        $refundAmount = $amount;

        $query = "UPDATE `users` SET `balance` = `balance` + ? WHERE `chat_id` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$refundAmount, $chatId]);

        editMessage($chat_id, $message_id, $responseText, json_encode([
            'inline_keyboard' => [
                [
                    ['text' => '❌ رد شد', 'callback_data' => '0']
                ]
            ]
        ]));
    }
    die;
}
