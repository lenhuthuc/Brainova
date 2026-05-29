<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * RagMessage model representing a single message in a RAG conversation.
 *
 * @property int $id
 * @property int $rag_conversation_id
 * @property string $role
 * @property string $content
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class RagMessage extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rag_messages';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'rag_conversation_id',
        'role',
        'content',
    ];

    /**
     * Get the conversation that this message belongs to.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(RagConversation::class, 'rag_conversation_id');
    }
}
