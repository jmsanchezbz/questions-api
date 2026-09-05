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
        Schema::create('question', function (Blueprint $table) {
            $table->increments('id');
            $table->string('administration',10);
            $table->string('grup',2);
            $table->unsignedInteger('theme');
            $table->unsignedInteger('number');
            $table->string('question',1000);
            $table->string('option1',1000);
            $table->string('option2',1000);
            $table->string('option3',1000);
            $table->string('option4',1000);
            $table->unsignedInteger('answer')->nullable();
            $table->string('explanation',4000);
            $table->boolean('verified');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question');
    }
};
