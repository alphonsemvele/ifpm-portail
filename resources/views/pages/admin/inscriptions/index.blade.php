<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use App\Models\Filiere;
use App\Models\Inscription;
use App\Models\Versement;
use App\Services\ConcoursService;

name('admin.inscriptions.index');
middleware(['auth', 'verified', 'role']);

new class extends Component {
    use WithPagination;

    public string $annee = '';
    public string $filiere = '';
    public string $statut = '';
    public string $recherche = '';
    public ?int $ficheId = null;
    public string $message = '';
    public string $erreur = '';

    // Saisie d'un versement
    public ?int $versementId = null;
    public string $montant = '';
    public string $date_versement = '';
    public bool $vise = false;

    public function mount(): void
    {
        $this->annee = (string) request('annee', app(ConcoursService::class)->anneeCourante());
        $this->message = (string) session('inscription_message', '');
    }

    public function updating($propriete): void
    {
        if (in_array($propriete, ['annee', 'filiere', 'statut', 'recherche'], true)) {
            $this->resetPage();
        }
    }

    #[Computed]
    public function annees()
    {
        return Inscription::query()->distinct()->pluck('annee')
            ->push(app(ConcoursService::class)->anneeCourante())
            ->unique()->sortDesc()->values();
    }

    #[Computed]
    public function filieres()
    {
        return Filiere::where('status', 'Success')->orderBy('name')->get(['id', 'name']);
    }

    #[Computed]
    public function inscriptions()
    {
        return Inscription::with(['filiere', 'specialite', 'versements'])
            ->when($this->annee, fn ($q) => $q->where('annee', $this->annee))
            ->when($this->filiere, fn ($q) => $q->where('filiere_id', $this->filiere))
            ->when($this->statut, fn ($q) => $q->where('statut', $this->statut))
            ->when($this->recherche, fn ($q) => $q->where(fn ($sub) => $sub
                ->where('nom', 'like', '%'.$this->recherche.'%')
                ->orWhere('prenoms', 'like', '%'.$this->recherche.'%')
                ->orWhere('matricule', 'like', '%'.$this->recherche.'%')
                ->orWhere('telephone', 'like', '%'.$this->recherche.'%')))
            ->orderByDesc('id')
            ->paginate(30);
    }

    #[Computed]
    public function bilan()
    {
        $base = Inscription::query()->when($this->annee, fn ($q) => $q->where('annee', $this->annee));
        $inscriptions = (clone $base)->with('versements')->get();

        return [
            'total' => $inscriptions->count(),
            'soldees' => $inscriptions->where('statut', 'solde')->count(),
            'attendu' => (float) $inscriptions->sum('montant_scolarite'),
            // Seuls les versements datés sont encaissés.
            'encaisse' => (float) $inscriptions->sum(fn ($i) => $i->versements->whereNotNull('date_versement')->sum('montant')),
        ];
    }

    public function ouvrirVersement(int $id): void
    {
        $versement = Versement::findOrFail($id);
        $this->versementId = $versement->id;
        $this->montant = (string) ($versement->montant > 0 ? (int) $versement->montant : '');
        $this->date_versement = $versement->date_versement?->format('Y-m-d') ?? now()->toDateString();
        $this->vise = (bool) $versement->vise;
    }

    public function enregistrerVersement(ConcoursService $service): void
    {
        $this->validate([
            'montant' => ['required', 'numeric', 'min:0'],
            'date_versement' => ['required', 'date'],
        ], [], ['montant' => 'montant', 'date_versement' => 'date']);

        $service->enregistrerVersement(
            Versement::findOrFail($this->versementId),
            (float) $this->montant,
            $this->date_versement,
            $this->vise,
        );

        $this->reset(['versementId', 'montant', 'date_versement', 'vise']);
        $this->message = 'Versement enregistré.';
        unset($this->inscriptions, $this->bilan);
    }
};
?>

