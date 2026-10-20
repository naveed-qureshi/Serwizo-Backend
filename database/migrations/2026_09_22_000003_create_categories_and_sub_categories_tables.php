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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150)->unique();
            $table->string('slug', 150)->unique();
            $table->text('description')->nullable();
            $table->string('icon', 255)->nullable();
            $table->string('status', 20)->default('active'); // active, inactive
            $table->timestamps();
        });

        Schema::create('sub_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('title', 150);
            $table->string('slug', 150);
            $table->text('description')->nullable();
            $table->string('icon', 255)->nullable();
            $table->string('status', 20)->default('active'); // active, inactive
            $table->timestamps();

            $table->unique(['category_id', 'title']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_categories');
        Schema::dropIfExists('categories');
    }
};
