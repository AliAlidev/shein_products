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
            $table->string('ar_name')->nullable();
            $table->string('en_name')->nullable();
            $table->float('price')->nullable();
            $table->text('ar_description')->nullable();
            $table->text('en_description')->nullable();
            $table->string('ar_brand')->nullable();
            $table->string('en_brand')->nullable();
            $table->string('store')->nullable();
            $table->string('barcode')->nullable()->unique();
            $table->date('creation_date')->nullable();
            $table->json('images')->nullable();
            $table->json('additional_data')->nullable();
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
