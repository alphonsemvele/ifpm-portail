<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use App\Models\SessionConcours;
use App\Services\ConcoursService;

name('admin.concours.index');
middleware(['auth', 'verified', 'role']);

new class extends Component {
    public ?int $modifieId = null;
    public ?int $supprimeId = null;
    public string $message = '';
    public string $erreur = '';

    // Formulaire
    public string $libelle = '';
    public string $date_concours = '';
    public string $annee = '';
    public string $lieu = '';
    public string $type = 'concours';
    public string $statut = 'ouverte';
    public string $observations = '';

    public function mount(): void
    {
        $this->annee = app(ConcoursService::class)->anneeCourante();
    }

    #[Computed]
    public function sessions()
    {
        return SessionConcours::withCount('candidats')
            ->orderByDesc('date_concours')->orderByDesc('id')->get();
    }

    public function editer(int $id): void
    {
        $session = SessionConcours::findOrFail($id);
        $this->modifieId = $session->id;
        $this->libelle = $session->libelle;
        $this->date_concours = $session->date_concours?->format('Y-m-d') ?? '';
        $this->annee = $session->annee;
        $this->lieu = (string) $session->lieu;
        $this->type = $session->type;
        $this->statut = $session->statut;
        $this->observations = (string) $session->observations;
    }

    public function annuler(): void
    {
        $this->reset(['modifieId', 'libelle', 'date_concours', 'lieu', 'observations', 'erreur']);
        $this->type = 'concours';
        $this->statut = 'ouverte';
        $this->annee = app(ConcoursService::class)->anneeCourante();
    }

    public function enregistrer(): void
    {
        $donnees = $this->validate([
            'libelle' => ['required', 'string', 'max:160'],
            'date_concours' => ['nullable', 'date'],
            'annee' => ['required', 'string', 'max:12'],
            'lieu' => ['nullable', 'string', 'max:160'],
            'type' => ['required', 'in:concours,dossier,mixte'],
            'statut' => ['required', 'in:ouverte,fermee'],
            'observations' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($this->modifieId) {
            SessionConcours::findOrFail($this->modifieId)->update($donnees);
            $this->message = 'Session mise à jour.';
        } else {
            SessionConcours::create($donnees);
            $this->message = 'Session de concours créée.';
        }

        $this->annuler();
        unset($this->sessions);
    }

    public function supprimer(): void
    {
        $session = SessionConcours::withCount('candidats')->findOrFail($this->supprimeId);

        if ($session->candidats_count > 0) {
            $this->erreur = 'Impossible : '.$session->candidats_count.' candidat(s) sont rattachés à cette session.';
            $this->supprimeId = null;

            return;
        }

        $session->delete();
        $this->supprimeId = null;
        $this->message = 'Session supprimée.';
        unset($this->sessions);
    }
};
?>

<x-layouts.app title="Sessions de concours">
    @volt
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Sessions de concours</h1>
                <p class="text-gray-500">Chaque session regroupe les candidats d’une même date d’examen.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="/admin/concours/candidats" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">Voir les candidats</a>
            </div>
        </div>

        @if ($message)
            <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">{{ $message }}</div>
        @endif
        @if ($erreur)
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">{{ $erreur }}</div>
        @endif

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <section class="xl:col-span-2 bg-white rounded-xl shadow p-4 sm:p-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-base font-semibold text-gray-800">Sessions enregistrées</h2>
                    <span class="text-sm text-gray-500">{{ $this->sessions->count() }} session(s)</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm" data-export-titre="Sessions de concours">
                        <thead>
                            <tr>
                                <th>Session</th><th>Date</th><th>Année</th><th>Type</th>
                                <th class="text-right">Candidats</th><th>Statut</th><th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($this->sessions as $session)
                                <tr>
                                    <td>
                                        <span class="block font-medium">{{ $session->libelle }}</span>
                                        @if ($session->lieu)<span class="text-xs text-gray-500">{{ $session->lieu }}</span>@endif
                                    </td>
                                    <td>{{ $session->date_concours?->format('d/m/Y') ?? '—' }}</td>
                                    <td>{{ $session->annee }}</td>
                                    <td>{{ ['concours' => 'Concours', 'dossier' => 'Étude de dossier', 'mixte' => 'Mixte'][$session->type] }}</td>
                                    <td class="text-right font-medium">
                                        <a href="/admin/concours/candidats?session={{ $session->id }}" class="text-blue-700 hover:underline">{{ $session->candidats_count }}</a>
                                    </td>
                                    <td>
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $session->statut === 'ouverte' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                                            {{ $session->statut === 'ouverte' ? 'Ouverte' : 'Fermée' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-1">
                                            <button wire:click="editer({{ $session->id }})" class="bouton-icone text-indigo-600 hover:bg-indigo-50" title="Modifier" aria-label="Modifier"><x-icone-action nom="modifier" /></button>
                                            <button wire:click="$set('supprimeId', {{ $session->id }})" class="bouton-icone text-red-600 hover:bg-red-50" title="Supprimer" aria-label="Supprimer"><x-icone-action nom="supprimer" /></button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-gray-500">Aucune session pour le moment.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="bg-white rounded-xl shadow p-4 sm:p-5" data-sans-export>
                <h2 class="text-base font-semibold text-gray-800 mb-3">{{ $modifieId ? 'Modifier la session' : 'Nouvelle session' }}</h2>
                <form wire:submit="enregistrer" class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Libellé <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="libelle" placeholder="Concours du 19 septembre 2026"
                               class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('libelle')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date du concours</label>
                            <input type="date" wire:model="date_concours" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Année <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="annee" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Lieu</label>
                        <input type="text" wire:model="lieu" placeholder="Ndazoa, Mbankomo" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Admission</label>
                            <select wire:model="type" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="concours">Concours</option>
                                <option value="dossier">Étude de dossier</option>
                                <option value="mixte">Les deux</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Statut</label>
                            <select wire:model="statut" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="ouverte">Ouverte</option>
                                <option value="fermee">Fermée</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Observations</label>
                        <textarea wire:model="observations" rows="2" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>
                    <div class="flex gap-2 pt-1">
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-700 text-white rounded-lg text-sm font-medium hover:bg-blue-800">
                            <span wire:loading.remove wire:target="enregistrer">{{ $modifieId ? 'Enregistrer' : 'Créer la session' }}</span>
                            <span wire:loading wire:target="enregistrer">Enregistrement…</span>
                        </button>
                        @if ($modifieId)
                            <button type="button" wire:click="annuler" class="px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">Annuler</button>
                        @endif
                    </div>
                </form>
            </section>
        </div>

        @if ($supprimeId)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                <div class="w-full max-w-sm rounded-xl bg-white p-6 shadow-xl">
                    <h3 class="text-base font-semibold text-gray-800">Supprimer cette session ?</h3>
                    <p class="mt-2 text-sm text-gray-500">Cette action est définitive.</p>
                    <div class="mt-5 flex justify-end gap-2">
                        <button wire:click="$set('supprimeId', null)" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm">Annuler</button>
                        <button wire:click="supprimer" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">Supprimer</button>
                    </div>
                </div>
            </div>
        @endif
    </div>
    @endvolt
</x-layouts.app>
