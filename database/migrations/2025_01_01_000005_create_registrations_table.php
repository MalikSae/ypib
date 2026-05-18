<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('pmb_periods');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('referrer_id')->nullable()->constrained('referrers')->nullOnDelete();
            $table->string('registration_number')->unique();
            $table->foreignId('first_choice_program_id')->constrained('programs');
            $table->foreignId('second_choice_program_id')->nullable()->constrained('programs')->nullOnDelete();
            $table->string('full_name');
            $table->string('nik', 20);
            $table->string('birth_place');
            $table->date('birth_date');
            $table->enum('gender', ['male', 'female']);
            $table->text('address');
            $table->string('phone', 20);
            $table->string('school_name');
            $table->year('graduation_year');
            $table->decimal('school_grade', 5, 2)->nullable();
            $table->string('payment_proof')->nullable();
            $table->enum('status', [
                'draft', 'submitted', 'payment_pending', 'payment_confirmed',
                'document_pending', 'verified', 'revision', 'accepted', 'rejected'
            ])->default('draft');
            $table->text('internal_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
