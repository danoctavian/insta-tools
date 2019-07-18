
<?php
set_time_limit(0);
date_default_timezone_set('UTC');
require __DIR__.'/vendor/autoload.php';
/////// CONFIG ///////
$configFile = file_get_contents("./instagramConfig.json");
$jsonConfig = json_decode($configFile);

$username = $jsonConfig->username;
$password = $jsonConfig->password;
$startingFollowedUserIndex = $jsonConfig->startingFollowedUserIndex;
$debug = true;
$truncatedDebug = false;

echo "Running mute script for user {$username}..";
$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);
try {
    $ig->login($username, $password);
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit(1);
}
try {

    echo "Printing self following..";

    $rankToken = \InstagramAPI\Signatures::generateUUID();
    $selfFollowingAccounts = $ig->people->getSelfFollowing($rankToken);
    // $selfFollowingAccounts->printJson();



    $blockedList = $ig->people->getBlockedList();

    $blockedList->printJson();

    $items = $selfFollowingAccounts->getUsers();

    $userCount = sizeof($items);
    echo "Processing {$userCount} users";

    $i = 0;
    foreach ($items as $followedUser) {

        if ($i < $startingFollowedUserIndex) {
            echo "User already muted based on index. skipping {$i}\n";
            $i++;
            continue;
        }

        echo "Muting user {$i}: {$followedUser->getUsername()} {$followedUser->getPk()}\n";
        $muteResponse = $ig->people->muteUserMedia($followedUser->getPk(), "all");
        $sleepTime = rand(2, 3);
        echo "Sleeping for {$sleepTime}..\n";
        sleep($sleepTime);
        $i++;
    }
    echo "Done\n";
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit(1);
}