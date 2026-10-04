<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use App\Models\Candidat;
use App\Models\Filiere;
use App\Models\SessionConcours;
use App\Services\ConcoursService;

name('admin.concours.candidats');
middleware(['auth', 'verified', 'role']);

new class extends Component {
    use WithPagination;

    public string $session = '';
    public string $filiere = '';
    public string $niveau = '';
    public string $statut = '';
    public string $recherche = '';
    public ?int $detailId = null;
    public string $message = '';

    public function mount(): void
    {
        $this->session = (string) request('session', '');
        $this->message = (string) session('candidat_message', '');
    }

    public function updating($propriete): void
    {
        if (in_array($propriete, ['session', 'filiere', 'niveau', 'statut', 'recherche'], true)) {
            $this->resetPage();
        }
    }

    #[Computed]
    public function sessions()
    {
        return SessionConcours::orderByDesc('date_concours')->orderByDesc('id')->get();
    }

    #[Computed]
    public function filieres()
    {
        return Filiere::where('status', 'Success')->orderBy('name')->get(['id', 'name']);
    }

    #[Computed]
    public function candidats()
    {
        return Candidat::with(['session', 'filiere1', 'filiere2', 'filiere3'])
            ->when($this->session, fn ($q) => $q->where('session_id', $this->session))
            ->when($this->filiere, fn ($q) => $q->where(fn ($sub) => $sub
                ->where('filiere1_id', $this->filiere)
                ->orWhere('filiere2_id', $this->filiere)
                ->orWhere('filiere3_id', $this->filiere)))
            ->when($this->niveau, fn ($q) => $q->where('niveau', $this->niveau))
            ->when($this->statut, fn ($q) => $q->where('statut', $this->statut))
            ->when($this->recherche, fn ($q) => $q->where(fn ($sub) => $sub
                ->where('nom', 'like', '%'.$this->recherche.'%')
                ->orWhere('prenoms', 'like', '%'.$this->recherche.'%')
                ->orWhere('telephone', 'like', '%'.$this->recherche.'%')
                ->orWhere('cni', 'like', '%'.$this->recherche.'%')))
            ->orderBy('date_inscription')
            ->orderBy('nom')
            ->paginate(50);
    }

    #[Computed]
    public function compteurs()
    {
        $base = Candidat::query()->when($this->session, fn ($q) => $q->where('session_id', $this->session));

        return [
            'total' => (clone $base)->count(),
            'admis' => (clone $base)->where('statut', 'admis')->count(),
            'complets' => (clone $base)->where('piece_fiche', true)->where('piece_acte', true)
                ->where('piece_diplome', true)->where('piece_photos', true)->where('piece_enveloppe', true)->count(),
        ];
    }

    public function changerStatut(int $id, string $statut): void
    {
        if (! in_array($statut, ['inscrit', 'admis', 'refuse', 'absent'], true)) {
            return;
        }

        Candidat::findOrFail($id)->update(['statut' => $statut]);
        $this->message = 'Statut mis à jour.';
        unset($this->candidats, $this->compteurs);
    }

    public function niveaux(): array
    {
        return ConcoursService::NIVEAUX;
    }

    public function pieces(): array
    {
        return ConcoursService::PIECES;
    }
};
?>

