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
        Schema::create('shein_nodes', function (Blueprint $table) {
            $table->id();
            $table->string('channel');
            $table->string('root_name');
            $table->string('node_name');
            $table->string('nav_node_id')->nullable();
            $table->string('cate_tree_node_id')->nullable();
            $table->string('href_type')->nullable();
            $table->string('href_target');
            $table->string('goods_id')->nullable();
            $table->string('image_url')->nullable();
            $table->timestamps();

            $table->index('channel');
            $table->index('root_name');
            $table->index('href_target');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shein_nodes');
    }
};
