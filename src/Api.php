<?php
namespace LINE\Notify;
use LINE\Notify\Token;

class Api {
    public
        $api_url = "https://notify-bot.line.me/";
    protected
        $client_secret,
        $client_id,
        $token,
        $guzzle,
        $ratelimit = [
            "Limit"=> null,
            "Remaining"=> null,
            "ImageLimit"=> null,
            "ImageRemaining"=> null,
            "Reset"=> null
        ];

    /**
     * Create a new Instance of this API client.
     * 
     * @param $options you should supply client_secret and client_id as an array object to this.
     *  alternatively, you can add token too, to add token.
     * @throw InvalidArgumentException if some of those 
     */
    public function __construct(array $options)
    {
        $this->guzzle = new \GuzzleHttp\Client([
            "base_uri" => $this->api_url
        ]);
        // must-include:
        // - client_secret
        // - client_id
        if (!\key_exists('client_secret', $options) || !$options['client_secret']) {
            throw new \InvalidArgumentException("client_secret is not supplied.");
        }
        if (!\key_exists('client_id', $options) || !$options['client_id']) {
            throw new \InvalidArgumentException("client_id is not supplied.");
        }
        $this->setClientID($options['client_id']);
        $this->setClientSecret($options['client_secret']);
        // sets token
        if (key_exists('token', $options)) {
            if ($options['token'] instanceof \LINE\Notify\Token) {
                $this->setToken($options['token']);
            } else if (is_string($options['token'])) {
                $this->setToken(\LINE\Notify\Token::fromIDToken($options['token'], $this));
            } else {
                throw new \InvalidArgumentException("token should be a string, or a LINE\Token object, got " . $options['token']);
            }
        }
    }

    public function setClientID($id) {
        $this->client_id = $id;
    }

    public function setClientSecret($secret) {
        $this->client_secret = $secret;
    }

    public function getClientID() {
        return $this->client_id;
    }

    public function getClientSecret() {
        return $this->client_secret;
    }

    public function getToken(): Token
    {
        return $this->token;
    }

    public function setToken(Token $token): void
    {
        $this->token = $token;
    }

    public function getRateLimit() {
        return $this->ratelimit;
    }

    protected function updateLimitStatus($header) {
        $this->ratelimit["Limit"] = $header['X-RateLimit-Limit'];
        $this->ratelimit["Remaining"] = $header['X-RateLimit-Remaining'];
        $this->ratelimit["ImageLimit"] = $header['X-RateLimit-ImageLimit'];
        $this->ratelimit["ImageRemaining"] = $header['X-RateLimit-ImageRemaining'];
        $this->ratelimit["Reset"] = $header['X-RateLimit-Reset'];
    }

    protected function generate_request_options($type, $param, $options, $auth_type)
    {
        $type = \strtolower($type);
        $supply_to = [
            "post" => 'form_params',
            "get" => 'query'
        ];
        if (!array_key_exists($type, $supply_to)) {
            throw new \InvalidArgumentException("Not supported type. Got " . $type);
        }
        // Supply the access token and etcs.
        $headers = [];
        if ($auth_type == "client") {
            try {
                $param = array_merge([
                    "client_id" => $this->getClientID(),
                    "client_secret" => $this->getClientSecret()
                ], $param);
            } catch (\Exception $e) {
                // soft error handling. If it's not present then let it go.
                // maybe, from $the option, channel id will be provided.
            }
        } else if ($auth_type == "header") {
            // No soft error handling.
            // Because it's set mannually from the param, meaning it's has consent from the developer.
            $headers['Authorization'] = "Bearer " . $this->getToken()->getAccessToken();
        }
        $request_options = array_merge([
            "headers" => $headers,
            $supply_to[$type] => $param
        ], $options);
        return $request_options;
    }

    /**
     * Do a public GET call to api.
     */
    public function get($url, $param = [], $options = [], $auth_type = "client")
    {
        $request_param = $this->generate_request_options('get', $param, $options, $auth_type);
        $response = $this->guzzle->request('GET', $url, $request_param);

        $this->updateLimitStatus($response->getHeaders());
        return $response;
    }

    /**
     * Do a public POST call to api
     */
    public function post($url, $param = [], $options = [], $auth_type = "client")
    {
        $request_param = $this->generate_request_options('post', $param, $options, $auth_type);
        $response = $this->guzzle->request('POST', $url, $request_param);

        $this->updateLimitStatus($response->getHeaders());
        return $response;
    }
}