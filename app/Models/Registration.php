<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    use HasFactory;

    protected $fillable = [
        'period_id',
        'user_id',
        'referrer_id',
        'registration_number',
        'admission_path',
        'first_choice_program_id',
        'second_choice_program_id',
        'full_name',
        'nik',
        'birth_place',
        'birth_date',
        'gender',
        'address',
        'phone',
        'school_name',
        'graduation_year',
        'school_grade',
        'payment_proof',
        'document_proof',
        're_registration_payment_proof',
        'status',
        'internal_notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function referrer()
    {
        return $this->belongsTo(Referrer::class);
    }

    public function period()
    {
        return $this->belongsTo(PmbPeriod::class, 'period_id');
    }

    public function firstChoiceProgram()
    {
        return $this->belongsTo(Program::class, 'first_choice_program_id');
    }

    public function secondChoiceProgram()
    {
        return $this->belongsTo(Program::class, 'second_choice_program_id');
    }

    public function paymentLogs()
    {
        return $this->hasMany(PaymentLog::class);
    }

    public function rewards()
    {
        return $this->hasMany(Reward::class);
    }
}
