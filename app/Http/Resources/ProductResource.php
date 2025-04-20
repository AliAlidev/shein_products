<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $couponData = $this->details->coupon_prices ?? null;
        $priceRule = $this->priceRule;
        $finalPrice = $this->price;
        $finalCouponPrice = $couponData[0]['after_coupon_price']['amount'] ?? 0;
        $ruleAmount = 0;

        if ($priceRule) {
            if ($priceRule->type === 'fixed') {
                $finalPrice += $priceRule->value;
                $finalCouponPrice += $priceRule->value;
                $ruleAmount = $priceRule->value;
            } elseif ($priceRule->type === 'percentage') {
                $finalPrice += ($finalPrice * ($priceRule->value / 100));
                $finalCouponPrice += ($finalCouponPrice * ($priceRule->value / 100));
                $ruleAmount = ($finalCouponPrice * ($priceRule->value / 100));
            }
        }
        $data = [
            'id' => $this->external_id,
            'name' => getCurrentLanguage() == 'ar' ? $this->ar_name : $this->en_name,
            'description' => getCurrentLanguage() == 'ar' ? $this->ar_description : $this->en_description,
            'slug' => $this->slug,
            'category' =>  getCurrentLanguage() == 'ar' ? $this->category?->name_ar : $this->category?->name_en,
            'primary_image' => $this->primaryImage(),
            'images' => $this->images,
            'store' => $this->store,
            'video_url' => is_numeric($this->video_url) ? '' : $this->video_url,
            'brand' => optional($this->brand)['brand_name_' . getCurrentLanguage()],
            'section' => getTranslatedSection($this->node->channel),
            'section_type' => getTranslatedSectionTypes($this->node->root_name),
            'stock' => $this->details->stock,
            'sold_out_status' => $this->details->sold_out_status,
            'is_on_sale' => $this->details->is_on_sale,
            'original_price' => $this->formatAmount($this->currencyConversion(getDesiredCurrency(), $this->price)),  // the price before adding our rulePrice amount
            'price' => $this->formatAmount($this->currencyConversion(getDesiredCurrency(), $finalPrice)),  // the price after adding our rulePrice amount
            'currency' => getDesiredCurrency(),
            'textual_price' => $this->getTextualPrice($this->price),   //  textual value of the price
            'rule_amount' => $this->formatAmount($this->currencyConversion(getDesiredCurrency(), $ruleAmount)),  // the amount of the applied priceRule
            'rule_type' => $priceRule->type ?? null,  // the type of the applied priceRule fixed or percentage
            'shein_url' => 'https://us.shein.com/' . $this->slug . '-p-' . $this->external_id . '.html',
            'last_updated' => $this->last_updated
        ];
        if ($couponData) {
            $data['coupon_end_time'] = date('Y-m-d H:i:s', $couponData[0]['end_time']);
            $data['coupon_code'] = $couponData ? $couponData[0]['coupon_code'] ?? null : null;
            $data['coupon_discount'] = $couponData[0]['discount_percent'] ?? 0;  // the discount percentage of the coupon
            $data['coupon_price'] = $this->formatAmount($this->currencyConversion(getDesiredCurrency(), $finalCouponPrice)) ?? 0; // coupon price after adding our priceRule amount
            $data['original_coupon_price'] = $this->formatAmount($this->currencyConversion(getDesiredCurrency(), ($couponData[0]['after_coupon_price']['amount']))) ?? 0; // coupon price before adding our priceRule amount
        }
        return  $data;
    }
}
