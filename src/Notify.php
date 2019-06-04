<?php
namespace LINE\Notify;

use function GuzzleHttp\json_decode;

class Notify {
    protected
        $api;
    
    public function __construct(Api $api)
    {
        $this->api = $api;
    }

    public function notify(string $message, $image=null, int $stickerPkgId=null, int $stickerId=null) {
        $request = [
            "message" => $message
        ];

        if(is_string($image)) {
            if(substr($image, 1, 4) == "http") {
                $request = [
                    "imageFullsize"=>$image
                ];
            } else {
                $request['imageFile'] = $image;
            }
        } else if (is_resource($image) && in_array(\get_resource_type($image), ["stream", "gd"])) {
            // For resource type, see: https://www.php.net/manual/en/resource.php
            if(\get_resource_type($image) == "gd") {
                $temp = tempnam(sys_get_temp_dir(), "limg");
                imagepng($image, $temp);
                $request['imageFile'] = file_get_contents($temp);
                unlink($temp);
            } else {
                rewind($image);
                $request['imageFile'] = stream_get_contents($image);
            }
        } else if ($image) {
            throw new \InvalidArgumentException("Unknown \$image var type or it's unsupported.");
        }

        if($stickerId && $stickerPkgId) {
            $request['stickerPackageId'] = $stickerPkgId;
            $request['stickerId'] = $stickerId;
        } else if ((!$stickerId || !$stickerPkgId) && !(!$stickerId && !$stickerPkgId)) {
            throw new \InvalidArgumentException("If you fill sticker ID/sticker package ID, you need to fill both.");
        }

        $body = $this->api->post("api/notify", $request,[], "header");
        $res = json_decode($body->getBody(), true);

        return $res;
    }

    public function status() {
        $body = $this->api->get("api/status", [],[], "header");
        $res = json_decode($body->getBody(), true);

        return $res;
    }

    public function revoke() {
        $body = $this->api->get("api/revoke", [],[], "header");
        $res = json_decode($body->getBody(), true);

        return $res;
    }
}