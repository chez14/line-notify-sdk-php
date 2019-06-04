<?php
require_once("../vendor/autoload.php");

// we'll retrive access-token first.

$lineApi = new LINE\Notify\Api([
    "client_id" => "",
    "client_secret" => ""
]);

$token = LINE\Notify\Token::fromAuthCode($lineApi, $_GET['code'], "http://localhost:8087/oauthcallback.php");

$lineApi->setToken($token);

$notify = new LINE\Notify\Notify($lineApi);
try {
    $notify->notify("HELLO!", null, 1, 106);
} catch(GuzzleHttp\Exception\ClientErrorResponseException $e){
    echo "GUZZLE ERROR: " . $e->getMessage() . "\n";
    echo "GUZZLE BODY:\n\n";
    echo $e->getResponse()->getBody(true);
} catch (\Exception $e) {
    echo "GENERIC ERROR " . $e->getMessage();
}
echo "\n\n";