<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Cycle;
use App\Models\Filiere;
use App\Models\Specialite;
use App\Models\ProfilSalaire;

name('admin.personnel');
middleware(['auth', 'verified']);
?>

<?php

new class extends Component {
    public $users;
    public $cycles;
    public $filieres;
    public $specialites;
    public $name = '';
    public $role = '';
    public $cycle_id = '';
    public $filiere_id = '';
    public $specialite_id = '';
    public $email = '';
    public $contact = '';
    public $showAddModal      = false;
    public $showEditModal     = false;
    public $showDeleteModal   = false;
    public $showDetailsModal  = false;
    public $showActivateModal   = false;
    public $showDeactivateModal = false;
    public $showConnexionModal  = false;
    public $userElement;
    public $showNotification = false;
    public $notificationMessage = '';
    public $notificationType = '';

    /** Anciens comptes locaux, conserves en base mais hors de cette liste. */
    public int $anciensComptes = 0;

    /**
     * Va chercher la liste du personnel au portail.
     *
     * Le portail la diffuse a chaque changement d'acces, mais tant qu'aucun
     * n'a eu lieu l'institut ne connait personne : ce bouton amorce.
     */
    public function actualiserDepuisLePortail(): void
    {
        try {
            $r = app(\App\Services\EffectifPortail::class)->recupererDuPortail();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Actualisation du personnel.', ['raison' => $e->getMessage()]);
            $r = ['erreur' => "La récupération a échoué : ".$e->getMessage()];
        }

        if (isset($r['erreur'])) {
            $this->notificationType = 'error';
            $this->notificationMessage = $r['erreur'];
            $this->showNotification = true;

            return;
        }

        $this->chargerUsers();

        $this->notificationType = 'success';
        $this->notificationMessage = $r['crees'] + $r['mis_a_jour'] === 0
            ? "Le portail n'a transmis aucun agent pour l'institut."
            : sprintf('%d compte(s) créé(s), %d mis à jour.', $r['crees'], $r['mis_a_jour']);

        if (($r['erreurs'] ?? 0) > 0) {
            $this->notificationMessage .= ' '.$r['erreurs'].' agent(s) n’ont pas pu être repris — voir le journal.';
        }
        $this->showNotification = true;
    }

    public function mount()
    {
        $this->cycles     = Cycle::all();
        $this->filieres   = Filiere::all();
        $this->specialites = Specialite::all();
        $this->chargerUsers();
    }

    /**
     * Le personnel de l'institut : enseignants, coordonnateurs, agents.
     *
     * Les administrateurs n'y figurent pas — administrer n'est pas un emploi —
     * ni les comptes que le portail ne compte pas dans ses effectifs.
     *
     * Un agent apparait qu'il soit deja rattache a un compte du portail ou
     * non : la reprise se fait au fil de l'eau, et l'institut ne peut pas
     * attendre qu'elle soit finie pour voir son personnel.
     */
    private function chargerUsers(): void
    {
        $this->users = User::with(['cycle', 'filiere', 'specialite'])
            ->personnelDeLInstitut()
            ->orderBy('name')->orderBy('lastname')
            ->get();

        $this->anciensComptes = User::anciensComptesLocaux()->count();
    }

    public function save()
    {
        $this->validate([
            'name'         => 'required|string|max:255',
            'role'         => 'required|in:enseignant,filiere,specialite,concierge,bibliothecaire,admin',
            'cycle_id'     => 'nullable|exists:cycles,id',
            'filiere_id'   => 'required_if:role,filiere,specialite,enseignant|exists:filieres,id',
            'specialite_id'=> 'nullable|exists:specialites,id',
            'email'        => 'required|email|unique:users,email',
            'contact'      => 'required|string|max:20',
        ]);
        User::create([
            'name'         => $this->name,
            'role'         => $this->role,
            'cycle_id'     => $this->cycle_id ?: null,
            'filiere_id'   => in_array($this->role, ['filiere', 'specialite', 'enseignant']) ? $this->filiere_id : null,
            'specialite_id'=> in_array($this->role, ['specialite', 'enseignant']) && $this->specialite_id ? $this->specialite_id : null,
            'email'        => $this->email,
            'contact'      => $this->contact,
            'status'       => 'pending',
            'password'     => bcrypt('password'),
        ]);
        $this->reset(['name', 'role', 'cycle_id', 'filiere_id', 'specialite_id', 'email', 'contact']);
        $this->showAddModal = false;
        $this->chargerUsers();
        $this->toast('Personnel ajouté avec succès !');
    }

    public function functionShowAddModal()
    {
        $this->reset(['name', 'role', 'cycle_id', 'filiere_id', 'specialite_id', 'email', 'contact']);
        $this->showAddModal = true;
    }

    public function functionShowEditModal($id)
    {
        $this->userElement   = User::with(['cycle', 'filiere', 'specialite'])->findOrFail($id);
        $this->name          = $this->userElement->name;
        $this->role          = $this->userElement->role;
        $this->cycle_id      = $this->userElement->cycle_id;
        $this->filiere_id    = $this->userElement->filiere_id;
        $this->specialite_id = $this->userElement->specialite_id;
        $this->email         = $this->userElement->email;
        $this->contact       = $this->userElement->contact;
        $this->showEditModal = true;
    }

    public function update()
    {
        $this->validate([
            'name'         => 'required|string|max:255',
            'role'         => 'required|in:enseignant,filiere,specialite,concierge,bibliothecaire,admin',
            'cycle_id'     => 'nullable|exists:cycles,id',
            'filiere_id'   => 'required_if:role,filiere,specialite,enseignant|exists:filieres,id',
            'specialite_id'=> 'nullable|exists:specialites,id',
            'email'        => 'required|email|unique:users,email,' . $this->userElement->id,
            'contact'      => 'required|string|max:20',
        ]);
        $this->userElement->update([
            'name'         => $this->name,
            'role'         => $this->role,
            'cycle_id'     => $this->cycle_id ?: null,
            'filiere_id'   => in_array($this->role, ['filiere', 'specialite', 'enseignant']) ? $this->filiere_id : null,
            'specialite_id'=> in_array($this->role, ['specialite', 'enseignant']) && $this->specialite_id ? $this->specialite_id : null,
            'email'        => $this->email,
            'contact'      => $this->contact,
        ]);
        $this->reset(['name', 'role', 'cycle_id', 'filiere_id', 'specialite_id', 'email', 'contact']);
        $this->showEditModal = false;
        $this->chargerUsers();
        $this->toast('Personnel mis à jour avec succès !');
    }

    public function functionShowDeleteModal($id)
    {
        $this->userElement    = User::findOrFail($id);
        $this->showDeleteModal = true;
    }

    public function functionShowDetailsModal($id)
    {
        $this->userElement      = User::with(['cycle', 'filiere', 'specialite'])->findOrFail($id);
        $this->showDetailsModal = true;
    }

    public function functionShowActivateModal($id)
    {
        $this->userElement      = User::findOrFail($id);
        $this->showActivateModal = true;
    }

    public function functionShowDeactivateModal($id)
    {
        $this->userElement        = User::findOrFail($id);
        $this->showDeactivateModal = true;
    }

    public function activateUser()
    {
        $this->userElement->update(['status' => 'Success']);
        $this->showActivateModal = false;
        $this->chargerUsers();
        $this->toast('Personnel activé avec succès !');
    }

    public function deactivateUser()
    {
        $this->userElement->update(['status' => 'pending']);
        $this->showDeactivateModal = false;
        $this->chargerUsers();
        $this->toast('Personnel désactivé avec succès !');
    }

    public function functionShowConnexionModal($id)
    {
        $this->userElement       = User::findOrFail($id);
        $this->showConnexionModal = true;
    }

    public function toggleConnexion()
    {
        $this->userElement->update(['peut_se_connecter' => ! $this->userElement->peut_se_connecter]);
        $this->showConnexionModal = false;
        $this->chargerUsers();
        $this->toast($this->userElement->peut_se_connecter ? 'Connexion réactivée avec succès !' : 'Connexion désactivée avec succès !');
    }

    public function deleteUser()
    {
        $this->userElement->update(['status' => 'failed']);
        $this->showDeleteModal = false;
        $this->chargerUsers();
        $this->toast('Personnel supprimé avec succès !');
    }

    public function closeModal()
    {
        $this->showAddModal        = false;
        $this->showEditModal       = false;
        $this->showDeleteModal     = false;
        $this->showDetailsModal    = false;
        $this->showActivateModal   = false;
        $this->showDeactivateModal = false;
        $this->showConnexionModal  = false;
    }

    public function closeNotification() { $this->showNotification = false; }

    private function toast(string $msg, string $type = 'success'): void
    {
        $this->showNotification    = true;
        $this->notificationMessage = $msg;
        $this->notificationType    = $type;
        $this->dispatch('auto-hide-notification');
    }

    public function getRoleLabel(string $role): string
    {
        return match($role) {
            'enseignant'    => 'Enseignant',
            'filiere'       => 'Resp. Filière',
            'specialite'    => 'Resp. Spécialité',
            'concierge'     => 'Concierge',
            'bibliothecaire'=> 'Bibliothécaire',
            'admin'         => 'Administrateur',
            default         => $role,
        };
    }

    public function getPosteLabel($poste): string
    {
        $postes = [
            'dir_ism'         => 'Directeur ISM',
            'dir_ifpm'        => 'Directrice IFPM',
            'dir_aaf'         => 'Directrice Affaires Admin. et Financières',
            'dir_aac'         => 'Directeur Affaires Académiques',
            'dir_rh'          => 'Directrice RH',
            'dir_mc'          => 'Directrice Marketing',
            'coord_sante'     => 'Coordonnateur Filière Santé',
            'coord_industrie' => 'Coordonnateur Filière Industrie',
            'coord_info'      => 'Coordonnateur Informatique',
            'coord_meca'      => 'Coordonnateur Génie Mécanique',
            'coord_gi'        => 'Coordonnateur Génie Informatique',
            'comptable'       => 'Comptable',
            'asst_dir_fp'     => 'Assistante Direction Formation Pro',
            'asst_dir_is'     => 'Assistante Direction Institut',
            'coord_ap'        => 'Coordonnateur Activités Pédagogiques',
            'coord_hnd'       => 'Coordonnateur HND',
            'coord_tourisme'  => 'Coordonnateur Tourisme',
            'coord_adj_sante' => 'Coordonnateur Adjoint Santé',
            'medecin'         => 'Médecin Référent',
            'gest_stocks'     => 'Gestionnaire Stocks',
            'chef_entretien'  => 'Chef Agent Entretien',
            'agent_scolarite' => 'Agent de Scolarité',
            'coord_droit'     => 'Coordonnateur Droit',
        ];
        if (isset($postes[$poste])) {
            return $postes[$poste];
        }

        /*
         * Hors de cette table : soit un code local jamais recense
         * (coord_genie_info), soit l'intitule libre que le portail envoie
         * (« Chef de departement »). Dans les deux cas on l'affiche, mis en
         * forme comme ailleurs dans l'application, plutot que de le perdre
         * derriere un « N/A ».
         */
        if (blank($poste)) {
            return 'N/A';
        }

        // Un code local (coord_genie_info) se met en forme ; l'intitule libre
        // du portail (« Chef de departement ») s'affiche tel qu'il a ete ecrit.
        return str_contains((string) $poste, '_')
            ? (string) \Illuminate\Support\Str::of((string) $poste)->replace('_', ' ')->title()
            : (string) $poste;
    }

    public function getSectionLabel($section_id): string
    {
        return match((string)$section_id) { '1' => 'ISM', '2' => 'IFPM', default => 'N/A' };
    }

};
?>