<x-layouts.app title="Candidats au concours">
    @volt
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Candidats au concours</h1>
                <p class="text-gray-500">Liste des candidatures reçues, par session d’examen.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="/admin/concours/candidats/nouvelle" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-700 text-white rounded-lg text-sm font-medium hover:bg-blue-800">Nouvelle candidature</a>
                <a href="/admin/concours" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">Sessions</a>
            </div>
        </div>

        @if ($message)
            <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">{{ $message }}</div>
        @endif

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow p-4">
                <p class="text-xs uppercase text-gray-500">Candidats</p>
                <p class="text-3xl font-bold text-blue-800">{{ $this->compteurs['total'] }}</p>
                <p class="text-xs text-gray-500">{{ $this->session ? 'dans cette session' : 'toutes sessions' }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-4">
                <p class="text-xs uppercase text-gray-500">Admis</p>
                <p class="text-3xl font-bold text-emerald-700">{{ $this->compteurs['admis'] }}</p>
                <p class="text-xs text-gray-500">à inscrire</p>
            </div>
            <div class="bg-white rounded-xl shadow p-4">
                <p class="text-xs uppercase text-gray-500">Dossiers complets</p>
                <p class="text-3xl font-bold text-gray-800">{{ $this->compteurs['complets'] }}</p>
                <p class="text-xs text-gray-500">5 pièces fournies</p>
            </div>
            <div class="bg-white rounded-xl shadow p-4">
                <p class="text-xs uppercase text-gray-500">Sessions</p>
                <p class="text-3xl font-bold text-gray-800">{{ $this->sessions->count() }}</p>
                <p class="text-xs text-gray-500">{{ $this->sessions->where('statut', 'ouverte')->count() }} ouverte(s)</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow p-4 sm:p-5 mb-6" data-sans-export>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Session</label>
                    <select wire:model.live="session" class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Toutes</option>
                        @foreach ($this->sessions as $s)
                            <option value="{{ $s->id }}">{{ $s->libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Filière sollicitée</label>
                    <select wire:model.live="filiere" class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Toutes</option>
                        @foreach ($this->filieres as $f)
                            <option value="{{ $f->id }}">{{ $f->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Niveau</label>
                    <select wire:model.live="niveau" class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Tous</option>
                        @foreach ($this->niveaux() as $n)
                            <option value="{{ $n }}">{{ $n }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Statut</label>
                    <select wire:model.live="statut" class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Tous</option>
                        <option value="inscrit">Inscrit</option>
                        <option value="admis">Admis</option>
                        <option value="refuse">Refusé</option>
                        <option value="absent">Absent</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Recherche</label>
                    <input type="search" wire:model.live.debounce.400ms="recherche" placeholder="Nom, téléphone, CNI…"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow p-4 sm:p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-base font-semibold text-gray-800">
                    Liste des candidats{{ $this->session ? ' — '.$this->sessions->firstWhere('id', (int) $this->session)?->libelle : '' }}
                </h2>
                <span class="text-sm text-gray-500">{{ $this->candidats->total() }} candidat(s)</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm" data-export-titre="Liste des candidats au concours">
                    <thead>
                        <tr>
                            <th>Date d’inscription</th>
                            <th>Noms et prénoms</th>
                            <th>Filière sollicitée</th>
                            <th>Niveau requis</th>
                            <th>Contact</th>
                            <th>Dossier</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->candidats as $candidat)
                            <tr>
                                <td>{{ $candidat->date_inscription?->format('d/m/y') }}</td>
                                <td>
                                    <span class="block font-medium">{{ $candidat->nomComplet() }}</span>
                                    <span class="text-xs text-gray-500">{{ $candidat->session?->libelle }}</span>
                                </td>
                                <td>
                                    {{ $candidat->filiere1?->name ?? '—' }}
                                    @if ($candidat->filiere2)
                                        <span class="block text-xs text-gray-500">2ᵉ : {{ $candidat->filiere2->name }}</span>
                                    @endif
                                </td>
                                <td>{{ $candidat->niveau ?? '—' }}</td>
                                <td>
                                    {{ $candidat->telephone ?? '—' }}
                                    @if ($candidat->email)<span class="block text-xs text-gray-500">{{ $candidat->email }}</span>@endif
                                </td>
                                <td>
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $candidat->dossierComplet() ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                        {{ $candidat->piecesFournies() }}/5
                                    </span>
                                </td>
                                <td>
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                        @if ($candidat->statut === 'admis') bg-emerald-100 text-emerald-700
                                        @elseif ($candidat->statut === 'refuse') bg-red-100 text-red-700
                                        @elseif ($candidat->statut === 'absent') bg-gray-100 text-gray-600
                                        @else bg-blue-100 text-blue-800 @endif">
                                        {{ ['inscrit' => 'Inscrit', 'admis' => 'Admis', 'refuse' => 'Refusé', 'absent' => 'Absent'][$candidat->statut] }}
                                    </span>
                                </td>
                                <td>
                                    <div class="flex items-center gap-1">
                                        <button wire:click="$set('detailId', {{ $candidat->id }})" class="bouton-icone text-sky-600 hover:bg-sky-50" title="Détails" aria-label="Détails"><x-icone-action nom="voir" /></button>
                                        @if ($candidat->statut !== 'admis')
                                            <button wire:click="changerStatut({{ $candidat->id }}, 'admis')" class="bouton-icone text-emerald-600 hover:bg-emerald-50" title="Déclarer admis" aria-label="Déclarer admis"><x-icone-action nom="valider" /></button>
                                        @endif
                                        @if ($candidat->statut === 'admis' && ! $candidat->inscription)
                                            <a href="/admin/inscriptions/nouvelle?candidat={{ $candidat->id }}" class="bouton-icone text-indigo-600 hover:bg-indigo-50" title="Inscrire" aria-label="Inscrire"><x-icone-action nom="ajouter" /></a>
                                        @endif
                                        @if ($candidat->statut !== 'refuse')
                                            <button wire:click="changerStatut({{ $candidat->id }}, 'refuse')" class="bouton-icone text-red-600 hover:bg-red-50" title="Refuser" aria-label="Refuser"><x-icone-action nom="annuler" /></button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-gray-500">Aucun candidat ne correspond à ces critères.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $this->candidats->links() }}</div>
        </div>

        @if ($detailId)
            @php($fiche = \App\Models\Candidat::with(['session', 'filiere1', 'filiere2', 'filiere3'])->find($detailId))
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                <div class="w-full max-w-2xl max-h-[85vh] overflow-y-auto rounded-xl bg-white p-6 shadow-xl">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">{{ $fiche->nomComplet() }}</h3>
                            <p class="text-sm text-gray-500">{{ $fiche->session?->libelle }} · {{ $fiche->admission === 'concours' ? 'Concours' : 'Étude de dossier' }}</p>
                        </div>
                        <button wire:click="$set('detailId', null)" class="bouton-icone text-gray-500 hover:bg-gray-100" title="Fermer" aria-label="Fermer"><x-icone-action nom="annuler" /></button>
                    </div>

                    <dl class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                        <div><dt class="text-gray-500">Né(e) le</dt><dd class="font-medium">{{ $fiche->date_naissance?->format('d/m/Y') ?? '—' }} {{ $fiche->lieu_naissance ? 'à '.$fiche->lieu_naissance : '' }}</dd></div>
                        <div><dt class="text-gray-500">Sexe</dt><dd class="font-medium">{{ ['M' => 'Masculin', 'F' => 'Féminin'][$fiche->sexe] ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">CNI</dt><dd class="font-medium">{{ $fiche->cni ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">Nationalité</dt><dd class="font-medium">{{ $fiche->nationalite }}</dd></div>
                        <div><dt class="text-gray-500">Origine</dt><dd class="font-medium">{{ collect([$fiche->departement_origine, $fiche->region_origine])->filter()->implode(', ') ?: '—' }}</dd></div>
                        <div><dt class="text-gray-500">Résidence</dt><dd class="font-medium">{{ collect([$fiche->quartier, $fiche->ville])->filter()->implode(', ') ?: '—' }}</dd></div>
                        <div><dt class="text-gray-500">Téléphone</dt><dd class="font-medium">{{ $fiche->telephone ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">WhatsApp</dt><dd class="font-medium">{{ $fiche->whatsapp ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">Profession</dt><dd class="font-medium">{{ $fiche->profession ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">Situation familiale</dt><dd class="font-medium">{{ ['marie' => 'Marié(e)', 'celibataire' => 'Célibataire', 'divorce' => 'Divorcé(e)'][$fiche->situation_familiale] ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">Diplôme le plus élevé</dt><dd class="font-medium">{{ $fiche->diplome ?? '—' }} {{ $fiche->date_obtention ? '('.$fiche->date_obtention->format('Y').')' : '' }}</dd></div>
                        <div><dt class="text-gray-500">Langue</dt><dd class="font-medium">{{ $fiche->langue === 'en' ? 'Anglais' : 'Français' }}</dd></div>
                    </dl>

                    <h4 class="mt-6 text-sm font-semibold text-gray-800">Choix de filières</h4>
                    <ol class="mt-2 space-y-1 text-sm">
                        <li>1ᵉʳ choix : <span class="font-medium">{{ $fiche->filiere1?->name ?? '—' }}</span></li>
                        <li>2ᵉ choix : <span class="font-medium">{{ $fiche->filiere2?->name ?? '—' }}</span></li>
                        <li>3ᵉ choix : <span class="font-medium">{{ $fiche->filiere3?->name ?? '—' }}</span></li>
                    </ol>

                    <h4 class="mt-6 text-sm font-semibold text-gray-800">Pièces du dossier</h4>
                    <ul class="mt-2 space-y-1 text-sm">
                        @foreach ($this->pieces() as $champ => $libelle)
                            <li class="flex items-center gap-2">
                                <span class="{{ $fiche->{$champ} ? 'text-emerald-600' : 'text-gray-300' }}">
                                    <x-icone-action nom="{{ $fiche->{$champ} ? 'valider' : 'annuler' }}" class="w-4 h-4" />
                                </span>
                                <span class="{{ $fiche->{$champ} ? 'text-gray-800' : 'text-gray-400' }}">{{ $libelle }}</span>
                            </li>
                        @endforeach
                    </ul>

                    @if ($fiche->observations)
                        <p class="mt-5 rounded-lg bg-gray-50 p-3 text-sm text-gray-600">{{ $fiche->observations }}</p>
                    @endif
                </div>
            </div>
        @endif
    </div>
    @endvolt
</x-layouts.app>
