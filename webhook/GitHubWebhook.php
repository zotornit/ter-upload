<?php

namespace ZOTORN\Webhook;

class GitHubWebhook implements Webhook
{
    private $secret;
    private $json;
    private $canHandle = false;

    /**
     * GitHubWebhook constructor.
     * @param string $secret
     * @param string $payload
     * @throws \Exception
     */
    public function __construct(string $secret)
    {

        $this->secret = $secret;

        if (!isset($_SERVER['HTTP_X_HUB_SIGNATURE'])) {
            throw new \Exception("HTTP header 'X-Hub-Signature' is missing.");
        } elseif (!extension_loaded('hash')) {
            throw new \Exception("Missing 'hash' extension to check the secret code validity.");
        }

        list($algo, $hash) = explode('=', $_SERVER['HTTP_X_HUB_SIGNATURE'], 2) + array('', '');
        if (!in_array($algo, hash_algos(), TRUE)) {
            throw new \Exception("Hash algorithm '$algo' is not supported.");
        }

        $payload = file_get_contents('php://input');

        if ($hash !== hash_hmac($algo, $payload, $this->secret)) {
            throw new \Exception('Hook secret does not match.');
        }


        if (!isset($_SERVER['CONTENT_TYPE'])) {
            throw new \Exception("Missing HTTP 'Content-Type' header.");
        } elseif (!isset($_SERVER['HTTP_X_GITHUB_EVENT'])) {
            throw new \Exception("Missing HTTP 'X-Github-Event' header.");
        }

        # Payload structure depends on triggered event
        # https://developer.github.com/v3/activity/events/types/
        switch ($_SERVER['CONTENT_TYPE']) {
            case 'application/json':
                $this->json = json_decode($payload, true);
                break;

            case 'application/x-www-form-urlencoded':
                $this->json = json_decode($_POST['payload'], true);
                break;

            default:
                throw new \Exception("Unsupported content type: \$_SERVER['CONTENT_TYPE']");
        }

        $this->canHandle = true;

    }

    function handleEvent(string $event, string $storagePath): bool
    {
        if (!$this->canHandle) return false;

        /*
         * handle the 'push_tag' event
         */
        if ($event === 'push_tag' && preg_match('/^refs\/tags\/(.+)$/', $this->json['ref'], $m)) {
            $tag = $m[1] ?? null;

            if (is_string($tag) && $this->json['created']) { // ignore deleted tags
                $array = [
                    'type' => 'tag',
                    'tag' => $tag,
                    'url' => $this->json["repository"]["url"],
                    'fullname' => $this->json["repository"]["full_name"],
                    'host' => 'github.com',
                    'time' => time(),
                ];
                file_put_contents(rtrim($storagePath, '/') . '/' . md5(time()) . '.new', json_encode($array));
                return true;
            }
        }
        return false;
    }
}
