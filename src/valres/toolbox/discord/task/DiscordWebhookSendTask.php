<?php

declare(strict_types=1);

namespace valres\toolbox\discord\task;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

final class DiscordWebhookSendTask extends AsyncTask {
    public function __construct(
        private readonly string $url,
        private readonly string $payload
    ) {
    }

    public function onRun(): void {
        if (!function_exists("curl_init")) {
            $this->setResult([
                "ok" => false,
                "code" => 0,
                "body" => "The curl extension is not available."
            ]);
            return;
        }

        $curl = curl_init($this->url);
        if ($curl === false) {
            $this->setResult([
                "ok" => false,
                "code" => 0,
                "body" => "Unable to initialize curl."
            ]);
            return;
        }

        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $this->payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json"
            ]
        ]);

        $body = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        $this->setResult([
            "ok" => $body !== false && ($code === 200 || $code === 204),
            "code" => $code,
            "body" => $body === false ? $error : (string) $body
        ]);
    }

    public function onCompletion(): void {
        $result = $this->getResult();
        if (!is_array($result) || ($result["ok"] ?? false)) {
            return;
        }

        Server::getInstance()->getLogger()->error(
            "[DiscordWebhook] Got error ({$result["code"]}): {$result["body"]}"
        );
    }
}
