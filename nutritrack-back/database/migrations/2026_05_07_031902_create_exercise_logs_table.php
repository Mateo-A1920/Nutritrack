<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exercise_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workout_session_id')->constrained()->onDelete('cascade');
            $table->string('exercise_name');
            $table->integer('set_number');
            $table->integer('reps')->nullable();
            $table->decimal('weight_kg', 6, 2)->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->integer('rounds')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('exercise_logs'); }
};