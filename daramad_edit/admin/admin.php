<?php

if (($text == 'پنل' || $text == '🔙 بازگشت به مدیریت') && in_array($from_id, $adminsList)) {
    $responseText = "👋 سلام ادمین عزیز! به پنل مدیریت خوش آمدید.\n\nاز دکمه‌های پایین برای مدیریت ربات استفاده کنید.";
    sendMessage($from_id, $responseText, $adminKeyboard);
    setStep($from_id, 'panel');
    return;
}

if ($text == '📊 آمار ربات' && in_array($from_id, $adminsList)) {

    $query = "SELECT COUNT(*) FROM `users`";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $totalUsers = $stmt->fetchColumn();

    $query = "SELECT SUM(`amount`) FROM `withdrawal_requests` WHERE `status` = 'confirme'";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $totalWithdrawalsAmount = $stmt->fetchColumn();

    $today = date('Y-m-d');
    $query = "SELECT SUM(`amount`) FROM `withdrawal_requests` WHERE DATE(`created_at`) = ? AND `status` = 'confirme'";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$today]);
    $todayWithdrawalsAmount = $stmt->fetchColumn();

    $query = "SELECT COUNT(*) FROM `users` WHERE DATE(`created_at`) = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$today]);
    $newUsersToday = $stmt->fetchColumn();

    $responseText = "📊 *آمار ربات*\n\n";
    $responseText .= "👥 *تعداد کل اعضا:* $totalUsers\n";
    $responseText .= "🆕 *اعضای جدید امروز:* $newUsersToday\n";
    $responseText .= "💸 *مجموع مبلغ برداشت‌های تایید شده:* " . number_format($totalWithdrawalsAmount) . " تومان\n";
    $responseText .= "💰 *مجموع مبلغ برداشت‌های تایید شده امروز:* " . number_format($todayWithdrawalsAmount) . " تومان\n";

    sendMessage($from_id, $responseText);
    die;
}

if ($text == '✏️ متن بنر' && in_array($from_id, $adminsList)) {
    sendMessage($from_id, "🔘 لطفاً *متن جدید بنر کسب درآمد* را وارد کنید. برای جایگزاری لینک دعوت از عبارت LINK و برای جایگزاری مبلغ هدیه از عبارت TOMAN استفاده کنید. سیستم به طور اتوماتیک این مقادیر را جایگزین خواهد کرد:", $backToAdminKeyboard);
    setStep($from_id, 'set-banner-text');
    die;
}

if ($currentUser->step == 'set-banner-text' && in_array($from_id, $adminsList)) {

    $query = "SELECT * FROM `settings` WHERE `_key` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['bannerText']);
    $exists = $stmt->fetch();

    if ($exists) {
        $query = "UPDATE `settings` SET `_value` = ? WHERE `_key` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$text, 'bannerText']);
    } else {
        $query = "INSERT INTO `settings` (`_key`, `_value`) VALUES (?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['bannerText', $text]);
    }

    sendMessage($from_id, "✅ متن بنر کسب درآمد با موفقیت ذخیره شد.", $adminKeyboard);
    setStep($from_id, 'panel');
    die;
}

if ($text == '📷 عکس بنر' && in_array($from_id, $adminsList)) {
    $bannerPhotoExists = settings('bannerImage') ?: null;
    $keyboard = [['text' => '🔙 بازگشت به مدیریت']];

    if ($bannerPhotoExists) {
        $keyboard = [['text' => '❌ حذف عکس'], ['text' => '🔙 بازگشت به مدیریت']];
    }

    sendMessage($from_id, "📷 لطفاً *عکس جدید بنر کسب درآمد* را ارسال کنید. فایل عکس شما به طور خودکار ذخیره خواهد شد.", json_encode([
        'keyboard' => [$keyboard]
    ]));

    setStep($from_id, 'set-banner-photo');
    die;
}

if ($text == '❌ حذف عکس' && in_array($from_id, $adminsList)) {

    $query = "SELECT * FROM `settings` WHERE `_key` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['bannerImage']);
    $exists = $stmt->fetch();

    if ($exists) {
        $query = "UPDATE `settings` SET `_value` = ? WHERE `_key` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([null, 'bannerImage']);
    } else {
        $query = "INSERT INTO `settings` (`_key`, `_value`) VALUES (?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['bannerImage', null]);
    }

    sendMessage($from_id, "✅ عکس بنر کسب درآمد با موفقیت حذف شد.", $adminKeyboard);
    setStep($from_id, 'panel');
    die;
}

