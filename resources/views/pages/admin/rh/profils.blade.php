<?php

use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\ProfilSalaire;
use App\Models\CategorieRh;
use App\Models\Indemnite;
use App\Models\Retenue;
use App\Models\Echelon;
use App\Models\User;

name('admin.rh.profils');
middleware(['auth', 'verified', 'role']);

new class extends Component {

    public string $view = 'list';

    public string $nom          = '';
    public string $description  = '';
    public        $categorie_id  = null;
    public        $echelon_id    = null;
    public bool   $actif        = true;
    public array  $indemnites_selectionnees = [];
    public array  $retenues_selectionnees   = [];
    public        $editProfilId = null;

    public bool   $showModalInd  = false;
    public bool   $showModalRet  = false;
    public        $modalIndId    = null;
    public string $modalIndType  = 'fixe';
    public        $modalIndValue = '';
    public        $modalRetId    = null;
    public string $modalRetType  = 'fixe';
    public        $modalRetValue = '';

    public        $affecterProfilId      = null;
    public array  $employes_selectionnes = [];

    public $profils          = [];
    public $categories       = [];
    public $echelons_dispo   = [];
    public $indemnites_dispo = [];
    public $retenues_dispo   = [];
    public $tous_employes    = [];

    public bool   $showDeleteModal     = false;
    public        $deleteId            = null;
    public bool   $showNotification    = false;
    public string $notificationMessage = '';
    public string $notificationType    = 'success';

    public function mount(): void { $this->loadData(); }

    private function loadData(): void
    {
        $this->profils = ProfilSalaire::with(['categorie', 'echelon', 'indemnites', 'retenues'])
            ->withCount('employes as nb_employes')->orderBy('nom')->get();
        $this->categories       = CategorieRh::where('actif', true)->orderBy('libelle')->get();
        $this->echelons_dispo   = collect();
        $this->indemnites_dispo = Indemnite::where('actif', true)->orderBy('libelle')->get();
        $this->retenues_dispo   = Retenue::where('actif', true)->orderBy('libelle')->get();
        $this->tous_employes    = User::whereNotIn('role', ['student', 'etudiant', 'admin'])
            ->where('status', 'Success')->orderBy('name')->get();
    }

    public function updatedCategorieId($value): void
    {
        $this->echelon_id    = null;
        $this->echelons_dispo = $value
            ? Echelon::where('categorie_rh_id', $value)->where('actif', true)->orderBy('numero')->get()
            : collect();
    }

    public function openCreate(): void
    {
        $this->nom         = '';
        $this->description = '';
        $this->categorie_id = null;
        $this->echelon_id   = null;
        $this->actif        = true;
        $this->editProfilId = null;
        $this->indemnites_selectionnees = [];
        $this->retenues_selectionnees   = [];
        $this->view = 'create';
    }

    public function openEdit(int $id): void
    {
        $profil = ProfilSalaire::with(['indemnites', 'retenues'])->find($id);
        if (!$profil) return;
        $this->nom          = $profil->nom;
        $this->description  = $profil->description ?? '';
        $this->categorie_id   = $profil->categorie_rh_id;
        $this->echelon_id     = $profil->echelon_id;
        $this->echelons_dispo = $profil->categorie_rh_id
            ? Echelon::where('categorie_rh_id', $profil->categorie_rh_id)->where('actif', true)->orderBy('numero')->get()
            : collect();
        $this->actif        = $profil->actif;
        $this->editProfilId = $id;
        $this->indemnites_selectionnees = $profil->indemnites->map(fn($i) => [
            'indemnite_id' => $i->id, 'libelle' => $i->libelle,
            'type_calcul'  => $i->pivot->type_calcul, 'value' => $i->pivot->value,
        ])->toArray();
        $this->retenues_selectionnees = $profil->retenues->map(fn($r) => [
            'retenue_id'  => $r->id, 'libelle' => $r->libelle,
            'type_calcul' => $r->pivot->type_calcul, 'value' => $r->pivot->value,
        ])->toArray();
        $this->view = 'create';
    }

    public function ajouterIndemnite(): void
    {
        if (!$this->modalIndId || $this->modalIndValue === '') return;
        $ind = $this->indemnites_dispo->firstWhere('id', $this->modalIndId);
        if (!$ind) return;
        foreach ($this->indemnites_selectionnees as $item) {
            if ($item['indemnite_id'] == $this->modalIndId) { $this->showModalInd = false; return; }
        }
        $this->indemnites_selectionnees[] = [
            'indemnite_id' => $this->modalIndId, 'libelle' => $ind->libelle,
            'type_calcul'  => $this->modalIndType, 'value'  => (float) $this->modalIndValue,
        ];
        $this->modalIndId = null; $this->modalIndType = 'fixe'; $this->modalIndValue = '';
        $this->showModalInd = false;
    }

    public function retirerIndemnite(int $index): void
    {
        array_splice($this->indemnites_selectionnees, $index, 1);
    }

    public function ajouterRetenue(): void
    {
        if (!$this->modalRetId || $this->modalRetValue === '') return;
        $ret = $this->retenues_dispo->firstWhere('id', $this->modalRetId);
        if (!$ret) return;
        foreach ($this->retenues_selectionnees as $item) {
            if ($item['retenue_id'] == $this->modalRetId) { $this->showModalRet = false; return; }
        }
        $this->retenues_selectionnees[] = [
            'retenue_id'  => $this->modalRetId, 'libelle' => $ret->libelle,
            'type_calcul' => $this->modalRetType, 'value'  => (float) $this->modalRetValue,
        ];
        $this->modalRetId = null; $this->modalRetType = 'fixe'; $this->modalRetValue = '';
        $this->showModalRet = false;
    }

    public function retirerRetenue(int $index): void
    {
        array_splice($this->retenues_selectionnees, $index, 1);
    }

    public function saveProfil(): void
    {
        $this->validate([
            'nom'          => 'required|string|max:255',
            'description'  => 'nullable|string',
            'categorie_id' => 'nullable|exists:categories_rh,id',
            'echelon_id'   => 'nullable|exists:echelons,id',
        ]);
        try {
            $data = [
                'nom'             => $this->nom,
                'description'     => $this->description ?: null,
                'categorie_rh_id' => $this->categorie_id ?: null,
                'echelon_id'      => $this->echelon_id ?: null,
                'actif'           => $this->actif,
            ];
            $profil = $this->editProfilId
                ? tap(ProfilSalaire::findOrFail($this->editProfilId), fn($p) => $p->update($data))
                : ProfilSalaire::create($data);

            $syncInd = [];
            foreach ($this->indemnites_selectionnees as $item) {
                $syncInd[$item['indemnite_id']] = ['type_calcul' => $item['type_calcul'], 'value' => $item['value']];
            }
            $profil->indemnites()->sync($syncInd);

            $syncRet = [];
            foreach ($this->retenues_selectionnees as $item) {
                $syncRet[$item['retenue_id']] = ['type_calcul' => $item['type_calcul'], 'value' => $item['value']];
            }
            $profil->retenues()->sync($syncRet);

            $this->view = 'list';
            $this->loadData();
            $this->notify($this->editProfilId ? 'Profil mis à jour !' : 'Profil créé avec succès !');
        } catch (\Exception $e) {
            logger('Erreur saveProfil: ' . $e->getMessage() . ' | ' . $e->getTraceAsString());
            $this->notify('Erreur : ' . $e->getMessage(), 'error');
        }
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId        = $id;
        $this->showDeleteModal = true;
    }

    public function deleteProfil(): void
    {
        try {
            $profil = ProfilSalaire::findOrFail($this->deleteId);
            if ($profil->employes()->count() > 0) {
                $this->notify('Impossible : des employés sont liés à ce profil.', 'error');
                $this->showDeleteModal = false;
                return;
            }
            $profil->delete();
            $this->showDeleteModal = false;
            $this->loadData();
            $this->notify('Profil supprimé.');
        } catch (\Exception $e) {
            $this->notify('Erreur.', 'error');
        }
    }

    public function openAffecter(int $id): void
    {
        $this->affecterProfilId      = $id;
        $this->employes_selectionnes = User::whereNotIn('role', ['student', 'etudiant', 'admin'])
            ->where('profil_salaire_id', $id)->where('status', 'Success')
            ->pluck('id')->map(fn($i) => (string)$i)->toArray();
        $this->view = 'affecter';
    }

    public function saveAffectation(): void
    {
        try {
            User::where('profil_salaire_id', $this->affecterProfilId)->update(['profil_salaire_id' => null]);
            if (!empty($this->employes_selectionnes)) {
                User::whereIn('id', $this->employes_selectionnes)
                    ->update(['profil_salaire_id' => $this->affecterProfilId]);
            }
            $this->view = 'list';
            $this->loadData();
            $this->notify(count($this->employes_selectionnes) . ' employé(s) affecté(s).');
        } catch (\Exception $e) {
            $this->notify('Erreur.', 'error');
        }
    }

    public function getSalaireBasePreview(): float
    {
        if ($this->echelon_id) {
            return (float)(Echelon::find($this->echelon_id)?->salaire ?? 0);
        }
        return 0;
    }

    public function getPreviewNet(): float
    {
        $base = $this->getSalaireBasePreview();
        $ind  = array_sum(array_map(fn($i) => $i['type_calcul'] === 'fixe' ? $i['value'] : round($base * $i['value'] / 100), $this->indemnites_selectionnees));
        $ret  = array_sum(array_map(fn($r) => $r['type_calcul'] === 'fixe' ? $r['value'] : round($base * $r['value'] / 100), $this->retenues_selectionnees));
        return $base + $ind - $ret;
    }

    private function notify(string $msg, string $type = 'success'): void
    {
        $this->notificationMessage = $msg;
        $this->notificationType    = $type;
        $this->showNotification    = true;
    }
};
?>

