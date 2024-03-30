<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TrialQuestions extends Model
{
    use HasFactory;
    protected $table = 'trial_questions';

    protected $fillable = ['trial_id', 'question_id', 'answer', 'is_right'];

    public function question(): HasOne
    {
        return $this->hasOne(Question::class, 'id', 'question_id');
    }

    public function trial(): BelongsTo
    {
        return $this->belongsTo(Trial::class);
    }
}
