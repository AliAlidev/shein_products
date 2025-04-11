<?php

use App\Services\ChatGPTTranslationService;
use Stichoza\GoogleTranslate\GoogleTranslate;

if (!function_exists('translateToArabic')) {

    function translateToArabic($text, $openAiInstance)
    {
        if (config('services.openai.want_translation')) {
            if ($openAiInstance instanceof ChatGPTTranslationService) {
                try {
                    return $openAiInstance->translateToArabic($text);
                } catch (\Throwable $th) {
                    return googleFreeTranslation($text);
                }
            } else {
                return googleFreeTranslation($text);
            }
        } else {
            return null;
        }
    }
}

if (!function_exists('googleFreeTranslation')) {
    function googleFreeTranslation($text)
    {
        $tr = new GoogleTranslate('ar');
        return $tr->translate($text);
    }
}