<x-layouts.app header="true">
@volt
<div class="min-h-screen bg-gray-50">

    {{-- ── Barre titre ── --}}
    <div class="bg-white border-b border-gray-200 px-6 py-5">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                @if ($view !== 'list')
                    <button wire:click="$set('view','list')" class="w-9 h-9 rounded-xl bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-500 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                @endif
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#0284c7;">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-gray-900 leading-tight">
                        @if ($view === 'list') Profils Salaires
                        @elseif ($view === 'create') {{ $editProfilId ? 'Modifier le profil' : 'Nouveau profil' }}
                        @else Affecter des employés
                        @endif
                    </h1>
                    <p class="text-xs text-gray-500">Profils de rémunération</p>
                </div>
            </div>

            {{-- Boutons toujours dans le DOM, visibilité via style --}}
                <button wire:click="openCreate"
                    style="{{ $view === 'list' ? 'background:#0284c7;' : 'display:none;' }}"
                    class="inline-flex items-center gap-2 px-5 py-2.5 text-white text-sm font-semibold rounded-xl shadow">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nouveau profil
                </button>
                <button wire:click="saveProfil"
                    style="{{ $view === 'create' ? 'background:#0284c7;' : 'display:none;' }}"
                    class="inline-flex items-center gap-2 px-5 py-2.5 text-white text-sm font-semibold rounded-xl shadow">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ $editProfilId ? 'Mettre à jour' : 'Créer le profil' }}
                </button>
                <button wire:click="saveAffectation"
                    style="{{ $view === 'affecter' ? 'background:#4f46e5;' : 'display:none;' }}"
                    class="inline-flex items-center gap-2 px-5 py-2.5 text-white text-sm font-semibold rounded-xl shadow">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Enregistrer l'affectation
                </button>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- ════════ VUE LISTE ════════ --}}
    @if ($view === 'list')

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5 flex items-center gap-4">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#e0f2fe;">
                    <svg class="w-5 h-5" style="color:#0284c7;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                </div>
                <div><p class="text-xs text-gray-500">Profils</p><p class="text-2xl font-bold text-gray-900">{{ $profils->count() }}</p></div>
            </div>
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5 flex items-center gap-4">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#dcfce7;">
                    <svg class="w-5 h-5" style="color:#16a34a;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div><p class="text-xs text-gray-500">Actifs</p><p class="text-2xl font-bold text-gray-900">{{ $profils->where('actif', true)->count() }}</p></div>
            </div>
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5 flex items-center gap-4">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#e0e7ff;">
                    <svg class="w-5 h-5" style="color:#4f46e5;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div><p class="text-xs text-gray-500">Employés affectés</p><p class="text-2xl font-bold text-gray-900">{{ $profils->sum('nb_employes') }}</p></div>
            </div>
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5 flex items-center gap-4">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#fef3c7;">
                    <svg class="w-5 h-5" style="color:#d97706;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div><p class="text-xs text-gray-500">Sans profil</p><p class="text-2xl font-bold text-gray-900">{{ $tous_employes->whereNull('profil_salaire_id')->count() }}</p></div>
            </div>
        </div>

        @if ($profils->isEmpty())
            <div class="bg-white rounded-2xl border border-dashed border-gray-300 p-16 text-center">
                <p class="text-base font-semibold text-gray-700 mb-5">Aucun profil créé</p>
                <button wire:click="openCreate" class="inline-flex items-center gap-2 px-5 py-2.5 text-white text-sm font-semibold rounded-xl shadow" style="background:#0284c7;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Créer un profil
                </button>
            </div>
        @else
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                    <div class="w-1.5 h-5 rounded-full" style="background:#0284c7;"></div>
                    <span class="text-sm font-bold text-gray-700 uppercase tracking-wider">Tous les profils</span>
                </div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs font-bold uppercase tracking-wider text-white" style="background:#111827;">
                            <th class="px-6 py-4">Profil</th>
                            <th class="px-6 py-4">Catégorie</th>
                            <th class="px-6 py-4 text-right">Salaire net</th>
                            <th class="px-6 py-4 text-center">Ind. / Ret.</th>
                            <th class="px-6 py-4 text-center">Employés</th>
                            <th class="px-6 py-4 text-center">Statut</th>
                            <th class="px-6 py-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($profils as $i => $profil)
                            @php
                                $base = $profil->echelon ? (float)$profil->echelon->salaire : 0;
                                $ind  = $profil->indemnites->sum(fn($x) => $x->pivot->type_calcul === 'fixe' ? $x->pivot->value : round($base * $x->pivot->value / 100));
                                $ret  = $profil->retenues->sum(fn($x)  => $x->pivot->type_calcul === 'fixe' ? $x->pivot->value : round($base * $x->pivot->value / 100));
                                $net  = $base + $ind - $ret;
                            @endphp
                            <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} border-t border-gray-100 hover:bg-sky-50 transition-colors">
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-gray-900">{{ $profil->nom }}</p>
                                    @if ($profil->description)
                                        <p class="text-xs text-gray-400 truncate max-w-xs">{{ $profil->description }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if ($profil->categorie)
                                        <span class="text-xs font-semibold px-2 py-1 rounded-lg" style="background:#e0f2fe;color:#0369a1;">{{ $profil->categorie->libelle }}</span>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right font-bold" style="color:#059669;">
                                    {{ number_format($net, 0, ',', ' ') }} <span class="text-xs text-gray-400 font-normal">FCFA</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        @if ($profil->indemnites->count() > 0)
                                            <span class="text-xs font-bold px-1.5 py-0.5 rounded" style="background:#dcfce7;color:#15803d;">+{{ $profil->indemnites->count() }}</span>
                                        @endif
                                        @if ($profil->retenues->count() > 0)
                                            <span class="text-xs font-bold px-1.5 py-0.5 rounded" style="background:#fee2e2;color:#dc2626;">-{{ $profil->retenues->count() }}</span>
                                        @endif
                                        @if ($profil->indemnites->count() === 0 && $profil->retenues->count() === 0)
                                            <span class="text-xs text-gray-400">—</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold {{ $profil->nb_employes > 0 ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-400' }}">
                                        {{ $profil->nb_employes }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if ($profil->actif)
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full" style="background:#dcfce7;color:#15803d;">
                                            <span class="w-1.5 h-1.5 rounded-full" style="background:#22c55e;"></span> Actif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full bg-gray-100 text-gray-500">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Inactif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button wire:click="openAffecter({{ $profil->id }})"
                                            class="text-xs font-semibold px-3 py-1.5 rounded-lg transition" style="background:#e0e7ff;color:#4338ca;">
                                            Affecter
                                        </button>
                                        <button wire:click="openEdit({{ $profil->id }})"
                                            class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-sky-100 flex items-center justify-center text-gray-500 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        @if ($profil->nb_employes === 0)
                                            <button wire:click="confirmDelete({{ $profil->id }})"
                                                class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-red-100 flex items-center justify-center text-gray-500 hover:text-red-600 transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @endif

    {{-- ════════ VUE CRÉATION / ÉDITION ════════ --}}
    @if ($view === 'create')
        <div class="max-w-5xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

                {{-- Formulaire (3/5) --}}
                <div class="lg:col-span-3 space-y-5">

                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                            <div class="w-1.5 h-5 rounded-full" style="background:#0284c7;"></div>
                            <span class="text-sm font-bold text-gray-700 uppercase tracking-wider">Informations générales</span>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">Nom du profil *</label>
                                <input wire:model.live="nom" type="text"
                                    class="w-full text-sm border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-sky-400 transition"
                                    placeholder="Ex: Profil Cadre A"/>
                                @error('nom')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">Description</label>
                                <textarea wire:model="description" rows="2"
                                    class="w-full text-sm border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-sky-400 transition resize-none"
                                    placeholder="Description optionnelle…"></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">Catégorie (salaire de base)</label>
                                <select wire:model.live="categorie_id"
                                    class="w-full text-sm border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-sky-400 transition bg-white">
                                    <option value="">— Aucune —</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->libelle }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">Échelon (salaire de base)</label>
                                <select wire:model.live="echelon_id"
                                    class="w-full text-sm border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-sky-400 transition bg-white {{ !$categorie_id ? 'opacity-50 cursor-not-allowed' : '' }}"
                                    {{ !$categorie_id ? 'disabled' : '' }}>
                                    <option value="">— Choisir un échelon —</option>
                                    @foreach ($echelons_dispo as $ech)
                                        <option value="{{ $ech->id }}">Éch. {{ $ech->numero }} — {{ $ech->libelle }} — {{ number_format($ech->salaire, 0, ',', ' ') }} FCFA</option>
                                    @endforeach
                                </select>
                                @if (!$categorie_id)
                                    <p class="text-xs text-gray-400 mt-1">Sélectionnez d'abord une catégorie</p>
                                @endif
                            </div>
                            @if ($editProfilId)
                                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl border border-gray-200">
                                    <input wire:model="actif" type="checkbox" id="profil_actif" class="w-4 h-4 rounded"/>
                                    <label for="profil_actif" class="text-sm font-medium text-gray-700 cursor-pointer">Profil actif</label>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Indemnités --}}
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-1.5 h-5 rounded-full" style="background:#16a34a;"></div>
                                <span class="text-sm font-bold text-gray-700 uppercase tracking-wider">Indemnités</span>
                                @if (count($indemnites_selectionnees) > 0)
                                    <span class="text-xs font-bold px-2 py-0.5 rounded-full" style="background:#dcfce7;color:#15803d;">{{ count($indemnites_selectionnees) }}</span>
                                @endif
                            </div>
                            <button wire:click="$set('showModalInd', true)" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-white text-xs font-semibold rounded-lg" style="background:#16a34a;">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Ajouter
                            </button>
                        </div>
                        <div class="p-5">
                            @if (empty($indemnites_selectionnees))
                                <div class="py-6 text-center text-sm text-gray-400 border-2 border-dashed border-gray-200 rounded-xl">Aucune indemnité</div>
                            @else
                                <div class="space-y-2">
                                    @foreach ($indemnites_selectionnees as $idx => $item)
                                        <div class="flex items-center justify-between px-4 py-3 rounded-xl border" style="background:#f0fdf4;border-color:#86efac;">
                                            <div>
                                                <p class="text-sm font-semibold text-gray-800">{{ $item['libelle'] }}</p>
                                                <p class="text-xs mt-0.5" style="color:#15803d;">
                                                    {{ $item['type_calcul'] === 'fixe' ? number_format($item['value'], 0, ',', ' ') . ' FCFA (fixe)' : $item['value'] . '% du salaire de base' }}
                                                </p>
                                            </div>
                                            <button wire:click="retirerIndemnite({{ $idx }})" class="w-7 h-7 rounded-lg flex items-center justify-center transition" style="background:#fee2e2;color:#dc2626;">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Retenues --}}
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-1.5 h-5 rounded-full" style="background:#dc2626;"></div>
                                <span class="text-sm font-bold text-gray-700 uppercase tracking-wider">Retenues</span>
                                @if (count($retenues_selectionnees) > 0)
                                    <span class="text-xs font-bold px-2 py-0.5 rounded-full" style="background:#fee2e2;color:#dc2626;">{{ count($retenues_selectionnees) }}</span>
                                @endif
                            </div>
                            <button wire:click="$set('showModalRet', true)" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-white text-xs font-semibold rounded-lg" style="background:#dc2626;">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Ajouter
                            </button>
                        </div>
                        <div class="p-5">
                            @if (empty($retenues_selectionnees))
                                <div class="py-6 text-center text-sm text-gray-400 border-2 border-dashed border-gray-200 rounded-xl">Aucune retenue</div>
                            @else
                                <div class="space-y-2">
                                    @foreach ($retenues_selectionnees as $idx => $item)
                                        <div class="flex items-center justify-between px-4 py-3 rounded-xl border" style="background:#fff5f5;border-color:#fca5a5;">
                                            <div>
                                                <p class="text-sm font-semibold text-gray-800">{{ $item['libelle'] }}</p>
                                                <p class="text-xs mt-0.5" style="color:#dc2626;">
                                                    {{ $item['type_calcul'] === 'fixe' ? number_format($item['value'], 0, ',', ' ') . ' FCFA (fixe)' : $item['value'] . '% du salaire de base' }}
                                                </p>
                                            </div>
                                            <button wire:click="retirerRetenue({{ $idx }})" class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:#fee2e2;color:#dc2626;">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Aperçu (2/5) --}}
                <div class="lg:col-span-2">
                    <div class="sticky top-24 bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                            <div class="w-1.5 h-5 rounded-full" style="background:#059669;"></div>
                            <span class="text-sm font-bold text-gray-700 uppercase tracking-wider">Aperçu bulletin</span>
                        </div>
                        <div class="p-5 space-y-4">
                            <div>
                                <p class="font-bold text-gray-900">{{ $nom ?: 'Nom du profil' }}</p>
                                @if ($categorie_id)
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $categories->firstWhere('id', $categorie_id)?->libelle }}</p>
                                @endif
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-xl text-sm border border-gray-200">
                                <span class="text-gray-600 font-medium">Salaire de base</span>
                                <span class="font-bold" style="color:#4f46e5;">{{ number_format($this->getSalaireBasePreview(), 0, ',', ' ') }} FCFA</span>
                            </div>
                            @if (!empty($indemnites_selectionnees))
                                <div class="space-y-1.5">
                                    <p class="text-xs font-bold uppercase tracking-wide" style="color:#16a34a;">Indemnités</p>
                                    @foreach ($indemnites_selectionnees as $item)
                                        @php $base = $this->getSalaireBasePreview(); $m = $item['type_calcul'] === 'fixe' ? $item['value'] : round($base * $item['value'] / 100); @endphp
                                        <div class="flex justify-between text-xs py-1.5 px-3 rounded-lg" style="background:#f0fdf4;">
                                            <span class="text-gray-700">{{ $item['libelle'] }}</span>
                                            <span class="font-bold" style="color:#16a34a;">+{{ number_format($m, 0, ',', ' ') }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            @if (!empty($retenues_selectionnees))
                                <div class="space-y-1.5">
                                    <p class="text-xs font-bold uppercase tracking-wide" style="color:#dc2626;">Retenues</p>
                                    @foreach ($retenues_selectionnees as $item)
                                        @php $base = $this->getSalaireBasePreview(); $m = $item['type_calcul'] === 'fixe' ? $item['value'] : round($base * $item['value'] / 100); @endphp
                                        <div class="flex justify-between text-xs py-1.5 px-3 rounded-lg" style="background:#fff5f5;">
                                            <span class="text-gray-700">{{ $item['libelle'] }}</span>
                                            <span class="font-bold" style="color:#dc2626;">-{{ number_format($m, 0, ',', ' ') }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            <div class="pt-3 border-t border-gray-100">
                                <div class="flex items-center justify-between p-4 rounded-xl text-white" style="background:#059669;">
                                    <div>
                                        <p class="text-xs font-semibold" style="color:#a7f3d0;">Salaire Net</p>
                                        <p class="text-xl font-bold mt-0.5">{{ number_format($this->getPreviewNet(), 0, ',', ' ') }}</p>
                                        <p class="text-xs" style="color:#6ee7b7;">Francs CFA</p>
                                    </div>
                                    <svg class="w-8 h-8" style="color:#6ee7b7;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ════════ VUE AFFECTER ════════ --}}
    @if ($view === 'affecter')
        @php $profilCourant = $profils->firstWhere('id', $affecterProfilId); @endphp
        <div class="max-w-3xl mx-auto space-y-5">
            <div class="border rounded-2xl px-5 py-4 flex items-center gap-4" style="background:#eef2ff;border-color:#c7d2fe;">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#c7d2fe;">
                    <svg class="w-5 h-5" style="color:#4338ca;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-bold" style="color:#3730a3;">{{ $profilCourant?->nom }}</p>
                    <p class="text-xs mt-0.5" style="color:#4338ca;">Cochez les employés à affecter</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-1.5 h-5 rounded-full" style="background:#4f46e5;"></div>
                        <span class="text-sm font-bold text-gray-700 uppercase tracking-wider">Employés</span>
                        <span class="text-xs font-bold px-2 py-0.5 rounded-full" style="background:#e0e7ff;color:#4338ca;">{{ $tous_employes->count() }}</span>
                    </div>
                    <span class="text-xs text-gray-500">{{ count($employes_selectionnes) }} sélectionné(s)</span>
                </div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs font-bold uppercase tracking-wider text-white" style="background:#111827;">
                            <th class="px-6 py-4 text-center w-12">✓</th>
                            <th class="px-6 py-4">Employé</th>
                            <th class="px-6 py-4 text-center">Matricule</th>
                            <th class="px-6 py-4 text-center">Profil actuel</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($tous_employes as $i => $emp)
                            @php $isSelected = in_array((string)$emp->id, $employes_selectionnes); @endphp
                            <tr class="{{ $isSelected ? '' : ($i % 2 === 0 ? 'bg-white' : 'bg-gray-50') }} hover:bg-indigo-50 transition-colors" style="{{ $isSelected ? 'background:#eef2ff;' : '' }}">
                                <td class="px-6 py-4 text-center">
                                    <input type="checkbox" wire:model="employes_selectionnes" value="{{ $emp->id }}" class="w-4 h-4 rounded border-gray-300"/>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0" style="{{ $isSelected ? 'background:#4f46e5;color:white;' : 'background:#f3f4f6;color:#6b7280;' }}">
                                            {{ strtoupper(substr($emp->name, 0, 1)) }}
                                        </div>
                                        <p class="font-semibold text-gray-800">{{ strtoupper($emp->name) }} {{ $emp->lastname }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="font-mono text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-md">{{ $emp->matricule ?? '—' }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if ($emp->profil_salaire_id == $affecterProfilId)
                                        <span class="text-xs font-semibold px-2 py-1 rounded-md" style="background:#dcfce7;color:#15803d;">Ce profil</span>
                                    @elseif ($emp->profil_salaire_id)
                                        <span class="text-xs font-semibold px-2 py-1 rounded-md" style="background:#fef9c3;color:#92400e;">Autre profil</span>
                                    @else
                                        <span class="text-xs text-gray-400">Sans profil</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    </div>

    {{-- ════════ MODAL INDEMNITÉ ════════ --}}
    <div class="fixed inset-0 z-50 overflow-y-auto" style="{{ $showModalInd ? 'background:rgba(0,0,0,0.55);' : 'display:none;' }}">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
                <div class="px-6 py-5 rounded-t-2xl flex items-center justify-between" style="background:#16a34a;">
                    <h3 class="font-bold text-white">Ajouter une indemnité</h3>
                    <button wire:click="$set('showModalInd',false)" class="text-white opacity-80 hover:opacity-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">Indemnité *</label>
                        <select wire:model="modalIndId" class="w-full text-sm border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-400 bg-white">
                            <option value="">— Choisir —</option>
                            @foreach ($indemnites_dispo as $ind)
                                <option value="{{ $ind->id }}">{{ $ind->libelle }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">Type de calcul</label>
                        <select wire:model.live="modalIndType" class="w-full text-sm border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-400 bg-white">
                            <option value="fixe">Montant fixe (FCFA)</option>
                            <option value="pourcentage">Pourcentage du salaire de base (%)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">
                            {{ $modalIndType === 'pourcentage' ? 'Pourcentage (%)' : 'Montant (FCFA)' }} *
                        </label>
                        <input wire:model="modalIndValue" type="number" min="0" step="0.01"
                            class="w-full text-sm border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-400"
                            placeholder="{{ $modalIndType === 'pourcentage' ? 'Ex: 15' : 'Ex: 50000' }}"/>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-2xl flex justify-end gap-3">
                    <button wire:click="$set('showModalInd',false)" class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50">Annuler</button>
                    <button wire:click="ajouterIndemnite" class="px-6 py-2.5 text-sm font-semibold text-white rounded-xl" style="background:#16a34a;">Ajouter</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════ MODAL RETENUE ════════ --}}
    <div class="fixed inset-0 z-50 overflow-y-auto" style="{{ $showModalRet ? 'background:rgba(0,0,0,0.55);' : 'display:none;' }}">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
                <div class="px-6 py-5 rounded-t-2xl flex items-center justify-between" style="background:#dc2626;">
                    <h3 class="font-bold text-white">Ajouter une retenue</h3>
                    <button wire:click="$set('showModalRet',false)" class="text-white opacity-80 hover:opacity-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">Retenue *</label>
                        <select wire:model="modalRetId" class="w-full text-sm border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-red-400 bg-white">
                            <option value="">— Choisir —</option>
                            @foreach ($retenues_dispo as $ret)
                                <option value="{{ $ret->id }}">{{ $ret->libelle }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">Type de calcul</label>
                        <select wire:model.live="modalRetType" class="w-full text-sm border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-red-400 bg-white">
                            <option value="fixe">Montant fixe (FCFA)</option>
                            <option value="pourcentage">Pourcentage du salaire de base (%)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">
                            {{ $modalRetType === 'pourcentage' ? 'Pourcentage (%)' : 'Montant (FCFA)' }} *
                        </label>
                        <input wire:model="modalRetValue" type="number" min="0" step="0.01"
                            class="w-full text-sm border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-red-400"
                            placeholder="{{ $modalRetType === 'pourcentage' ? 'Ex: 8' : 'Ex: 20000' }}"/>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-2xl flex justify-end gap-3">
                    <button wire:click="$set('showModalRet',false)" class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50">Annuler</button>
                    <button wire:click="ajouterRetenue" class="px-6 py-2.5 text-sm font-semibold text-white rounded-xl" style="background:#dc2626;">Ajouter</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════ MODAL SUPPRESSION ════════ --}}
    <div class="fixed inset-0 z-50 overflow-y-auto" style="{{ $showDeleteModal ? 'background:rgba(0,0,0,0.55);' : 'display:none;' }}">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center">
                <div class="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-4" style="background:#fee2e2;">
                    <svg class="w-7 h-7" style="color:#dc2626;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">Supprimer ce profil ?</h3>
                <p class="text-sm text-gray-500 mb-6">Cette action est irréversible.</p>
                <div class="flex gap-3">
                    <button wire:click="$set('showDeleteModal',false)" class="flex-1 py-2.5 text-sm font-semibold text-gray-700 border border-gray-300 rounded-xl hover:bg-gray-50">Annuler</button>
                    <button wire:click="deleteProfil" class="flex-1 py-2.5 text-sm font-semibold text-white rounded-xl" style="background:#dc2626;">Supprimer</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Toast ── --}}
    @if ($showNotification)
        <div class="fixed bottom-6 right-6 z-50"
             x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => { show = false; $wire.set('showNotification', false) }, 3500)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-end="opacity-0">
            <div class="flex items-center gap-3 px-5 py-4 rounded-2xl shadow-2xl text-sm font-medium" style="{{ $notificationType === 'success' ? 'background:#111827;color:white;' : 'background:#dc2626;color:white;' }}">
                <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0" style="{{ $notificationType === 'success' ? 'background:#22c55e;' : 'background:#f87171;' }}">
                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                </div>
                {{ $notificationMessage }}
            </div>
        </div>
    @endif

</div>
@endvolt
</x-layouts.app>