if ($currentUser->step == 'set-banner-photo' && in_array($from_id, $adminsList)) {
    if (isset($update->message->photo)) {
        $file_id = $update->message->photo[0]->file_id;

        $query = "SELECT * FROM `settings` WHERE `_key` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['bannerImage']);
        $exists = $stmt->fetch();

        if ($exists) {
            $query = "UPDATE `settings` SET `_value` = ? WHERE `_key` = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$file_id, 'bannerImage']);
        } else {
            $query = "INSERT INTO `settings` (`_key`, `_value`) VALUES (?, ?)";
            $stmt = $pdo->prepare($query);
            $stmt->execute(['bannerImage', $file_id]);
        }

        sendMessage($from_id, "✅ عکس بنر کسب درآمد با موفقیت ذخیره شد.", $adminKeyboard);
        setStep($from_id, 'panel');
        die;
    } else {
        sendMessage($from_id, "❌ لطفاً یک عکس ارسال کنید.", json_encode([
            'keyboard' => [
                [['text' => '❌ حذف عکس'], ['text' => '🔙 بازگشت به مدیریت']]
            ]
        ]));
        die;
    }
}

if ($text == '🔘 توضیحات کسب درآمد' && in_array($from_id, $adminsList)) {
    sendMessage($from_id, "🔘 لطفاً *توضیحات جدید کسب درآمد* را وارد کنید. برای جایگزاری لینک دعوت از عبارت LINK و برای جایگزاری مبلغ هدیه از عبارت TOMAN استفاده کنید. سیستم به طور اتوماتیک این مقادیر را جایگزین خواهد کرد:", $backToAdminKeyboard);
    setStep($from_id, 'set-earnings-description');
    die;
}

if ($currentUser->step == 'set-earnings-description' && in_array($from_id, $adminsList)) {
    $responseText = $text;

    $query = "SELECT * FROM `settings` WHERE `_key` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['earningsDescription']);
    $exists = $stmt->fetch();

    if ($exists) {
        $query = "UPDATE `settings` SET `_value` = ? WHERE `_key` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$responseText, 'earningsDescription']);
    } else {
        $query = "INSERT INTO `settings` (`_key`, `_value`) VALUES (?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['earningsDescription', $responseText]);
    }

    sendMessage($from_id, "✅ توضیحات کسب درآمد با موفقیت ذخیره شد.", $adminKeyboard);
    setStep($from_id, 'panel');
    die;
}

if ($text == '🚫 مسدود کردن کاربر' && in_array($from_id, $adminsList)) {
    sendMessage($from_id, "🚫 لطفاً *آیدی عددی کاربر* را وارد کنید که می‌خواهید مسدود شود.", $backToAdminKeyboard);
    setStep($from_id, 'block-user');
    die;
}

if ($currentUser->step == 'block-user' && in_array($from_id, $adminsList)) {

    if (!is_numeric($text)) {
        sendMessage($from_id, "❌ لطفاً یک آیدی عددی معتبر وارد کنید.", $backToAdminKeyboard);
        die;
    }

    $query = "SELECT * FROM `users` WHERE `chat_id` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$text]);
    $user = $stmt->fetch();

    if ($user) {
        switch ($user->is_banned) {
            case 0:
                $query = "UPDATE `users` SET `is_banned` = 1 WHERE `chat_id` = ?";
                $stmt = $pdo->prepare($query);
                $stmt->execute([$text]);

                sendMessage($from_id, "✅ کاربر با آیدی $text با موفقیت مسدود شد.", $adminKeyboard);
                break;
            default:
                sendMessage($from_id, "⚠️ این کاربر قبلاً مسدود شده است.", $adminKeyboard);
                break;
        }
    } else {
        sendMessage($from_id, "❌ کاربری با این آیدی وجود ندارد.", $adminKeyboard);
    }
    setStep($from_id, 'panel');
    die;
}


if ($text == '✅ رفع مسدود کاربر' && in_array($from_id, $adminsList)) {
    sendMessage($from_id, "✅ لطفاً *آیدی عددی کاربر* را وارد کنید که می‌خواهید از مسدودیت خارج شود.", $backToAdminKeyboard);
    setStep($from_id, 'unblock-user');
    die;
}

