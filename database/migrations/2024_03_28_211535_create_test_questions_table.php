<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('trial_questions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('trial_id');
            $table->unsignedInteger('question_id');
            $table->foreign('trial_id')->references('id')->on('trial');
            $table->foreign('question_id')->references('id')->on('question');
            $table->unsignedInteger('answer')->nullable();
            $table->boolean('is_right')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_questions');
    }
};
