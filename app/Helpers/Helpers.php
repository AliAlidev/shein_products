<?php

use App\Services\ChatGPTTranslationService;
use Stichoza\GoogleTranslate\GoogleTranslate;

if (!function_exists('translateToArabic')) {

    function translateToArabic($text, $openAiInstance)
    {
        if ($openAiInstance instanceof ChatGPTTranslationService) {
            try {
                return $openAiInstance->translateToArabic($text);
            } catch (\Throwable $th) {
                return googleFreeTranslation($text);
            }
        } else {
            return googleFreeTranslation($text);
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

if (!function_exists('getCurrentLanguage')) {
    function getCurrentLanguage()
    {
        if (request()->hasHeader('lang')) {
            $lang = request()->header('lang');
            $lang = str_replace(' ', '', $lang);
            in_array($lang, ['ar', 'en']) ?: $lang = 'en';
            return $lang;
        } else
            return 'en';
    }
}
