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
    public $showBulletinModal   = false;
    public $userElement;
    public $bulletinUser = null;
    public $showNotification = false;
    public $notificationMessage = '';
    public $notificationType = '';

    public function mount()
    {
        $this->cycles     = Cycle::all();
        $this->filieres   = Filiere::all();
        $this->specialites = Specialite::all();
        $this->chargerUsers();
    }

    private function chargerUsers(): void
    {
        $this->users = User::with(['cycle', 'filiere', 'specialite', 'profilSalaire.categorie'])
            ->where('role', '!=', 'student')
            ->where('status', '!=', 'failed')
            ->get();
    }

    public function voirBulletin(int $id): void
    {
        $this->bulletinUser = User::with([
            'profilSalaire.categorie',
            'profilSalaire.echelon',
            'echelon',
            'profilSalaire.indemnites',
            'profilSalaire.retenues',
        ])->find($id);
        $this->showBulletinModal = true;
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
        $this->showBulletinModal   = false;
        $this->bulletinUser        = null;
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
        return $postes[$poste] ?? 'N/A';
    }

    public function getSectionLabel($section_id): string
    {
        return match((string)$section_id) { '1' => 'ISM', '2' => 'IFPM', default => 'N/A' };
    }

    public function getBulletinData(): array
    {
        if (!$this->bulletinUser || !$this->bulletinUser->profilSalaire) return [];
        $profil    = $this->bulletinUser->profilSalaire;
        $categorie = $profil->categorie;
        // Salaire de base = échelon de l'employé si défini, sinon échelon du profil, sinon 0
        $echelon   = $this->bulletinUser->echelon
                   ?? ($profil->echelon ?? null);
        $base      = $echelon ? (float)$echelon->salaire : 0;

        $indemnites = $profil->indemnites->map(function($ind) use ($base) {
            $montant = $ind->pivot->type_calcul === 'fixe'
                ? $ind->pivot->value
                : round($base * $ind->pivot->value / 100);
            return ['libelle' => $ind->libelle, 'type' => $ind->pivot->type_calcul, 'valeur' => $ind->pivot->value, 'montant' => $montant];
        })->toArray();

        $retenues = $profil->retenues->map(function($ret) use ($base) {
            $montant = $ret->pivot->type_calcul === 'fixe'
                ? $ret->pivot->value
                : round($base * $ret->pivot->value / 100);
            return ['libelle' => $ret->libelle, 'type' => $ret->pivot->type_calcul, 'valeur' => $ret->pivot->value, 'montant' => $montant];
        })->toArray();

        $total_ind = array_sum(array_column($indemnites, 'montant'));
        $total_ret = array_sum(array_column($retenues,   'montant'));

        return compact('profil', 'categorie', 'base', 'echelon', 'indemnites', 'retenues', 'total_ind', 'total_ret') + [
            'brut' => $base + $total_ind,
            'net'  => $base + $total_ind - $total_ret,
        ];
    }
};
?>

