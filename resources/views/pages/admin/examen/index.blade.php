<?php 

use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Examen;
use App\Models\Cycle;

name('admin.examens');
middleware(['auth', 'verified']);

new class extends Component {
    use WithPagination;

    public $titre = '';
    public $date = '';
    public $description = '';
    public $cycle_id = '';

    public $showCreateModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;
    public $showDetailsModal = false;

    public $selectedExamen = null;

    public bool $showNotification = false;
    public string $notificationMessage = '';
    public string $notificationType = 'success';

    public function getCyclesProperty()
    {
        return Cycle::orderBy('name')->get();
    }

    public function getExamensProperty()
    {
        return Examen::with('cycle')
            ->where('statut', '!=', 'annule')
            ->orderBy('date', 'desc')
            ->paginate(15);
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function openEditModal($id)
    {
        $this->selectedExamen = Examen::findOrFail($id);
        $this->titre = $this->selectedExamen->titre;
        $this->date = $this->selectedExamen->date?->format('Y-m-d');
        $this->description = $this->selectedExamen->description ?? '';
        $this->cycle_id = $this->selectedExamen->cycle_id ?? '';
        $this->showEditModal = true;
    }

    public function openDetailsModal($id)
    {
        $this->selectedExamen = Examen::with('cycle')->findOrFail($id);
        $this->showDetailsModal = true;
    }

    public function openDeleteModal($id)
    {
        $this->selectedExamen = Examen::findOrFail($id);
        $this->showDeleteModal = true;
    }

    public function closeModal()
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showDeleteModal = false;
        $this->showDetailsModal = false;
        $this->selectedExamen = null;
        $this->resetForm();
    }

    public function save()
    {
        $this->validate([
            'titre'       => 'required|string|max:255',
            'date'        => 'required|date',
            'description' => 'nullable|string',
            'cycle_id'    => 'required|exists:cycles,id',
        ]);

        Examen::create([
            'titre'       => $this->titre,
            'date'        => $this->date,
            'description' => $this->description,
            'cycle_id'    => $this->cycle_id,
            'statut'      => 'ouvert',
        ]);

        $this->closeModal();
        $this->showSuccessNotification('Examen créé avec succès !');
    }

    public function update()
    {
        $this->validate([
            'titre'       => 'required|string|max:255',
            'date'        => 'required|date',
            'description' => 'nullable|string',
            'cycle_id'    => 'required|exists:cycles,id',
        ]);

        $this->selectedExamen->update([
            'titre'       => $this->titre,
            'date'        => $this->date,
            'description' => $this->description,
            'cycle_id'    => $this->cycle_id,
        ]);

        $this->closeModal();
        $this->showSuccessNotification('Examen modifié avec succès !');
    }

    public function delete()
    {
        $this->selectedExamen->update(['statut' => 'annule']);
        $this->closeModal();
        $this->showSuccessNotification('Examen supprimé avec succès !');
    }

    public function toggleStatus($id)
    {
        $examen = Examen::findOrFail($id);
        $newStatus = $examen->statut === 'ouvert' ? 'ferme' : 'ouvert';
        $examen->update(['statut' => $newStatus]);
        $this->showSuccessNotification('Statut mis à jour !');
    }

    private function resetForm()
    {
        $this->titre = '';
        $this->date = '';
        $this->description = '';
        $this->cycle_id = '';
    }

    private function showSuccessNotification($message)
    {
        $this->notificationMessage = $message;
        $this->notificationType = 'success';
        $this->showNotification = true;
    }
};

?>

