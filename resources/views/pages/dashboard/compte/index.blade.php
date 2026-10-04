
<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;

name('compte.index');
middleware(['auth', 'verified']);

new class extends Component {
    public $user;

    public function mount()
    {
        \Log::info('mount appelé pour compte.index');
        $this->user = auth()->user()->load([
            'filiere',
            'specialite',
            'cycle',
            'region',
            'department',
            'arrondissement'
        ]);
        \Log::info('Utilisateur chargé', [
            'user_id' => $this->user->id,
            'name' => $this->user->name,
            'relations' => [
                'filiere' => $this->user->filiere ? $this->user->filiere->name : null,
                'specialite' => $this->user->specialite ? $this->user->specialite->name : null,
                'cycle' => $this->user->cycle ? $this->user->cycle->name : null,
            ]
        ]);
    }
};
?>

<x-layouts.app header="true">
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Mon Compte Apprenant</h1>
                    <p class="text-gray-500">Consultez vos informations personnelles et votre parcours académique.</p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Profil Personnel</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Nom</label>
                        <input type="text" value="{{ $user->lastname ?? 'Non défini' }}" class="mt-1 w-full p-3 border border-gray-300 rounded-lg bg-gray-50" disabled>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Prénom</label>
                        <input type="text" value="{{ $user->name ?? 'Non défini' }}" class="mt-1 w-full p-3 border border-gray-300 rounded-lg bg-gray-50" disabled>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Email</label>
                        <input type="email" value="{{ $user->email ?? 'Non défini' }}" class="mt-1 w-full p-3 border border-gray-300 rounded-lg bg-gray-50" disabled>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Numéro de téléphone</label>
                        <input type="text" value="{{ $user->contact ?? 'Non défini' }}" class="mt-1 w-full p-3 border border-gray-300 rounded-lg bg-gray-50" disabled>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">WhatsApp</label>
                        <input type="text" value="{{ $user->whatsapp ?? 'Non défini' }}" class="mt-1 w-full p-3 border border-gray-300 rounded-lg bg-gray-50" disabled>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Rôle</label>
                        <input type="text" value="{{ $user->role ?? 'Non défini' }}" class="mt-1 w-full p-3 border border-gray-300 rounded-lg bg-gray-50" disabled>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Région</label>
                        <input type="text" value="{{ $user->region->name ?? 'Non défini' }}" class="mt-1 w-full p-3 border border-gray-300 rounded-lg bg-gray-50" disabled>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Département</label>
                        <input type="text" value="{{ $user->department->name ?? 'Non défini' }}" class="mt-1 w-full p-3 border border-gray-300 rounded-lg bg-gray-50" disabled>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Arrondissement</label>
                        <input type="text" value="{{ $user->arrondissement->name ?? 'Non défini' }}" class="mt-1 w-full p-3 border border-gray-300 rounded-lg bg-gray-50" disabled>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Dossier Académique</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Matricule</label>
                        <input type="text" value="{{ $user->matricule ?? 'Non défini' }}" class="mt-1 w-full p-3 border border-gray-300 rounded-lg bg-gray-50" disabled>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Filière</label>
                        <input type="text" value="{{ $user->filiere->name ?? 'Non défini' }}" class="mt-1 w-full p-3 border border-gray-300 rounded-lg bg-gray-50" disabled>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Spécialité</label>
                        <input type="text" value="{{ $user->specialite->name ?? 'Non défini' }}" class="mt-1 w-full p-3 border border-gray-300 rounded-lg bg-gray-50" disabled>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Cycle/Niveau</label>
                        <input type="text" value="{{ $user->cycle->name ?? 'Non défini' }}" class="mt-1 w-full p-3 border border-gray-300 rounded-lg bg-gray-50" disabled>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Statut</label>
                        <input type="text" value="{{ $user->status ?? 'Non défini' }}" class="mt-1 w-full p-3 border border-gray-300 rounded-lg bg-gray-50" disabled>
                    </div>
                </div>
            </div>
        </div>
    @endvolt
</x-layouts.app>

