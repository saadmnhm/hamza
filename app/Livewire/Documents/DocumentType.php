<?php

namespace App\Livewire\Documents;

enum DocumentType: string
{
    case EMPLOYMENT_CONTRACT = 'employment_contract';
    case SALARY_SLIP = 'salary_slip';
    case INVOICE = 'invoice';

    public function category(): string
    {
        return match ($this) {
            self::EMPLOYMENT_CONTRACT, self::SALARY_SLIP => 'HR',
            self::INVOICE => 'Accounting',
        };
    }

    public function title(): string
    {
        return match ($this) {
            self::EMPLOYMENT_CONTRACT => 'Contrat de travail',
            self::SALARY_SLIP => 'Fiche de paie',
            self::INVOICE => 'Facture',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::EMPLOYMENT_CONTRACT => 'Générez un contrat de travail pour un nouvel employé',
            self::SALARY_SLIP => 'Générez une fiche de paie mensuelle',
            self::INVOICE => 'Générez une facture client',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::EMPLOYMENT_CONTRACT => 'users',
            self::SALARY_SLIP => 'banknotes',
            self::INVOICE => 'document-text',
        };
    }
}
