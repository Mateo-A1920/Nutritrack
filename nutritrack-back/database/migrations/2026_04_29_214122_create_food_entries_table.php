<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('food_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_log_id')->constrained()->onDelete('cascade');
            $table->enum('meal_type', ['desayuno', 'almuerzo', 'cena', 'aperitivo']);
            $table->string('name');
            $table->decimal('calories', 8, 2)->default(0);
            $table->decimal('carbs', 8, 2)->default(0);
            $table->decimal('fats', 8, 2)->default(0);
            $table->decimal('protein', 8, 2)->default(0);
            $table->decimal('sodium', 8, 2)->default(0);
            $table->decimal('sugar', 8, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_entries');
    }
};