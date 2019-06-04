<?php
require_once("../vendor/autoload.php");
/**
 * Please add client secret and client id here.
 * set the oauth callback url to `http://localhost:8087/oauthcallback.php`.
 * 
 * to start the server, execute this in this folder:
 * $ php -S localhost:8087
 */

$lineApi = new LINE\Notify\Api([
    "client_id" => "",
    "client_secret" => ""
]);

$OAuthUrl = LINE\Notify\Token::generateAuthUrl($lineApi, "http://localhost:8087/oauthcallback.php", "stateA");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>LINE Login</title>
</head>
<body>
    To continue, please <a href="<?=$OAuthUrl?>">Authenticate LINE Notify Bot</a>.
</body>
</html>