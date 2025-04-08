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
        Schema::create('product_brands', function (Blueprint $table) {
            $table->id();
            $table->string('brand_name_en'); // premiumFlagNew > brandName
            $table->string('brand_name_ar'); // premiumFlagNew > brandName
            $table->string('external_id'); // premiumFlagNew > brandId
            $table->string('brand_badge_name_en')->nullable(); // premiumFlagNew > brand_badge_name
            $table->string('brand_badge_name_ar')->nullable(); // premiumFlagNew > brand_badge_name
            $table->string('brand_code')->nullable(); // premiumFlagNew > brand_code
            $table->string('series_badge_name_en')->nullable(); // premiumFlagNew > series_badge_name
            $table->string('series_badge_name_ar')->nullable(); // premiumFlagNew > series_badge_name
            $table->string('series_id')->nullable(); // premiumFlagNew > seriesId
            $table->json('series_logo')->nullable(); // premiumFlagNew > series_logo_url_left, series_logo_url_right
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
        Schema::dropIfExists('product_brands');
    }
};
