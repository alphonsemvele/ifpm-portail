<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;
use App\Models\Annonce;

name('admin.annonces');
middleware(['auth', 'verified']);

new class extends Component {
    use WithFileUploads;

    public $annonces;
    #[Validate('required|min:2')]
    public $title;
    #[Validate('required|min:2')]
    public $content;
    #[Validate('required')]
    public $date;
    #[Validate('required')]
    public $public = 'all';
    #[Validate('nullable|file|mimes:pdf,jpg,jpeg,png|max:10240')]
    public $file;
    public $showCreateModal = false;
    public $showActivatedModal = false;
    public $showDeletedModal = false;
    public $showDeleteModal = false;
    public $notificationMessage;
    public $notificationType;
    public $showNotification = false;
    public $annonceElement;

    public function mount()
    {
        $this->annonces = Annonce::where('status', '!=', 'failed')->orderBy('id', 'DESC')->get();
    }

    public function activatedAnnonce($id)
    {
        Annonce::find($id)->update(['status' => 'Success']);
        $this->annonces = Annonce::where('status', '!=', 'failed')->orderBy('id', 'DESC')->get();
        $this->showActivatedModal = false;
        $this->showNotification = true;
        $this->notificationMessage = 'Annonce validée avec succès !';
        $this->notificationType = 'success';
        $this->dispatch('auto-hide-notification');
    }

    public function functionShowActivatedViewModal($id)
    {
        $this->showActivatedModal = true;
        $this->annonceElement = Annonce::where('id', $id)->first();
    }

    public function functionShowDeletedViewModal($id)
    {
        $this->showDeletedModal = true;
        $this->annonceElement = Annonce::where('id', $id)->first();
    }

    public function DeletedAnnonce($id)
    {
        Annonce::find($id)->update(['status' => 'pending']);
        $this->annonces = Annonce::where('status', '!=', 'failed')->orderBy('id', 'DESC')->get();
        $this->showDeletedModal = false;
        $this->showNotification = true;
        $this->notificationMessage = 'Annonce désactivée avec succès !';
        $this->notificationType = 'success';
        $this->dispatch('auto-hide-notification');
    }

    public function deleteAnnonce($id)
    {
        Annonce::find($id)->update(['status' => 'failed']);
        $this->showDeleteModal = false;
        $this->annonces = Annonce::where('status', '!=', 'failed')->orderBy('id', 'DESC')->get();
        $this->showNotification = true;
        $this->notificationMessage = 'Annonce supprimée avec succès !';
        $this->notificationType = 'success';
        $this->dispatch('auto-hide-notification');
    }

    public function storeAnnonce()
    {
        $this->validate();

        $filePath = null;
        if ($this->file) {
            $filePath = $this->file->store('annonces', 'public');
            if (!$filePath) {
                $this->showNotification = true;
                $this->notificationMessage = "Erreur lors du téléchargement du fichier.";
                $this->notificationType = 'error';
                $this->dispatch('auto-hide-notification');
                return;
            }
        }

        $insert = Annonce::insert([
            'title' => $this->title,
            'content' => $this->content,
            'date' => $this->date,
            'public' => $this->public,
            'file' => $filePath,
            'status' => 'pending',
        ]);

        $this->showNotification = true;

        if ($insert) {
            $this->annonces = Annonce::where('status', '!=', 'failed')->orderBy('id', 'DESC')->get();
            $this->notificationMessage = 'Annonce créée avec succès';
            $this->notificationType = 'success';
        } else {
            $this->notificationMessage = "Une erreur s'est produite";
            $this->notificationType = 'error';
        }
        $this->reset(['title', 'content', 'date', 'file']);
        $this->public = 'all';
        $this->showCreateModal = false;
        $this->dispatch('auto-hide-notification');
    }

    public function functionShowCreateModal()
    {
        $this->reset(['title', 'content', 'date', 'file']);
        $this->public = 'all';
        $this->showCreateModal = true;
    }

    public function functionShowDeleteModal($id)
    {
        $this->showDeleteModal = true;
        $this->annonceElement = Annonce::where('id', $id)->first();
    }

    public function hideNotification()
    {
        $this->showNotification = false;
    }
};
?>