<x-layouts.app title="Inscriptions des apprenants">
    @volt
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Inscriptions des apprenants</h1>
                <p class="text-gray-500">Fiches d’inscription et suivi des versements de scolarité.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="/admin/inscriptions/nouvelle" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-700 text-white rounded-lg text-sm font-medium hover:bg-blue-800">Nouvelle inscription</a>
                <a href="/admin/concours/candidats?statut=admis" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">Candidats admis</a>
            </div>
        </div>

        @if ($message)
            <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">{{ $message }}</div>
        @endif
        @if ($erreur)
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">{{ $erreur }}</div>
        @endif

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow p-4">
                <p class="text-xs uppercase text-gray-500">Apprenants inscrits</p>
                <p class="text-3xl font-bold text-blue-800">{{ $this->bilan['total'] }}</p>
                <p class="text-xs text-gray-500">année {{ $annee }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-4">
                <p class="text-xs uppercase text-gray-500">Scolarités soldées</p>
                <p class="text-3xl font-bold text-emerald-700">{{ $this->bilan['soldees'] }}</p>
                <p class="text-xs text-gray-500">sur {{ $this->bilan['total'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-4">
                <p class="text-xs uppercase text-gray-500">Encaissé</p>
                <p class="text-3xl font-bold text-gray-800">{{ number_format($this->bilan['encaisse'], 0, ',', ' ') }}</p>
                <p class="text-xs text-gray-500">FCFA</p>
            </div>
            <div class="bg-white rounded-xl shadow p-4">
                <p class="text-xs uppercase text-gray-500">Reste à recouvrer</p>
                <p class="text-3xl font-bold {{ $this->bilan['attendu'] - $this->bilan['encaisse'] > 0 ? 'text-amber-600' : 'text-gray-800' }}">
                    {{ number_format(max(0, $this->bilan['attendu'] - $this->bilan['encaisse']), 0, ',', ' ') }}
                </p>
                <p class="text-xs text-gray-500">FCFA</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow p-4 sm:p-5 mb-6" data-sans-export>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Année</label>
                    <select wire:model.live="annee" class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        @foreach ($this->annees as $a)
                            <option value="{{ $a }}">{{ $a }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Filière</label>
                    <select wire:model.live="filiere" class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Toutes</option>
                        @foreach ($this->filieres as $f)
                            <option value="{{ $f->id }}">{{ $f->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Statut</label>
                    <select wire:model.live="statut" class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Tous</option>
                        <option value="en_cours">En cours</option>
                        <option value="solde">Soldée</option>
                        <option value="abandon">Abandon</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Recherche</label>
                    <input type="search" wire:model.live.debounce.400ms="recherche" placeholder="Nom, matricule, téléphone…"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow p-4 sm:p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-base font-semibold text-gray-800">Fiches d’inscription</h2>
                <span class="text-sm text-gray-500">{{ $this->inscriptions->total() }} apprenant(s)</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm" data-export-titre="Inscriptions des apprenants">
                    <thead>
                        <tr>
                            <th>Matricule</th><th>Apprenant</th><th>Filière</th><th>Régime</th>
                            <th class="text-right">Scolarité</th><th class="text-right">Versé</th>
                            <th class="text-right">Reste</th><th>Statut</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->inscriptions as $inscription)
                            <tr>
                                <td class="font-mono text-xs">{{ $inscription->matricule }}</td>
                                <td>
                                    <span class="block font-medium">{{ $inscription->nomComplet() }}</span>
                                    <span class="text-xs text-gray-500">{{ $inscription->telephone }}</span>
                                </td>
                                <td>
                                    {{ $inscription->filiere?->name ?? '—' }}
                                    @if ($inscription->duree_formation)<span class="block text-xs text-gray-500">{{ $inscription->duree_formation }}</span>@endif
                                </td>
                                <td>{{ $inscription->regime === 'interne' ? 'Interne' : 'Externe' }}</td>
                                <td class="text-right">{{ number_format((float) $inscription->montant_scolarite, 0, ',', ' ') }}</td>
                                <td class="text-right">{{ number_format($inscription->totalVerse(), 0, ',', ' ') }}</td>
                                <td class="text-right {{ $inscription->resteAPayer() > 0 ? 'text-amber-700 font-medium' : 'text-emerald-700' }}">
                                    {{ number_format($inscription->resteAPayer(), 0, ',', ' ') }}
                                </td>
                                <td>
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                        @if ($inscription->statut === 'solde') bg-emerald-100 text-emerald-700
                                        @elseif ($inscription->statut === 'abandon') bg-gray-100 text-gray-600
                                        @else bg-blue-100 text-blue-800 @endif">
                                        {{ ['en_cours' => 'En cours', 'solde' => 'Soldée', 'abandon' => 'Abandon'][$inscription->statut] }}
                                    </span>
                                </td>
                                <td>
                                    <button wire:click="$set('ficheId', {{ $inscription->id }})" class="bouton-icone text-sky-600 hover:bg-sky-50" title="Fiche et versements" aria-label="Fiche et versements"><x-icone-action nom="voir" /></button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-gray-500">Aucune inscription pour ces critères.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $this->inscriptions->links() }}</div>
        </div>

        @if ($ficheId)
            @php($fiche = \App\Models\Inscription::with(['filiere', 'specialite', 'versements', 'candidat'])->find($ficheId))
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                <div class="w-full max-w-3xl max-h-[88vh] overflow-y-auto rounded-xl bg-white p-6 shadow-xl">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">{{ $fiche->civilite }} {{ $fiche->nomComplet() }}</h3>
                            <p class="text-sm text-gray-500">{{ $fiche->matricule }} · {{ $fiche->filiere?->name }} · année {{ $fiche->annee }}</p>
                        </div>
                        <button wire:click="$set('ficheId', null)" class="bouton-icone text-gray-500 hover:bg-gray-100" title="Fermer" aria-label="Fermer"><x-icone-action nom="annuler" /></button>
                    </div>

                    <dl class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                        <div><dt class="text-gray-500">Né(e) le</dt><dd class="font-medium">{{ $fiche->date_naissance?->format('d/m/Y') ?? '—' }} {{ $fiche->lieu_naissance ? 'à '.$fiche->lieu_naissance : '' }}</dd></div>
                        <div><dt class="text-gray-500">Nationalité</dt><dd class="font-medium">{{ $fiche->nationalite }}</dd></div>
                        <div><dt class="text-gray-500">Contact</dt><dd class="font-medium">{{ $fiche->telephone ?? '—' }}{{ $fiche->email ? ' · '.$fiche->email : '' }}</dd></div>
                        <div><dt class="text-gray-500">Régime</dt><dd class="font-medium">{{ $fiche->regime === 'interne' ? 'Interne' : 'Externe' }}</dd></div>
                        <div><dt class="text-gray-500">Diplôme visé</dt><dd class="font-medium">{{ $fiche->diplome_vise ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">Durée de formation</dt><dd class="font-medium">{{ $fiche->duree_formation ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">Conditions d’accès</dt><dd class="font-medium">{{ $fiche->conditions_acces ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">Métiers correspondants</dt><dd class="font-medium">{{ $fiche->metiers ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">Assurance maladie IFPM</dt><dd class="font-medium">{{ $fiche->assurance_ifpm ? 'Oui' : ($fiche->compagnie_assurance ?: 'Non') }}</dd></div>
                        <div><dt class="text-gray-500">Visite médicale</dt><dd class="font-medium">{{ $fiche->date_visite_medicale?->format('d/m/Y') ?? '—' }} {{ $fiche->code_visite ? '· code '.$fiche->code_visite : '' }}</dd></div>
                    </dl>

                    <h4 class="mt-6 mb-2 text-sm font-semibold text-gray-800">Validation des paiements</h4>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm" data-export-titre="Échéancier {{ $fiche->matricule }}">
                            <thead><tr><th>Description</th><th class="text-right">Dû</th><th class="text-right">Versé</th><th>Date</th><th>Visa</th><th>Pénalité</th><th></th></tr></thead>
                            <tbody>
                                @foreach ($fiche->versements as $versement)
                                    <tr>
                                        <td>{{ $versement->libelle }}</td>
                                        <td class="text-right text-gray-500">{{ (float) $versement->montant_prevu > 0 ? number_format((float) $versement->montant_prevu, 0, ',', ' ') : '—' }}</td>
                                        <td class="text-right {{ $versement->estRegle() ? 'font-medium text-emerald-700' : '' }}">{{ $versement->estRegle() ? number_format((float) $versement->montant, 0, ',', ' ') : '—' }}</td>
                                        <td>{{ $versement->date_versement?->format('d/m/Y') ?? '—' }}</td>
                                        <td>
                                            @if ($versement->vise)
                                                <span class="text-emerald-600"><x-icone-action nom="valider" class="w-4 h-4" /></span>
                                            @else
                                                <span class="text-gray-300">—</span>
                                            @endif
                                        </td>
                                        <td>{{ $versement->penalite ? number_format((float) $versement->penalite, 0, ',', ' ') : '—' }}</td>
                                        <td>
                                            <button wire:click="ouvrirVersement({{ $versement->id }})" class="bouton-icone text-indigo-600 hover:bg-indigo-50" title="Saisir" aria-label="Saisir"><x-icone-action nom="modifier" /></button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 flex items-center justify-between rounded-lg bg-gray-50 px-4 py-3 text-sm">
                        <span class="text-gray-600">Scolarité annoncée : <strong>{{ number_format((float) $fiche->montant_scolarite, 0, ',', ' ') }} FCFA</strong></span>
                        <span class="text-gray-600">Versé : <strong>{{ number_format($fiche->totalVerse(), 0, ',', ' ') }}</strong> · Reste : <strong class="{{ $fiche->resteAPayer() > 0 ? 'text-amber-700' : 'text-emerald-700' }}">{{ number_format($fiche->resteAPayer(), 0, ',', ' ') }}</strong></span>
                    </div>
                </div>
            </div>
        @endif

        @if ($versementId)
            <div class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 p-4">
                <div class="w-full max-w-sm rounded-xl bg-white p-6 shadow-xl">
                    <h3 class="text-base font-semibold text-gray-800">Enregistrer un versement</h3>
                    <div class="mt-4 space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Montant (FCFA)</label>
                            <input type="number" min="0" step="100" wire:model="montant" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('montant')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date</label>
                            <input type="date" wire:model="date_versement" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('date_versement')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" wire:model="vise" class="rounded border-gray-300 text-blue-700 focus:ring-blue-500">
                            Visa du Secrétariat (reçu bancaire présenté)
                        </label>
                    </div>
                    <div class="mt-5 flex justify-end gap-2">
                        <button wire:click="$set('versementId', null)" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm">Annuler</button>
                        <button wire:click="enregistrerVersement" class="px-4 py-2 bg-blue-700 text-white rounded-lg text-sm hover:bg-blue-800">
                            <span wire:loading.remove wire:target="enregistrerVersement">Enregistrer</span>
                            <span wire:loading wire:target="enregistrerVersement">Enregistrement…</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
    @endvolt
</x-layouts.app>
