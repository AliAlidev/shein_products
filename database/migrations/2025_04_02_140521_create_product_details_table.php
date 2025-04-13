<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('product_relation_id')->nullable();

            $table->json('product_material')->nullable(); // Stores material data as JSON

            // Pricing
            $table->decimal('retail_price', 10, 2);
            $table->decimal('sale_price', 10, 2);
            $table->decimal('discount_price', 10, 2)->default(0);
            $table->decimal('retail_discount_price', 10, 2)->default(0);
            $table->decimal('unit_discount', 5, 2)->default(0); // Percentage
            $table->decimal('srp_discount', 5, 2)->default(0); // Percentage
            $table->json('promotion_info')->nullable(); // Stores array of promotions

            // Stock & availability
            $table->integer('stock')->default(0);
            $table->boolean('sold_out_status')->default(false);
            $table->boolean('is_on_sale')->default(false);

            // Variants
            $table->json('related_color_new')->nullable(); // Stores array of color variant images

            $table->json('feature_subscript')->nullable(); // Stores array of feature labels
            $table->json('coupon_prices')->nullable(); // Stores array of feature labels
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_details');
    }
};
