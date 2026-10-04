<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;

name('personnel.profil');
middleware(['auth', 'verified', 'role']);

new class extends Component {
    public $user;

    public function mount()
    {
        $this->user = auth()->user()->load(['profilSalaire', 'categorieRh', 'echelon', 'section']);
    }
};
?>

<x-layouts.app header="true">
    @volt
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Mon Profil</h1>
                    <p class="text-gray-500">Vos informations personnelles et administratives.</p>
                </div>
                <a href="/personnel" class="text-gray-400 hover:text-emerald-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div>
                    
                    
                </div>
            </div>

            {{-- En-tête profil --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 mb-6 flex items-center gap-6">
                <div class="w-20 h-20 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 text-2xl font-bold flex-shrink-0">
                    {{ strtoupper(substr($user->name ?? 'P', 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">{{ $user->name }} {{ $user->lastname }}</h2>
                    <p class="text-gray-500">{{ $user->poste ? \Illuminate\Support\Str::of($user->poste)->replace('_',' ')->title() : ucfirst($user->role) }}</p>
                    <span class="inline-block mt-1 px-2.5 py-0.5 rounded-full text-xs font-mono bg-gray-100 text-gray-600">{{ $user->matricule ?? '—' }}</span>
                </div>
            </div>

            {{-- Coordonnées --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-6">Coordonnées</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @php
                        $fields = [
                            'Email' => $user->email,
                            'Téléphone' => $user->contact,
                            'WhatsApp' => $user->whatsapp,
                            'Entité' => $user->entite,
                            'Section' => $user->section->name ?? null,
                            'Statut du compte' => $user->status,
                        ];
                    @endphp
                    @foreach ($fields as $label => $value)
                        <div>
                            <label class="block text-sm font-medium text-gray-500">{{ $label }}</label>
                            <input type="text" value="{{ $value ?? 'Non défini' }}" disabled
                                   class="mt-1 w-full p-3 border border-gray-200 rounded-lg bg-gray-50 text-gray-700">
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Informations RH --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-6">Informations RH &amp; Paie</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Profil salaire</label>
                        <input type="text" value="{{ $user->profilSalaire->nom ?? 'Non défini' }}" disabled
                               class="mt-1 w-full p-3 border border-gray-200 rounded-lg bg-gray-50 text-gray-700">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Catégorie</label>
                        <input type="text" value="{{ $user->categorieRh->libelle ?? 'Non défini' }}" disabled
                               class="mt-1 w-full p-3 border border-gray-200 rounded-lg bg-gray-50 text-gray-700">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Échelon</label>
                        <input type="text" value="{{ $user->echelon->libelle ?? 'Non défini' }}" disabled
                               class="mt-1 w-full p-3 border border-gray-200 rounded-lg bg-gray-50 text-gray-700">
                    </div>
                </div>
                <p class="mt-6 text-sm text-gray-400">
                    Pour toute modification de vos informations, veuillez contacter le service des Ressources Humaines.
                </p>
            </div>
        </div>
    @endvolt
</x-layouts.app>
