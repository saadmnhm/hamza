<?php

namespace App\Livewire\Documents;

enum DocumentType: string
{
    case EMPLOYMENT_CONTRACT = 'employment_contract';
    case SALARY_SLIP = 'salary_slip';
    case INVOICE = 'invoice';
    case DELIVERY_NOTE = 'delivery_note';
    case PURCHASE_ORDER = 'purchase_order';

    public function category(): string
    {
        return match ($this) {
            self::EMPLOYMENT_CONTRACT, self::SALARY_SLIP => 'HR',
            self::INVOICE, self::DELIVERY_NOTE, self::PURCHASE_ORDER => 'Accounting',
        };
    }

    public function title(): string
    {
        return match ($this) {
            self::EMPLOYMENT_CONTRACT => 'Contrat de travail',
            self::SALARY_SLIP => 'Fiche de paie',
            self::INVOICE => 'Facture',
            self::DELIVERY_NOTE => 'Bon de livraison',
            self::PURCHASE_ORDER => 'Bon de commande',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::EMPLOYMENT_CONTRACT => 'Générez un contrat de travail pour un nouvel employé',
            self::SALARY_SLIP => 'Générez une fiche de paie mensuelle',
            self::INVOICE => 'Générez une facture client',
            self::DELIVERY_NOTE => 'Générez un bon de livraison client',
            self::PURCHASE_ORDER => 'Générez un bon de commande client',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::EMPLOYMENT_CONTRACT => 'users',
            self::SALARY_SLIP => 'banknotes',
            self::INVOICE => 'document-text',
            self::DELIVERY_NOTE => 'truck',
            self::PURCHASE_ORDER => 'clipboard-document-list',
        };
    }
}
