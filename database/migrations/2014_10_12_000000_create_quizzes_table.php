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
        Schema::create('quizzes', function (Blueprint $table) {
            $table->string('quiz_id')->unique();
            $table->string('user_id');
            $table->string('quiz_name');
            $table->string('quiz_sub_name')->default('');
            $table->integer('questions')->default(0);
            $table->string('description')->default('');
            $table->string('twitter_id')->default('');
            $table->string('url1')->default('');
            $table->string('url2')->default('');
            $table->string('url3')->default('');
            $table->integer('publishing');
            $table->integer('challenge_count');
            // $table->rememberToken();
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
