<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Trial extends Model
{
    use HasFactory;
    protected $table = 'trial';

    protected $fillable = ['name', 'administration', 'grup', 'theme', 'num_questions', 'user_id'];

    public function trialQuestions(): HasMany
    {
        return $this->hasMany(TrialQuestions::class);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }
}
