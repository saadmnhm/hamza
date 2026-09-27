<?php

namespace App\Livewire\Config;

use Livewire\Attributes\Title;
use Livewire\Component;

class Config extends Component
{

    public function save(){

    }
    
    public function render()
    {
        return view('livewire.config.config');
    }
}