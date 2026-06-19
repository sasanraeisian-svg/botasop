<?php

$update = json_decode(file_get_contents("php://input"));

function getUpdateValue($object, $key, $default = 'none')
{
    return $object->$key ?: $default;
}

if (isset($update->message)) {
    $update_id    = getUpdateValue($update, 'update_id');
    $message      = $update->message;
    $text         = getUpdateValue($message, 'text', '');
    $from_id      = getUpdateValue($message->from, 'id');
    $chat_id      = getUpdateValue($message->chat, 'id');
    $type         = getUpdateValue($message->chat, 'type');
    $chat_type    = getUpdateValue($message->chat, 'type', 'private');
    $user_name    = getUpdateValue($message->from, 'username', 'ندارد');
    $first_name   = htmlspecialchars(getUpdateValue($message->from, 'first_name'), ENT_QUOTES, 'UTF-8');
    $message_id   = getUpdateValue($message, 'message_id');
    $file_id      = isset($message->photo[0]) ? getUpdateValue($message->photo[0], 'file_id') : 'none';
}

if (isset($update->callback_query)) {
    $callback_query = $update->callback_query;
    $from_id        = getUpdateValue($callback_query->from, 'id');
    $chat_id        = getUpdateValue($callback_query->message->chat, 'id');
    $data           = getUpdateValue($callback_query, 'data');
    $query_id       = getUpdateValue($callback_query, 'id');
    $type           = getUpdateValue($callback_query->message->chat, 'type');
    $first_name     = htmlspecialchars(getUpdateValue($callback_query->from, 'first_name'), ENT_QUOTES, 'UTF-8');
    $user_name      = getUpdateValue($callback_query->from, 'username', 'ندارد');
    $message_id     = getUpdateValue($callback_query->message, 'message_id');
}
