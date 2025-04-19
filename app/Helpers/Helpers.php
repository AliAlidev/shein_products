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

if (!function_exists('getDesiredCurrency')) {
    function getDesiredCurrency()
    {
        if (request()->hasHeader('currency')) {
            $currency = request()->header('currency');
            $currency = str_replace(' ', '', $currency);
            in_array($currency, ['AED', 'USD', 'SYP']) ?: $currency = 'USD';
            return $currency;
        } else
            return 'USD';
    }
}

if (!function_exists('getTranslatedSection')) {
    function getTranslatedSection($sectionEn)
    {
        $data = [
            'Beauty' => [
                'en' => 'Beauty',
                'ar' => 'الجمال'
            ],
            'Kids' => [
                'en' => 'Kids',
                'ar' => 'الأطفال'
            ],
            'Men' => [
                'en' => 'Men',
                'ar' => 'الرجال'
            ],
            'PlusSize' => [
                'en' => 'Plus Size',
                'ar' => 'مقاسات كبيرة'
            ],
            'Women' => [
                'en' => 'Women',
                'ar' => 'النساء'
            ]
        ];
        return $data[$sectionEn][getCurrentLanguage()];
    }
}

if (!function_exists('getTranslatedSectionTypes')) {
    function getTranslatedSectionTypes($serctionType)
    {
        $data = [
            '#sheinfw23' => [
                'en' => '#sheinfw23',
                'ar' => '#شينفو23'
            ],
            'Baby 0-3Yrs' => [
                'en' => 'Baby 0-3Yrs',
                'ar' => 'طفل 0-3 سنوات'
            ],
            'Beachwear' => [
                'en' => 'Beachwear',
                'ar' => 'ثياب البحر'
            ],
            'Beauty Tools' => [
                'en' => 'Beauty Tools',
                'ar' => 'أدوات التجميل'
            ],
            'Bottoms' => [
                'en' => 'Bottoms',
                'ar' => 'قيعان'
            ],
            'Brands' => [
                'en' => 'Brands',
                'ar' => 'العلامات التجارية'
            ],
            'Clothing' => [
                'en' => 'Clothing',
                'ar' => 'ملابس'
            ],
            'Denim & Jeans' => [
                'en' => 'Denim & Jeans',
                'ar' => 'الدينيم والجينز'
            ],
            'Dresses' => [
                'en' => 'Dresses',
                'ar' => 'فساتين'
            ],
            'Electronics & Stationery' => [
                'en' => 'Electronics & Stationery',
                'ar' => 'الالكترونيات والقرطاسية'
            ],
            'Extended Sizes' => [
                'en' => 'Extended Sizes',
                'ar' => 'أحجام ممتدة'
            ],
            'Fall & Winter' => [
                'en' => 'Fall & Winter',
                'ar' => 'الخريف والشتاء'
            ],
            'Hair' => [
                'en' => 'Hair',
                'ar' => 'شعر'
            ],
            'Home & Pets' => [
                'en' => 'Home & Pets',
                'ar' => 'المنزل والحيوانات الأليفة'
            ],
            'Lingerie & Lounge' => [
                'en' => 'Lingerie & Lounge',
                'ar' => 'الملابس الداخلية والصالة'
            ],
            'Makeup' => [
                'en' => 'Makeup',
                'ar' => 'ماكياج'
            ],
            'Maternity & Nursing' => [
                'en' => 'Maternity & Nursing',
                'ar' => 'الأمومة والتمريض'
            ],
            'Nail Hand & Foot Care' => [
                'en' => 'Nail Hand & Foot Care',
                'ar' => 'العناية بالأظافر واليدين والقدمين'
            ],
            'New In' => [
                'en' => 'New In',
                'ar' => 'جديد في'
            ],
            'Personal Care' => [
                'en' => 'Personal Care',
                'ar' => 'العناية الشخصية'
            ],
            'Sale' => [
                'en' => 'Sale',
                'ar' => 'أُوكَازيُون'
            ],
            'Shein X Designers' => [
                'en' => 'Shein X Designers',
                'ar' => 'المصممين شيين X'
            ],
            'Shoes' => [
                'en' => 'Shoes',
                'ar' => 'أحذية'
            ],
            'Shoes & Accessories' => [
                'en' => 'Shoes & Accessories',
                'ar' => 'الأحذية والإكسسوارات'
            ],
            'Shoes & Accs' => [
                'en' => 'Shoes & Accs',
                'ar' => 'الأحذية والإكسسوارات'
            ],
            'Sports & Outdoor' => [
                'en' => 'Sports & Outdoor',
                'ar' => 'الرياضة والخارجية'
            ],
            'Swimwear' => [
                'en' => 'Swimwear',
                'ar' => 'ملابس السباحة'
            ],
            'Teen Boys 13-16Yrs' => [
                'en' => 'Teen Boys 13-16Yrs',
                'ar' => 'الأولاد المراهقون 13-16 سنة'
            ],
            'Teen Girls 13-16Yrs' => [
                'en' => 'Teen Girls 13-16Yrs',
                'ar' => 'الفتيات المراهقات 13-16 سنة'
            ],
            'Tops' => [
                'en' => 'Tops',
                'ar' => 'قمم'
            ],
            'Trends' => [
                'en' => 'Trends',
                'ar' => 'الاتجاهات'
            ],
            'Tween Boys 8-12Yrs' => [
                'en' => 'Tween Boys 8-12Yrs',
                'ar' => 'توين بويز 8-12 سنة'
            ],
            'Tween Girls 8-12Yrs' => [
                'en' => 'Tween Girls 8-12Yrs',
                'ar' => 'توين بنات 8-12 سنة'
            ],
            'Young Boys 3-7Yrs' => [
                'en' => 'Young Boys 3-7Yrs',
                'ar' => 'الأولاد الصغار 3-7 سنوات'
            ],
            'Young Girls 3-7Yrs' => [
                'en' => 'Young Girls 3-7Yrs',
                'ar' => 'الفتيات الصغيرات 3-7 سنوات'
            ],
        ];
        return $data[$serctionType][getCurrentLanguage()];
    }
}
