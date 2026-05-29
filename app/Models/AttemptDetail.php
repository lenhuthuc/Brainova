<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AttemptDetail model representing a student's answer to a specific question in an attempt.
 *
 * @property int $id
 * @property int $attempt_id
 * @property int $question_id
 * @property int|null $answer_id
 * @property string|null $text_answer
 * @property bool $is_correct
 * @property float $points_earned
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class AttemptDetail extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'attempt_details';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'attempt_id',
        'question_id',
        'answer_id',
        'text_answer',
        'is_correct',
        'points_earned',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'points_earned' => 'decimal:2',
        ];
    }

    /**
     * Get the attempt that this detail belongs to.
     */
    public function attempt(): BelongsTo
    {
        return $this->belongsTo(Attempt::class);
    }

    /**
     * Get the question that was answered.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Get the selected answer (if applicable).
     */
    public function answer(): BelongsTo
    {
        return $this->belongsTo(Answer::class);
    }
}
