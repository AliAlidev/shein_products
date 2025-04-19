<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use NumberFormatter;

class Product extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        'images' => 'array',
        'additional_data' => 'array',
        'parent_categories' => 'array'
    ];

    public function scopeSearch($query, $term)
    {
        $term = $term . '%';
        return $query->where(function ($q) use ($term) {
            $q->where('ar_name', 'LIKE', $term)
                ->orWhere('en_name', 'LIKE', $term)
                ->orWhere('slug', 'LIKE', $term)
                ->orWhere('external_sku', 'LIKE', $term)
                ->orWhere('price', 'LIKE', $term)
                ->orWhere('currency', 'LIKE', $term)
                ->orWhere('ar_description', 'LIKE', $term)
                ->orWhere('en_description', 'LIKE', $term)
                ->orWhere('store', 'LIKE', $term)
                ->orWhere('barcode', 'LIKE', $term)
                ->orWhere('video_url', 'LIKE', $term)
                ->orWhere('mall_code', 'LIKE', $term);
        });
    }

    function primaryImage()
    {
        return $this->primary_image ?? $this->images[0] ?? null;
    }

    function details()
    {
        return $this->hasOne(ProductDetail::class, 'product_id');
    }

    function brand()
    {
        return $this->hasOne(ProductBrand::class, 'id', 'brand_id');
    }

    function category()
    {
        return $this->hasOne(ProductCategory::class, 'external_id', 'category_id');
    }

    function node()
    {
        return $this->hasOne(SheinNode::class, 'id', 'node_id');
    }

    public function getPriceRuleAttribute()
    {
        $basePrice = $this->price;
        $rule = PriceRule::where('apply_per', 'Product')->whereJsonContains('apply_to', (string)$this->external_id)->first();
        if (!$rule)
            $rule = PriceRule::where('apply_per', 'Category')->whereJsonContains('apply_to', (string)$this->category_id)->first();
        if (!$rule)
            $rule = PriceRule::where('apply_per', 'Default')->first();
        return $rule;
    }

    function getTextualPrice($number)
    {
        $fmt = new NumberFormatter('en', NumberFormatter::SPELLOUT);
        $number = number_format($number, 2, '.', '');
        [$main, $fraction] = explode('.', $number);
        $mainWords = $fmt->format($main);
        $fractionWords = $fmt->format($fraction);
        $mainUnit = 'dollar';
        $fractionUnit = 'cent';
        switch (strtoupper(getDesiredCurrency())) {
            case 'AED': // UAE Dirham
                $mainUnit = 'dirham';
                $fractionUnit = 'fils';
                break;
            case 'SYP': // Syrian Pound
                $mainUnit = 'Syrian pound';
                $fractionUnit = 'piastre';
                break;
            case 'USD':
            default:
                $mainUnit = 'dollar';
                $fractionUnit = 'cent';
                break;
        }
        $result = ucfirst($mainWords) . ' ' . $mainUnit . ($main != 1 ? 's' : '');
        if ((int)$fraction > 0) {
            $result .= ' and ' . $fractionWords . ' ' . $fractionUnit . ($fraction != 1 ? 's' : '');
        }
        return $result;
    }

    function currencyConversion($desiredCurrency, $amount = 0)
    {
        return $amount * $this->currencyRatios()[$this->currency][$desiredCurrency];
    }

    function currencyRatios()
    {
        return [
            'USD' => [
                'AED' => 3.67,
                'SYP' => 12500,
                'USD' => 1
            ]
        ];
    }

    function formatAmount($number, $decimals = 2, $decimalPoint = '.', $thousandsSep = ',')
    {
        $factor = pow(10, $decimals);
        $truncated = floor($number * $factor) / $factor;
        return number_format($truncated, $decimals, $decimalPoint, $thousandsSep);
    }
}
