<?php

use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\CategorieRh;
use App\Models\Indemnite;
use App\Models\Retenue;

name('admin.rh.index');
middleware(['auth', 'verified', 'role']);

new class extends Component {

    public string $tab         = 'categories';
    public string $libelle     = '';
    public string $description = '';
    public string $salaire_base = '';
    public bool   $actif       = true;
    public ?int   $editId      = null;
    public string $editType    = '';

    public bool   $showModal           = false;
    public bool   $showDeleteModal     = false;
    public ?int   $deleteId            = null;
    public string $deleteType          = '';
    public bool   $showNotification    = false;
    public string $notificationMessage = '';
    public string $notificationType    = 'success';

    public $categories;
    public $indemnites;
    public $retenues;

    public function mount(): void
    {
        $this->categories = collect();
        $this->indemnites = collect();
        $this->retenues   = collect();
        $this->charger();
    }

    public function charger(): void
    {
        $this->categories = CategorieRh::withCount('employes as nb_employes')->orderBy('libelle')->get();
        $this->indemnites = Indemnite::orderBy('libelle')->get();
        $this->retenues   = Retenue::orderBy('libelle')->get();
    }

    public function ouvrir(string $type, int $id = 0): void
    {
        $this->libelle      = '';
        $this->description  = '';
        $this->salaire_base = '';
        $this->actif        = true;
        $this->editId       = $id > 0 ? $id : null;
        $this->editType     = $type;

        if ($id > 0) {
            $model = match($type) {
                'categorie' => CategorieRh::find($id),
                'indemnite' => Indemnite::find($id),
                'retenue'   => Retenue::find($id),
                default     => null,
            };
            if ($model) {
                $this->libelle      = $model->libelle;
                $this->description  = $model->description ?? '';
                $this->actif        = $model->actif;
                if ($type === 'categorie') {
                    $this->salaire_base = (string) $model->salaire_base;
                }
            }
        }

        $this->showModal = true;
    }

    public function sauvegarder(): void
    {
        $rules = [
            'libelle'     => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ];
        if ($this->editType === 'categorie') {
            $rules['salaire_base'] = 'required|numeric|min:0';
        }
        $this->validate($rules);

        try {
            if ($this->editType === 'categorie') {
                $data = [
                    'libelle'      => $this->libelle,
                    'description'  => $this->description ?: null,
                    'salaire_base' => $this->salaire_base,
                    'actif'        => $this->actif,
                ];
                $this->editId
                    ? CategorieRh::findOrFail($this->editId)->update($data)
                    : CategorieRh::create($data);

            } elseif ($this->editType === 'indemnite') {
                $data = [
                    'libelle'     => $this->libelle,
                    'description' => $this->description ?: null,
                    'actif'       => $this->actif,
                ];
                $this->editId
                    ? Indemnite::findOrFail($this->editId)->update($data)
                    : Indemnite::create($data);

            } elseif ($this->editType === 'retenue') {
                $data = [
                    'libelle'     => $this->libelle,
                    'description' => $this->description ?: null,
                    'actif'       => $this->actif,
                ];
                $this->editId
                    ? Retenue::findOrFail($this->editId)->update($data)
                    : Retenue::create($data);
            }

            $this->showModal = false;
            $this->charger();
            $this->toast($this->editId ? 'Mis à jour !' : 'Créé avec succès !');

        } catch (\Exception $e) {
            logger('RH save error: ' . $e->getMessage());
            $this->toast('Erreur lors de l\'enregistrement.', 'error');
        }
    }

    public function confirmerSuppression(int $id, string $type): void
    {
        $this->deleteId   = $id;
        $this->deleteType = $type;
        $this->showDeleteModal = true;
    }

    public function supprimer(): void
    {
        try {
            match($this->deleteType) {
                'categorie' => CategorieRh::findOrFail($this->deleteId)->delete(),
                'indemnite' => Indemnite::findOrFail($this->deleteId)->delete(),
                'retenue'   => Retenue::findOrFail($this->deleteId)->delete(),
            };
            $this->showDeleteModal = false;
            $this->charger();
            $this->toast('Supprimé avec succès !');
        } catch (\Exception $e) {
            $this->toast('Erreur lors de la suppression.', 'error');
        }
    }

    public function basculerActif(int $id, string $type): void
    {
        $model = match($type) {
            'categorie' => CategorieRh::find($id),
            'indemnite' => Indemnite::find($id),
            'retenue'   => Retenue::find($id),
            default     => null,
        };
        if ($model) {
            $model->update(['actif' => !$model->actif]);
            $this->charger();
        }
    }

    private function toast(string $msg, string $type = 'success'): void
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
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#059669;">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-gray-900">Ressources Humaines</h1>
                    <p class="text-xs text-gray-500">Catégories · Indemnités · Retenues</p>
                </div>
            </div>

            {{-- Bouton contextuel --}}
            @if ($tab === 'categories')
                <button wire:click="ouvrir('categorie')"
                    class="inline-flex items-center gap-2 px-4 py-2 text-white text-sm font-semibold rounded-xl shadow"
                    style="background:#059669;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nouvelle catégorie
                </button>
            @elseif ($tab === 'indemnites')
                <button wire:click="ouvrir('indemnite')"
                    class="inline-flex items-center gap-2 px-4 py-2 text-white text-sm font-semibold rounded-xl shadow"
                    style="background:#16a34a;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nouvelle indemnité
                </button>
            @else
                <button wire:click="ouvrir('retenue')"
                    class="inline-flex items-center gap-2 px-4 py-2 text-white text-sm font-semibold rounded-xl shadow"
                    style="background:#dc2626;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nouvelle retenue
                </button>
            @endif
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        {{-- ── Onglets ── --}}
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-1.5 flex gap-1">

            @if ($tab === 'categories')
                <button wire:click="$set('tab','categories')" class="flex-1 flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl text-sm font-semibold text-white shadow-sm" style="background:#059669;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
                    Catégories <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-white" style="color:#059669;">{{ $categories->count() }}</span>
                </button>
            @else
                <button wire:click="$set('tab','categories')" class="flex-1 flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl text-sm font-semibold text-gray-600 hover:bg-gray-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
                    Catégories <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">{{ $categories->count() }}</span>
                </button>
            @endif

            @if ($tab === 'indemnites')
                <button wire:click="$set('tab','indemnites')" class="flex-1 flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl text-sm font-semibold text-white shadow-sm" style="background:#16a34a;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Indemnités <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-white" style="color:#16a34a;">{{ $indemnites->count() }}</span>
                </button>
            @else
                <button wire:click="$set('tab','indemnites')" class="flex-1 flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl text-sm font-semibold text-gray-600 hover:bg-gray-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Indemnités <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">{{ $indemnites->count() }}</span>
                </button>
            @endif

            @if ($tab === 'retenues')
                <button wire:click="$set('tab','retenues')" class="flex-1 flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl text-sm font-semibold text-white shadow-sm" style="background:#dc2626;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                    Retenues <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-white" style="color:#dc2626;">{{ $retenues->count() }}</span>
                </button>
            @else
                <button wire:click="$set('tab','retenues')" class="flex-1 flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl text-sm font-semibold text-gray-600 hover:bg-gray-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                    Retenues <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">{{ $retenues->count() }}</span>
                </button>
            @endif
        </div>

        {{-- ── Table ── --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">

            {{-- Header section --}}
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                @if ($tab === 'categories')
                    <div class="w-1.5 h-5 rounded-full" style="background:#059669;"></div>
                    <span class="text-sm font-bold text-gray-700 uppercase tracking-wider">Catégories professionnelles</span>
                @elseif ($tab === 'indemnites')
                    <div class="w-1.5 h-5 rounded-full" style="background:#16a34a;"></div>
                    <span class="text-sm font-bold text-gray-700 uppercase tracking-wider">Indemnités</span>
                @else
                    <div class="w-1.5 h-5 rounded-full" style="background:#dc2626;"></div>
                    <span class="text-sm font-bold text-gray-700 uppercase tracking-wider">Retenues</span>
                @endif
            </div>

            {{-- ── CATÉGORIES ── --}}
            @if ($tab === 'categories')
                @if ($categories->isEmpty())
                    <div class="py-16 text-center">
                        <p class="text-gray-500 font-medium mb-3">Aucune catégorie</p>
                        <button wire:click="ouvrir('categorie')" class="text-sm font-semibold underline" style="color:#059669;">+ Créer la première</button>
                    </div>
                @else
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs font-bold uppercase tracking-wider text-white" style="background:#111827;">
                                <th class="px-6 py-4">Libellé</th>
                                <th class="px-6 py-4">Description</th>
                                <th class="px-6 py-4 text-right">Salaire de base</th>
                                <th class="px-6 py-4 text-center">Employés</th>
                                <th class="px-6 py-4 text-center">Statut</th>
                                <th class="px-6 py-4 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($categories as $i => $cat)
                                <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} border-t border-gray-100 hover:bg-green-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background:#d1fae5;">
                                                <svg class="w-4 h-4" style="color:#059669;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/>
                                                </svg>
                                            </div>
                                            <span class="font-semibold text-gray-900">{{ $cat->libelle }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-500 max-w-xs truncate">{{ $cat->description ?: '—' }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="font-bold" style="color:#059669;">{{ number_format($cat->salaire_base, 0, ',', ' ') }}</span>
                                        <span class="text-xs text-gray-400 ml-1">FCFA</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold
                                            {{ $cat->nb_employes > 0 ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-400' }}">
                                            {{ $cat->nb_employes }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if ($cat->actif)
                                            <button wire:click="basculerActif({{ $cat->id }}, 'categorie')"
                                                class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full"
                                                style="background:#dcfce7;color:#15803d;">
                                                <span class="w-1.5 h-1.5 rounded-full" style="background:#22c55e;"></span> Actif
                                            </button>
                                        @else
                                            <button wire:click="basculerActif({{ $cat->id }}, 'categorie')"
                                                class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full bg-gray-100 text-gray-500">
                                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Inactif
                                            </button>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button wire:click="ouvrir('categorie', {{ $cat->id }})"
                                                class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-indigo-100 flex items-center justify-center text-gray-500 hover:text-indigo-700 transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>
                                            @if ($cat->nb_employes === 0)
                                                <button wire:click="confirmerSuppression({{ $cat->id }}, 'categorie')"
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
                @endif
            @endif

            {{-- ── INDEMNITÉS ── --}}
            @if ($tab === 'indemnites')
                @if ($indemnites->isEmpty())
                    <div class="py-16 text-center">
                        <p class="text-gray-500 font-medium mb-3">Aucune indemnité</p>
                        <button wire:click="ouvrir('indemnite')" class="text-sm font-semibold underline" style="color:#16a34a;">+ Créer la première</button>
                    </div>
                @else
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs font-bold uppercase tracking-wider text-white" style="background:#111827;">
                                <th class="px-6 py-4">Libellé</th>
                                <th class="px-6 py-4">Description</th>
                                <th class="px-6 py-4 text-center">Statut</th>
                                <th class="px-6 py-4 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($indemnites as $i => $ind)
                                <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} border-t border-gray-100 hover:bg-green-50 transition-colors">
                                    <td class="px-6 py-4 font-semibold text-gray-900">
                                        <span class="inline-flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full" style="background:#4ade80;"></span>
                                            {{ $ind->libelle }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-500">{{ $ind->description ?: '—' }}</td>
                                    <td class="px-6 py-4 text-center">
                                        @if ($ind->actif)
                                            <button wire:click="basculerActif({{ $ind->id }}, 'indemnite')"
                                                class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full"
                                                style="background:#dcfce7;color:#15803d;">
                                                <span class="w-1.5 h-1.5 rounded-full" style="background:#22c55e;"></span> Actif
                                            </button>
                                        @else
                                            <button wire:click="basculerActif({{ $ind->id }}, 'indemnite')"
                                                class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full bg-gray-100 text-gray-500">
                                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Inactif
                                            </button>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button wire:click="ouvrir('indemnite', {{ $ind->id }})"
                                                class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-indigo-100 flex items-center justify-center text-gray-500 hover:text-indigo-700 transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>
                                            <button wire:click="confirmerSuppression({{ $ind->id }}, 'indemnite')"
                                                class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-red-100 flex items-center justify-center text-gray-500 hover:text-red-600 transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            @endif

            {{-- ── RETENUES ── --}}
            @if ($tab === 'retenues')
                @if ($retenues->isEmpty())
                    <div class="py-16 text-center">
                        <p class="text-gray-500 font-medium mb-3">Aucune retenue</p>
                        <button wire:click="ouvrir('retenue')" class="text-sm font-semibold underline" style="color:#dc2626;">+ Créer la première</button>
                    </div>
                @else
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs font-bold uppercase tracking-wider text-white" style="background:#111827;">
                                <th class="px-6 py-4">Libellé</th>
                                <th class="px-6 py-4">Description</th>
                                <th class="px-6 py-4 text-center">Statut</th>
                                <th class="px-6 py-4 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($retenues as $i => $ret)
                                <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} border-t border-gray-100 hover:bg-red-50 transition-colors">
                                    <td class="px-6 py-4 font-semibold text-gray-900">
                                        <span class="inline-flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full" style="background:#f87171;"></span>
                                            {{ $ret->libelle }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-500">{{ $ret->description ?: '—' }}</td>
                                    <td class="px-6 py-4 text-center">
                                        @if ($ret->actif)
                                            <button wire:click="basculerActif({{ $ret->id }}, 'retenue')"
                                                class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full"
                                                style="background:#dcfce7;color:#15803d;">
                                                <span class="w-1.5 h-1.5 rounded-full" style="background:#22c55e;"></span> Actif
                                            </button>
                                        @else
                                            <button wire:click="basculerActif({{ $ret->id }}, 'retenue')"
                                                class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full bg-gray-100 text-gray-500">
                                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Inactif
                                            </button>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button wire:click="ouvrir('retenue', {{ $ret->id }})"
                                                class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-indigo-100 flex items-center justify-center text-gray-500 hover:text-indigo-700 transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>
                                            <button wire:click="confirmerSuppression({{ $ret->id }}, 'retenue')"
                                                class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-red-100 flex items-center justify-center text-gray-500 hover:text-red-600 transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            @endif
        </div>
    </div>

    {{-- ═══ MODAL ═══ --}}
        <div class="fixed inset-0 z-50 overflow-y-auto" style="{{ $showModal ? 'background:rgba(0,0,0,0.6);' : 'display:none;' }}">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">

                    {{-- Header --}}
                    @if ($editType === 'categorie')
                        <div class="px-6 py-5 rounded-t-2xl flex items-center justify-between" style="background:#059669;">
                            <h3 class="font-bold text-white">{{ $editId ? 'Modifier la catégorie' : 'Nouvelle catégorie' }}</h3>
                            <button wire:click="$set('showModal', false)" class="text-white opacity-80 hover:opacity-100">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    @elseif ($editType === 'indemnite')
                        <div class="px-6 py-5 rounded-t-2xl flex items-center justify-between" style="background:#16a34a;">
                            <h3 class="font-bold text-white">{{ $editId ? 'Modifier l\'indemnité' : 'Nouvelle indemnité' }}</h3>
                            <button wire:click="$set('showModal', false)" class="text-white opacity-80 hover:opacity-100">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    @else
                        <div class="px-6 py-5 rounded-t-2xl flex items-center justify-between" style="background:#dc2626;">
                            <h3 class="font-bold text-white">{{ $editId ? 'Modifier la retenue' : 'Nouvelle retenue' }}</h3>
                            <button wire:click="$set('showModal', false)" class="text-white opacity-80 hover:opacity-100">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    @endif

                    <div class="p-6 space-y-5">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">
                                Libellé <span class="text-red-500">*</span>
                            </label>
                            <input wire:model="libelle" type="text"
                                class="w-full text-sm border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400"
                                placeholder="Ex: Transport, CNPS…"/>
                            @error('libelle')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        @if ($editType === 'categorie')
                            <div>
                                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">
                                    Salaire de base (FCFA) <span class="text-red-500">*</span>
                                </label>
                                <input wire:model="salaire_base" type="number" min="0" step="1"
                                    class="w-full text-sm border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400"
                                    placeholder="Ex: 250000"/>
                                @error('salaire_base')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">Description</label>
                            <textarea wire:model="description" rows="2"
                                class="w-full text-sm border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400 resize-none"
                                placeholder="Description optionnelle…"></textarea>
                        </div>

                        @if ($editId)
                            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl border border-gray-200">
                                <input wire:model="actif" type="checkbox" id="modal_actif"
                                    class="w-4 h-4 text-green-600 rounded border-gray-300"/>
                                <label for="modal_actif" class="text-sm font-medium text-gray-700 cursor-pointer">Actif</label>
                            </div>
                        @endif
                    </div>

                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-2xl flex justify-end gap-3">
                        <button wire:click="$set('showModal', false)"
                            class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50">
                            Annuler
                        </button>
                        @if ($editType === 'categorie')
                            <button wire:click="sauvegarder" class="px-6 py-2.5 text-sm font-semibold text-white rounded-xl" style="background:#059669;">
                                {{ $editId ? 'Mettre à jour' : 'Créer' }}
                            </button>
                        @elseif ($editType === 'indemnite')
                            <button wire:click="sauvegarder" class="px-6 py-2.5 text-sm font-semibold text-white rounded-xl" style="background:#16a34a;">
                                {{ $editId ? 'Mettre à jour' : 'Créer' }}
                            </button>
                        @else
                            <button wire:click="sauvegarder" class="px-6 py-2.5 text-sm font-semibold text-white rounded-xl" style="background:#dc2626;">
                                {{ $editId ? 'Mettre à jour' : 'Créer' }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    

        {{-- ═══ MODAL SUPPRESSION ═══ --}}
        <div class="fixed inset-0 z-50 overflow-y-auto" style="{{ $showDeleteModal ? 'background:rgba(0,0,0,0.6);' : 'display:none;' }}">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center">
                    <div class="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-4" style="background:#fee2e2;">
                        <svg class="w-7 h-7" style="color:#dc2626;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Supprimer ?</h3>
                    <p class="text-sm text-gray-500 mb-6">Cette action est irréversible.</p>
                    <div class="flex gap-3">
                        <button wire:click="$set('showDeleteModal', false)"
                            class="flex-1 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50">
                            Annuler
                        </button>
                        <button wire:click="supprimer"
                            class="flex-1 py-2.5 text-sm font-semibold text-white rounded-xl"
                            style="background:#dc2626;">
                            Supprimer
                        </button>
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
            <div class="flex items-center gap-3 px-5 py-4 rounded-2xl shadow-2xl text-sm font-medium"
                 style="{{ $notificationType === 'success' ? 'background:#111827;color:white;' : 'background:#dc2626;color:white;' }}">
                <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0"
                     style="{{ $notificationType === 'success' ? 'background:#22c55e;' : 'background:#f87171;' }}">
                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                {{ $notificationMessage }}
            </div>
        </div>
    @endif

</div>
@endvolt
</x-layouts.app>