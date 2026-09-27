<div class= md:p-8 space-y-8 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-sm">
    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-zinc-200 dark:border-zinc-800 pb-6">
        <div>
            <flux:heading size="xl" level="1">Configuration de l'Entreprise</flux:heading>
            <flux:subheading class="mt-1">Gérez les informations légales, l'identité visuelle et les identifiants fiscaux de votre société.</flux:subheading>
        </div>
        <div class="flex items-center gap-3">
            <flux:button variant="ghost">Annuler</flux:button>
            <flux:button variant="primary" icon="check">Enregistrer</flux:button>
        </div>
    </div>

    <form wire:submit="save" class="space-y-8">
        <!-- Section 1: Logo & Raison Sociale -->
        <section class="space-y-6">
            <div>
                <flux:heading level="2" size="lg">Identité Visuelle & Raison Sociale</flux:heading>
                <flux:subheading>Définissez l'image de marque et la dénomination officielle de l'entreprise.</flux:subheading>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                <!-- Téléversement du Logo -->
                <div class="space-y-2">
                    <flux:label>Logo Officiel</flux:label>
                    <div class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-zinc-300 dark:border-zinc-700 rounded-xl hover:border-zinc-400 dark:hover:border-zinc-600 transition cursor-pointer text-center bg-zinc-50 dark:bg-zinc-800/40">
                        <flux:icon name="photo" class="size-10 text-zinc-400 mb-2" />
                        <span class="text-xs text-zinc-500 dark:text-zinc-400">PNG, JPG, SVG (Max 2 Mo)</span>
                        <input type="file" id="logo-upload" class="hidden" wire:model="logo" accept="image/*" />
                        <flux:button size="sm" variant="subtle" class="mt-3" onclick="document.getElementById('logo-upload').click()">Choisir une image</flux:button>
                    </div>
                </div>

                <!-- Noms de la société -->
                <div class="md:col-span-2 space-y-4">
                    <flux:field>
                        <flux:label required>Raison Sociale</flux:label>
                        <flux:input wire:model="raison_sociale" placeholder="Ex: Société Exemple SARL AU" icon="building-office" />
                        <flux:error name="raison_sociale" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Nom Commercial / Enseigne</flux:label>
                        <flux:input wire:model="nom_commercial" placeholder="Ex: Acme Brand" />
                        <flux:error name="nom_commercial" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Forme Juridique</flux:label>
                        <flux:select wire:model="forme_juridique" placeholder="Sélectionner la forme juridique...">
                            <flux:select.option value="sarl">SARL (Société à Responsabilité Limitée)</flux:select.option>
                            <flux:select.option value="sarl_au">SARL AU (Associé Unique)</flux:select.option>
                            <flux:select.option value="sa">SA (Société Anonyme)</flux:select.option>
                            <flux:select.option value="snc">SNC (Société en Nom Collectif)</flux:select.option>
                            <flux:select.option value="auto_entrepreneur">Auto-entrepreneur</flux:select.option>
                        </flux:select>
                    </flux:field>
                </div>
            </div>
        </section>

        <flux:separator />

        <!-- Section 2: Identifiants Fiscaux & Administratifs -->
        <section class="space-y-6">
            <div>
                <flux:heading level="2" size="lg">Identifiants Légaux & Fiscaux</flux:heading>
                <flux:subheading>Renseignez vos numéros officiels d'immatriculation administrative.</flux:subheading>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                <!-- ICE -->
                <flux:field>
                    <flux:label required>N° ICE</flux:label>
                    <flux:description>Identifiant Commun (15 chiffres)</flux:description>
                    <flux:input wire:model="ice" placeholder="001234567000089" icon="identification" />
                    <flux:error name="ice" />
                </flux:field>

                <!-- Identifiant Fiscal (IF) -->
                <flux:field>
                    <flux:label required>N° IF (Identifiant Fiscal)</flux:label>
                    <flux:description>Numéro DGI</flux:description>
                    <flux:input wire:model="if_number" placeholder="12345678" />
                    <flux:error name="if_number" />
                </flux:field>

                <!-- CNSS -->
                <flux:field>
                    <flux:label required>N° CNSS</flux:label>
                    <flux:description>Affiliation Sécurité Sociale</flux:description>
                    <flux:input wire:model="cnss" placeholder="1234567" icon="shield-check" />
                    <flux:error name="cnss" />
                </flux:field>

                <!-- RC -->
                <flux:field>
                    <flux:label>N° RC (Registre du Commerce)</flux:label>
                    <flux:input wire:model="rc" placeholder="Ex: 123456" />
                    <flux:error name="rc" />
                </flux:field>

                <!-- Patente -->
                <flux:field>
                    <flux:label>N° Patente (TP)</flux:label>
                    <flux:input wire:model="patente" placeholder="Ex: 34567890" />
                    <flux:error name="patente" />
                </flux:field>

                <!-- Tribunal de Commerce / Ville RC -->
                <flux:field>
                    <flux:label>Tribunal du RC</flux:label>
                    <flux:input wire:model="tribunal_rc" placeholder="Ex: Casablanca" />
                    <flux:error name="tribunal_rc" />
                </flux:field>
            </div>
        </section>

        <flux:separator />

        <!-- Section 3: Coordonnées & Siège Social -->
        <section class="space-y-6">
            <div>
                <flux:heading level="2" size="lg">Coordonnées & Siège Social</flux:heading>
                <flux:subheading>Adresses et contacts à afficher sur vos factures et documents officiels.</flux:subheading>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <flux:field>
                    <flux:label>Adresse E-mail Officielle</flux:label>
                    <flux:input type="email" wire:model="email" placeholder="contact@entreprise.ma" icon="envelope" />
                    <flux:error name="email" />
                </flux:field>

                <flux:field>
                    <flux:label>Téléphone</flux:label>
                    <flux:input type="tel" wire:model="telephone" placeholder="+212 5 22 00 00 00" icon="phone" />
                    <flux:error name="telephone" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>Adresse du Siège Social</flux:label>
                    <flux:textarea wire:model="adresse" rows="2" placeholder="N° et rue, Boulevard, Étage..." />
                    <flux:error name="adresse" />
                </flux:field>

                <flux:field>
                    <flux:label>Ville</flux:label>
                    <flux:input wire:model="ville" placeholder="Casablanca" />
                    <flux:error name="ville" />
                </flux:field>

                <flux:field>
                    <flux:label>Code Postal</flux:label>
                    <flux:input wire:model="code_postal" placeholder="20000" />
                    <flux:error name="code_postal" />
                </flux:field>
            </div>
        </section>

        <!-- Actions Pied de Page -->
        <div class="flex items-center justify-end gap-3 pt-6 border-t border-zinc-200 dark:border-zinc-800">
            <flux:button variant="ghost">Annuler</flux:button>
            <flux:button type="submit" variant="primary" icon="check">Enregistrer les modifications</flux:button>
        </div>
    </form>
</div>