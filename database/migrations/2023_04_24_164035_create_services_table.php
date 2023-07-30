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
        Schema::create('services', function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->uuid("user_id");  // company desgin
            $table->string("offer")->nullable();
            $table->date("expaired_offer")->nullable();
            $table->string("desc")->nullable();
            $table->string("time_to_finish")->nullable();
            $table->string("address_project")->nullable();
            $table->string("notes")->nullable();
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
        Schema::dropIfExists('services');
    }
};