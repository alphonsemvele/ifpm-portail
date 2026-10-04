<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\Indemnite;

name('admin.rh.indemnites');
middleware(['auth', 'verified', 'role']);

new class extends Component {

    public string $libelle      = '';
    public string $description  = '';

    public ?int   $editId         = null;
    public bool   $actif           = true;
    public bool   $showModal       = false;
    public bool   $showDeleteModal = false;
    public ?int   $deleteId        = null;

    public bool   $showNotification    = false;
    public string $notificationMessage = '';
    public string $notificationType    = 'success';

    public $items = [];

    public function mount(): void
    {
        $this->items = collect();
        $this->charger();
    }

    public function charger(): void
    {
        $this->items = Indemnite::orderBy('libelle')->get();
    }

    public function ouvrir(int $id = 0): void
    {
        $this->libelle      = '';
        $this->description  = '';
        $this->actif        = true;
        $this->editId       = $id > 0 ? $id : null;

        if ($id > 0) {
            $m = Indemnite::find($id);
            if ($m) {
                $this->libelle      = $m->libelle;
                $this->description  = $m->description ?? '';
                $this->actif        = $m->actif;
            }
        }
        $this->showModal = true;
    }

    public function sauvegarder(): void
    {
        $this->validate(['libelle' => 'required|string|max:255', 'description' => 'nullable|string']);
        try {
            $data = [
                'libelle'     => $this->libelle,
                'description' => $this->description ?: null,
                'actif'       => $this->actif,
            ];
            $this->editId
                ? Indemnite::findOrFail($this->editId)->update($data)
                : Indemnite::create($data);
            $this->showModal = false;
            $this->charger();
            $this->toast($this->editId ? 'Mis à jour !' : 'Créé avec succès !');
        } catch (\Exception $e) {
            $this->toast('Erreur : ' . $e->getMessage(), 'error');
        }
    }

    public function confirmer(int $id): void
    {
        $this->deleteId        = $id;
        $this->showDeleteModal = true;
    }

    public function supprimer(): void
    {
        try {
            Indemnite::findOrFail($this->deleteId)->delete();
            $this->showDeleteModal = false;
            $this->charger();
            $this->toast('Supprimé !');
        } catch (\Exception $e) {
            $this->toast('Erreur.', 'error');
        }
    }

    public function basculer(int $id): void
    {
        $m = Indemnite::find($id);
        if ($m) { $m->update(['actif' => !$m->actif]); $this->charger(); }
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

    <div class="bg-white border-b border-gray-200 px-6 py-5">
        <div class="max-w-5xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#16a34a;">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-gray-900">Indemnités</h1>
                    <p class="text-xs text-gray-500">Gérez les indemnités de salaire</p>
                </div>
            </div>
            <button wire:click="ouvrir()" class="inline-flex items-center gap-2 px-4 py-2 text-white text-sm font-semibold rounded-xl shadow" style="background:#16a34a;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nouvelle indemnité
            </button>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                <div class="w-1.5 h-5 rounded-full" style="background:#16a34a;"></div>
                <span class="text-sm font-bold text-gray-700 uppercase tracking-wider">Indemnités</span>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">{{ $items->count() }}</span>
            </div>

                        @if ($items->isEmpty())
                <div class="py-20 text-center">
                    <p class="text-gray-500 mb-3">Aucune indemnité créée.</p>
                    <button wire:click="ouvrir()" class="text-sm font-semibold underline" style="color:#16a34a;">+ Créer la première</button>
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
                        @foreach ($items as $i => $item)
                            <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} border-t border-gray-100 hover:bg-green-50 transition-colors">
                                <td class="px-6 py-4 font-semibold text-gray-900">{{ $item->libelle }}</td>
                                <td class="px-6 py-4 text-xs text-gray-500">{{ $item->description ?: '—' }}</td>
                                <td class="px-6 py-4 text-center">
                                    @if ($item->actif)
                                        <button wire:click="basculer({{ $item->id }})" class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full" style="background:#dcfce7;color:#15803d;">
                                            <span class="w-1.5 h-1.5 rounded-full" style="background:#22c55e;"></span> Actif
                                        </button>
                                    @else
                                        <button wire:click="basculer({{ $item->id }})" class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full bg-gray-100 text-gray-500">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Inactif
                                        </button>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button wire:click="ouvrir({{ $item->id }})" class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-indigo-100 flex items-center justify-center text-gray-500 hover:text-indigo-700 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <button wire:click="confirmer({{ $item->id }})" class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-red-100 flex items-center justify-center text-gray-500 hover:text-red-600 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif


    {{-- Modal --}}
    <div class="fixed inset-0 z-50 overflow-y-auto" style="{{ $showModal ? 'background:rgba(0,0,0,0.6);' : 'display:none;' }}">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
                <div class="px-6 py-5 rounded-t-2xl flex items-center justify-between" style="background:#16a34a;">
                    <h3 class="font-bold text-white">{{ $editId ? "Modifier l'indemnité" : "Nouvelle indemnité" }}</h3>
                    <button wire:click="$set('showModal',false)" class="text-white opacity-80 hover:opacity-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">Libellé *</label>
                        <input wire:model="libelle" type="text" class="w-full text-sm border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="Ex: Transport, Logement…"/>
                        @error('libelle')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">Description</label>
                        <textarea wire:model="description" rows="2" class="w-full text-sm border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400 resize-none" placeholder="Description optionnelle…"></textarea>
                    </div>
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl border border-gray-200">
                        <input wire:model="actif" type="checkbox" id="item_actif" class="w-4 h-4 text-green-600 rounded"/>
                        <label for="item_actif" class="text-sm font-medium text-gray-700 cursor-pointer">Actif</label>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-2xl flex justify-end gap-3">
                    <button wire:click="$set('showModal',false)" class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50">Annuler</button>
                    <button wire:click="sauvegarder" class="px-6 py-2.5 text-sm font-semibold text-white rounded-xl" style="background:#16a34a;">{{ $editId ? 'Mettre à jour' : 'Créer' }}</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal suppression --}}
    <div class="fixed inset-0 z-50 overflow-y-auto" style="{{ $showDeleteModal ? 'background:rgba(0,0,0,0.6);' : 'display:none;' }}">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center">
                <div class="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-4" style="background:#fee2e2;">
                    <svg class="w-7 h-7" style="color:#dc2626;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">Supprimer ?</h3>
                <p class="text-sm text-gray-500 mb-6">Cette action est irréversible.</p>
                <div class="flex gap-3">
                    <button wire:click="$set('showDeleteModal',false)" class="flex-1 py-2.5 text-sm font-semibold text-gray-700 border border-gray-300 rounded-xl hover:bg-gray-50">Annuler</button>
                    <button wire:click="supprimer" class="flex-1 py-2.5 text-sm font-semibold text-white rounded-xl" style="background:#dc2626;">Supprimer</button>
                </div>
            </div>
        </div>
    </div>
    {{-- Toast --}}
    @if ($showNotification)
        <div class="fixed bottom-6 right-6 z-50"
             x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => { show = false; $wire.set('showNotification', false) }, 3000)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
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