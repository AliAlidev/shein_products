<?php

namespace App\Services;

use GuzzleHttp\Client;

class ChatGPTTranslationService
{
    protected $client;
    protected $apiKey;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = config('services.openai.key');
    }

    public function translateToArabic($text)
    {
        $response = $this->client->post('https://api.openai.com/v1/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'gpt-3.5-turbo',  // You can use any GPT model
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a helpful assistant that translates text to Arabic.'],
                    ['role' => 'user', 'content' => "Translate the following text to Arabic: '$text'"]
                ],
                'temperature' => 0.7,
                'max_tokens' => 1000,
            ]
        ]);

        $body = json_decode($response->getBody()->getContents(), true);
        return $body['choices'][0]['message']['content'] ?? 'Translation failed.';
    }
}