<x-layouts.app header="true">
@volt
<div class="min-h-screen bg-gray-50">

    {{-- ── Barre titre ── --}}
    <div class="px-4 sm:px-6 lg:px-8 pt-8">
        <div class="max-w-7xl mx-auto flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div class="flex items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Personnel</h1>
                    <p class="text-gray-500">Enseignants, coordonnateurs et agents de l’institut</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
            <button wire:click="actualiserDepuisLePortail" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-xl border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 disabled:opacity-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span wire:loading.remove wire:target="actualiserDepuisLePortail">Actualiser depuis le portail</span>
                <span wire:loading wire:target="actualiserDepuisLePortail">Récupération…</span>
            </button>
            <a href="{{ rtrim(config('portail.url'), '/') }}/admin/users" target="_blank" rel="noopener"
               class="inline-flex items-center gap-2 px-4 py-2 text-white text-sm font-semibold rounded-xl shadow"
               style="background:#4f46e5;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                Gérer les comptes au portail
            </a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div class="mb-6 p-4 bg-sky-50 border-l-4 border-sky-400 text-sky-900 rounded-lg text-sm">
            Le personnel inscrit au <strong>portail La Majestueuse</strong> pour l'institut. Les
            comptes se créent et se retirent là-bas, la rémunération s'y gère aussi. Les
            <strong>administrateurs</strong> n'y figurent pas : administrer n'est pas un emploi.
            <span class="block mt-1">
                @if ($recuLe = \App\Services\EffectifPortail::recuLe())
                    Dernière liste reçue le {{ \Carbon\Carbon::parse($recuLe)->format('d/m/Y à H:i') }}.
                @else
                    <strong>Aucune liste n'a encore été reçue</strong> — c'est pourquoi cette liste est vide.
                    Cliquez sur « Actualiser depuis le portail ».
                @endif
            </span>
            @if ($anciensComptes > 0)
                <span class="block mt-1 text-sky-800">
                    {{ $anciensComptes }} ancien(s) compte(s) d'avant le raccordement restent conservés
                    en base, hors de cette liste : l'historique de la scolarité s'y rattache.
                </span>
            @endif
        </div>

        {{-- Toast --}}
        @if ($showNotification)
            <div class="fixed top-6 right-6 z-50"
                 x-data="{ show: true }"
                 x-show="show"
                 x-init="setTimeout(() => show = false, 3500)"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-end="opacity-0">
                <div class="flex items-center gap-3 px-5 py-4 rounded-2xl shadow-2xl text-sm font-medium" style="background:#111827;color:white;">
                    <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0"
                         style="background:{{ $notificationType === 'error' ? '#dc2626' : '#22c55e' }};">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    {{ $notificationMessage }}
                </div>
            </div>
        @endif

        {{-- Table --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                <div class="w-1.5 h-5 rounded-full" style="background:#4f46e5;"></div>
                <span class="text-sm font-bold text-gray-700 uppercase tracking-wider">Liste du Personnel</span>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">{{ $users->count() }}</span>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs font-bold uppercase tracking-wider text-white" style="background:#111827;">
                        <th class="px-4 py-4">Photo</th>
                        <th class="px-4 py-4">Nom</th>
                        <th class="px-4 py-4">Rôle</th>
                        <th class="px-4 py-4">Poste</th>
                        <th class="px-4 py-4">Section</th>
                        <th class="px-4 py-4 text-center">Statut</th>
                        <th class="px-4 py-4 text-center">Connexion</th>
                        <th class="px-4 py-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $i => $user)
                        <tr wire:key="personnel-{{ $user->id }}" class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} border-t border-gray-100 hover:bg-indigo-50 transition-colors">
                            <td class="px-4 py-3">
                                @if($user->photo)
                                    <img src="https://ism-ndazoa.com/{{ $user->photo }}" class="w-9 h-9 rounded-full object-cover border-2 border-gray-200">
                                @else
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold text-white" style="background:#4f46e5;">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-semibold text-gray-900">
                                {{ trim($user->name.' '.$user->lastname) }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-xs font-semibold px-2 py-1 rounded-lg bg-indigo-50 text-indigo-700">{{ $this->getRoleLabel($user->role) }}</span>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $this->getPosteLabel($user->poste) }}</td>
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $this->getSectionLabel($user->section_id) }}</td>
                            <td class="px-4 py-3 text-center">
                                @if ($user->status === 'Success')
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full" style="background:#dcfce7;color:#15803d;">
                                        <span class="w-1.5 h-1.5 rounded-full" style="background:#22c55e;"></span> Actif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full bg-gray-100 text-gray-500">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> En attente
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($user->peut_se_connecter)
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full" style="background:#dcfce7;color:#15803d;">
                                        <span class="w-1.5 h-1.5 rounded-full" style="background:#22c55e;"></span> Autorisée
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full" style="background:#fee2e2;color:#b91c1c;">
                                        <span class="w-1.5 h-1.5 rounded-full" style="background:#ef4444;"></span> Bloquée
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                    {{-- Activer / Désactiver --}}
                                    @if ($user->status === 'pending')
                                        <button wire:key="btn-activate-{{ $user->id }}" wire:click="functionShowActivateModal({{ $user->id }})"
                                            class="w-8 h-8 rounded-lg flex items-center justify-center text-white" style="background:#16a34a;" title="Activer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        </button>
                                    @else
                                        <button wire:key="btn-deactivate-{{ $user->id }}" wire:click="functionShowDeactivateModal({{ $user->id }})"
                                            class="w-8 h-8 rounded-lg flex items-center justify-center text-white" style="background:#d97706;" title="Désactiver">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </button>
                                    @endif
                                    {{-- Autoriser / Bloquer connexion --}}
                                    @if ($user->peut_se_connecter)
                                        <button wire:key="btn-block-connexion-{{ $user->id }}" wire:click="functionShowConnexionModal({{ $user->id }})"
                                            class="w-8 h-8 rounded-lg flex items-center justify-center text-white" style="background:#dc2626;" title="Bloquer la connexion">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        </button>
                                    @else
                                        <button wire:key="btn-allow-connexion-{{ $user->id }}" wire:click="functionShowConnexionModal({{ $user->id }})"
                                            class="w-8 h-8 rounded-lg flex items-center justify-center text-white" style="background:#0d9488;" title="Autoriser la connexion">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-9 4h10a1 1 0 011 1v7a1 1 0 01-1 1H7a1 1 0 01-1-1v-7a1 1 0 011-1z"/></svg>
                                        </button>
                                    @endif
                                    {{-- Détails --}}
                                    <button wire:click="functionShowDetailsModal({{ $user->id }})"
                                        class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-blue-100 flex items-center justify-center text-gray-500 hover:text-blue-700 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </button>
                                    {{-- Modifier --}}
                                    <button wire:click="functionShowEditModal({{ $user->id }})"
                                        class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-indigo-100 flex items-center justify-center text-gray-500 hover:text-indigo-700 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    {{-- Supprimer --}}
                                    <button wire:click="functionShowDeleteModal({{ $user->id }})"
                                        class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-red-100 flex items-center justify-center text-gray-500 hover:text-red-600 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ════════ MODAL AJOUTER ════════ --}}
    <div class="fixed inset-0 z-50 overflow-y-auto" style="{{ $showAddModal ? 'background:rgba(0,0,0,0.6);' : 'display:none;' }}">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl max-h-screen overflow-y-auto">
                <div class="px-6 py-5 rounded-t-2xl flex items-center justify-between" style="background:#4f46e5;">
                    <h3 class="font-bold text-white text-base">Ajouter un personnel</h3>
                    <button wire:click="closeModal" class="text-white opacity-80 hover:opacity-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ([
                        ['Nom complet *', 'text', 'name', 'name'],
                        ['Email *', 'email', 'email', 'email'],
                        ['Téléphone *', 'text', 'contact', 'contact'],
                    ] as [$label, $type, $model, $err])
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">{{ $label }}</label>
                            <input type="{{ $type }}" wire:model="{{ $model }}" class="w-full text-sm border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                            @error($err)<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    @endforeach
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">Rôle *</label>
                        <select wire:model="role" class="w-full text-sm border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                            <option value="">— Choisir —</option>
                            <option value="enseignant">Enseignant</option>
                            <option value="filiere">Resp. Filière</option>
                            <option value="specialite">Resp. Spécialité</option>
                            <option value="concierge">Concierge</option>
                            <option value="bibliothecaire">Bibliothécaire</option>
                            <option value="admin">Administrateur</option>
                        </select>
                        @error('role')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">Cycle</label>
                        <select wire:model="cycle_id" class="w-full text-sm border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                            <option value="">— Aucun —</option>
                            @foreach ($cycles as $cycle)
                                <option value="{{ $cycle->id }}">{{ $cycle->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">Filière</label>
                        <select wire:model="filiere_id" class="w-full text-sm border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                            <option value="">— Aucune —</option>
                            @foreach ($filieres as $filiere)
                                <option value="{{ $filiere->id }}">{{ $filiere->name }}</option>
                            @endforeach
                        </select>
                        @error('filiere_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">Spécialité</label>
                        <select wire:model="specialite_id" class="w-full text-sm border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                            <option value="">— Aucune —</option>
                            @foreach ($specialites as $sp)
                                <option value="{{ $sp->id }}">{{ $sp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-2xl flex justify-end gap-3">
                    <button wire:click="closeModal" class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50">Annuler</button>
                    <button wire:click="save" class="px-6 py-2.5 text-sm font-semibold text-white rounded-xl" style="background:#4f46e5;">Créer</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════ MODAL MODIFIER ════════ --}}
    <div class="fixed inset-0 z-50 overflow-y-auto" style="{{ $showEditModal ? 'background:rgba(0,0,0,0.6);' : 'display:none;' }}">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl max-h-screen overflow-y-auto">
                <div class="px-6 py-5 rounded-t-2xl flex items-center justify-between" style="background:#4f46e5;">
                    <h3 class="font-bold text-white text-base">Modifier le personnel</h3>
                    <button wire:click="closeModal" class="text-white opacity-80 hover:opacity-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">Nom complet *</label>
                        <input type="text" wire:model="name" class="w-full text-sm border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                        @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">Email *</label>
                        <input type="email" wire:model="email" class="w-full text-sm border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                        @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">Téléphone *</label>
                        <input type="text" wire:model="contact" class="w-full text-sm border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                        @error('contact')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">Rôle *</label>
                        <select wire:model="role" class="w-full text-sm border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                            <option value="">— Choisir —</option>
                            <option value="enseignant">Enseignant</option>
                            <option value="filiere">Resp. Filière</option>
                            <option value="specialite">Resp. Spécialité</option>
                            <option value="concierge">Concierge</option>
                            <option value="bibliothecaire">Bibliothécaire</option>
                            <option value="admin">Administrateur</option>
                        </select>
                        @error('role')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">Cycle</label>
                        <select wire:model="cycle_id" class="w-full text-sm border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                            <option value="">— Aucun —</option>
                            @foreach ($cycles as $cycle)
                                <option value="{{ $cycle->id }}">{{ $cycle->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">Filière</label>
                        <select wire:model="filiere_id" class="w-full text-sm border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                            <option value="">— Aucune —</option>
                            @foreach ($filieres as $filiere)
                                <option value="{{ $filiere->id }}">{{ $filiere->name }}</option>
                            @endforeach
                        </select>
                        @error('filiere_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">Spécialité</label>
                        <select wire:model="specialite_id" class="w-full text-sm border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                            <option value="">— Aucune —</option>
                            @foreach ($specialites as $sp)
                                <option value="{{ $sp->id }}">{{ $sp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-2xl flex justify-end gap-3">
                    <button wire:click="closeModal" class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50">Annuler</button>
                    <button wire:click="update" class="px-6 py-2.5 text-sm font-semibold text-white rounded-xl" style="background:#4f46e5;">Mettre à jour</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════ MODAL DÉTAILS ════════ --}}
    <div class="fixed inset-0 z-50 overflow-y-auto" style="{{ $showDetailsModal ? 'background:rgba(0,0,0,0.6);' : 'display:none;' }}">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
                <div class="px-6 py-5 rounded-t-2xl" style="background:#1e293b;">
                    <h3 class="font-bold text-white">Détails du personnel</h3>
                </div>
                <div class="p-6 space-y-3 text-sm">
                    @if ($userElement)
                        @if($userElement->photo)
                            <div class="flex justify-center mb-4">
                                <img src="https://ism-ndazoa.com/{{ $userElement->photo }}" class="w-20 h-20 rounded-full object-cover border-2 border-indigo-300">
                            </div>
                        @endif
                        @foreach ([
                            ['Nom',        trim(($userElement->name ?? '').' '.($userElement->lastname ?? '')) ?: 'N/A'],
                            ['Poste',      $this->getPosteLabel($userElement->poste)],
                            ['Section',    $this->getSectionLabel($userElement->section_id)],
                            ['Rôle',       $this->getRoleLabel($userElement->role ?? '')],
                            ['Cycle',      $userElement->cycle->name ?? '—'],
                            ['Filière',    $userElement->filiere->name ?? '—'],
                            ['Spécialité', $userElement->specialite->name ?? '—'],
                            ['Email',      $userElement->email ?? 'N/A'],
                            ['Téléphone',  $userElement->contact ?? 'N/A'],
                        ] as [$k, $v])
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <span class="font-semibold text-gray-500 text-xs uppercase tracking-wide">{{ $k }}</span>
                                <span class="text-gray-800 font-medium">{{ $v }}</span>
                            </div>
                        @endforeach
                    @endif
                </div>
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-2xl flex justify-end">
                    <button wire:click="closeModal" class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════ MODALS CONFIRMATION ════════ --}}
    @foreach ([
        [$showActivateModal,   'Activer',    "Activer {$userElement?->name} ?",    'activateUser',    '#16a34a', 'Activer'],
        [$showDeactivateModal, 'Désactiver', "Désactiver {$userElement?->name} ?", 'deactivateUser',  '#d97706', 'Désactiver'],
        [$showConnexionModal,
            $userElement?->peut_se_connecter ? 'Bloquer la connexion' : 'Autoriser la connexion',
            ($userElement?->peut_se_connecter ? "Bloquer la connexion de {$userElement?->name} ?" : "Autoriser la connexion de {$userElement?->name} ?"),
            'toggleConnexion',
            $userElement?->peut_se_connecter ? '#dc2626' : '#0d9488',
            $userElement?->peut_se_connecter ? 'Bloquer' : 'Autoriser'],
        [$showDeleteModal,     'Supprimer',  "Supprimer {$userElement?->name} ?",  'deleteUser',      '#dc2626', 'Supprimer'],
    ] as [$show, $titre, $msg, $action, $color, $label])
        <div class="fixed inset-0 z-50 overflow-y-auto" style="{{ $show ? 'background:rgba(0,0,0,0.6);' : 'display:none;' }}">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center">
                    <h3 class="text-lg font-bold text-gray-900 mb-2">{{ $titre }}</h3>
                    <p class="text-sm text-gray-500 mb-6">{{ $msg }}</p>
                    <div class="flex gap-3">
                        <button wire:click="closeModal" class="flex-1 py-2.5 text-sm font-semibold text-gray-700 border border-gray-300 rounded-xl hover:bg-gray-50">Annuler</button>
                        <button wire:click="{{ $action }}" class="flex-1 py-2.5 text-sm font-semibold text-white rounded-xl" style="background:{{ $color }};">{{ $label }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

</div>
@endvolt
</x-layouts.app>