if ($currentUser->step == 'unblock-user' && in_array($from_id, $adminsList)) {

    if (!is_numeric($text)) {
        sendMessage($from_id, "❌ لطفاً یک آیدی عددی معتبر وارد کنید.", $backToAdminKeyboard);
        die;
    }

    $query = "SELECT * FROM `users` WHERE `chat_id` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$text]);
    $user = $stmt->fetch();

    if ($user) {
        switch ($user->is_banned) {
            case 1:
                $query = "UPDATE `users` SET `is_banned` = 0 WHERE `chat_id` = ?";
                $stmt = $pdo->prepare($query);
                $stmt->execute([$text]);

                sendMessage($from_id, "✅ کاربر با آیدی $text با موفقیت از مسدودیت خارج شد.", $adminKeyboard);
                break;
            default:
                sendMessage($from_id, "⚠️ این کاربر قبلاً مسدود نبوده است.", $adminKeyboard);
                break;
        }
    } else {
        sendMessage($from_id, "❌ کاربری با این آیدی وجود ندارد.", $adminKeyboard);
    }
    setStep($from_id, 'panel');
    die;
}

if ($text == '🔍 جستجوی کاربر' && in_array($from_id, $adminsList)) {
    sendMessage($from_id, "🔍 لطفاً *آیدی عددی کاربر* را وارد کنید که می‌خواهید جستجو کنید.", $backToAdminKeyboard);
    setStep($from_id, 'search-user');
    die;
}

if ($currentUser->step == 'search-user' && in_array($from_id, $adminsList)) {
    if (!is_numeric($text)) {
        sendMessage($from_id, "❌ لطفاً یک آیدی عددی معتبر وارد کنید.", $backToAdminKeyboard);
        die;
    }

    $query = "SELECT * FROM `users` WHERE `chat_id` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$text]);
    $user = $stmt->fetch();

    if ($user) {
        $balance = $user->balance ?: 'موجودی در دسترس نیست';
        $banStatus = $user->is_banned ? 'مسدود شده' : 'آزاد';

        $query = "SELECT * FROM `referrals` WHERE `referrer_id` = ? ORDER BY `id` DESC LIMIT 5";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$text]);
        $referrals = $stmt->fetchAll();

        $referralCount = count($referrals);
        $recentReferrals = '';
        foreach ($referrals as $referral) {
            $recentReferrals .= "🧑‍💻 کاربر: {$referral->referee_id} | تاریخ: " . jdate('H:i - Y/m/d', strtotime($referral->created_at)) . "\n";
        }

        $responseText = "*🔍 اطلاعات کاربر با آیدی $text:*\n\n";
        $responseText .= "💳 موجودی: $balance\n";
        $responseText .= "🚫 وضعیت بن: $banStatus\n";
        $responseText .= "👥 تعداد زیرمجموعه‌ها: $referralCount\n\n";
        $responseText .= "📋 5 زیرمجموعه اخیر:\n";
        $responseText .= $recentReferrals ?: "❌ هیچ زیرمجموعه‌ای برای این کاربر ثبت نشده است.";

        sendMessage($from_id, $responseText, $adminKeyboard);
    } else {
        sendMessage($from_id, "❌ کاربری با این آیدی یافت نشد.", $adminKeyboard);
    }

    setStep($from_id, 'panel');
    die;
}

if ($text == '📢 ارسال همگانی' && in_array($from_id, $adminsList)) {
    $responseText = 'لطفا پیامی که می‌خواهید ارسال همگانی کنید را ارسال کنید:';
    sendMessage($from_id, $responseText, $backToAdminKeyboard);
    setStep($from_id, 'prepare-send-all');
    die;
}

if (strpos($currentUser->step, 'prepare-send-all') === 0 && in_array($from_id, $adminsList)) {
    $pdo->query("DELETE FROM `send_all` WHERE 1");

    $query = "SELECT COUNT(*) AS user_count FROM `users`";
    $stmt = $pdo->query($query);
    $userCount = $stmt->fetch()->user_count;

    $query = "INSERT INTO `send_all` (`user_id`, `text`) SELECT `chat_id`, ? FROM `users`";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$text]);

    $responseText = "پیام شما برای ارسال همگانی تنظیم شد!\n\n📊 تعداد کاربران: $userCount نفر";
    sendMessage($from_id, $responseText, $adminKeyboard);

    setStep($from_id, 'panel');
    die;
}

