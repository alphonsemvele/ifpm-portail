<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use App\Models\Filiere;
use App\Models\SessionConcours;
use App\Services\ConcoursService;
use Illuminate\Validation\Rule;

name('admin.concours.candidats.nouvelle');
middleware(['auth', 'verified', 'role']);

/**
 * Saisie d'une candidature : reprend, champ pour champ, la fiche de
 * renseignement IFPM-NDAZOA de public/inscription.
 */
new class extends Component {
    public string $session_id = '';
    public string $date_inscription = '';
    public string $admission = 'concours';

    // Identité
    public string $nom = '';
    public string $prenoms = '';
    public string $sexe = '';
    public string $date_naissance = '';
    public string $lieu_naissance = '';
    public string $cni = '';
    public string $nationalite = 'Camerounaise';
    public string $profession = '';
    public string $situation_familiale = '';
    public string $profession_conjoint = '';

    // Origine et résidence
    public string $region_origine = '';
    public string $departement_origine = '';
    public string $ville = '';
    public string $quartier = '';

    // Contacts
    public string $telephone = '';
    public string $whatsapp = '';
    public string $email = '';
    public string $langue = 'fr';

    // Formation demandée
    public string $filiere1_id = '';
    public string $filiere2_id = '';
    public string $filiere3_id = '';
    public string $diplome = '';
    public string $date_obtention = '';
    public string $niveau = '';

    // Pièces
    public bool $piece_fiche = false;
    public bool $piece_acte = false;
    public bool $piece_diplome = false;
    public bool $piece_photos = false;
    public bool $piece_enveloppe = false;

    public string $observations = '';
    public string $erreur = '';

    public function mount(): void
    {
        $this->date_inscription = now()->toDateString();
        $this->session_id = (string) (SessionConcours::where('statut', 'ouverte')
            ->orderByDesc('date_concours')->value('id') ?? '');
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

    public function niveaux(): array
    {
        return ConcoursService::NIVEAUX;
    }

    public function pieces(): array
    {
        return ConcoursService::PIECES;
    }

    public function enregistrer(ConcoursService $service)
    {
        $this->erreur = '';

        $donnees = $this->validate([
            'session_id' => ['required', 'exists:sessions_concours,id'],
            'date_inscription' => ['required', 'date'],
            'admission' => ['required', 'in:concours,dossier'],
            'nom' => ['required', 'string', 'max:120'],
            'prenoms' => ['nullable', 'string', 'max:160'],
            'sexe' => ['nullable', 'in:M,F'],
            'date_naissance' => ['nullable', 'date', 'before:today'],
            'lieu_naissance' => ['nullable', 'string', 'max:120'],
            'cni' => ['nullable', 'string', 'max:60'],
            'nationalite' => ['nullable', 'string', 'max:60'],
            'profession' => ['nullable', 'string', 'max:120'],
            'situation_familiale' => ['nullable', 'in:marie,celibataire,divorce'],
            'profession_conjoint' => ['nullable', 'string', 'max:120'],
            'region_origine' => ['nullable', 'string', 'max:120'],
            'departement_origine' => ['nullable', 'string', 'max:120'],
            'ville' => ['nullable', 'string', 'max:120'],
            'quartier' => ['nullable', 'string', 'max:120'],
            'telephone' => ['required', 'string', 'max:40'],
            'whatsapp' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:160'],
            'langue' => ['required', 'in:fr,en'],
            'filiere1_id' => ['required', 'exists:filieres,id'],
            'filiere2_id' => ['nullable', 'different:filiere1_id', 'exists:filieres,id'],
            'filiere3_id' => ['nullable', 'different:filiere1_id', 'different:filiere2_id', 'exists:filieres,id'],
            'diplome' => ['nullable', 'string', 'max:120'],
            'date_obtention' => ['nullable', 'date'],
            'niveau' => ['nullable', Rule::in(ConcoursService::NIVEAUX)],
            'piece_fiche' => ['boolean'],
            'piece_acte' => ['boolean'],
            'piece_diplome' => ['boolean'],
            'piece_photos' => ['boolean'],
            'piece_enveloppe' => ['boolean'],
            'observations' => ['nullable', 'string', 'max:1000'],
        ], [], [
            'session_id' => 'session', 'filiere1_id' => '1ᵉʳ choix de filière',
            'filiere2_id' => '2ᵉ choix', 'filiere3_id' => '3ᵉ choix',
        ]);

        // Les champs vides partent en NULL plutôt qu'en chaîne vide.
        foreach (['sexe', 'date_naissance', 'date_obtention', 'situation_familiale',
                  'filiere2_id', 'filiere3_id', 'niveau'] as $champ) {
            $donnees[$champ] = $donnees[$champ] ?: null;
        }

        try {
            $candidat = $service->enregistrerCandidat($donnees, auth()->id());
        } catch (\RuntimeException $e) {
            $this->erreur = $e->getMessage();

            return null;
        }

        session()->flash('candidat_message', 'Candidature enregistrée : '.$candidat->nomComplet().'.');

        return redirect('/admin/concours/candidats?session='.$candidat->session_id);
    }
};
?>

<x-layouts.app title="Nouvelle candidature">
    @volt
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Nouvelle candidature</h1>
                <p class="text-gray-500">Fiche de renseignement du candidat au concours d’entrée.</p>
            </div>
            <a href="/admin/concours/candidats" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">Retour à la liste</a>
        </div>

        @if ($erreur)
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">{{ $erreur }}</div>
        @endif

        <form wire:submit="enregistrer" class="space-y-6">
            <section class="bg-white rounded-xl shadow p-4 sm:p-5">
                <h2 class="text-base font-semibold text-gray-800 mb-4">Session et mode d’admission</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Session <span class="text-red-500">*</span></label>
                        <select wire:model="session_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">— Choisir —</option>
                            @foreach ($this->sessions as $s)
                                <option value="{{ $s->id }}">{{ $s->libelle }} @if ($s->statut === 'fermee') (fermée) @endif</option>
                            @endforeach
                        </select>
                        @error('session_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        @if ($this->sessions->isEmpty())
                            <p class="mt-1 text-xs text-amber-700">Aucune session : <a href="/admin/concours" class="underline">créez-en une</a> d’abord.</p>
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date d’inscription <span class="text-red-500">*</span></label>
                        <input type="date" wire:model="date_inscription" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('date_inscription')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Admission par</label>
                        <select wire:model="admission" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="concours">Concours</option>
                            <option value="dossier">Étude de dossier</option>
                        </select>
                    </div>
                </div>
            </section>

            <section class="bg-white rounded-xl shadow p-4 sm:p-5">
                <h2 class="text-base font-semibold text-gray-800 mb-4">Identité du candidat</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
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
                        <label class="block text-sm font-medium text-gray-700">Sexe</label>
                        <select wire:model="sexe" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">—</option>
                            <option value="M">Masculin</option>
                            <option value="F">Féminin</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date de naissance</label>
                        <input type="date" wire:model="date_naissance" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('date_naissance')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Lieu de naissance</label>
                        <input type="text" wire:model="lieu_naissance" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">N° CNI</label>
                        <input type="text" wire:model="cni" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nationalité</label>
                        <input type="text" wire:model="nationalite" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Profession</label>
                        <input type="text" wire:model="profession" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Situation familiale</label>
                        <select wire:model="situation_familiale" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">—</option>
                            <option value="marie">Marié(e)</option>
                            <option value="celibataire">Célibataire</option>
                            <option value="divorce">Divorcé(e)</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2 lg:col-span-3">
                        <label class="block text-sm font-medium text-gray-700">Profession du conjoint</label>
                        <input type="text" wire:model="profession_conjoint" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
            </section>

            <section class="bg-white rounded-xl shadow p-4 sm:p-5">
                <h2 class="text-base font-semibold text-gray-800 mb-4">Origine, résidence et contacts</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Région d’origine</label>
                        <input type="text" wire:model="region_origine" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Département</label>
                        <input type="text" wire:model="departement_origine" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Ville</label>
                        <input type="text" wire:model="ville" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Quartier de résidence</label>
                        <input type="text" wire:model="quartier" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Téléphone <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="telephone" placeholder="+237 6 00 00 00 00" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('telephone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">WhatsApp</label>
                        <input type="text" wire:model="whatsapp" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Adresse e-mail</label>
                        <input type="email" wire:model="email" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Langue de composition</label>
                        <select wire:model="langue" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="fr">Français</option>
                            <option value="en">Anglais</option>
                        </select>
                    </div>
                </div>
            </section>

            <section class="bg-white rounded-xl shadow p-4 sm:p-5">
                <h2 class="text-base font-semibold text-gray-800 mb-1">Filières sollicitées</h2>
                <p class="text-sm text-gray-500 mb-4">Trois choix possibles, par ordre de préférence.</p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    @foreach ([['filiere1_id', '1ᵉʳ choix', true], ['filiere2_id', '2ᵉ choix', false], ['filiere3_id', '3ᵉ choix', false]] as [$champ, $libelle, $requis])
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ $libelle }} @if ($requis)<span class="text-red-500">*</span>@endif</label>
                            <select wire:model="{{ $champ }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">—</option>
                                @foreach ($this->filieres as $f)
                                    <option value="{{ $f->id }}">{{ $f->name }}</option>
                                @endforeach
                            </select>
                            @error($champ)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    @endforeach
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Diplôme le plus élevé</label>
                        <input type="text" wire:model="diplome" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date d’obtention</label>
                        <input type="date" wire:model="date_obtention" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Niveau requis</label>
                        <select wire:model="niveau" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">—</option>
                            @foreach ($this->niveaux() as $n)
                                <option value="{{ $n }}">{{ $n }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </section>

            <section class="bg-white rounded-xl shadow p-4 sm:p-5">
                <h2 class="text-base font-semibold text-gray-800 mb-1">Pièces du dossier</h2>
                <p class="text-sm text-gray-500 mb-4">Cochez ce que le candidat a effectivement remis.</p>
                <div class="space-y-2">
                    @foreach ($this->pieces() as $champ => $libelle)
                        <label class="flex items-center gap-3 rounded-lg border border-gray-200 px-3 py-2 hover:border-gray-300">
                            <input type="checkbox" wire:model="{{ $champ }}" class="rounded border-gray-300 text-blue-700 focus:ring-blue-500">
                            <span class="text-sm text-gray-700">{{ $libelle }}</span>
                        </label>
                    @endforeach
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">Observations</label>
                    <textarea wire:model="observations" rows="2" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                </div>
            </section>

            <div class="flex flex-wrap items-center gap-3">
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-700 text-white rounded-lg text-sm font-medium hover:bg-blue-800">
                    <span wire:loading.remove wire:target="enregistrer">Enregistrer la candidature</span>
                    <span wire:loading wire:target="enregistrer">Enregistrement…</span>
                </button>
                <a href="/admin/concours/candidats" class="px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">Annuler</a>
            </div>
        </form>
    </div>
    @endvolt
</x-layouts.app>
