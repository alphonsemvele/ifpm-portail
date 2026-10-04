<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\Support;
use App\Models\User;

name('admin.support');
middleware(['auth', 'verified']);

new class extends Component {
    public $supports;
    public $showDetailModal = false;
    public $selectedSupport;
    public $showNotification = false;
    public $notificationMessage = '';
    public $notificationType = '';

    public function mount()
    {
        $this->loadData();
    }

    private function loadData()
    {
        \Log::info('loadData appelé');
        $this->supports = Support::with('user')->get();
        \Log::info('Données chargées : ', ['supports_count' => $this->supports->count()]);
    }

    public function showDetail($id)
    {
        try {
            \Log::info('showDetail appelé avec ID: ' . $id);
            $this->selectedSupport = Support::with('user')->findOrFail($id);
            $this->showDetailModal = true;
            $this->dispatch('detail-modal-opened', id: $id);
        } catch (\Exception $e) {
            \Log::error('Erreur dans showDetail : ' . $e->getMessage());
            $this->showNotification('Erreur lors de l\'ouverture des détails : ' . $e->getMessage(), 'error');
        }
    }

    public function markAsResolved($id)
    {
        try {
            \Log::info('markAsResolved appelé avec ID: ' . $id);
            $support = Support::findOrFail($id);
            if ($support->status === 'pending') {
                $support->update(['status' => 'Success']);
                \Log::info('Requête marquée comme résolue avec succès');
                $this->loadData();
                $this->showNotification('Requête marquée comme résolue avec succès !', 'success');
                $this->dispatch('support-resolved', id: $id);
            } else {
                $this->showNotification('La requête n\'est pas en attente.', 'error');
            }
        } catch (\Exception $e) {
            \Log::error('Erreur dans markAsResolved : ' . $e->getMessage());
            $this->showNotification('Erreur lors du marquage comme résolu : ' . $e->getMessage(), 'error');
        }
    }

    public function markAsFailed($id)
    {
        try {
            \Log::info('markAsFailed appelé avec ID: ' . $id);
            $support = Support::findOrFail($id);
            if ($support->status === 'pending') {
                $support->update(['status' => 'failed']);
                \Log::info('Requête marquée comme annulée avec succès');
                $this->loadData();
                $this->showNotification('Requête marquée comme annulée avec succès !', 'success');
                $this->dispatch('support-failed', id: $id);
            } else {
                $this->showNotification('La requête n\'est pas en attente.', 'error');
            }
        } catch (\Exception $e) {
            \Log::error('Erreur dans markAsFailed : ' . $e->getMessage());
            $this->showNotification('Erreur lors du marquage comme annulé : ' . $e->getMessage(), 'error');
        }
    }

    public function closeModal()
    {
        \Log::info('closeModal appelé');
        $this->showDetailModal = false;
        $this->selectedSupport = null;
    }

    private function showNotification($message, $type)
    {
        \Log::info('showNotification appelé', ['message' => $message, 'type' => $type]);
        $this->notificationMessage = $message;
        $this->notificationType = $type;
        $this->showNotification = true;
        $this->dispatch('auto-hide-notification');
    }
};
?>

<x-layouts.app header="true">
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Gestion des Requêtes de Support</h1>
                    <p class="text-gray-500">Consultez les requêtes de support et les informations des émetteurs.</p>
                </div>
            </div>

            <!-- Notification -->
            @if ($showNotification)
                <div class="fixed top-6 right-6 z-50 max-w-sm w-full" x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)"
                    x-show="show" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-4">
                    <div class="{{ $notificationType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }} p-4 rounded-xl shadow-md border-l-4 {{ $notificationType === 'success' ? 'border-green-500' : 'border-red-500' }} animate-pulse">
                        {{ $notificationMessage }}
                    </div>
                </div>
            @endif

            <!-- Modal Détails -->
            @if ($showDetailModal && $selectedSupport)
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                    <div class="bg-white rounded-2xl p-8 w-full max-w-md shadow-2xl transform transition-all duration-300 ease-in-out">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Détails de la Requête</h3>
                        <div class="space-y-4">
                            <p><strong>Référence :</strong> {{ $selectedSupport->code ?? 'Non défini' }}</p>
                            <p><strong>Émetteur :</strong> {{ $selectedSupport->user->name ?? 'Non défini' }}</p>
                            <p><strong>Rôle :</strong> {{ $selectedSupport->user->role ?? 'Non défini' }}</p>
                            <p><strong>Problème :</strong> {{ $selectedSupport->description ?? 'Non défini' }}</p>
                            <p><strong>Date :</strong> {{ \Carbon\Carbon::parse($selectedSupport->created_at)->format('d/m/Y') }}</p>
                            <p><strong>Statut :</strong>
                                <span class="{{ $selectedSupport->status === 'Success' ? 'text-green-600' : ($selectedSupport->status === 'pending' ? 'text-yellow-600' : 'text-red-600') }}">
                                    {{ $selectedSupport->status === 'Success' ? 'Résolu' : ($selectedSupport->status === 'pending' ? 'En attente' : 'Annulé') }}
                                </span>
                            </p>
                        </div>
                        <div class="mt-6 flex justify-end">
                            <button wire:click="closeModal"
                                class="bg-gray-500 text-white py-2 px-4 rounded-xl hover:bg-gray-600 transition duration-300 shadow-md">
                                Fermer
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Liste des Requêtes -->
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Requêtes de Support</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-2">
                        <thead>
                            <tr class="bg-gray-100 rounded-lg">
                                <th class="p-4 text-sm font-medium text-gray-600 rounded-tl-lg">Référence</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Émetteur</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Rôle</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Problème</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Date</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Statut</th>
                                <th class="p-4 text-sm font-medium text-gray-600 rounded-tr-lg">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($supports as $support)
                                <tr class="bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-200">
                                    <td class="p-4 rounded-l-lg">{{ $support->code ?? 'Non défini' }}</td>
                                    <td class="p-4">{{ $support->user->name ?? 'Non défini' }}</td>
                                    <td class="p-4">{{ $support->user->role ?? 'Non défini' }}</td>
                                    <td class="p-4">{{ $support->description ?? 'Non défini' }}</td>
                                    <td class="p-4">

                                            {{ \Carbon\Carbon::parse($support->created_at)->format('d/m/Y') }}

                                    </td>
                                    <td class="p-4">
                                        <span class="{{ $support->status === 'Success' ? 'text-green-600' : ($support->status === 'pending' ? 'text-yellow-600' : 'text-red-600') }}">
                                            {{ $support->status === 'Success' ? 'Résolu' : ($support->status === 'pending' ? 'En attente' : 'Annulé') }}
                                        </span>
                                    </td>
                                    <td class="p-4 rounded-r-lg flex space-x-2">
                                        <button wire:click="showDetail({{ $support->id }})"
                                            class="text-indigo-600 hover:underline"
                                            x-on:click="console.log('Bouton Voir détails cliqué pour requête ID: {{ $support->id }}')">
                                            Voir détails
                                        </button>
                                        @if ($support->status === 'pending')
                                            <button wire:click="markAsResolved({{ $support->id }})"
                                                class="text-green-600 hover:underline"
                                                x-on:click="console.log('Bouton Résoudre cliqué pour requête ID: {{ $support->id }}')">
                                                Résolu
                                            </button>
                                            <button wire:click="markAsFailed({{ $support->id }})"
                                                class="text-red-600 hover:underline"
                                                x-on:click="console.log('Bouton Annuler cliqué pour requête ID: {{ $support->id }}')">
                                                Annuler
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endvolt
</x-layouts.app>
