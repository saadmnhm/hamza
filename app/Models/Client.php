<?php

namespace App\Models;

use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (Client $client): void {
            if (empty($client->public_token)) {
                $client->public_token = (string) Str::uuid();
            }
        });
    }

    public const TYPE_COMPANY = 'company';

    public const TYPE_INDIVIDUAL = 'individual';

    /** @var list<string> */
    public const TYPES = [self::TYPE_COMPANY, self::TYPE_INDIVIDUAL];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'public_token',
        'type',
        'company_name',
        'first_name',
        'last_name',
        'ice',
        'if_number',
        'rc',
        'cnss',
        'cin',
        'address',
        'city',
        'postal_code',
        'phone',
        'email',
        'notes',
    ];

    /**
     * Invoices issued to this client.
     *
     * @return HasMany<Invoice, $this>
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Documents generated for this client.
     *
     * @return HasMany<DocumentHistory, $this>
     */
    public function documentHistories(): HasMany
    {
        return $this->hasMany(DocumentHistory::class);
    }

    /**
     * Demandes recorded for this client.
     *
     * @return HasMany<Demande, $this>
     */
    public function Demandes(): HasMany
    {
        return $this->hasMany(Demande::class);
    }

    public function isCompany(): bool
    {
        return $this->type === self::TYPE_COMPANY;
    }

    /**
     * Human-readable client name for lists and documents.
     */
    public function displayName(): string
    {
        if ($this->isCompany()) {
            return $this->company_name ?: 'Client #'.$this->getKey();
        }

        $name = trim(($this->first_name ?? '').' '.($this->last_name ?? ''));

        return $name !== '' ? $name : 'Client #'.$this->getKey();
    }

    /**
     * Full formatted address.
     */
    public function fullAddress(): string
    {
        return collect([$this->address, $this->postal_code, $this->city])
            ->filter()
            ->implode(', ');
    }

    /**
     * Normalised client payload consumed by the document templates.
     *
     * @return array<string, string|null>
     */
    public function toDocumentData(): array
    {
        return [
            'type' => $this->type,
            'name' => $this->displayName(),
            'company_name' => $this->company_name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'ice' => $this->ice,
            'if_number' => $this->if_number,
            'rc' => $this->rc,
            'cnss' => $this->cnss,
            'cin' => $this->cin,
            'address' => $this->address,
            'city' => $this->city,
            'postal_code' => $this->postal_code,
            'phone' => $this->phone,
            'email' => $this->email,
        ];
    }
}
