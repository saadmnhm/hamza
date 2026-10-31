<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Permission groups
    |--------------------------------------------------------------------------
    |
    | Central catalogue of the permissions used by the administration area.
    | Each group is rendered as a block in the role configuration modal.
    |
    */

    'groups' => [
        'company' => [
            'label' => 'Configuration & Infos Légales',
            'icon' => 'building-office',
            'permissions' => [
                'company_view' => 'Consulter la fiche entreprise',
                'company_edit' => 'Modifier le logo & coordonnées',
                'fiscal_edit' => 'Gérer les identifiants fiscaux (ICE, CNSS, IF)',
            ],
        ],

        'users' => [
            'label' => 'Gestion des Utilisateurs',
            'icon' => 'users',
            'permissions' => [
                'users_view' => 'Voir la liste des utilisateurs',
                'users_invite' => 'Inviter de nouveaux membres',
                'users_edit' => 'Modifier les profils utilisateurs',
                'roles_manage' => 'Gérer les rôles et permissions',
            ],
        ],

        'billing' => [
            'label' => 'Facturation & Financement',
            'icon' => 'document-text',
            'permissions' => [
                'invoices_view' => 'Consulter les factures et devis',
                'invoices_create' => 'Créer et émettre des factures',
                'invoices_delete' => 'Supprimer/Annuler des documents',
                'reports_export' => 'Exporter les états comptables',
            ],
        ],

        'documents' => [
            'label' => 'Génération de documents',
            'icon' => 'document-duplicate',
            'permissions' => [
                'documents_view' => 'Consulter et générer des documents imprimables',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Role appearance
    |--------------------------------------------------------------------------
    |
    | Default colours and icons for the built-in roles. Custom roles fall back
    | to the "default" entry.
    |
    */

    'colors' => [
        'admin' => 'purple',
        'manager' => 'blue',
        'comptable' => 'emerald',
        'employe' => 'zinc',
        'default' => 'zinc',
    ],

    'icons' => [
        'admin' => 'shield-check',
        'manager' => 'user-group',
        'comptable' => 'banknotes',
        'employe' => 'users',
        'default' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default roles
    |--------------------------------------------------------------------------
    |
    | Seeded when the roles table is created and kept up to date by the
    | RoleSeeder. The administrator role is a system role and always has
    | every permission.
    |
    */

    'defaults' => [
        [
            'name' => 'Administrateur',
            'slug' => 'admin',
            'description' => 'Accès total et illimité à toutes les fonctionnalités et configurations.',
            'permissions' => ['*'],
            'is_system' => true,
        ],
        [
            'name' => 'Manager Ops',
            'slug' => 'manager',
            'description' => "Gestion des équipes, validation des factures et suivi d'activité.",
            'permissions' => [
                'company_view',
                'users_view',
                'users_invite',
                'users_edit',
                'invoices_view',
                'invoices_create',
                'reports_export',
                'documents_view',
            ],
            'is_system' => false,
        ],
        [
            'name' => 'Comptable',
            'slug' => 'comptable',
            'description' => 'Gestion de la facturation, accès aux identifiants fiscaux (ICE, CNSS, IF).',
            'permissions' => [
                'company_view',
                'fiscal_edit',
                'invoices_view',
                'invoices_create',
                'invoices_delete',
                'reports_export',
                'documents_view',
            ],
            'is_system' => false,
        ],
        [
            'name' => 'Employé',
            'slug' => 'employe',
            'description' => 'Accès restreint à la consultation des informations et documents.',
            'permissions' => ['company_view', 'invoices_view', 'documents_view'],
            'is_system' => false,
        ],
    ],
];