<x-layouts.app header="true">
@volt
<div class="min-h-screen bg-gray-50">

    {{-- ── Barre titre ── --}}
    <div class="bg-white border-b border-gray-200 px-6 py-5">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#4f46e5;">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-gray-900">Gestion du Personnel</h1>
                    <p class="text-xs text-gray-500">Responsables, enseignants et staff</p>
                </div>
            </div>
            <button wire:click="functionShowAddModal"
                class="inline-flex items-center gap-2 px-4 py-2 text-white text-sm font-semibold rounded-xl shadow"
                style="background:#4f46e5;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Ajouter un personnel
            </button>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

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
                    <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0" style="background:#22c55e;">
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
                        <th class="px-4 py-4 text-center">Profil salaire</th>
                        <th class="px-4 py-4 text-center">Statut</th>
                        <th class="px-4 py-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $i => $user)
                        <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} border-t border-gray-100 hover:bg-indigo-50 transition-colors">
                            <td class="px-4 py-3">
                                @if($user->photo)
                                    <img src="https://ism-ndazoa.com/{{ $user->photo }}" class="w-9 h-9 rounded-full object-cover border-2 border-gray-200">
                                @else
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold text-white" style="background:#4f46e5;">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-semibold text-gray-900">{{ $user->name }}</td>
                            <td class="px-4 py-3">
                                <span class="text-xs font-semibold px-2 py-1 rounded-lg bg-indigo-50 text-indigo-700">{{ $this->getRoleLabel($user->role) }}</span>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $this->getPosteLabel($user->poste) }}</td>
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $this->getSectionLabel($user->section_id) }}</td>
                            <td class="px-4 py-3 text-center">
                                @if ($user->profilSalaire)
                                    <span class="text-xs font-semibold px-2 py-1 rounded-lg" style="background:#e0f2fe;color:#0369a1;">
                                        {{ $user->profilSalaire->nom }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
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
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                    {{-- Bulletin --}}
                                    <button wire:click="voirBulletin({{ $user->id }})"
                                        class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1.5 rounded-lg text-white"
                                        style="background:#059669;" title="Bulletin de paie">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                                        Bulletin
                                    </button>
                                    {{-- Activer / Désactiver --}}
                                    @if ($user->status === 'pending')
                                        <button wire:click="functionShowActivateModal({{ $user->id }})"
                                            class="w-8 h-8 rounded-lg flex items-center justify-center text-white" style="background:#16a34a;" title="Activer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        </button>
                                    @else
                                        <button wire:click="functionShowDeactivateModal({{ $user->id }})"
                                            class="w-8 h-8 rounded-lg flex items-center justify-center text-white" style="background:#d97706;" title="Désactiver">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
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

    {{-- ════════ MODAL BULLETIN DE PAIE ════════ --}}
    <div class="fixed inset-0 z-50 overflow-y-auto" style="{{ $showBulletinModal ? 'background:rgba(0,0,0,0.6);' : 'display:none;' }}">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg">

                @if ($bulletinUser)
                    {{-- Header --}}
                    <div class="px-6 py-5 rounded-t-2xl flex items-center justify-between" style="background:#059669;">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm font-bold text-white" style="background:rgba(255,255,255,0.2);">
                                {{ strtoupper(substr($bulletinUser->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-bold text-white">{{ strtoupper($bulletinUser->name) }}</p>
                                <p class="text-xs text-green-100">{{ $this->getRoleLabel($bulletinUser->role) }}</p>
                            </div>
                        </div>
                        <button wire:click="closeModal" class="text-white opacity-80 hover:opacity-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    @php $bd = $this->getBulletinData(); @endphp

                    @if (empty($bd))
                        {{-- Pas de profil --}}
                        <div class="p-10 text-center">
                            <div class="w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-4" style="background:#f0fdf4;">
                                <svg class="w-7 h-7" style="color:#86efac;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                            </div>
                            <p class="font-semibold text-gray-700 mb-1">Aucun profil salaire assigné</p>
                            <p class="text-sm text-gray-400 mb-5">Assignez un profil à cet employé pour voir son bulletin.</p>
                            <a href="{{ route('admin.rh.profils') }}" class="inline-flex items-center gap-2 px-4 py-2 text-white text-sm font-semibold rounded-xl" style="background:#0284c7;">
                                Gérer les profils
                            </a>
                        </div>
                    @else
                        <div class="p-6 space-y-4">
                            {{-- Infos profil --}}
                            <div class="flex items-center justify-between p-4 rounded-xl border border-gray-200 bg-gray-50">
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wide font-bold">Profil</p>
                                    <p class="font-bold text-gray-900">{{ $bd['profil']->nom }}</p>
                                </div>
                                @if ($bd['categorie'])
                                    <div class="text-right">
                                        <p class="text-xs text-gray-500 uppercase tracking-wide font-bold">Catégorie</p>
                                        <p class="font-semibold text-gray-700">{{ $bd['categorie']->libelle }}</p>
                                    </div>
                                @endif
                            </div>
                            @if ($bd['echelon'] ?? null)
                                <div class="flex items-center justify-between px-4 py-2.5 rounded-xl border" style="background:#f0f9ff;border-color:#bae6fd;">
                                    <span class="text-sm font-semibold text-sky-800">
                                        Éch. {{ $bd['echelon']->numero }} — {{ $bd['echelon']->libelle }}
                                    </span>
                                    @if ($bd['echelon']->anciennete_min !== null)
                                        <span class="text-xs text-sky-500">≥ {{ $bd['echelon']->anciennete_min }} an(s)</span>
                                    @endif
                                </div>
                            @endif

                            {{-- Salaire de base --}}
                            <div class="flex justify-between items-center px-4 py-3 rounded-xl bg-indigo-50 border border-indigo-100">
                                <span class="text-sm font-semibold text-indigo-800">Salaire de base</span>
                                <span class="font-bold text-indigo-700">{{ number_format($bd['base'], 0, ',', ' ') }} FCFA</span>
                            </div>

                            {{-- Indemnités --}}
                            @if (!empty($bd['indemnites']))
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-wide mb-2" style="color:#15803d;">+ Indemnités</p>
                                    <div class="space-y-1.5">
                                        @foreach ($bd['indemnites'] as $ind)
                                            <div class="flex justify-between items-center px-4 py-2.5 rounded-xl" style="background:#f0fdf4;border:1px solid #bbf7d0;">
                                                <div>
                                                    <span class="text-sm text-gray-800 font-medium">{{ $ind['libelle'] }}</span>
                                                    <span class="ml-2 text-xs text-gray-400">
                                                        ({{ $ind['type'] === 'fixe' ? 'fixe' : $ind['valeur'] . '%' }})
                                                    </span>
                                                </div>
                                                <span class="font-bold" style="color:#15803d;">+{{ number_format($ind['montant'], 0, ',', ' ') }}</span>
                                            </div>
                                        @endforeach
                                        <div class="flex justify-between px-4 py-2 text-xs font-bold text-right" style="color:#15803d;">
                                            <span>Total indemnités</span>
                                            <span>+{{ number_format($bd['total_ind'], 0, ',', ' ') }} FCFA</span>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Salaire brut --}}
                            <div class="flex justify-between items-center px-4 py-3 rounded-xl border border-gray-200 bg-gray-50">
                                <span class="text-sm font-semibold text-gray-700">Salaire brut</span>
                                <span class="font-bold text-gray-900">{{ number_format($bd['brut'], 0, ',', ' ') }} FCFA</span>
                            </div>

                            {{-- Retenues --}}
                            @if (!empty($bd['retenues']))
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-wide mb-2" style="color:#dc2626;">− Retenues</p>
                                    <div class="space-y-1.5">
                                        @foreach ($bd['retenues'] as $ret)
                                            <div class="flex justify-between items-center px-4 py-2.5 rounded-xl" style="background:#fff5f5;border:1px solid #fecaca;">
                                                <div>
                                                    <span class="text-sm text-gray-800 font-medium">{{ $ret['libelle'] }}</span>
                                                    <span class="ml-2 text-xs text-gray-400">
                                                        ({{ $ret['type'] === 'fixe' ? 'fixe' : $ret['valeur'] . '%' }})
                                                    </span>
                                                </div>
                                                <span class="font-bold" style="color:#dc2626;">-{{ number_format($ret['montant'], 0, ',', ' ') }}</span>
                                            </div>
                                        @endforeach
                                        <div class="flex justify-between px-4 py-2 text-xs font-bold text-right" style="color:#dc2626;">
                                            <span>Total retenues</span>
                                            <span>-{{ number_format($bd['total_ret'], 0, ',', ' ') }} FCFA</span>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Salaire net --}}
                            <div class="flex items-center justify-between p-5 rounded-2xl text-white" style="background:#059669;">
                                <div>
                                    <p class="text-xs font-semibold" style="color:#a7f3d0;">SALAIRE NET À PAYER</p>
                                    <p class="text-2xl font-bold mt-0.5">{{ number_format($bd['net'], 0, ',', ' ') }}</p>
                                    <p class="text-xs" style="color:#6ee7b7;">Francs CFA</p>
                                </div>
                                <svg class="w-10 h-10" style="color:#6ee7b7;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                    @endif

                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-2xl flex justify-end">
                        <button wire:click="closeModal" class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50">Fermer</button>
                    </div>
                @endif
            </div>
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
                            ['Nom',        $userElement->name ?? 'N/A'],
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