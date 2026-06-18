<?php

function BaleRequest(string $method, array $data)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://tapi.bale.ai/bot' . API_KEY . '/' . $method);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        return null;
    }
    return json_decode($response);
}

function sendMessage(int $chat_id, string $text, $reply_markup = null, $message_id = null)
{
    return BaleRequest('sendMessage', [
        'chat_id' => $chat_id,
        'text' => $text,
        'reply_markup' => $reply_markup,
        'reply_to_message_id' => $message_id
    ]);
}

function editMessage(int $chat_id, int $message_id, string $text, $reply_markup = null)
{
    return BaleRequest('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => $text,
        'reply_markup' => $reply_markup
    ]);
}

function deleteMessage(int $chat_id, int $message_id)
{
    return BaleRequest('deleteMessage', [
        'chat_id' => $chat_id,
        'message_id' => $message_id
    ]);
}

function forwardMessage(int $chat_id, int $from_id, int $message_id)
{
    return BaleRequest('ForwardMessage', [
        'chat_id' => $chat_id,
        'from_chat_id' => $from_id,
        'message_id' => $message_id
    ]);
}

function sendPhoto(int $chat_id, int $from_chat_id, string $photo, mixed $caption = null)
{
    return BaleRequest('sendPhoto', [
        'chat_id' => $chat_id,
        'from_chat_id' => $from_chat_id,
        'photo' => $photo,
        'caption' => $caption
    ]);
}

function getChat(int $chat_id)
{
    return BaleRequest('getChat', [
        'chat_id' => $chat_id
    ]);
}

function getChatMember(mixed $chat_id, int $user_id)
{
    return BaleRequest('getChatMember', [
        'chat_id' => $chat_id,
        'user_id' => $user_id
    ]);
}

function setStep(int $chat_id, string $step)
{
    global $pdo;
    $query = "UPDATE `users` SET `step` = ? WHERE `chat_id` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$step, $chat_id]);
}

function debug(int $from_id, $value)
{
    sendMessage($from_id, print_r($value, true));
}

function convertPersianToEnglishNumbers(string $string): string
{
    $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    return str_replace($persian, $english, $string);
}

function settings(string $key)
{
    global $pdo;
    $query = "SELECT _value FROM `settings` WHERE `_key` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$key]);
    return $stmt->fetchColumn();
}

function answerCallbackQuery(string $callback_query_id, string $text, bool $show_alert = false)
{
    return BaleRequest('answerCallbackQuery', [
        'callback_query_id' => $callback_query_id,
        'text' => $text,
        'show_alert' => $show_alert
    ]);
}
