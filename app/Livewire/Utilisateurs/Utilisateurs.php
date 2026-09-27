<?php

namespace App\Livewire\Utilisateurs;

use Livewire\Attributes\Title;
use Livewire\Component;

class Utilisateurs extends Component
{
    public function render()
    {
        return view('livewire.utilisateurs.users');
    }
}