if ($text == '📤 فروارد همگانی' && in_array($from_id, $adminsList)) {
    sendMessage($from_id, '📤 لطفاً *پیامی که می‌خواهید فوروارد همگانی کنید* را ارسال کنید:', $backToAdminKeyboard);
    setStep($from_id, 'forward-all-message');
    die;
}

if ($currentUser->step == 'forward-all-message' && in_array($from_id, $adminsList)) {
    $pdo->query("DELETE FROM `forward_all` WHERE 1");

    $query = "SELECT COUNT(*) AS user_count FROM `users`";
    $stmt = $pdo->query($query);
    $userCount = $stmt->fetch()->user_count;

    $query = "INSERT INTO `forward_all` (`from_id`, `message_id`, `user_id`) SELECT ?, ?, `chat_id` FROM `users`";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$from_id, $message_id]);

    $responseText = "✅ پیام شما برای فوروارد همگانی تنظیم شد!\n\n📊 تعداد کاربران: $userCount نفر";
    sendMessage($from_id, $responseText, $adminKeyboard);

    setStep($from_id, 'panel');
    die;
}

if ($text == '🎁 تنظیم هدیه دعوت' && in_array($from_id, $adminsList)) {
    sendMessage($from_id, "🎁 لطفاً *مبلغ هدیه دعوت* را وارد کنید (بر حسب تومان):", $backToAdminKeyboard);
    setStep($from_id, 'set-invite-bonus');
    die;
}

if ($currentUser->step == 'set-invite-bonus' && in_array($from_id, $adminsList)) {
    if (!is_numeric($text) || $text <= 0) {
        sendMessage($from_id, "❌ لطفاً یک مبلغ معتبر و بزرگتر از صفر وارد کنید.", $backToAdminKeyboard);
        die;
    }

    $query = "SELECT * FROM `settings` WHERE `_key` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['inviteBonus']);
    $exists = $stmt->fetch();

    if ($exists) {
        $query = "UPDATE `settings` SET `_value` = ? WHERE `_key` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$text, 'inviteBonus']);
    } else {
        $query = "INSERT INTO `settings` (`_key`, `_value`) VALUES (?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['inviteBonus', $text]);
    }

    sendMessage($from_id, "✅ مبلغ هدیه دعوت با موفقیت تنظیم شد. مبلغ: $text تومان", $adminKeyboard);
    setStep($from_id, 'panel');
    die;
}

if ($text == '💰 تنظیم حداقل برداشت' && in_array($from_id, $adminsList)) {
    sendMessage($from_id, "💰 لطفاً *مبلغ حداقل برداشت* را وارد کنید (بر حسب تومان):", $backToAdminKeyboard);
    setStep($from_id, 'set-minimum-withdrawal');
    die;
}

if ($currentUser->step == 'set-minimum-withdrawal' && in_array($from_id, $adminsList)) {
    if (!is_numeric($text) || $text <= 0) {
        sendMessage($from_id, "❌ لطفاً یک مبلغ معتبر و بزرگتر از صفر وارد کنید.", $backToAdminKeyboard);
        die;
    }

    $query = "SELECT * FROM `settings` WHERE `_key` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['minimumWithdrawal']);
    $exists = $stmt->fetch();

    if ($exists) {
        $query = "UPDATE `settings` SET `_value` = ? WHERE `_key` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$text, 'minimumWithdrawal']);
    } else {
        $query = "INSERT INTO `settings` (`_key`, `_value`) VALUES (?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['minimumWithdrawal', $text]);
    }

    sendMessage($from_id, "✅ مبلغ حداقل برداشت با موفقیت تنظیم شد. مبلغ: $text تومان", $adminKeyboard);
    setStep($from_id, 'panel');
    die;
}

if ($text == '✏️ تنظیم متن شروع' && in_array($from_id, $adminsList)) {
    sendMessage($from_id, "✏️ لطفاً *متن شروع* را وارد کنید که به کاربران جدید نمایش داده خواهد شد.", $backToAdminKeyboard);
    setStep($from_id, 'set-start-text');
    die;
}