<x-layouts.app header="true">
    @volt
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Header -->
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Gestion des Examens</h1>
                <p class="text-gray-500">Créez et gérez les sessions d'examens</p>
            </div>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div class="flex items-center space-x-4">
                    <a href="/admin" class="p-2 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <div>
                        
                        
                    </div>
                </div>
                <button wire:click="openCreateModal" class="mt-4 md:mt-0 px-6 py-3 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition duration-200 flex items-center shadow-lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Nouvel Examen
                </button>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-blue-500 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100">Total Examens</p>
                        <p class="text-3xl font-bold">{{ $this->examens->total() }}</p>
                    </div>
                    <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-green-500 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100">Examens Ouverts</p>
                        <p class="text-3xl font-bold">{{ $this->examens->where('statut', 'ouvert')->count() }}</p>
                    </div>
                    <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-yellow-500 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-yellow-100">Examens Fermés</p>
                        <p class="text-3xl font-bold">{{ $this->examens->where('statut', 'ferme')->count() }}</p>
                    </div>
                    <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des Examens -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">Liste des Examens</h2>
            </div>

            @if($this->examens->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Titre</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Cycle</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Statut</th>
                                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($this->examens as $examen)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center mr-3">
                                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </div>
                                            <p class="font-medium text-gray-800">{{ $examen->titre }}</p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($examen->cycle)
                                            <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-xs font-medium">
                                                {{ $examen->cycle->name }}
                                            </span>
                                        @else
                                            <span class="text-gray-400 text-sm">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center text-gray-700">
                                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            {{ $examen->date?->format('d/m/Y') ?? '—' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm text-gray-600 truncate max-w-xs">{{ $examen->description ?? '—' }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($examen->statut === 'ouvert')
                                            <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">Ouvert</span>
                                        @elseif($examen->statut === 'ferme')
                                            <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">Fermé</span>
                                        @else
                                            <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium">Annulé</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end space-x-2">
                                            <button wire:click="openDetailsModal({{ $examen->id }})" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Détails">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </button>
                                            <button wire:click="openEditModal({{ $examen->id }})" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Modifier">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>
                                            <button wire:click="toggleStatus({{ $examen->id }})" class="p-2 {{ $examen->statut === 'ouvert' ? 'text-yellow-600 hover:bg-yellow-50' : 'text-green-600 hover:bg-green-50' }} rounded-lg transition" title="{{ $examen->statut === 'ouvert' ? 'Fermer' : 'Ouvrir' }}">
                                                @if($examen->statut === 'ouvert')
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                                    </svg>
                                                @else
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                                                    </svg>
                                                @endif
                                            </button>
                                            <button wire:click="openDeleteModal({{ $examen->id }})" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition" title="Supprimer">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $this->examens->links() }}
                </div>
            @else
                <div class="p-12 text-center">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-800 mb-2">Aucun examen</h3>
                    <p class="text-gray-500 mb-6">Commencez par créer votre premier examen.</p>
                    <button wire:click="openCreateModal" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                        Créer un examen
                    </button>
                </div>
            @endif
        </div>

        <!-- Modal Créer -->
        @if($showCreateModal)
            <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-2xl p-8 w-full max-w-lg max-h-[90vh] overflow-y-auto shadow-2xl">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Nouvel Examen</h2>
                    
                    <form wire:submit="save">
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Titre de l'examen <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="titre" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="Ex: Examen Final Semestre 1">
                                @error('titre') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Cycle <span class="text-red-500">*</span></label>
                                <select wire:model="cycle_id" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white">
                                    <option value="">— Sélectionner un cycle —</option>
                                    @foreach($this->cycles as $cycle)
                                        <option value="{{ $cycle->id }}">{{ $cycle->name }}</option>
                                    @endforeach
                                </select>
                                @error('cycle_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Date <span class="text-red-500">*</span></label>
                                <input type="date" wire:model="date" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                @error('date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Description <span class="text-gray-400">(optionnel)</span></label>
                                <textarea wire:model="description" rows="4" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="Description de l'examen..."></textarea>
                            </div>
                        </div>
                        
                        <div class="mt-8 flex justify-end space-x-4">
                            <button type="button" wire:click="closeModal" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                                Annuler
                            </button>
                            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                                Créer l'examen
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- Modal Modifier -->
        @if($showEditModal && $selectedExamen)
            <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-2xl p-8 w-full max-w-lg max-h-[90vh] overflow-y-auto shadow-2xl">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Modifier l'Examen</h2>
                    
                    <form wire:submit="update">
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Titre de l'examen <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="titre" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                @error('titre') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Cycle <span class="text-red-500">*</span></label>
                                <select wire:model="cycle_id" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white">
                                    <option value="">— Sélectionner un cycle —</option>
                                    @foreach($this->cycles as $cycle)
                                        <option value="{{ $cycle->id }}">{{ $cycle->name }}</option>
                                    @endforeach
                                </select>
                                @error('cycle_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Date <span class="text-red-500">*</span></label>
                                <input type="date" wire:model="date" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                @error('date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Description</label>
                                <textarea wire:model="description" rows="4" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"></textarea>
                            </div>
                        </div>
                        
                        <div class="mt-8 flex justify-end space-x-4">
                            <button type="button" wire:click="closeModal" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                                Annuler
                            </button>
                            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                                Mettre à jour
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- Modal Détails -->
        @if($showDetailsModal && $selectedExamen)
            <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-2xl p-8 w-full max-w-lg shadow-2xl">
                    <div class="text-center mb-6">
                        <div class="w-20 h-20 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-10 h-10 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-semibold text-gray-800">{{ $selectedExamen->titre }}</h2>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between py-3 border-b border-gray-100">
                            <span class="text-gray-500">Cycle</span>
                            @if($selectedExamen->cycle)
                                <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm font-medium">
                                    {{ $selectedExamen->cycle->name }}
                                </span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </div>
                        <div class="flex justify-between py-3 border-b border-gray-100">
                            <span class="text-gray-500">Date</span>
                            <span class="font-medium">{{ $selectedExamen->date?->format('d/m/Y') ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between py-3 border-b border-gray-100">
                            <span class="text-gray-500">Statut</span>
                            @if($selectedExamen->statut === 'ouvert')
                                <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">Ouvert</span>
                            @elseif($selectedExamen->statut === 'ferme')
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm">Fermé</span>
                            @else
                                <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm">Annulé</span>
                            @endif
                        </div>
                        @if($selectedExamen->description)
                            <div class="py-3">
                                <span class="text-gray-500 block mb-2">Description</span>
                                <p class="text-gray-700 bg-gray-50 p-3 rounded-lg">{{ $selectedExamen->description }}</p>
                            </div>
                        @endif
                    </div>
                    
                    <div class="mt-8 flex justify-end">
                        <button wire:click="closeModal" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                            Fermer
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Modal Supprimer -->
        @if($showDeleteModal && $selectedExamen)
            <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-2xl p-8 w-full max-w-md shadow-2xl">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800 mb-2">Supprimer l'examen</h3>
                        <p class="text-gray-600 mb-6">Voulez-vous vraiment supprimer l'examen <strong>{{ $selectedExamen->titre }}</strong> ?</p>
                        <div class="flex justify-center space-x-4">
                            <button wire:click="closeModal" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                                Annuler
                            </button>
                            <button wire:click="delete" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                                Supprimer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Notification -->
        @if($showNotification)
            <div class="fixed top-6 right-6 z-50 max-w-md w-full" 
                 x-data="{ show: true }" 
                 x-init="setTimeout(() => { show = false; $wire.set('showNotification', false); }, 5000)"
                 x-show="show"
                 x-transition>
                <div class="{{ $notificationType === 'success' ? 'bg-green-100 text-green-700 border-green-500' : 'bg-red-100 text-red-700 border-red-500' }} p-4 rounded-xl shadow-lg border-l-4">
                    <div class="flex items-start">
                        @if($notificationType === 'success')
                            <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        @else
                            <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        @endif
                        <span class="flex-1">{{ $notificationMessage }}</span>
                    </div>
                </div>
            </div>
        @endif
    </div>
    @endvolt
</x-layouts.app>