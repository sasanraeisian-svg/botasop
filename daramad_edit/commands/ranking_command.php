<?php

if ($text == '🌟 برترین کاربران') {

    $query = "
        SELECT `referrer_id`, COUNT(*) AS referral_count
        FROM `referrals`
        WHERE `referrer_id` != 0
        GROUP BY `referrer_id`
        ORDER BY referral_count DESC
        LIMIT 10
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $topUsers = $stmt->fetchAll();

    $responseText = "🌟 *برترین کاربران*\n\n";

    $rank = 1;
    foreach ($topUsers as $user) {
        $userId = $user->referrer_id;
        $referralCount = $user->referral_count;

        switch ($rank) {
            case 1:
                $medal = "🥇";
                break;
            case 2:
                $medal = "🥈";
                break;
            case 3:
                $medal = "🥉";
                break;
            default:
                $medal = "🎖️";
                break;
        }
        $responseText .= "$medal - $userId | $referralCount زیرمجموعه\n\n";
        $rank++;
    }
    sendMessage($from_id, $responseText);
    die;
}
