<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;
    protected $table = 'question';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['id', 'administration', 'grup', 'number', 'question', 'option1', 'option2', 'option3', 'option4','answer', 'explanation', 'verified'];

    protected $guarded = ['created_at'];

    public $timestamps = true;

    public function trialQuestions(): HasMany
    {
        return $this->hasMany(TrialQuestions::class);
    }

}
