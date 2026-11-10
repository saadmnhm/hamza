<?php

namespace Database\Seeders;

use App\Livewire\Documents\DocumentType;
use App\Models\Client;
use App\Models\Config;
use App\Models\Demande;
use App\Models\DocumentHistory;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\QrGroup;
use App\Models\QrGroupItem;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    /**
     * Moroccan cities with a representative postal code.
     *
     * @var array<string, string>
     */
    private array $cities = [
        'Casablanca' => '20100',
        'Rabat' => '10000',
        'Marrakech' => '40000',
        'Fès' => '30000',
        'Tanger' => '90000',
        'Agadir' => '80000',
        'Meknès' => '50000',
        'Oujda' => '60000',
        'Kénitra' => '14000',
        'Tétouan' => '93000',
        'Salé' => '11000',
        'El Jadida' => '24000',
        'Béni Mellal' => '23000',
        'Nador' => '62000',
        'Mohammedia' => '28800',
    ];

    /**
     * @var list<string>
     */
    private array $companyNames = [
        'Atlas Distribution SARL',
        'Maroc Tech Solutions',
        'Souss Équipement',
        'Sahara Logistics',
        'Nord Industrie',
        'Chaouia Matériaux',
        'Riad Immobilier',
        'Tanger Med Services',
        'Fès Artisanat',
        'Agadir Tourisme',
        'Kénitra Agro',
        'Oujda Énergie',
        'Meknès Textile',
        'Rabat Conseil',
        'Casablanca Finance',
        'El Jadida Pêche',
        'Béni Mellal Transport',
        'Nador Import Export',
        'Tétouan Design',
        'Mohammedia Plastique',
        'Salé Numérique',
        'Marrakech Hôtellerie',
        'Anfa Construction',
        'Zerktouni Médical',
        'Bouregreg Édition',
    ];

    /**
     * @var list<array{0: string, 1: string}>
     */
    private array $individualNames = [
        ['Youssef', 'El Amrani'],
        ['Fatima Zahra', 'Bennani'],
        ['Mohamed', 'Ait Ali'],
        ['Salma', 'Chraibi'],
        ['Karim', 'Benjelloun'],
        ['Nadia', 'El Fassi'],
        ['Hicham', 'Ouazzani'],
        ['Imane', 'Berrada'],
        ['Rachid', 'Tazi'],
        ['Soukaina', 'Idrissi'],
        ['Amine', 'Lahlou'],
        ['Khadija', 'Sbai'],
        ['Omar', 'Kettani'],
        ['Leila', 'Mansouri'],
        ['Mehdi', 'Alaoui'],
    ];

    /**
     * @var list<array{name: string, description: string, unit: string, price: float, tva: int}>
     */
    private array $catalogue = [
        ['name' => 'Ordinateur portable Pro 14"', 'description' => 'PC portable professionnel, 16 Go RAM, SSD 512 Go', 'unit' => 'unité', 'price' => 8990.00, 'tva' => 20],
        ['name' => 'Écran 24 pouces Full HD', 'description' => 'Écran IPS 24" avec connectique HDMI et DisplayPort', 'unit' => 'unité', 'price' => 1490.00, 'tva' => 20],
        ['name' => 'Imprimante laser couleur', 'description' => 'Imprimante multifonction réseau A4', 'unit' => 'unité', 'price' => 3290.00, 'tva' => 20],
        ['name' => 'Cartouche d\'encre', 'description' => 'Cartouche compatible, rendement standard', 'unit' => 'unité', 'price' => 320.00, 'tva' => 20],
        ['name' => 'Disque SSD 1 To', 'description' => 'SSD NVMe interne haute performance', 'unit' => 'unité', 'price' => 890.00, 'tva' => 20],
        ['name' => 'Onduleur 650 VA', 'description' => 'Onduleur avec protection contre les surtensions', 'unit' => 'unité', 'price' => 650.00, 'tva' => 20],
        ['name' => 'Clavier mécanique', 'description' => 'Clavier filaire rétroéclairé AZERTY', 'unit' => 'unité', 'price' => 450.00, 'tva' => 20],
        ['name' => 'Souris sans fil ergonomique', 'description' => 'Souris rechargeable avec récepteur USB', 'unit' => 'unité', 'price' => 220.00, 'tva' => 20],
        ['name' => 'Smartphone professionnel', 'description' => 'Smartphone 128 Go, double SIM', 'unit' => 'unité', 'price' => 3490.00, 'tva' => 20],
        ['name' => 'Licence Microsoft 365', 'description' => 'Abonnement annuel, 1 utilisateur', 'unit' => 'forfait', 'price' => 1190.00, 'tva' => 20],
        ['name' => 'Développement site web', 'description' => 'Site vitrine responsive, 5 pages, formulaire de contact', 'unit' => 'forfait', 'price' => 12000.00, 'tva' => 20],
        ['name' => 'Hébergement annuel', 'description' => 'Hébergement mutualisé, domaine et certificat SSL inclus', 'unit' => 'forfait', 'price' => 850.00, 'tva' => 20],
        ['name' => 'Maintenance informatique', 'description' => 'Contrat de maintenance mensuel, interventions sur site', 'unit' => 'mois', 'price' => 2500.00, 'tva' => 20],
        ['name' => 'Audit sécurité', 'description' => 'Analyse des vulnérabilités et rapport détaillé', 'unit' => 'forfait', 'price' => 7500.00, 'tva' => 20],
        ['name' => 'Formation bureautique', 'description' => 'Formation Word, Excel et PowerPoint, 2 jours', 'unit' => 'jour', 'price' => 1800.00, 'tva' => 20],
        ['name' => 'Fourniture de bureau', 'description' => 'Pack papier, stylos, classeurs et accessoires', 'unit' => 'forfait', 'price' => 480.00, 'tva' => 20],
        ['name' => 'Chaise ergonomique', 'description' => 'Chaise de bureau réglable avec support lombaire', 'unit' => 'unité', 'price' => 1290.00, 'tva' => 20],
        ['name' => 'Bureau droit 160 cm', 'description' => 'Bureau professionnel avec caisson de rangement', 'unit' => 'unité', 'price' => 2150.00, 'tva' => 20],
        ['name' => 'Consultation juridique', 'description' => 'Accompagnement juridique et rédaction de contrats', 'unit' => 'heure', 'price' => 700.00, 'tva' => 20],
        ['name' => 'Livraison et installation', 'description' => 'Livraison sur site et installation du matériel', 'unit' => 'forfait', 'price' => 350.00, 'tva' => 20],
    ];

    /**
     * @var list<string>
     */
    private array $paymentTerms = [
        'Paiement à 30 jours',
        'Paiement comptant',
        'Virement bancaire à 45 jours',
        'Paiement à réception de facture',
        '50 % à la commande, solde à la livraison',
    ];

    public function run(): void
    {
        $admin = $this->seedAdmin();
        $config = $this->seedCompanyConfig();
        $items = $this->seedItems();

        $this->seedTeam();

        $clients = $this->seedClients();

        $this->seedCommercialActivity($clients, $items, $admin);
        $this->seedHumanResources($clients, $config, $admin);
        $this->seedQrGroups($admin);
    }

    /**
     * Seed a ready-to-print QR group so the module has demo content.
     */
    private function seedQrGroups(User $admin): void
    {
        if (QrGroup::query()->exists()) {
            return;
        }

        $documents = DocumentHistory::query()
            ->with(['client', 'invoice'])
            ->latest()
            ->take(6)
            ->get();

        $clients = Client::query()->latest()->take(4)->get();

        if ($documents->isEmpty() && $clients->isEmpty()) {
            return;
        }

        $group = QrGroup::query()->create([
            'user_id' => $admin->id,
            'name' => 'Lots de démonstration',
            'description' => 'Sélection de clients et de documents avec QR codes imprimables.',
        ]);

        foreach ($documents as $document) {
            $group->items()->create([
                'item_type' => QrGroupItem::TYPE_DOCUMENT,
                'item_id' => $document->id,
                'public_token' => $document->public_token,
                'label' => $document->title,
                'meta' => [
                    'document_type' => $document->document_type,
                    'type_label' => DocumentType::tryFrom($document->document_type)?->title(),
                    'number' => data_get($document, 'invoice.number') ?? data_get($document->data, 'invoice_number'),
                    'client' => $document->client?->displayName() ?? data_get($document->data, 'client.name'),
                    'total' => data_get($document->data, 'total'),
                    'date' => $document->created_at?->format('d/m/Y'),
                ],
            ]);
        }

        foreach ($clients as $client) {
            $group->items()->create([
                'item_type' => QrGroupItem::TYPE_CLIENT,
                'item_id' => $client->id,
                'public_token' => $client->public_token,
                'label' => $client->displayName(),
                'meta' => [
                    'client_type' => $client->type,
                    'type_label' => $client->isCompany() ? 'Entreprise' : 'Particulier',
                    'email' => $client->email,
                    'phone' => $client->phone,
                    'city' => $client->city,
                ],
            ]);
        }
    }

    private function seedAdmin(): User
    {
        $admin = User::query()->where('email', 'test@example.com')->first();

        if ($admin !== null) {
            return $admin;
        }

        return User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role_id' => Role::query()->where('slug', 'admin')->value('id'),
        ]);
    }

    private function seedCompanyConfig(): Config
    {
        return Config::query()->firstOrCreate(
            ['raison_sociale' => 'Atlas Business Solutions SARL'],
            [
                'nom_commercial' => 'Atlas Business',
                'forme_juridique' => 'SARL',
                'ice' => '002345678000012',
                'if_number' => '12345678',
                'cnss' => '1234567',
                'rc' => '456789',
                'patente' => '12345678',
                'tribunal_rc' => 'Casablanca',
                'email' => 'contact@atlas-business.ma',
                'telephone' => '+212 522 123 456',
                'adresse' => '45, Boulevard Zerktouni',
                'ville' => 'Casablanca',
                'code_postal' => '20100',
            ],
        );
    }

    /**
     * @return list<Item>
     */
    private function seedItems(): array
    {
        $items = [];

        foreach ($this->catalogue as $entry) {
            $items[] = Item::query()->firstOrCreate(
                ['name' => $entry['name']],
                [
                    'description' => $entry['description'],
                    'unit' => $entry['unit'],
                    'unit_price' => $entry['price'],
                    'tva_rate' => $entry['tva'],
                ],
            );
        }

        return $items;
    }

    private function seedTeam(): void
    {
        $roles = Role::query()->pluck('id', 'slug');

        $team = [
            ['name' => 'Amina El Idrissi', 'email' => 'manager@example.com', 'role' => 'manager'],
            ['name' => 'Yassine Bennis', 'email' => 'comptable@example.com', 'role' => 'comptable'],
            ['name' => 'Sara Bouzid', 'email' => 'employe@example.com', 'role' => 'employe'],
        ];

        foreach ($team as $member) {
            if (User::query()->where('email', $member['email'])->exists()) {
                continue;
            }

            User::factory()->create([
                'name' => $member['name'],
                'email' => $member['email'],
                'role_id' => $roles[$member['role']] ?? null,
            ]);
        }
    }

    /**
     * @return list<Client>
     */
    private function seedClients(): array
    {
        $clients = [];
        $cityNames = array_keys($this->cities);
        $suffix = now()->format('His');

        foreach ($this->companyNames as $index => $name) {
            $city = $cityNames[$index % count($cityNames)];

            $clients[] = Client::query()->create([
                'type' => Client::TYPE_COMPANY,
                'company_name' => $name,
                'ice' => fake()->numerify('###############'),
                'if_number' => fake()->numerify('########'),
                'rc' => fake()->numerify('######'),
                'cnss' => fake()->numerify('#######'),
                'address' => fake()->buildingNumber().', '.fake()->streetName(),
                'city' => $city,
                'postal_code' => $this->cities[$city],
                'phone' => '+212 6'.fake()->numerify('########'),
                'email' => 'contact'.($index + 1).'.'.$suffix.'@demo-entreprises.ma',
                'notes' => fake()->boolean(25) ? 'Client historique — '.fake()->sentence(6) : null,
            ]);
        }

        foreach ($this->individualNames as $index => [$firstName, $lastName]) {
            $city = $cityNames[($index + 3) % count($cityNames)];

            $clients[] = Client::query()->create([
                'type' => Client::TYPE_INDIVIDUAL,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'cin' => fake()->bothify('??######'),
                'address' => fake()->buildingNumber().', '.fake()->streetName(),
                'city' => $city,
                'postal_code' => $this->cities[$city],
                'phone' => '+212 6'.fake()->numerify('########'),
                'email' => Str::slug($firstName.'.'.$lastName, '.').'.'.$suffix.'@demo-particuliers.ma',
                'notes' => null,
            ]);
        }

        return $clients;
    }

    /**
     * @param  list<Client>  $clients
     * @param  list<Item>  $items
     */
    private function seedCommercialActivity(array $clients, array $items, User $admin): void
    {
        foreach ($clients as $client) {
            $demandeCount = random_int(1, 3);

            for ($d = 0; $d < $demandeCount; $d++) {
                $date = Carbon::now()->subDays(random_int(0, 330));

                $demande = Demande::query()->create([
                    'client_id' => $client->id,
                    'date' => $date->toDateString(),
                    'notes' => fake()->boolean(35) ? 'Demande : '.fake()->sentence(4) : null,
                ]);

                $documentCount = random_int(1, 3);

                for ($i = 0; $i < $documentCount; $i++) {
                    $type = fake()->randomElement([
                        Invoice::TYPE_INVOICE,
                        Invoice::TYPE_INVOICE,
                        Invoice::TYPE_INVOICE,
                        Invoice::TYPE_DELIVERY_NOTE,
                        Invoice::TYPE_PURCHASE_ORDER,
                    ]);

                    $invoice = $this->createInvoice($client, $demande, $type, $items, $date);

                    $invoice->generateDocument((int) $admin->id);
                }
            }
        }
    }

    /**
     * @param  list<Item>  $items
     */
    private function createInvoice(Client $client, Demande $demande, string $type, array $items, Carbon $date): Invoice
    {
        $invoice = Invoice::query()->create([
            'client_id' => $client->id,
            'demande_id' => $demande->id,
            'type' => $type,
            'number' => Invoice::generateNumber($type, $date->toDateString()),
            'invoice_date' => $date->toDateString(),
            'due_date' => $date->copy()->addDays(random_int(15, 60))->toDateString(),
            'payment_terms' => fake()->randomElement($this->paymentTerms),
            'notes' => fake()->boolean(30) ? 'Merci de votre confiance.' : null,
        ]);

        $lineCount = min(count($items), random_int(1, 5));
        $selected = collect($items)->shuffle()->take($lineCount)->values();

        foreach ($selected as $position => $item) {
            $invoice->items()->create([
                'position' => $position,
                'item_id' => $item->id,
                'product' => $item->name,
                'description' => $item->description,
                'quantity' => fake()->randomElement([1, 1, 2, 2, 3, 5, 10]),
                'unit_price' => $item->unit_price,
                'tva_rate' => $item->tva_rate,
            ]);
        }

        $invoice->recalculateTotals();

        return $invoice->refresh();
    }

    /**
     * @param  list<Client>  $clients
     */
    private function seedHumanResources(array $clients, Config $config, User $admin): void
    {
        $positions = [
            ['Développeur web', 'Informatique'],
            ['Comptable', 'Finance'],
            ['Assistant administratif', 'Administration'],
            ['Commercial', 'Ventes'],
            ['Chef de projet', 'Production'],
            ['Technicien réseau', 'Informatique'],
            ['Responsable RH', 'Ressources humaines'],
        ];

        foreach ($clients as $client) {
            if (! fake()->boolean(55)) {
                continue;
            }

            $demande = Demande::query()->create([
                'client_id' => $client->id,
                'date' => Carbon::now()->subDays(random_int(10, 300))->toDateString(),
                'notes' => 'Dossier RH',
            ]);

            [$position, $department] = fake()->randomElement($positions);

            $hireDate = Carbon::now()->subMonths(random_int(2, 36));
            $salary = random_int(45, 180) * 100;

            $contractData = [
                'employee_name' => fake()->name(),
                'employee_position' => $position,
                'employee_department' => $department,
                'employee_hire_date' => $hireDate->toDateString(),
                'employee_salary' => $salary,
                'employee_address' => fake()->buildingNumber().', '.fake()->streetName().', '.$client->city,
                'employer_name' => $config->raison_sociale,
                'employer_address' => $config->fullAddress(),
                'contract_duration' => fake()->randomElement(['Durée indéterminée', '12 mois renouvelable', '6 mois']),
                'contract_start_date' => $hireDate->toDateString(),
                'signing_date' => $hireDate->copy()->subDays(5)->toDateString(),
            ];

            $this->storeDocument(
                client: $client,
                demande: $demande,
                admin: $admin,
                type: DocumentType::EMPLOYMENT_CONTRACT,
                title: 'Contrat de travail — '.$contractData['employee_name'],
                data: $contractData,
                view: 'livewire.documents.print.employmentcontract',
                company: $config,
            );

            if (! fake()->boolean(70)) {
                continue;
            }

            $periodEnd = Carbon::now()->subDays(random_int(1, 60));
            $periodStart = $periodEnd->copy()->startOfMonth();
            $allowances = random_int(0, 30) * 100;
            $deductions = random_int(0, 20) * 100;
            $net = $salary + $allowances - $deductions;

            $slipData = [
                'employee_name' => $contractData['employee_name'],
                'employee_matricule' => 'EMP'.fake()->numerify('####'),
                'period_start' => $periodStart->toDateString(),
                'period_end' => $periodEnd->toDateString(),
                'basic_salary' => $salary,
                'allowances' => $allowances,
                'deductions' => $deductions,
                'net_salary' => $net,
                'payment_method' => fake()->randomElement(['Virement bancaire', 'Chèque', 'Espèces']),
                'payment_date' => $periodEnd->copy()->addDays(3)->toDateString(),
            ];

            $this->storeDocument(
                client: $client,
                demande: $demande,
                admin: $admin,
                type: DocumentType::SALARY_SLIP,
                title: 'Fiche de paie — '.$slipData['employee_name'].' — '.$periodStart->format('m/Y'),
                data: $slipData,
                view: 'livewire.documents.print.salary-slip',
                company: $config,
            );
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  view-string  $view
     */
    private function storeDocument(
        Client $client,
        Demande $demande,
        User $admin,
        DocumentType $type,
        string $title,
        array $data,
        string $view,
        Config $company,
    ): DocumentHistory {
        $token = (string) Str::uuid();

        $html = view($view, [
            'data' => $data + ['qr_url' => route('archive.documents.show', $token)],
            'company' => $company,
        ])->render();

        return DocumentHistory::query()->create([
            'user_id' => $admin->id,
            'public_token' => $token,
            'client_id' => $client->id,
            'demande_id' => $demande->id,
            'document_type' => $type->value,
            'title' => $title,
            'data' => $data,
            'html' => $html,
        ]);
    }
}
