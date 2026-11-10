<?php

namespace App\Models;

use App\Livewire\Documents\DocumentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property array<string, mixed>|null $meta
 */
class QrGroupItem extends Model
{
    public const TYPE_CLIENT = 'client';

    public const TYPE_DOCUMENT = 'document';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'qr_group_id',
        'item_type',
        'item_id',
        'public_token',
        'label',
        'meta',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    /**
     * @return BelongsTo<QrGroup, $this>
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(QrGroup::class, 'qr_group_id');
    }

    /**
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'item_id');
    }

    /**
     * @return BelongsTo<DocumentHistory, $this>
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(DocumentHistory::class, 'item_id');
    }

    public function isClient(): bool
    {
        return $this->item_type === self::TYPE_CLIENT;
    }

    public function isDocument(): bool
    {
        return $this->item_type === self::TYPE_DOCUMENT;
    }

    /**
     * Archive URL the QR code points to.
     */
    public function url(): string
    {
        return $this->isClient()
            ? route('archive.clients.show', $this->public_token)
            : route('archive.documents.show', $this->public_token);
    }

    /**
     * Human-readable type shown on labels.
     */
    public function typeLabel(): string
    {
        if ($this->isClient()) {
            return (string) (data_get($this->meta, 'type_label') ?: 'Client');
        }

        $type = DocumentType::tryFrom((string) data_get($this->meta, 'document_type', ''));

        return $type?->title() ?? 'Document';
    }

    public function icon(): string
    {
        return $this->isClient() ? 'user' : 'document-text';
    }
}
