<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use App\Models\Candidat;
use App\Models\Filiere;
use App\Models\Specialite;
use App\Services\ConcoursService;

name('admin.inscriptions.nouvelle');
middleware(['auth', 'verified', 'role']);

/**
 * Saisie d'une fiche d'inscription, telle que le document officiel :
 * identite de l'apprenant, admission, prise en charge, puis l'echeancier.
 *
 * Arrive avec ?candidat=ID depuis la liste des admis : la fiche est alors
 * prete a etre completee.
 */
new class extends Component {
    public ?int $candidat_id = null;
    public string $matricule = '';
    public string $annee = '';

    public string $civilite = 'M';
    public string $nom = '';
    public string $prenoms = '';
    public string $date_naissance = '';
    public string $lieu_naissance = '';
    public string $nationalite = 'Camerounaise';
    public string $religion = '';
    public string $email = '';
    public string $telephone = '';
    public string $regime = 'externe';

    public string $filiere_id = '';
    public string $specialite_id = '';
    public string $conditions_acces = '';
    public string $duree_formation = '';
    public string $diplome_vise = '';
    public string $metiers = '';
    public string $montant_scolarite = '';

    public bool $assurance_ifpm = false;
    public string $compagnie_assurance = '';
    public string $date_visite_medicale = '';
    public string $code_visite = '';
    public string $observations = '';

    public string $erreur = '';

    public function mount(ConcoursService $service): void
    {
        $this->annee = $service->anneeCourante();
        $this->matricule = $service->genererMatricule();

        if ($id = request('candidat')) {
            $this->reprendreCandidat((int) $id);
        }
    }

    /** Recopie ce que le candidat a deja declare sur sa fiche de renseignement. */
    public function reprendreCandidat(int $id): void
    {
        $candidat = Candidat::with('filiere1')->find($id);

        if (! $candidat) {
            return;
        }

        $this->candidat_id = $candidat->id;
        $this->nom = $candidat->nom;
        $this->prenoms = (string) $candidat->prenoms;
        $this->civilite = $candidat->sexe === 'F' ? 'Mme' : 'M';
        $this->date_naissance = $candidat->date_naissance?->format('Y-m-d') ?? '';
        $this->lieu_naissance = (string) $candidat->lieu_naissance;
        $this->nationalite = $candidat->nationalite ?: 'Camerounaise';
        $this->email = (string) $candidat->email;
        $this->telephone = (string) $candidat->telephone;
        $this->filiere_id = (string) ($candidat->filiere1_id ?? '');
        $this->conditions_acces = (string) $candidat->niveau;
        $this->appliquerFiliere();
    }

    public function updatedFiliereId(): void
    {
        $this->specialite_id = '';
        $this->appliquerFiliere();
    }

    /** La filière porte déjà sa durée et son parcours : on les propose. */
    private function appliquerFiliere(): void
    {
        $filiere = $this->filiere_id ? Filiere::find($this->filiere_id) : null;

        if (! $filiere) {
            return;
        }

        $this->metiers = $this->metiers ?: $filiere->name;

        if (preg_match('/(\d+)\s*mois/i', (string) $filiere->description, $trouve)) {
            $this->duree_formation = $this->duree_formation ?: $trouve[1].' mois';
        }

        $parcours = Specialite::where('filiere_id', $filiere->id)->where('status', 'Success')->first();

        if ($parcours) {
            $this->specialite_id = (string) $parcours->id;
            $this->montant_scolarite = $this->montant_scolarite ?: (string) (int) ($parcours->price ?? 0);
        }
    }

    #[Computed]
    public function filieres()
    {
        return Filiere::where('status', 'Success')->orderBy('name')->get(['id', 'name']);
    }

    #[Computed]
    public function parcours()
    {
        return $this->filiere_id
            ? Specialite::where('filiere_id', $this->filiere_id)->where('status', 'Success')->orderBy('name')->get(['id', 'name'])
            : collect();
    }

    #[Computed]
    public function echeancier()
    {
        return app(ConcoursService::class)->echeancierParDefaut((float) ($this->montant_scolarite ?: 0));
    }

    public function enregistrer(ConcoursService $service)
    {
        $this->erreur = '';

        $donnees = $this->validate([
            'candidat_id' => ['nullable', 'exists:candidats,id'],
            'matricule' => ['required', 'string', 'max:40'],
            'annee' => ['required', 'string', 'max:12'],
            'civilite' => ['required', 'in:Mme,Mlle,M'],
            'nom' => ['required', 'string', 'max:120'],
            'prenoms' => ['nullable', 'string', 'max:160'],
            'date_naissance' => ['nullable', 'date', 'before:today'],
            'lieu_naissance' => ['nullable', 'string', 'max:120'],
            'nationalite' => ['nullable', 'string', 'max:60'],
            'religion' => ['nullable', 'string', 'max:60'],
            'email' => ['nullable', 'email', 'max:160'],
            'telephone' => ['required', 'string', 'max:40'],
            'regime' => ['required', 'in:externe,interne'],
            'filiere_id' => ['required', 'exists:filieres,id'],
            'specialite_id' => ['nullable', 'exists:specialites,id'],
            'conditions_acces' => ['nullable', 'string', 'max:160'],
            'duree_formation' => ['nullable', 'string', 'max:60'],
            'diplome_vise' => ['nullable', 'string', 'max:160'],
            'metiers' => ['nullable', 'string', 'max:200'],
            'montant_scolarite' => ['required', 'numeric', 'min:0'],
            'assurance_ifpm' => ['boolean'],
            'compagnie_assurance' => ['nullable', 'string', 'max:120'],
            'date_visite_medicale' => ['nullable', 'date'],
            'code_visite' => ['nullable', 'string', 'max:60'],
            'observations' => ['nullable', 'string', 'max:1000'],
        ], [], ['filiere_id' => 'filière', 'montant_scolarite' => 'montant de la scolarité']);

        foreach (['date_naissance', 'date_visite_medicale', 'specialite_id', 'candidat_id'] as $champ) {
            $donnees[$champ] = $donnees[$champ] ?: null;
        }

        try {
            $inscription = $service->inscrire($donnees, null, auth()->id());
        } catch (\RuntimeException $e) {
            $this->erreur = $e->getMessage();

            return null;
        }

        session()->flash('inscription_message',
            'Inscription enregistrée : '.$inscription->nomComplet().' — matricule '.$inscription->matricule.'.');

        return redirect('/admin/inscriptions');
    }
};
?>

