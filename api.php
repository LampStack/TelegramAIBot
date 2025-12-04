<?php

class BytezAPI {
    private $apiKey;
    private $baseUrl;

    public function __construct(string $apiKey, string $baseUrl = "https://api.bytez.com/models/v2/") {
        $this->apiKey = $apiKey;
        $this->baseUrl = rtrim($baseUrl, "/") . "/";
    }

    private function request(string $model, array $payload) {
        $url = $this->baseUrl . $model;

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                "Authorization: {$this->apiKey}",
                "Content-Type: application/json"
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return "cURL Error: $error";
        }

        curl_close($ch);
        return $response;
    }

    public function chat(string $model, array $messages) {
        return $this->request($model, [
            "messages" => $messages
        ]);
    }

    public function text_to_image(string $model, string $text) {
        return $this->request($model, [
            "text" => $text
        ]);
    }
}

// include 'config.php';

// $bytez = new BytezAPI($bytez_api_key);

// $chatResponse = $bytez->chat(
//     $models['chat'][0],
//     [
//         ["role" => "user", "content" => 'سلام یه میوه نام ببر']
//     ]
// );
// echo $chatResponse;


// $imageResponse = $bytez->text_to_image(
//     $models['text_to_image'][2],
//     "A beautiful landscape with mountains and a river"
// );
// echo $imageResponse;



