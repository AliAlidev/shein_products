<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $productName = $this->faker->unique()->name();
        $productDescription = $this->faker->unique()->sentence(100);
        $productBrand= $this->faker->randomElement([
            'samsung', 'lg', 'sony', 'apple', 'huawei', 'xiaomi', 'oppo', 'vivo', 'oneplus', 'motorola'
        ]);
        $productCreationDate = $this->faker->dateTimeBetween('-1 year', 'now');
        return [
            'ar_name' => $productName,
            'en_name' => $productName,
            'ar_description' => $productDescription,
            'en_description' => $productDescription,
            'ar_brand' => $productBrand,
            'en_brand' => $productBrand,
            'store' => 'Photo Store',
            'barcode' => $this->faker->unique()->ean13(),
            'creation_date' => $productCreationDate,
            'images' => ['camera1.jpg'],
            'additional_data' => ['resolution' => '24MP', 'lens' => '18-55mm'],
            'price' => rand(100, 500)
        ];
    }
}
