<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class QrGroup extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'public_token',
    ];

    protected static function booted(): void
    {
        static::creating(function (QrGroup $group): void {
            if (empty($group->public_token)) {
                $group->public_token = (string) Str::uuid();
            }
        });
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<QrGroupItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(QrGroupItem::class);
    }

    /**
     * @return HasMany<QrGroupItem, $this>
     */
    public function documentItems(): HasMany
    {
        return $this->items()->where('item_type', QrGroupItem::TYPE_DOCUMENT);
    }

    /**
     * @return HasMany<QrGroupItem, $this>
     */
    public function clientItems(): HasMany
    {
        return $this->items()->where('item_type', QrGroupItem::TYPE_CLIENT);
    }
}
