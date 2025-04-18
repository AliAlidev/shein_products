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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('normal_en_name')->nullable();
            $table->string('ar_name')->nullable();
            $table->string('en_name')->nullable();
            $table->string('slug')->nullable();  // goods_url_name
            $table->string('external_id')->nullable();  // goods_id
            $table->string('external_sku')->nullable();  // goods_sn
            $table->float('price')->nullable();
            $table->string('currency')->nullable();
            $table->text('ar_description')->nullable();
            $table->text('en_description')->nullable();
            $table->string('store')->nullable();
            $table->string('barcode')->nullable();
            $table->string('primary_image')->nullable();
            $table->date('creation_date')->nullable();
            $table->date('last_updated')->nullable();
            $table->boolean('view_in_app')->default(0);
            $table->boolean('is_lowest_price')->default(0);
            $table->boolean('is_highest_sales')->default(0);
            $table->string('video_url')->nullable();
            $table->string('mall_code')->nullable();
            $table->json('images')->nullable();
            $table->json('parent_categories')->nullable();
            $table->foreignId('category_id')->nullable();
            $table->foreignId('brand_id')->nullable();
            $table->foreignId('node_id')->nullable();
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
        Schema::dropIfExists('products');
    }
};