<x-layouts.app title="Nouvelle inscription">
    @volt
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Nouvelle inscription</h1>
                <p class="text-gray-500">Fiche d’inscription de l’apprenant et échéancier de scolarité.</p>
            </div>
            <a href="/admin/inscriptions" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">Retour à la liste</a>
        </div>

        @if ($erreur)
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">{{ $erreur }}</div>
        @endif

        @if ($candidat_id)
            <div class="mb-4 rounded-lg bg-blue-50 border border-blue-200 px-4 py-3 text-sm text-blue-800">
                Fiche pré-remplie depuis la candidature n° {{ $candidat_id }} : vérifiez et complétez l’admission.
            </div>
        @endif

        <form wire:submit="enregistrer" class="space-y-6">
            <section class="bg-white rounded-xl shadow p-4 sm:p-5">
                <h2 class="text-base font-semibold text-gray-800 mb-4">Identité de l’apprenant</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Matricule <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="matricule" class="mt-1 w-full rounded-lg border-gray-300 font-mono text-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('matricule')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Année de formation <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="annee" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Statut</label>
                        <select wire:model="civilite" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="Mme">Mme</option>
                            <option value="Mlle">Mlle</option>
                            <option value="M">M</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nom(s) <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="nom" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('nom')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Prénom(s)</label>
                        <input type="text" wire:model="prenoms" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Né(e) le</label>
                        <input type="date" wire:model="date_naissance" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Lieu de naissance</label>
                        <input type="text" wire:model="lieu_naissance" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nationalité</label>
                        <input type="text" wire:model="nationalite" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Religion</label>
                        <input type="text" wire:model="religion" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Téléphone <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="telephone" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('telephone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">E-mail</label>
                        <input type="email" wire:model="email" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Régime</label>
                        <select wire:model="regime" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="externe">Externe</option>
                            <option value="interne">Interne</option>
                        </select>
                    </div>
                </div>
            </section>

            <section class="bg-white rounded-xl shadow p-4 sm:p-5">
                <h2 class="text-base font-semibold text-gray-800 mb-4">Admission</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Filière (spécialité sollicitée) <span class="text-red-500">*</span></label>
                        <select wire:model.live="filiere_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">— Choisir —</option>
                            @foreach ($this->filieres as $f)
                                <option value="{{ $f->id }}">{{ $f->name }}</option>
                            @endforeach
                        </select>
                        @error('filiere_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Parcours</label>
                        <select wire:model="specialite_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">—</option>
                            @foreach ($this->parcours as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Conditions d’accès</label>
                        <input type="text" wire:model="conditions_acces" placeholder="BEPC, CAP…" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Durée de formation</label>
                        <input type="text" wire:model="duree_formation" placeholder="12 mois" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Diplôme envisagé</label>
                        <input type="text" wire:model="diplome_vise" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Montant scolarité (FCFA) <span class="text-red-500">*</span></label>
                        <input type="number" min="0" step="1000" wire:model.live.debounce.500ms="montant_scolarite" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('montant_scolarite')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2 lg:col-span-3">
                        <label class="block text-sm font-medium text-gray-700">Métiers correspondants</label>
                        <input type="text" wire:model="metiers" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
            </section>

            <section class="bg-white rounded-xl shadow p-4 sm:p-5">
                <h2 class="text-base font-semibold text-gray-800 mb-4">Prise en charge</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <label class="flex items-center gap-3 rounded-lg border border-gray-200 px-3 py-2">
                        <input type="checkbox" wire:model.live="assurance_ifpm" class="rounded border-gray-300 text-blue-700 focus:ring-blue-500">
                        <span class="text-sm text-gray-700">Assurance maladie IFPM</span>
                    </label>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Sinon, compagnie</label>
                        <input type="text" wire:model="compagnie_assurance" @disabled($assurance_ifpm) class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500 disabled:bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date de visite médicale</label>
                        <input type="date" wire:model="date_visite_medicale" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Code</label>
                        <input type="text" wire:model="code_visite" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
            </section>

            <section class="bg-white rounded-xl shadow p-4 sm:p-5">
                <h2 class="text-base font-semibold text-gray-800 mb-1">Échéancier prévu</h2>
                <p class="text-sm text-gray-500 mb-4">Créé automatiquement : les trois lignes fixes de la fiche, puis la scolarité répartie sur huit versements. Les montants se saisissent ensuite, au fil des paiements.</p>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm" data-sans-export>
                        <thead><tr><th>Description</th><th class="text-right">Montant prévu</th></tr></thead>
                        <tbody>
                            @foreach ($this->echeancier as $ligne)
                                <tr>
                                    <td>{{ $ligne['libelle'] }}</td>
                                    <td class="text-right">{{ $ligne['montant'] > 0 ? number_format($ligne['montant'], 0, ',', ' ').' FCFA' : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">Observations</label>
                    <textarea wire:model="observations" rows="2" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                </div>
            </section>

            <div class="flex flex-wrap items-center gap-3">
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-700 text-white rounded-lg text-sm font-medium hover:bg-blue-800">
                    <span wire:loading.remove wire:target="enregistrer">Enregistrer l’inscription</span>
                    <span wire:loading wire:target="enregistrer">Enregistrement…</span>
                </button>
                <a href="/admin/inscriptions" class="px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">Annuler</a>
            </div>
        </form>
    </div>
    @endvolt
</x-layouts.app>
