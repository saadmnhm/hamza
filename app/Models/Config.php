<?php

namespace App\Models;

use Database\Factories\ConfigFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Config extends Model
{
    /** @use HasFactory<ConfigFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'configs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'logo_path',
        'raison_sociale',
        'nom_commercial',
        'forme_juridique',
        'ice',
        'if_number',
        'cnss',
        'rc',
        'patente',
        'tribunal_rc',
        'email',
        'telephone',
        'adresse',
        'ville',
        'code_postal',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the public URL for the company logo.
     */
    public function logoUrl(): ?string
    {
        return $this->logo_path ? Storage::url($this->logo_path) : null;
    }

    /**
     * Get the full formatted address string.
     */
    public function fullAddress(): string
    {
        return collect([$this->adresse, $this->code_postal, $this->ville])
            ->filter()
            ->implode(', ');
    }
}
