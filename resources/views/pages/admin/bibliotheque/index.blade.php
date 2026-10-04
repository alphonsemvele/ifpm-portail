
<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\Book;
use App\Models\bookUser;
use App\Models\User;

name('admin.bibliotheque');
middleware(['auth', 'verified']);

new class extends Component {
    public $books;
    public $users;
    public $showAddModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;
    public $showActivateModal = false;
    public $showDeactivateModal = false;
    public $bookElement;
    public $showNotification = false;
    public $notificationMessage = '';
    public $notificationType = '';
    public $formErrors = [];

    public $name = '';
    public $auteur = '';
    public $code = '';
    public $status = 'Success';
    public $user_id = '';
    public $return_date = '';

    public function mount()
    {
        $this->loadData();
    }

    private function loadData()
    {
        \Log::info('loadData appelé');
        $this->books = Book::with(['bookUsers.user'])->where('status', '!=', 'pending')->get();
        $this->users = User::all();
        \Log::info('Données chargées : ', [
            'books' => $this->books->count(),
            'users' => $this->users->count(),
        ]);
    }

    public function save()
    {
        \Log::info('Méthode save appelée', [
            'name' => $this->name,
            'auteur' => $this->auteur,
            'code' => $this->code,
            'status' => $this->status,
            'user_id' => $this->user_id,
            'return_date' => $this->return_date,
        ]);

        try {
            $this->validate([
                'name' => 'required|string|max:255',
                'auteur' => 'required|string|max:255',
                'code' => 'required|string|max:20|unique:books,code',
                'status' => 'required|in:Success,emprunt,pending',
                'user_id' => 'nullable|exists:users,id',
                'return_date' => 'nullable|date|after:today',
            ]);

            $book = Book::create([
                'name' => $this->name,
                'auteur' => $this->auteur,
                'code' => $this->code,
                'status' => $this->status,
            ]);

            if ($this->user_id && $this->return_date) {
                bookUser::create([
                    'book_id' => $book->id,
                    'user_id' => $this->user_id,
                    'return_date' => $this->return_date,
                    'status' => 'pending',
                ]);
                $book->update(['status' => 'emprunt']);
            }

            \Log::info('Livre créé avec succès');
            $this->resetForm();
            $this->showAddModal = false;
            $this->loadData();
            $this->showNotification('Livre ajouté avec succès !', 'success');
            $this->formErrors = [];
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Erreur de validation dans save : ', ['errors' => $e->errors(), 'formErrors_type' => gettype($e->errors())]);
            $this->formErrors = $e->errors();
            $this->showNotification('Veuillez corriger les erreurs dans le formulaire.', 'error');
        } catch (\Exception $e) {
            \Log::error('Erreur dans save : ' . $e->getMessage());
            $this->showNotification('Erreur lors de l\'ajout du livre : ' . $e->getMessage(), 'error');
        }
    }

    public function functionShowAddModal()
    {
        \Log::info('functionShowAddModal appelé');
        $this->resetForm();
        $this->showAddModal = true;
        $this->formErrors = [];
    }

    public function functionShowEditModal($id)
    {
        try {
            \Log::info('functionShowEditModal appelé avec ID: ' . $id);
            $this->bookElement = Book::with(['bookUsers' => function ($query) {
                $query->where('status', 'pending')->latest();
            }])->findOrFail($id);
            $this->name = $this->bookElement->name;
            $this->auteur = $this->bookElement->auteur;
            $this->code = $this->bookElement->code;
            $this->status = $this->bookElement->status;
            $this->user_id = $this->bookElement->bookUsers->first()?->user_id ?? '';
            $this->return_date = $this->bookElement->bookUsers->first()?->return_date ? \Carbon\Carbon::parse($this->bookElement->bookUsers->first()->return_date)->format('Y-m-d') : '';
            $this->showEditModal = true;
            $this->formErrors = [];
            $this->dispatch('edit-modal-opened', id: $id);
        } catch (\Exception $e) {
            \Log::error('Erreur dans functionShowEditModal : ' . $e->getMessage());
            $this->showNotification('Erreur lors de l\'ouverture du modal d\'édition : ' . $e->getMessage(), 'error');
        }
    }

    public function update()
    {
        \Log::info('Méthode update appelée', [
            'name' => $this->name,
            'auteur' => $this->auteur,
            'code' => $this->code,
            'status' => $this->status,
            'user_id' => $this->user_id,
            'return_date' => $this->return_date,
        ]);

        try {
            $this->validate([
                'name' => 'required|string|max:255',
                'auteur' => 'required|string|max:255',
                'code' => 'required|string|max:20|unique:books,code,' . $this->bookElement->id,
                'status' => 'required|in:Success,emprunt,pending',
                'user_id' => 'nullable|exists:users,id',
                'return_date' => 'nullable|date|after:today',
            ]);

            $this->bookElement->update([
                'name' => $this->name,
                'auteur' => $this->auteur,
                'code' => $this->code,
                'status' => $this->status,
            ]);

            $activeLoan = $this->bookElement->bookUsers()->where('status', 'pending')->first();
            if ($this->user_id && $this->return_date) {
                if ($activeLoan) {
                    $activeLoan->update([
                        'user_id' => $this->user_id,
                        'return_date' => $this->return_date,
                        'status' => 'pending',
                    ]);
                } else {
                    bookUser::create([
                        'book_id' => $this->bookElement->id,
                        'user_id' => $this->user_id,
                        'return_date' => $this->return_date,
                        'status' => 'pending',
                    ]);
                }
                $this->bookElement->update(['status' => 'emprunt']);
            } else {
                if ($activeLoan) {
                    $activeLoan->update(['status' => 'Success']);
                    $this->bookElement->update(['status' => 'Success']);
                }
            }

            \Log::info('Livre mis à jour avec succès');
            $this->resetForm();
            $this->showEditModal = false;
            $this->bookElement = null;
            $this->loadData();
            $this->showNotification('Livre mis à jour avec succès !', 'success');
            $this->formErrors = [];
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Erreur de validation dans update : ', ['errors' => $e->errors(), 'formErrors_type' => gettype($e->errors())]);
            $this->formErrors = $e->errors();
            $this->showNotification('Veuillez corriger les erreurs dans le formulaire.', 'error');
        } catch (\Exception $e) {
            \Log::error('Erreur dans update : ' . $e->getMessage());
            $this->showNotification('Erreur lors de la mise à jour du livre : ' . $e->getMessage(), 'error');
        }
    }

    public function functionShowActivateModal($id)
    {
        try {
            \Log::info('functionShowActivateModal appelé avec ID: ' . $id);
            $this->bookElement = Book::findOrFail($id);
            $this->showActivateModal = true;
            $this->dispatch('activate-modal-opened', id: $id);
        } catch (\Exception $e) {
            \Log::error('Erreur dans functionShowActivateModal : ' . $e->getMessage());
            $this->showNotification('Erreur lors de l\'ouverture du modal d\'activation : ' . $e->getMessage(), 'error');
        }
    }

    public function functionShowDeactivateModal($id)
    {
        try {
            \Log::info('functionShowDeactivateModal appelé avec ID: ' . $id);
            $this->bookElement = Book::findOrFail($id);
            $this->showDeactivateModal = true;
            $this->dispatch('deactivate-modal-opened', id: $id);
        } catch (\Exception $e) {
            \Log::error('Erreur dans functionShowDeactivateModal : ' . $e->getMessage());
            $this->showNotification('Erreur lors de l\'ouverture du modal de désactivation : ' . $e->getMessage(), 'error');
        }
    }

    public function functionShowDeleteModal($id)
    {
        try {
            \Log::info('functionShowDeleteModal appelé avec ID: ' . $id);
            $this->bookElement = Book::findOrFail($id);
            $this->showDeleteModal = true;
            $this->dispatch('delete-modal-opened', id: $id);
        } catch (\Exception $e) {
            \Log::error('Erreur dans functionShowDeleteModal : ' . $e->getMessage());
            $this->showNotification('Erreur lors de l\'ouverture du modal de suppression : ' . $e->getMessage(), 'error');
        }
    }

    public function activateBook($id)
    {
        try {
            \Log::info('activateBook appelé avec ID: ' . $id);
            $book = Book::findOrFail($id);
            $book->update(['status' => 'Success']);
            \Log::info('Livre activé avec succès');
            $this->showActivateModal = false;
            $this->bookElement = null;
            $this->loadData();
            $this->showNotification('Livre activé avec succès !', 'success');
            $this->dispatch('book-activated', id: $id);
        } catch (\Exception $e) {
            \Log::error('Erreur dans activateBook : ' . $e->getMessage());
            $this->showNotification('Erreur lors de l\'activation du livre : ' . $e->getMessage(), 'error');
        }
    }

    public function deactivateBook($id)
    {
        try {
            \Log::info('deactivateBook appelé avec ID: ' . $id);
            $book = Book::findOrFail($id);
            $book->update(['status' => 'pending']);
            \Log::info('Livre désactivé avec succès');
            $this->showDeactivateModal = false;
            $this->bookElement = null;
            $this->loadData();
            $this->showNotification('Livre désactivé avec succès !', 'success');
            $this->dispatch('book-deactivated', id: $id);
        } catch (\Exception $e) {
            \Log::error('Erreur dans deactivateBook : ' . $e->getMessage());
            $this->showNotification('Erreur lors de la désactivation du livre : ' . $e->getMessage(), 'error');
        }
    }

    public function deleteBook($id)
    {
        try {
            \Log::info('deleteBook appelé avec ID: ' . $id);
            $book = Book::findOrFail($id);
            $book->update(['status' => 'pending']);
            \Log::info('Livre marqué comme non disponible avec succès');
            $this->showDeleteModal = false;
            $this->bookElement = null;
            $this->loadData();
            $this->showNotification('Livre marqué comme non disponible avec succès !', 'success');
            $this->dispatch('book-deleted', id: $id);
        } catch (\Exception $e) {
            \Log::error('Erreur dans deleteBook : ' . $e->getMessage());
            $this->showNotification('Erreur lors de la suppression du livre : ' . $e->getMessage(), 'error');
        }
    }

    public function markAsReturned($id)
    {
        try {
            \Log::info('markAsReturned appelé avec ID: ' . $id);
            $book = Book::findOrFail($id);
            $activeLoan = $book->bookUsers()->where('status', 'pending')->first();
            if ($activeLoan) {
                $activeLoan->update(['status' => 'Success']);
                $book->update(['status' => 'Success']);
                \Log::info('Livre marqué comme rendu avec succès');
                $this->loadData();
                $this->showNotification('Livre marqué comme rendu avec succès !', 'success');
            } else {
                $this->showNotification('Aucun emprunt actif trouvé pour ce livre.', 'error');
            }
        } catch (\Exception $e) {
            \Log::error('Erreur dans markAsReturned : ' . $e->getMessage());
            $this->showNotification('Erreur lors du marquage comme rendu : ' . $e->getMessage(), 'error');
        }
    }

    public function closeModal()
    {
        \Log::info('closeModal appelé');
        $this->showAddModal = false;
        $this->showEditModal = false;
        $this->showDeleteModal = false;
        $this->showActivateModal = false;
        $this->showDeactivateModal = false;
        $this->resetForm();
        $this->bookElement = null;
        $this->formErrors = [];
    }

    private function resetForm()
    {
        \Log::info('resetForm appelé');
        $this->reset(['name', 'auteur', 'code', 'status', 'user_id', 'return_date']);
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
                    <h1 class="text-3xl font-bold text-gray-800">Gestion de la Bibliothèque</h1>
                    <p class="text-gray-500">Gérez les livres, suivez les emprunts et mettez à jour les statuts.</p>
                </div>
                <button wire:click="functionShowAddModal"
                    class="mt-6 bg-indigo-600 text-white py-3 px-8 rounded-xl hover:bg-indigo-700 transition duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1"
                    x-on:click="console.log('Bouton Ajouter un livre cliqué')">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Ajouter un livre
                </button>
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

            <!-- Modal Ajouter Livre -->
            @if ($showAddModal)
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                    <div class="bg-white rounded-2xl p-8 w-full max-w-xl max-h-[90vh] overflow-y-auto shadow-2xl transform transition-all duration-300 ease-in-out scale-100 hover:scale-[1.02]">
                        <h2 class="text-3xl font-bold text-gray-800 mb-6 bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Ajouter un Livre</h2>
                        @if (!empty($formErrors) && is_array($formErrors))
                            <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-xl border-l-4 border-red-500">
                                Veuillez corriger les erreurs suivantes :
                                <ul class="list-disc ml-5">
                                    @foreach ($formErrors as $field => $errors)
                                        @foreach ($errors as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <form wire:submit.prevent="save">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Titre</label>
                                    <input type="text" wire:model.defer="name" required class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 transition-all duration-200">
                                    @if (!empty($formErrors['name']) && is_array($formErrors['name']))
                                        <span class="text-red-500 text-sm">{{ $formErrors['name'][0] }}</span>
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Auteur</label>
                                    <input type="text" wire:model.defer="auteur" required class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 transition-all duration-200">
                                    @if (!empty($formErrors['auteur']) && is_array($formErrors['auteur']))
                                        <span class="text-red-500 text-sm">{{ $formErrors['auteur'][0] }}</span>
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Code</label>
                                    <input type="text" wire:model.defer="code" required class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 transition-all duration-200">
                                    @if (!empty($formErrors['code']) && is_array($formErrors['code']))
                                        <span class="text-red-500 text-sm">{{ $formErrors['code'][0] }}</span>
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Statut</label>
                                    <select wire:model.defer="status" required class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 transition-all duration-200">
                                        <option value="Success">Disponible</option>
                                        <option value="emprunt">Emprunt</option>
                                        <option value="pending">Non disponible</option>
                                    </select>
                                    @if (!empty($formErrors['status']) && is_array($formErrors['status']))
                                        <span class="text-red-500 text-sm">{{ $formErrors['status'][0] }}</span>
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Emprunteur (optionnel)</label>
                                    <select wire:model.defer="user_id" class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 transition-all duration-200">
                                        <option value="">Aucun</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                    @if (!empty($formErrors['user_id']) && is_array($formErrors['user_id']))
                                        <span class="text-red-500 text-sm">{{ $formErrors['user_id'][0] }}</span>
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Date de retour (optionnel)</label>
                                    <input type="date" wire:model.defer="return_date" class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 transition-all duration-200">
                                    @if (!empty($formErrors['return_date']) && is_array($formErrors['return_date']))
                                        <span class="text-red-500 text-sm">{{ $formErrors['return_date'][0] }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="mt-8 flex justify-end space-x-4">
                                <button type="submit"
                                    class="bg-indigo-600 text-white py-3 px-6 rounded-xl hover:bg-indigo-700 transition duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-1"
                                    x-on:click="console.log('Bouton Enregistrer cliqué')">
                                    Enregistrer
                                </button>
                                <button wire:click="closeModal"
                                    class="bg-gray-500 text-white py-3 px-6 rounded-xl hover:bg-gray-600 transition duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-1">
                                    Annuler
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Modal Modifier Livre -->
            @if ($showEditModal)
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                    <div class="bg-white rounded-2xl p-8 w-full max-w-xl max-h-[90vh] overflow-y-auto shadow-2xl transform transition-all duration-300 ease-in-out scale-100 hover:scale-[1.02]">
                        <h2 class="text-3xl font-bold text-gray-800 mb-6 bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Modifier un Livre</h2>
                        @if (!empty($formErrors) && is_array($formErrors))
                            <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-xl border-l-4 border-red-500">
                                Veuillez corriger les erreurs suivantes :
                                <ul class="list-disc ml-5">
                                    @foreach ($formErrors as $field => $errors)
                                        @foreach ($errors as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <form wire:submit.prevent="update">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Titre</label>
                                    <input type="text" wire:model.defer="name" required class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 transition-all duration-200">
                                    @if (!empty($formErrors['name']) && is_array($formErrors['name']))
                                        <span class="text-red-500 text-sm">{{ $formErrors['name'][0] }}</span>
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Auteur</label>
                                    <input type="text" wire:model.defer="auteur" required class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 transition-all duration-200">
                                    @if (!empty($formErrors['auteur']) && is_array($formErrors['auteur']))
                                        <span class="text-red-500 text-sm">{{ $formErrors['auteur'][0] }}</span>
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Code</label>
                                    <input type="text" wire:model.defer="code" required class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 transition-all duration-200">
                                    @if (!empty($formErrors['code']) && is_array($formErrors['code']))
                                        <span class="text-red-500 text-sm">{{ $formErrors['code'][0] }}</span>
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Statut</label>
                                    <select wire:model.defer="status" required class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 transition-all duration-200">
                                        <option value="Success">Disponible</option>
                                        <option value="emprunt">Emprunt</option>
                                        <option value="pending">Non disponible</option>
                                    </select>
                                    @if (!empty($formErrors['status']) && is_array($formErrors['status']))
                                        <span class="text-red-500 text-sm">{{ $formErrors['status'][0] }}</span>
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Emprunteur (optionnel)</label>
                                    <select wire:model.defer="user_id" class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 transition-all duration-200">
                                        <option value="">Aucun</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                    @if (!empty($formErrors['user_id']) && is_array($formErrors['user_id']))
                                        <span class="text-red-500 text-sm">{{ $formErrors['user_id'][0] }}</span>
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Date de retour (optionnel)</label>
                                    <input type="date" wire:model.defer="return_date" class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 transition-all duration-200">
                                    @if (!empty($formErrors['return_date']) && is_array($formErrors['return_date']))
                                        <span class="text-red-500 text-sm">{{ $formErrors['return_date'][0] }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="mt-8 flex justify-end space-x-4">
                                <button type="submit"
                                    class="bg-indigo-600 text-white py-3 px-6 rounded-xl hover:bg-indigo-700 transition duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-1"
                                    x-on:click="console.log('Bouton Mettre à jour cliqué')">
                                    Mettre à jour
                                </button>
                                <button wire:click="closeModal"
                                    class="bg-gray-500 text-white py-3 px-6 rounded-xl hover:bg-gray-600 transition duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-1">
                                    Annuler
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Modal Activer -->
            @if ($showActivateModal)
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl transform transition-all duration-300 ease-in-out">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Activer le livre</h3>
                        <p class="mb-4 text-gray-600">Voulez-vous activer le livre "{{ $bookElement->name ?? 'N/A' }}" ?</p>
                        <div class="flex justify-end space-x-4">
                            <button wire:click="activateBook({{ $bookElement->id }})"
                                class="bg-green-600 text-white py-2 px-4 rounded-xl hover:bg-green-700 transition duration-300 shadow-md"
                                x-on:click="console.log('Bouton Oui (Activer) cliqué pour livre ID: {{ $bookElement->id }}')">
                                Oui
                            </button>
                            <button wire:click="closeModal"
                                class="bg-gray-500 text-white py-2 px-4 rounded-xl hover:bg-gray-600 transition duration-300 shadow-md">
                                Annuler
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Modal Désactiver -->
            @if ($showDeactivateModal)
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl transform transition-all duration-300 ease-in-out">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Désactiver le livre</h3>
                        <p class="mb-4 text-gray-600">Voulez-vous désactiver le livre "{{ $bookElement->name ?? 'N/A' }}" ?</p>
                        <div class="flex justify-end space-x-4">
                            <button wire:click="deactivateBook({{ $bookElement->id }})"
                                class="bg-yellow-600 text-white py-2 px-4 rounded-xl hover:bg-yellow-700 transition duration-300 shadow-md"
                                x-on:click="console.log('Bouton Oui (Désactiver) cliqué pour livre ID: {{ $bookElement->id }}')">
                                Oui
                            </button>
                            <button wire:click="closeModal"
                                class="bg-gray-500 text-white py-2 px-4 rounded-xl hover:bg-gray-600 transition duration-300 shadow-md">
                                Annuler
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Modal Supprimer -->
            @if ($showDeleteModal)
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl transform transition-all duration-300 ease-in-out">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Supprimer le livre</h3>
                        <p class="mb-4 text-gray-600">Voulez-vous marquer le livre "{{ $bookElement->name ?? 'N/A' }}" comme non disponible ?</p>
                        <div class="flex justify-end space-x-4">
                            <button wire:click="deleteBook({{ $bookElement->id }})"
                                class="bg-red-600 text-white py-2 px-4 rounded-xl hover:bg-red-700 transition duration-300 shadow-md"
                                x-on:click="console.log('Bouton Oui (Supprimer) cliqué pour livre ID: {{ $bookElement->id }}')">
                                Oui
                            </button>
                            <button wire:click="closeModal"
                                class="bg-gray-500 text-white py-2 px-4 rounded-xl hover:bg-gray-600 transition duration-300 shadow-md">
                                Annuler
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Liste des Livres -->
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Liste des Livres</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-2">
                        <thead>
                            <tr class="bg-gray-100 rounded-lg">
                                <th class="p-4 text-sm font-medium text-gray-600 rounded-tl-lg">Titre</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Auteur</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Code</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Statut</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Emprunteur</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Date de retour</th>
                                <th class="p-4 text-sm font-medium text-gray-600 rounded-tr-lg">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($books as $book)
                                <tr class="bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-200">
                                    <td class="p-4 rounded-l-lg">{{ $book->name ?? 'Non défini' }}</td>
                                    <td class="p-4">{{ $book->auteur ?? 'Non défini' }}</td>
                                    <td class="p-4">{{ $book->code ?? 'Non défini' }}</td>
                                    <td class="p-4">
                                        <span class="inline-block px-3 py-1 text-xs font-medium rounded-full
                                            {{ $book->status === 'Success' ? 'bg-green-100 text-green-800' : ($book->status === 'emprunt' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ $book->status === 'Success' ? 'Disponible' : ($book->status === 'emprunt' ? 'Emprunt' : 'Non disponible') }}
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        @if ($book->bookUsers->where('status', 'pending')->first())
                                            {{ $book->bookUsers->where('status', 'pending')->first()->user->name ?? 'Non défini' }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="p-4">
                                        @if ($book->bookUsers->where('status', 'pending')->first())
                                          
                                                {{ \Carbon\Carbon::parse($book->bookUsers->where('status', 'pending')->first()->return_date)->format('d/m/Y') }}

                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="p-4 rounded-r-lg flex space-x-2">
                                        @if ($book->status !== 'Success')
                                            <button wire:click="functionShowActivateModal({{ $book->id }})"
                                                class="inline-flex items-center px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition duration-200"
                                                x-on:click="console.log('Bouton Activer cliqué pour livre ID: {{ $book->id }}')">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                                Activer
                                            </button>
                                        @else
                                            <button wire:click="functionShowDeactivateModal({{ $book->id }})"
                                                class="inline-flex items-center px-3 py-1 bg-yellow-600 text-white text-sm rounded hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 transition duration-200"
                                                x-on:click="console.log('Bouton Désactiver cliqué pour livre ID: {{ $book->id }}')">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Désactiver
                                            </button>
                                        @endif
                                        <button wire:click="functionShowEditModal({{ $book->id }})"
                                            class="inline-flex items-center px-3 py-1 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition duration-200"
                                            x-on:click="console.log('Bouton Modifier cliqué pour livre ID: {{ $book->id }}')">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Modifier
                                        </button>
                                        <button wire:click="functionShowDeleteModal({{ $book->id }})"
                                            class="inline-flex items-center px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 transition duration-200"
                                            x-on:click="console.log('Bouton Supprimer cliqué pour livre ID: {{ $book->id }}')">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            Supprimer
                                        </button>
                                        @if ($book->status === 'emprunt')
                                            <button wire:click="markAsReturned({{ $book->id }})"
                                                class="inline-flex items-center px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition duration-200"
                                                x-on:click="console.log('Bouton Marquer comme rendu cliqué pour livre ID: {{ $book->id }}')">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                                Marquer comme rendu
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

