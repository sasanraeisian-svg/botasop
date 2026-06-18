<?php

if ($text == '💎 شروع کسب درآمد 💎') {
    $bannerText  = str_replace('LINK', "https://ble.ir/$botUserName?start=$from_id", settings('bannerText') ?: 'تنظیم نشده!');
    $bannerText  = str_replace('TOMAN', settings('inviteBonus') ?: 200, $bannerText);
    $bannerPhoto = settings('bannerImage') ?: null;

    $bannerDescription = str_replace('LINK', "https://ble.ir/$botUserName?start=$from_id", settings('earningsDescription') ?: 'تنظیم نشده!');
    $bannerDescription = str_replace('TOMAN', settings('inviteBonus') ?: 200, $bannerDescription);

    if ($bannerPhoto) {
        sendPhoto($from_id, $from_id, $bannerPhoto, $bannerText);
        sendMessage($from_id, $bannerDescription);
    } else {
        sendMessage($from_id, $bannerText);
        sendMessage($from_id, $bannerDescription);
    }
    return;
}
