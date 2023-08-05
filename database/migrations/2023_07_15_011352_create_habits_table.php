<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('habits', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->string('name');
            $table->string('description');
            $table->string('frequency');
            $table->json('occurrence_days')->nullable();
            $table->integer('streak');
            $table->timestamps();
            $table->softDeletes();
        });
    }
};
