<?php

namespace App\Services;

use App\Models\Product;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChatGPTTranslationService
{
    protected $client;
    protected $apiKey;

    // Configuration
    const BATCH_SIZE = 50; // Number of texts to translate per API call (adjust based on token limits)
    const MAX_TOKENS_PER_BATCH = 6000; // Stay well below model's max token limit
    const DELAY_BETWEEN_BATCHES = 5; // Seconds to wait between batches to avoid rate limiting
    const API_TIMEOUT = 60; // Increased timeout to 60 seconds
    const MAX_RETRIES = 3;
    const RETRY_DELAY = 5; // Seconds between retries

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = config('services.openai.key');
    }

    public function translateToArabic($text)
    {
        try {
            $response = $this->client->post('https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-4', // Use GPT-4 for better translation quality
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a professional translator with native proficiency in English and Arabic. ' .
                                'Follow these rules for translation:' . PHP_EOL .
                                '- Preserve the exact meaning and context' . PHP_EOL .
                                '- Use natural, fluent Arabic appropriate for the context' . PHP_EOL .
                                '- Maintain technical terms when needed' . PHP_EOL .
                                '- Keep the same tone (formal, informal, etc.) as the original' . PHP_EOL .
                                '- For ambiguous terms, choose the most common Arabic equivalent'
                        ],
                        [
                            'role' => 'user',
                            'content' => "Translate the following text to Modern Standard Arabic while preserving all nuances:\n\n" .
                                "Original text: \"$text\"\n\n" .
                                "Provide only the Arabic translation without additional commentary or explanations."
                        ]
                    ],
                    'temperature' => 0.3, // Lower temperature for more deterministic output
                    'top_p' => 0.9,
                    'max_tokens' => 2000, // Increased token limit for longer texts
                    'frequency_penalty' => 0.2, // Slightly reduce repetition
                    'presence_penalty' => 0.1
                ],
                'timeout' => 30 // Increased timeout for longer texts
            ]);

            try {
                $body = json_decode($response->getBody()->getContents(), true);

                if (!isset($body['choices'][0]['message']['content'])) {
                    throw new Exception('No translation content in API response');
                }

                $translation = trim($body['choices'][0]['message']['content']);

                // Remove any quotation marks that might have been added
                $translation = preg_replace('/^["\']+|["\']+$/u', '', $translation);

                return $translation ?: 'Translation failed. No output generated.';
            } catch (Exception $e) {
                // Log error here if needed
                return 'Translation failed. Error: ' . $e->getMessage();
            }
        } catch (\Throwable $th) {
            dd($th->getMessage());
        }
    }


    public function translateBulkRecords()
    {
        Log::info("Translation batch starting ...");
        // 1. Fetch untranslated records
        $records = DB::table('products')
            ->whereNull('ar_name') // Or whatever condition marks untranslated records
            ->select('id', 'en_name') // Include primary key and text to translate
            ->limit(5000)
            ->get();

        // 2. Process in batches
        $processed = 0;

        foreach (array_chunk($records->toArray(), self::BATCH_SIZE) as $batch) {
            try {
                $englishTexts = array_column($batch, 'en_name');
                $translationResults = $this->translateBatch($englishTexts);
                $this->updateTranslations($batch, $translationResults);
                $processed += count($batch);
                sleep(self::DELAY_BETWEEN_BATCHES);
            } catch (\Exception $e) {
                Log::channel('translate_to_arabic')->alert("Batch failed: " . $e->getMessage() . PHP_EOL);
                continue;
            }
        }

        return "Completed. Translated $processed records.";
    }

    protected function translateBatch(array $texts)
    {
        $attempt = 0;
        $lastError = null;

        while ($attempt < self::MAX_RETRIES) {
            try {
                $messages = [
                    [
                        'role' => 'user',
                        'content' => "Translate the following English phrases into Arabic. Return ONLY a JSON array with numeric indexes of Arabic translations in the same order. " .
                            "No extra text. Example input: [\"Hello\", \"Goodbye\"] → Output: [\"مرحبا\", \"مع السلامة\"]\n\n" .
                            "Input: " . json_encode($texts, JSON_UNESCAPED_UNICODE)
                    ]
                ];

                $response = $this->client->post('https://api.openai.com/v1/chat/completions', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->apiKey,
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'model' => 'gpt-3.5-turbo', // More reliable than GPT-4 for batch processing
                        'messages' => $messages,
                        'temperature' => 0.3,
                        'response_format' => ['type' => 'json_object'],
                        'max_tokens' => 2000, // Conservative limit
                    ],
                    'timeout' => self::API_TIMEOUT
                ]);

                $raw = $response->getBody()->getContents();
                $utf8 = mb_convert_encoding($raw, 'UTF-8', mb_detect_encoding($raw));
                $body = json_decode($utf8, true);

                $responseContent = trim($body['choices'][0]['message']['content'] ?? '');
                $translations = json_decode($responseContent, true);
                if ($translations != null)
                    return $translations;
            } catch (\Exception $e) {
                $lastError = $e;
                $attempt++;
                sleep(self::RETRY_DELAY * $attempt); // Exponential backoff
            }
        }
        Log::channel('translate_to_arabic')->alert("Failed after: " . self::MAX_RETRIES . " attempts. Last error: " . $lastError->getMessage() . PHP_EOL);
        exit();
    }

    protected function updateTranslations(array $batch, array $translations)
    {
        $updates = [];

        foreach ($batch as $index => $record) {
            $record = json_decode(json_encode($record), true);
            if (!empty($translations[$index])) {
                $updates[] = [
                    'id' => $record['id'],
                    'ar_name' => $translations[$index]
                ];
            }
        }

        // Batch update using a single query
        DB::transaction(function () use ($updates) {
            foreach ($updates as $update) {
                DB::table('products')
                    ->where('id', $update['id'])
                    ->update([
                        'ar_name' => $update['ar_name']
                    ]);
            }
        });
        $this->counter += count($updates);
        Log::info("Total new records " . $this->counter);
    }
    protected  $counter = 0;
}
