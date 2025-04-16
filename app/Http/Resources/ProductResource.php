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
        return [
            'id' => $this->external_id,
            'name' => getCurrentLanguage() == 'ar' ? $this->ar_name : $this->en_name,
            'description' => getCurrentLanguage() == 'ar' ? $this->ar_description : $this->en_description,
            'slug' => $this->slug,
            'currency' => getDesiredCurrency(),
            'original_price' => $this->formatAmount($this->currencyConversion(getDesiredCurrency(), $this->price)),
            'price' => $this->formatAmount($this->finalPrice),
            'textural_price' => $this->getTextualPrice($this->finalPrice),
            'category' =>  getCurrentLanguage() == 'ar' ? $this->category?->name_ar : $this->category?->name_en,
            'primary_image' => $this->primaryImage(),
            'images' => $this->images,
            'store' => $this->store,
            'video_url' => $this->video_url,
            'brand' => optional($this->brand)['brand_name_' . getCurrentLanguage()],
            'section' => $this->node->channel,
            'section_type' => $this->node->root_name,
            'amount' => $this->details->stock,
            'sold_out_status' => $this->details->sold_out_status,
            'is_on_sale' => $this->details->is_on_sale,
            'coupon_discount' => $couponData ? $this->formatAmount($this->currencyConversion(getDesiredCurrency(), $couponData[0]['discount_value']['amount'])) : null,
            'coupon_code' => $couponData ? $couponData[0]['coupon_code'] ?? null : null,
            'coupon_end_time' => $couponData ? date('Y-m-d H:i:s', $couponData[0]['end_time']) ?? null : null,
            'price_after_coupon' => $couponData ? $this->formatAmount($this->currencyConversion(getDesiredCurrency(), ($this->finalPrice - $couponData[0]['discount_value']['amount']))) : null
        ];
    }
}