if ($currentUser->step == 'set-start-text' && in_array($from_id, $adminsList)) {
    $query = "SELECT * FROM `settings` WHERE `_key` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['startText']);
    $exists = $stmt->fetch();

    if ($exists) {
        $query = "UPDATE `settings` SET `_value` = ? WHERE `_key` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$text, 'startText']);
    } else {
        $query = "INSERT INTO `settings` (`_key`, `_value`) VALUES (?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['startText', $text]);
    }

    sendMessage($from_id, "✅ متن شروع با موفقیت ذخیره شد.", $adminKeyboard);
    setStep($from_id, 'panel');
    die;
}

if ($text == '📜 تنظیم متن قوانین' && in_array($from_id, $adminsList)) {
    sendMessage($from_id, "📜 لطفاً *متن قوانین* را وارد کنید که به کاربران نمایش داده خواهد شد.", $backToAdminKeyboard);
    setStep($from_id, 'set-rules-text');
    die;
}

if ($currentUser->step == 'set-rules-text' && in_array($from_id, $adminsList)) {
    $query = "SELECT * FROM `settings` WHERE `_key` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['rulesText']);
    $exists = $stmt->fetch();

    if ($exists) {
        $query = "UPDATE `settings` SET `_value` = ? WHERE `_key` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$text, 'rulesText']);
    } else {
        $query = "INSERT INTO `settings` (`_key`, `_value`) VALUES (?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['rulesText', $text]);
    }

    sendMessage($from_id, "✅ متن قوانین با موفقیت ذخیره شد.", $adminKeyboard);
    setStep($from_id, 'panel');
    die;
}

if ($text == '🧑‍💻 تنظیم متن پشتیبانی' && in_array($from_id, $adminsList)) {
    sendMessage($from_id, "🧑‍💻 لطفاً *متن پشتیبانی* را وارد کنید که به کاربران نمایش داده خواهد شد.", $backToAdminKeyboard);
    setStep($from_id, 'set-support-text');
    die;
}

if ($currentUser->step == 'set-support-text' && in_array($from_id, $adminsList)) {
    $query = "SELECT * FROM `settings` WHERE `_key` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['supportText']);
    $exists = $stmt->fetch();

    if ($exists) {
        $query = "UPDATE `settings` SET `_value` = ? WHERE `_key` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$text, 'supportText']);
    } else {
        $query = "INSERT INTO `settings` (`_key`, `_value`) VALUES (?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['supportText', $text]);
    }

    sendMessage($from_id, "✅ متن پشتیبانی با موفقیت ذخیره شد.", $adminKeyboard);
    setStep($from_id, 'panel');
    die;
}

if ($text == '📚 تنظیم متن راهنما' && in_array($from_id, $adminsList)) {
    sendMessage($from_id, "📚 لطفاً *متن راهنما* را وارد کنید که به کاربران نمایش داده خواهد شد.", $backToAdminKeyboard);
    setStep($from_id, 'set-guide-text');
    die;
}

if ($currentUser->step == 'set-guide-text' && in_array($from_id, $adminsList)) {
    $query = "SELECT * FROM `settings` WHERE `_key` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['guideText']);
    $exists = $stmt->fetch();

    if ($exists) {
        $query = "UPDATE `settings` SET `_value` = ? WHERE `_key` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$text, 'guideText']);
    } else {
        $query = "INSERT INTO `settings` (`_key`, `_value`) VALUES (?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['guideText', $text]);
    }

    sendMessage($from_id, "✅ متن راهنما با موفقیت ذخیره شد.", $adminKeyboard);
    setStep($from_id, 'panel');
    die;
}

if ($text == '➕ افزودن جوین اجباری' && in_array($from_id, $adminsList)) {
    sendMessage($from_id, "📥 لطفاً *یوزرنیم کانال* (بدون @) را وارد کنید:", $backToAdminKeyboard);
    setStep($from_id, 'add_force_channel');
    die;
}

if ($currentUser->step == 'add_force_channel' && in_array($from_id, $adminsList)) {
    $channelUsername = trim($text);

    $query = "INSERT IGNORE INTO `force_channels` (`channel_username`) VALUES (?)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$channelUsername]);

    sendMessage($from_id, "✅ کانال $channelUsername به لیست جوین اجباری افزوده شد.", $adminKeyboard);
    setStep($from_id, 'panel');
    die;
}