<x-layouts.app header="true">
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Gestion des Annonces</h1>
                    <p class="text-gray-500">Créez, visualisez et validez des annonces pour le tableau d'affichage.</p>
                </div>
                <button wire:click="functionShowCreateModal" class="mt-6 bg-indigo-600 text-white py-3 px-8 rounded-xl hover:bg-indigo-700 transition duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">Créer une annonce</button>
            </div>

            <!-- Notification -->
            @if ($showNotification)
                <div class="fixed top-6 right-6 z-50 max-w-sm w-full" x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition>
                    <div :class="'bg-' + $wire.notificationType + '-100 text-' + $wire.notificationType + '-700 p-4 rounded-xl shadow-md border-l-4 border-' + $wire.notificationType + '-500 animate-pulse'">
                        {{ $notificationMessage }}
                    </div>
                </div>
            @endif

            <!-- Modal Créer Annonce -->
            @if ($showCreateModal)
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                    <div class="bg-white rounded-2xl p-8 w-full max-w-xl max-h-[90vh] overflow-y-auto shadow-2xl transform transition-all duration-300 ease-in-out scale-100 hover:scale-[1.02]">
                        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Créer une Annonce</h2>
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-600">Titre <b class="text-red-600">*</b></label>
                                <input wire:model="title" type="text" name="title" class="mt-1 w-full p-3 border border-gray-300 rounded-lg">
                                @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @endif
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600">Contenu <b class="text-red-600">*</b></label>
                                <textarea wire:model="content" name="content" class="mt-1 w-full p-3 border border-gray-300 rounded-lg" rows="6"></textarea>
                                @error('content') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @endif
                            </div>
                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Date de Publication <b class="text-red-600">*</b></label>
                                    <input type="date" wire:model="date" name="date" class="mt-1 w-full p-3 border border-gray-300 rounded-lg" min="2025-08-23">
                                    @error('date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @endif
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Public Cible <b class="text-red-600">*</b></label>
                                    <select wire:model="public" class="mt-1 w-full p-3 border border-gray-300 rounded-lg">
                                        <option value="all">Tous (Apprenants et Personnel)</option>
                                        <option value="student">Apprenants uniquement</option>
                                        <option value="personnel">Personnel uniquement</option>
                                    </select>
                                    @error('public') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @endif
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600">Fichier</label>
                                <input wire:model="file" type="file" name="file" class="mt-1 w-full p-3 border border-gray-300 rounded-lg">
                                @error('file') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @endif
                            </div>
                        </div>
                        <div class="mt-8 flex justify-end space-x-4">
                            <button wire:click="storeAnnonce" class="bg-green-600 text-white py-2 px-6 rounded-lg hover:bg-green-700 transition duration-300">Valider</button>
                            <button wire:click="showCreateModal = false" class="bg-gray-600 text-white py-2 px-6 rounded-lg hover:bg-gray-700 transition duration-300">Annuler</button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Modal Activer Annonce -->
            @if ($showActivatedModal)
                <div class="overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full bg-black bg-opacity-50 flex">
                    <div class="relative p-4 w-full max-w-xl max-h-full mx-auto my-auto">
                        <div class="relative bg-white rounded-lg shadow-lg">
                            <button type="button" wire:click="showActivatedModal=false" class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center">
                                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                                </svg>
                                <span class="sr-only">Close modal</span>
                            </button>
                            <div class="p-4 md:p-5 text-center">
                                <svg class="mx-auto mb-4 text-gray-400 w-12 h-12" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                <h3 class="mb-5 text-lg font-normal text-gray-500">Êtes-vous sûr de vouloir activer l'annonce {{ $annonceElement->title ?? 'N/A' }}?</h3>
                                <button wire:click="activatedAnnonce({{ $annonceElement->id ?? 'N/A' }})" type="button" class="text-white bg-green-600 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-3">
                                    Oui
                                </button>
                                <button type="button" wire:click="showActivatedModal=false" class="py-2.5 px-5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100">
                                    Annuler
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Modal Désactiver Annonce -->
            @if ($showDeletedModal)
                <div class="overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full bg-black bg-opacity-50 flex">
                    <div class="relative p-4 w-full max-w-xl max-h-full mx-auto my-auto">
                        <div class="relative bg-white rounded-lg shadow-lg">
                            <button type="button" wire:click="showDeletedModal=false" class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center">
                                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                                </svg>
                                <span class="sr-only">Close modal</span>
                            </button>
                            <div class="p-4 md:p-5 text-center">
                                <svg class="mx-auto mb-4 text-gray-400 w-12 h-12" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                <h3 class="mb-5 text-lg font-normal text-gray-500">Êtes-vous sûr de vouloir désactiver l'annonce {{ $annonceElement->title ?? 'N/A' }}?</h3>
                                <button wire:click="DeletedAnnonce({{ $annonceElement->id ?? 'N/A' }})" type="button" class="text-white bg-yellow-600 hover:bg-yellow-800 focus:ring-4 focus:outline-none focus:ring-yellow-300 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-3">
                                    Oui
                                </button>
                                <button type="button" wire:click="showDeletedModal=false" class="py-2.5 px-5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100">
                                    Annuler
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Modal Supprimer Annonce -->
            @if ($showDeleteModal)
                <div class="overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full bg-black bg-opacity-50 flex">
                    <div class="relative p-4 w-full max-w-xl max-h-full mx-auto my-auto">
                        <div class="relative bg-white rounded-lg shadow-lg">
                            <button type="button" wire:click="showDeleteModal=false" class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center">
                                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                                </svg>
                                <span class="sr-only">Close modal</span>
                            </button>
                            <div class="p-4 md:p-5 text-center">
                                <svg class="mx-auto mb-4 text-gray-400 w-12 h-12" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                <h3 class="mb-5 text-lg font-normal text-gray-500">Êtes-vous sûr de vouloir supprimer l'annonce {{ $annonceElement->title ?? 'N/A' }}?</h3>
                                <button wire:click="deleteAnnonce({{ $annonceElement->id ?? 'N/A' }})" type="button" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-3">
                                    Oui
                                </button>
                                <button type="button" wire:click="showDeleteModal=false" class="py-2.5 px-5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100">
                                    Annuler
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Liste des Annonces -->
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Liste des Annonces</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-2">
                        <thead>
                            <tr class="bg-gray-100 rounded-lg">
                                <th class="p-4 text-sm font-medium text-gray-600">Titre</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Date de publication</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Public</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Statut</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($annonces as $data)
                                <tr class="bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-200">
                                    <td class="p-4">{{ $data->title }}</td>
                                    <td class="p-4">{{ $data->date }}</td>
                                    <td class="p-4">{{ $data->public }}</td>
                                    <td class="p-4">
                                        @if ($data->status == 'Success')
                                            <b class="text-green-600">Validée</b>
                                        @else
                                            <b class="text-yellow-600">En attente</b>
                                        @endif
                                    </td>
                                    <td class="p-4">
                                        @if ($data->status == 'pending')
                                            <button wire:click="functionShowActivatedViewModal({{ $data->id }})" class="inline-flex items-center px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 mr-2">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                                Activer
                                            </button>
                                        @elseif ($data->status == 'Success')
                                            <button wire:click="functionShowDeletedViewModal({{ $data->id }})" class="inline-flex items-center px-3 py-1 bg-yellow-600 text-white text-sm rounded hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 mr-2">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Désactiver
                                            </button>
                                        @endif
                                        <button wire:click="functionShowDeleteModal({{ $data->id }})" class="inline-flex items-center px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            Supprimer
                                        </button>
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
