<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\Filiere;
use App\Models\User;
use App\Models\Cours;

name('filiere.index');
middleware(['auth', 'verified']);

new class extends Component {
    public $filiere;
    public $responsable;
    public $nombreEtudiants;
    public $modules = [];
    public $errorMessage;

    public function mount()
    {
        \Log::info('mount appelé pour filiere.index à ' . now()->toDateTimeString());
        try {
            $user = auth()->user();
            \Log::info('Utilisateur chargé', [
                'user_id' => $user->id,
                'filiere_id' => $user->filiere_id,
            ]);

            // Récupérer la filière de l'utilisateur
            $this->filiere = $user->filiere()->first();
            if (!$this->filiere) {
                throw new \Exception('Aucune filière trouvée pour cet utilisateur.');
            }

            // Récupérer le responsable (utilisateur avec role = 'filiere')
            $this->responsable = User::where('role', 'filiere')
                ->where('filiere_id', $this->filiere->id)
                ->first();

            // Compter le nombre d'apprenants
            $this->nombreEtudiants = User::where('filiere_id', $this->filiere->id)
                ->where('role', 'etudiant')
                ->count();

            // Récupérer les modules (cours) de la filière
            $this->modules = Cours::where('filiere_id', $this->filiere->id)
                ->where('status', 'Success')
                ->get();

            \Log::info('Données de la filière chargées', [
                'filiere_id' => $this->filiere->id,
                'modules_count' => $this->modules->count(),
                'etudiants_count' => $this->nombreEtudiants,
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur lors du chargement des données de la filière : ' . $e->getMessage());
            $this->errorMessage = 'Erreur : ' . $e->getMessage();
            $this->dispatch('show-notification', message: $this->errorMessage, type: 'error');
        }
    }
};
?>

<x-layouts.app header="true">
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Gestion des Filières</h1>
                    <p class="text-gray-500">Explorez les détails de votre filière et ses modules associés.</p>
                </div>
            </div>

            <!-- Notification -->
            <div x-data="{ showNotification: false, notificationMessage: '', notificationType: '' }"
                 x-on:show-notification.window="showNotification = true; notificationMessage = $event.detail.message; notificationType = $event.detail.type; setTimeout(() => showNotification = false, 3000)">
                <div x-show="showNotification" class="fixed top-6 right-6 z-50 max-w-sm w-full"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 translate-y-4">
                    <div :class="notificationType === 'success' ? 'bg-green-100 text-green-700 border-green-500' : 'bg-red-100 text-red-700 border-red-500'"
                         class="p-4 rounded-xl shadow-md border-l-4">
                        <span x-text="notificationMessage"></span>
                    </div>
                </div>
            </div>

            <!-- Détails de la Filière -->
            <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Détails de la Filière</h2>
                @if ($filiere)
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Nom de la Filière</label>
                            <input type="text" value="{{ $filiere->name }}" class="mt-1 w-full p-3 border border-gray-300 rounded-lg bg-gray-50" disabled>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Responsable</label>
                            <input type="text" value="{{ $responsable ? $responsable->name . ' ' . $responsable->lastname : 'Non assigné' }}" class="mt-1 w-full p-3 border border-gray-300 rounded-lg bg-gray-50" disabled>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Nombre d'apprenants</label>
                            <input type="text" value="{{ $nombreEtudiants }}" class="mt-1 w-full p-3 border border-gray-300 rounded-lg bg-gray-50" disabled>
                        </div>
                    </div>
                @else
                    <p class="text-red-600">Aucune filière trouvée pour cet utilisateur.</p>
                @endif
            </div>

            <!-- Modules de la Filière -->
            <div class="bg-white rounded-xl shadow-lg p-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Modules de la Filière</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-2">
                        <thead>
                            <tr class="bg-gray-100 rounded-lg">
                                <th class="p-4 text-sm font-medium text-gray-600 rounded-tl-lg">Module</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Crédits</th>
                                <th class="p-4 text-sm font-medium text-gray-600 rounded-tr-lg">Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($modules as $module)
                                <tr class="bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-200">
                                    <td class="p-4 rounded-l-lg">{{ $module->name ?? 'Non défini' }}</td>
                                    <td class="p-4">{{ $module->credit ?? 'Non défini' }}</td>
                                    <td class="p-4 rounded-r-lg">{{ $module->status ?? 'Non défini' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="p-4 text-gray-600 text-center">Aucun module trouvé pour cette filière.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endvolt
</x-layouts.app>
