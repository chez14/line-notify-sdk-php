<?php
namespace LINE\Notify;

use LINE\Notify\Api;

class Token {
    protected
        $token,
        $client;

    private function __construct(Api $api, string $token) {
        $this->token = $token;
        $this->api = $api;
    }

    public static function generateAuthUrl(Api $api, string $redirect_url, string $state, string $response_mode = null, string $scope = "notify") {
        $query = [
            "response_type"=>"code",
            "client_id"=>$api->getClientID(),
            "redirect_uri"=>$redirect_url,
            "scope"=>$scope,
            "state"=>$state
        ];

        if($response_mode) {
            $query['response_mode'] = $response_mode;
        }

        return $api->auth_url . "oauth/authorize?" . http_build_query($query);
    }

    public static function fromString(Api $api, string $token):self {
        $token = new self($api, $token);
        return $token;
    }

    public static function fromAuthCode(Api $api, string $authCode, string $redirect_uri) {
        $response = $api->post($api->auth_url . "oauth/token", [
            "grant_type" => "authorization_code",
            "code" => $authCode,
            "redirect_uri" => $redirect_uri
        ]);
        $json = json_decode($response->getBody(), true);
        return new self($api, $json['access_token']);
    }
    
    public function getAccessToken() {
        return $this->token;
    }
}