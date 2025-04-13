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
        return [
            'id' => $this->id,
            'name' => getCurrentLanguage() == 'ar' ? $this->ar_name : $this->en_name,
            'description' => getCurrentLanguage() == 'ar' ? $this->ar_description : $this->en_description,
            'price' => $this->price,
            'category' =>  getCurrentLanguage() == 'ar' ? $this->category?->name_ar : $this->category?->name_en
        ];
    }
}
