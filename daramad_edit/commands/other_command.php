<?php

if ($text == '📚 راهنما') {
    $responseText = settings('guideText') ?: 'تنظیم نشده';
    sendMessage($from_id, $responseText);
    die;
}

if ($text == '📞 پشتیبانی') {
    $responseText = settings('supportText') ?: 'تنظیم نشده';
    sendMessage($from_id, $responseText);
    die;
}

if ($text == '📜 قوانین') {
    $responseText = settings('rulesText') ?: 'تنظیم نشده';
    sendMessage($from_id, $responseText);
    die;
}
