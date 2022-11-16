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
        Schema::create('questions', function (Blueprint $table) {
            $table->string('quiz_id');
            $table->integer('quiz_no');
            $table->string('quiz_body');
            $table->string('choices1')->default('');
            $table->string('choices2')->default('');
            $table->string('choices3')->default('');
            $table->string('choices4')->default('');
            // $table->rememberToken();
            $table->timestamps();
            $table->index(['quiz_id', 'quiz_no']);
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
