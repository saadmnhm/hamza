<?php

namespace App\Livewire\Config;

use App\Models\Config as configs;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class Config extends Component
{
    use WithFileUploads;

    public ?configs $config = null;

    public $logo;

    public $existing_logo_path = null;

    #[Rule('required|string|max:255')]
    public $raison_sociale = null;

    #[Rule('nullable|string|max:255')]
    public $nom_commercial = null;

    #[Rule('nullable|string|max:100')]
    public $forme_juridique = null;

    #[Rule('required|string|max:15')]
    public $ice = null;

    #[Rule('required|string|max:50')]
    public $if_number = null;

    #[Rule('required|string|max:50')]
    public $cnss = null;

    #[Rule('nullable|string|max:50')]
    public $rc = null;

    #[Rule('nullable|string|max:50')]
    public $patente = null;

    #[Rule('nullable|string|max:150')]
    public $tribunal_rc = null;

    #[Rule('nullable|email|max:255')]
    public $email = null;

    #[Rule('nullable|string|max:50')]
    public $telephone = null;

    #[Rule('nullable|string')]
    public $adresse = null;

    #[Rule('nullable|string|max:100')]
    public $ville = null;

    #[Rule('nullable|string|max:20')]
    public $code_postal = null;

    public function mount(): void
    {
        /** @var User|null $user */
        $user = auth()->user();
        abort_unless($user?->hasPermission('company_view'), 403);

        $this->config = configs::first();

        if ($this->config) {
            $this->fill($this->config->only([
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
            ]));

            $this->existing_logo_path = $this->config->logo_path ?? '';
        }
    }

    public function save(): void
    {
        $validated = $this->validate();

        // Gestion du téléchargement du logo s'il y a un nouveau fichier
        if ($this->logo) {
            if ($this->config && $this->config->logo_path) {
                Storage::disk('public')->delete($this->config->logo_path);
            }

            $validated['logo_path'] = $this->logo->store('logos', 'public');
        }

        // Sauvegarde (Création ou mise à jour du premier enregistrement)
        $this->config = configs::updateOrCreate(
            ['id' => $this->config?->id ?? 1],
            $validated
        );

        $this->existing_logo_path = $this->config->logo_path ?? '';
        $this->reset('logo');

        session()->flash('status', 'La configuration de l\'entreprise a été mise à jour.');
    }

    public function render()
    {

        if (isset($_GET['ddd'])) {
            echo '<pre>';
            print_r(configs::getList());
            exit;
        }

        return view('livewire.config.config');
    }
}
