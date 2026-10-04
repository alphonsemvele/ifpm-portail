<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\Support;

name('support-assistance.index');
middleware(['auth', 'verified']);

new class extends Component {
    public $supports;
    public $subject = '';
    public $description = '';
    public $showNotification = false;
    public $notificationMessage = '';
    public $notificationType = '';
    public $formErrors = [];

    public function mount()
    {
        \Log::info('mount appelé pour support-assistance.index à ' . now()->toDateTimeString());
        $this->loadData();
    }

    private function loadData()
    {
        try {
            $user = auth()->user();
            \Log::info('Utilisateur chargé', ['user_id' => $user->id]);

            // Charger les tickets de l'utilisateur connecté
            $this->supports = Support::where('user_id', $user->id)->get();
            \Log::info('Tickets chargés', ['supports_count' => $this->supports->count()]);
        } catch (\Exception $e) {
            \Log::error('Erreur lors du chargement des tickets : ' . $e->getMessage());
            $this->supports = collect();
            $this->showNotification('Erreur lors du chargement des tickets : ' . $e->getMessage(), 'error');
        }
    }

    public function submitTicket()
    {
        \Log::info('submitTicket appelé', [
            'subject' => $this->subject,
            'description' => $this->description,
        ]);

        try {
            $this->validate([
                'subject' => 'required|string|min:3|max:255',
                'description' => 'required|string|min:10',
            ]);

            $user = auth()->user();
            // Générer un code unique
            $lastSupport = Support::latest('id')->first();
            $nextId = $lastSupport ? $lastSupport->id + 1 : 1;
            $code = 'TCK-' . now()->format('Y') . '-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

            // Vérifier l'unicité du code
            while (Support::where('code', $code)->exists()) {
                $nextId++;
                $code = 'TCK-' . now()->format('Y') . '-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
            }

            Support::create([
                'user_id' => $user->id,
                'description' => $this->description,
                'status' => 'pending',
                'code' => $code,
            ]);

            \Log::info('Ticket créé avec succès', ['user_id' => $user->id, 'code' => $code]);
            $this->reset(['subject', 'description']);
            $this->loadData();
            $this->showNotification('Ticket soumis avec succès ! Référence : ' . $code, 'success');
            $this->formErrors = [];
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Erreur de validation dans submitTicket : ', $e->errors());
            $this->formErrors = $e->errors();
            $this->showNotification('Veuillez corriger les erreurs dans le formulaire.', 'error');
        } catch (\Exception $e) {
            \Log::error('Erreur dans submitTicket : ' . $e->getMessage());
            $this->showNotification('Erreur lors de la soumission du ticket : ' . $e->getMessage(), 'error');
        }
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
                    <h1 class="text-3xl font-bold text-gray-800">Support & Assistance</h1>
                    <p class="text-gray-500">Obtenez de l'aide pour vos questions administratives et techniques.</p>
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

            <!-- Mes Tickets -->
            <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Mes Tickets</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-2">
                        <thead>
                            <tr class="bg-gray-100 rounded-lg">
                                <th class="p-4 text-sm font-medium text-gray-600">Référence</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Sujet</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Date</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($supports as $support)
                                <tr class="bg-gray-50 rounded-lg">
                                    <td class="p-4">{{ $support->code ?? 'Non défini' }}</td>
                                    <td class="p-4">{{ $support->description ?? 'Non défini' }}</td>
                                    <td class="p-4">{{ \Carbon\Carbon::parse($support->created_at)->format('d/m/Y') }}</td>
                                    <td class="p-4 {{ $support->status === 'Success' ? 'text-green-600' : ($support->status === 'pending' ? 'text-yellow-600' : 'text-red-600') }}">
                                        {{ $support->status === 'Success' ? 'Résolu' : ($support->status === 'pending' ? 'En attente' : 'Annulé') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-4 text-gray-600 text-center">Aucun ticket soumis.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-8 flex justify-end">
                    <button class="bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-200">Voir tous les tickets</button>
                </div>
            </div>

            <!-- Nouveau Ticket -->
            <div class="bg-white rounded-xl shadow-lg p-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Nouveau Ticket</h2>
                @if (!empty($formErrors))
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
                <form wire:submit.prevent="submitTicket">
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Sujet</label>
                            <input type="text" wire:model.defer="subject" class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
                            @error('subject') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Description</label>
                            <textarea wire:model.defer="description" class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" rows="4" required></textarea>
                            @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="mt-8 flex justify-end">
                        <button type="submit" class="bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-200">Soumettre le ticket</button>
                    </div>
                </form>
            </div>
        </div>
    @endvolt
</x-layouts.app>