if ($text == '➖ حذف جوین اجباری' && in_array($from_id, $adminsList)) {
    $stmt = $pdo->prepare("SELECT `channel_username` FROM `force_channels`");
    $stmt->execute();
    $channels = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($channels)) {
        sendMessage($from_id, "⚠️ هیچ کانالی در لیست جوین اجباری ثبت نشده است.", $adminKeyboard);
        setStep($from_id, 'panel');
        die;
    }

    $responseText = "🗑 لطفاً *یوزرنیم کانالی* را وارد کنید که می‌خواهید از لیست حذف شود:\n\n";
    foreach ($channels as $index => $channel) {
        $responseText .= ($index + 1) . ". $channel\n";
    }

    sendMessage($from_id, $responseText, $backToAdminKeyboard);
    setStep($from_id, 'remove_force_channel');
    die;
}

if ($currentUser->step == 'remove_force_channel' && in_array($from_id, $adminsList)) {
    $channelUsername = trim($text);

    $query = "DELETE FROM `force_channels` WHERE `channel_username` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$channelUsername]);

    if ($stmt->rowCount()) {
        sendMessage($from_id, "✅ کانال $channelUsername از لیست جوین اجباری حذف شد.", $adminKeyboard);
    } else {
        sendMessage($from_id, "⚠️ کانال $channelUsername در لیست یافت نشد.", $adminKeyboard);
    }

    setStep($from_id, 'panel');
    die;
}

if ($text == '🔧 تنظیم کانال برداشت‌ ها' && in_array($from_id, $adminsList)) {
    sendMessage($from_id, "🔧 لطفاً شناسه عددی کانالی که می‌خواهید رسید برداشت کاربران در آن ارسال شود را وارد کنید:", $backToAdminKeyboard);
    setStep($from_id, 'set-withdrawal-channel');
    die;
}

if ($currentUser->step == 'set-withdrawal-channel' && in_array($from_id, $adminsList)) {
    $channelId = $text;

    if (!is_numeric($channelId)) {
        sendMessage($from_id, "⚠️ شناسه کانال باید یک عدد صحیح باشد. لطفاً دوباره تلاش کنید.", $backToAdminKeyboard);
        die;
    }

    $query = "SELECT * FROM `settings` WHERE `_key` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['withdrawalChannelId']);
    $exists = $stmt->fetch();

    if ($exists) {
        $query = "UPDATE `settings` SET `_value` = ? WHERE `_key` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$channelId, 'withdrawalChannelId']);
    } else {
        $query = "INSERT INTO `settings` (`_key`, `_value`) VALUES (?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['withdrawalChannelId', $channelId]);
    }

    sendMessage($from_id, "✅ شناسه کانال برداشت‌ها با موفقیت ذخیره شد.", $adminKeyboard);
    setStep($from_id, 'panel');
    die;
}

if ($text == '👀 کانال اثبات واریز' && in_array($from_id, $adminsList)) {
    sendMessage($from_id, "👀 لطفاً شناسه عددی کانالی که می‌خواهید رسید اثبات واریز کاربران در آن ارسال شود را وارد کنید:", $backToAdminKeyboard);
    setStep($from_id, 'set-deposit-proof-channel');
    die;
}

if ($currentUser->step == 'set-deposit-proof-channel' && in_array($from_id, $adminsList)) {
    $channelId = $text;

    if (!is_numeric($channelId)) {
        sendMessage($from_id, "⚠️ شناسه کانال باید یک عدد صحیح باشد. لطفاً دوباره تلاش کنید.", $backToAdminKeyboard);
        die;
    }

    $query = "SELECT * FROM `settings` WHERE `_key` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['depositProofChannelId']);
    $exists = $stmt->fetch();

    if ($exists) {
        $query = "UPDATE `settings` SET `_value` = ? WHERE `_key` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$channelId, 'depositProofChannelId']);
    } else {
        $query = "INSERT INTO `settings` (`_key`, `_value`) VALUES (?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['depositProofChannelId', $channelId]);
    }

    sendMessage($from_id, "✅ شناسه کانال اثبات واریز با موفقیت ذخیره شد.", $adminKeyboard);
    setStep($from_id, 'panel');
    die;
}
