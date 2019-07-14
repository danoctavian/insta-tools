
<?php
set_time_limit(0);
date_default_timezone_set('UTC');
require __DIR__.'/vendor/autoload.php';
/////// CONFIG ///////
$configFile = file_get_contents("./instagramConfig.json");
$jsonConfig = json_decode($configFile);

$username = $jsonConfig->username;
$password = $jsonConfig->password;
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

    $items = $selfFollowingAccounts->getUsers();

    foreach ($items as $followedUser) {
        echo "Muting user: {$followedUser->getUsername()} {$followedUser->getPk()}\n";
        $muteResponse = $ig->people->muteUserMedia($followedUser->getPk(), "all");
        $sleepTime = rand(2, 3);
        echo "Sleeping for {$sleepTime}..";
        sleep($sleepTime);
    }
    echo "Done\n";
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit(1);
}