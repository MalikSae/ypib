<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->text('question_text');
            $table->string('option_a', 500);
            $table->string('option_b', 500);
            $table->string('option_c', 500);
            $table->string('option_d', 500);
            $table->enum('correct_option', ['a', 'b', 'c', 'd']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('exam_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->unique()->constrained()->cascadeOnDelete();
            $table->enum('status', ['in_progress', 'completed'])->default('in_progress');
            $table->decimal('score', 5, 2)->nullable();
            $table->enum('result_label', ['sangat_baik', 'baik'])->nullable();
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('exam_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_question_id')->constrained()->cascadeOnDelete();
            $table->enum('selected_option', ['a', 'b', 'c', 'd'])->nullable();
            $table->boolean('is_correct')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_answers');
        Schema::dropIfExists('exam_sessions');
        Schema::dropIfExists('exam_questions');
    }
};
