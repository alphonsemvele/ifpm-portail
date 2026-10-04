<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\Book;
use App\Models\bookUser;

name('bibliotheque.index');
middleware(['auth', 'verified']);

new class extends Component {
    public $books;
    public $userLoans;
    public $showBorrowModal = false;
    public $showCancelModal = false;
    public $selectedBook;
    public $selectedLoan;
    public $notificationMessage = '';
    public $notificationType = '';
    public $return_date = '';

    public function mount()
    {
        \Log::info('mount appelé pour bibliotheque.index à ' . now()->toDateTimeString());
        $this->loadData();
    }

    private function loadData()
    {
        try {
            $user = auth()->user();
            \Log::info('Utilisateur chargé', ['user_id' => $user->id]);

            // Charger les livres (disponibles ou empruntés) avec leurs relations bookUsers
            $this->books = Book::whereIn('status', ['Success', 'emprunt'])
                ->with(['bookUsers' => function ($query) {
                    $query->where('status', 'pending');
                }])
                ->get();

            // Charger les emprunts actifs de l'utilisateur connecté
            $this->userLoans = bookUser::where('user_id', $user->id)
                ->where('status', 'pending')
                ->with('book')
                ->get();

            \Log::info('Données chargées', [
                'books_count' => $this->books->count(),
                'loans_count' => $this->userLoans->count(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur lors du chargement des données : ' . $e->getMessage());
            $this->notificationMessage = 'Erreur : ' . $e->getMessage();
            $this->notificationType = 'error';
            $this->dispatch('show-notification');
        }
    }

    public function openBorrowModal($bookId)
    {
        try {
            \Log::info('openBorrowModal appelé avec book ID: ' . $bookId);
            $this->selectedBook = Book::findOrFail($bookId);
            if ($this->selectedBook->status !== 'Success') {
                throw new \Exception('Ce livre n\'est pas disponible pour l\'emprunt.');
            }
            $this->return_date = \Carbon\Carbon::today()->addDays(7)->format('Y-m-d');
            $this->showBorrowModal = true;
            $this->dispatch('borrow-modal-opened', id: $bookId);
        } catch (\Exception $e) {
            \Log::error('Erreur dans openBorrowModal : ' . $e->getMessage());
            $this->notificationMessage = $e->getMessage();
            $this->notificationType = 'error';
            $this->dispatch('show-notification');
        }
    }

    public function borrowBook()
    {
        try {
            \Log::info('borrowBook appelé pour book ID: ' . ($this->selectedBook->id ?? 'inconnu'));
            $this->validate([
                'return_date' => 'required|date|after:today',
            ]);

            $user = auth()->user();
            bookUser::create([
                'book_id' => $this->selectedBook->id,
                'user_id' => $user->id,
                'return_date' => $this->return_date,
                'status' => 'pending',
            ]);

            $this->selectedBook->update(['status' => 'emprunt']);
            \Log::info('Livre emprunté avec succès', ['book_id' => $this->selectedBook->id, 'user_id' => $user->id]);
            $this->showBorrowModal = false;
            $this->selectedBook = null;
            $this->return_date = '';
            $this->loadData();
            $this->notificationMessage = 'Livre emprunté avec succès !';
            $this->notificationType = 'success';
            $this->dispatch('show-notification');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Erreur de validation dans borrowBook : ', ['errors' => $e->errors()]);
            $this->notificationMessage = 'Veuillez vérifier la date de retour.';
            $this->notificationType = 'error';
            $this->dispatch('show-notification');
        } catch (\Exception $e) {
            \Log::error('Erreur dans borrowBook : ' . $e->getMessage());
            $this->notificationMessage = 'Erreur lors de l\'emprunt : ' . $e->getMessage();
            $this->notificationType = 'error';
            $this->dispatch('show-notification');
        }
    }

    public function openCancelModal($loanId)
    {
        try {
            \Log::info('openCancelModal appelé avec loan ID: ' . $loanId);
            $this->selectedLoan = bookUser::where('user_id', auth()->user()->id)
                ->where('status', 'pending')
                ->findOrFail($loanId);
            $this->showCancelModal = true;
            $this->dispatch('cancel-modal-opened', id: $loanId);
        } catch (\Exception $e) {
            \Log::error('Erreur dans openCancelModal : ' . $e->getMessage());
            $this->notificationMessage = 'Erreur : ' . $e->getMessage();
            $this->notificationType = 'error';
            $this->dispatch('show-notification');
        }
    }

    public function cancelLoan()
    {
        try {
            \Log::info('cancelLoan appelé pour loan ID: ' . ($this->selectedLoan->id ?? 'inconnu'));
            $this->selectedLoan->update(['status' => 'Success']);
            $this->selectedLoan->book->update(['status' => 'Success']);
            \Log::info('Emprunt annulé avec succès', ['loan_id' => $this->selectedLoan->id]);
            $this->showCancelModal = false;
            $this->selectedLoan = null;
            $this->loadData();
            $this->notificationMessage = 'Emprunt annulé avec succès !';
            $this->notificationType = 'success';
            $this->dispatch('show-notification');
        } catch (\Exception $e) {
            \Log::error('Erreur dans cancelLoan : ' . $e->getMessage());
            $this->notificationMessage = 'Erreur lors de l\'annulation : ' . $e->getMessage();
            $this->notificationType = 'error';
            $this->dispatch('show-notification');
        }
    }

    public function closeModal()
    {
        \Log::info('closeModal appelé');
        $this->showBorrowModal = false;
        $this->showCancelModal = false;
        $this->selectedBook = null;
        $this->selectedLoan = null;
        $this->return_date = '';
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
                    <p class="text-gray-500">Consultez le catalogue et gérez vos emprunts de livres.</p>
                </div>
            </div>

            <!-- Notification -->
            <div x-data="{ show: false, message: '', type: '' }"
                 x-on:show-notification.window="show = true; message = $event.detail.message; type = $event.detail.type; setTimeout(() => show = false, 3000)"
                 x-show="show" class="fixed top-6 right-6 z-50 max-w-sm w-full"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-4">
                <div :class="type === 'success' ? 'bg-green-100 text-green-700 border-green-500' : 'bg-red-100 text-red-700 border-red-500'"
                     class="p-4 rounded-xl shadow-md border-l-4 animate-pulse">
                    <span x-text="message"></span>
                </div>
            </div>

            <!-- Modal Emprunter -->
            @if ($showBorrowModal)
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                    <div class="bg-white rounded-2xl p-8 w-full max-w-md shadow-2xl transform transition-all duration-300 ease-in-out">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6">Emprunter un Livre</h2>
                        <p class="mb-4 text-gray-600">Vous allez emprunter "<strong>{{ $selectedBook->name }}</strong>" de {{ $selectedBook->auteur }}.</p>
                        <form wire:submit.prevent="borrowBook">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Date de retour</label>
                                <input type="date" wire:model.defer="return_date" required class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50">
                            </div>
                            <div class="flex justify-end space-x-4">
                                <button type="submit" class="bg-indigo-600 text-white py-2 px-6 rounded-xl hover:bg-indigo-700 transition duration-300 shadow-md">
                                    Confirmer
                                </button>
                                <button wire:click="closeModal" class="bg-gray-500 text-white py-2 px-6 rounded-xl hover:bg-gray-600 transition duration-300 shadow-md">
                                    Annuler
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Modal Annuler Emprunt -->
            @if ($showCancelModal)
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl transform transition-all duration-300 ease-in-out">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Annuler l'emprunt</h3>
                        <p class="mb-4 text-gray-600">Voulez-vous annuler l'emprunt du livre "{{ $selectedLoan->book->name ?? 'N/A' }}" ?</p>
                        <div class="flex justify-end space-x-4">
                            <button wire:click="cancelLoan" class="bg-red-600 text-white py-2 px-4 rounded-xl hover:bg-red-700 transition duration-300 shadow-md">
                                Oui
                            </button>
                            <button wire:click="closeModal" class="bg-gray-500 text-white py-2 px-4 rounded-xl hover:bg-gray-600 transition duration-300 shadow-md">
                                Annuler
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Catalogue des Livres -->
            <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Catalogue des Livres</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-2">
                        <thead>
                            <tr class="bg-gray-100 rounded-lg">
                                <th class="p-4 text-sm font-medium text-gray-600 rounded-tl-lg">Titre</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Auteur</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Disponibilité</th>
                                <th class="p-4 text-sm font-medium text-gray-600 rounded-tr-lg">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($books as $book)
                                <tr class="bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-200">
                                    <td class="p-4 rounded-l-lg">{{ $book->name ?? 'Non défini' }}</td>
                                    <td class="p-4">{{ $book->auteur ?? 'Non défini' }}</td>
                                    <td class="p-4">
                                        <span class="{{ $book->status === 'Success' ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $book->status === 'Success' ? 'Disponible' : 'Emprunté' }}
                                        </span>
                                    </td>
                                    <td class="p-4 rounded-r-lg">
                                        @if ($book->status === 'Success')
                                            <button wire:click="openBorrowModal({{ $book->id }})" class="text-indigo-600 hover:underline">
                                                Emprunter
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-4 text-gray-600 text-center">Aucun livre disponible.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mes Emprunts -->
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Mes Emprunts</h2>
                <div class="space-y-4">
                    @forelse ($userLoans as $loan)
                        <div class="border-l-4 border-indigo-600 pl-4 flex justify-between items-center">
                            <p class="text-gray-800">
                                {{ $loan->book->name ?? 'Non défini' }} -
                                <span class="text-gray-600">
                                    Retour prévu le {{ \Carbon\Carbon::parse($loan->return_date)->format('d/m/Y') }}
                                </span>
                            </p>
                            <button wire:click="openCancelModal({{ $loan->id }})" class="text-red-600 hover:underline">
                                Annuler
                            </button>
                        </div>
                    @empty
                        <p class="text-gray-600">Aucun emprunt en cours.</p>
                    @endforelse
                </div>
                <div class="mt-8 flex justify-end">
                    <button class="bg-indigo-600 text-white py-2 px-6 rounded-xl hover:bg-indigo-700 transition duration-300 shadow-md">
                        Voir tous les emprunts
                    </button>
                </div>
            </div>
        </div>
    @endvolt
</x-layouts.app>
