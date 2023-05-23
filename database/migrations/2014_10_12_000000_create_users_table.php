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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->string('user_name');
            $table->string('phone_number')->unique();
            $table->string('password');
            $table->integer("user_type"); // 0 admin 1 users 2 hotels 3 company desgin
            $table->string("image")->nullable();
            $table->string("adderss")->nullable();
            $table->string("longetude")->nullable();
            $table->string("latetude")->nullable();
            $table->boolean("active")->default(1);
            $table->softDeletes();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
};
