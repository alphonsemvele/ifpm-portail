<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\PaiementSalaire;

name('personnel.index');
middleware(['auth', 'verified', 'role']);

new class extends Component {
    public $user;
    public int $bulletinsCount = 0;
    public $dernierBulletin = null;

    public function mount()
    {
        $this->user = auth()->user()->load(['profilSalaire', 'categorieRh', 'echelon']);

        $this->bulletinsCount = PaiementSalaire::where('user_id', $this->user->id)->count();
        $this->dernierBulletin = PaiementSalaire::where('user_id', $this->user->id)
            ->orderByDesc('annee')->orderByDesc('mois')->first();
    }
};
?>

<x-layouts.app header="true">
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Espace Personnel</h1>
                    <p class="text-gray-500">Bonjour {{ $user->name }}, gérez votre profil et vos bulletins de paie.</p>
                </div>
            </div>

            {{-- Résumé --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-10">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Poste</p>
                    <p class="mt-1 text-lg font-bold text-gray-800">{{ $user->poste ? \Illuminate\Support\Str::of($user->poste)->replace('_',' ')->title() : '—' }}</p>
                    <p class="text-sm text-gray-400">{{ $user->entite ?? '' }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Bulletins disponibles</p>
                    <p class="mt-1 text-3xl font-bold text-emerald-600">{{ $bulletinsCount }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Dernier bulletin</p>
                    <p class="mt-1 text-lg font-bold text-gray-800">
                        {{ $dernierBulletin ? $dernierBulletin->mois_nom . ' ' . $dernierBulletin->annee : '—' }}
                    </p>
                </div>
            </div>

            {{-- Cartes d'action --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <a href="/personnel/bulletins" class="group bg-white rounded-xl shadow-lg p-8 transition transform hover:-translate-y-1 hover:shadow-xl">
                    <div class="w-14 h-14 bg-indigo-100 rounded-2xl flex items-center justify-center mb-5 group-hover:bg-indigo-200 transition">
                        <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-800 mb-2">Mon Bulletin de Paie</h2>
                    <p class="text-gray-500">Téléchargez vos fiches de paie par mois ou par période, en PDF.</p>
                </a>

                <a href="/personnel/profil" class="group bg-white rounded-xl shadow-lg p-8 transition transform hover:-translate-y-1 hover:shadow-xl">
                    <div class="w-14 h-14 bg-emerald-100 rounded-2xl flex items-center justify-center mb-5 group-hover:bg-emerald-200 transition">
                        <svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-800 mb-2">Mon Profil</h2>
                    <p class="text-gray-500">Consultez vos informations personnelles et administratives.</p>
                </a>

                @if ($user->hasFiliereModule())
                    <a href="/filiere" class="group bg-white rounded-xl shadow-lg p-8 transition transform hover:-translate-y-1 hover:shadow-xl">
                        <div class="w-14 h-14 bg-amber-100 rounded-2xl flex items-center justify-center mb-5 group-hover:bg-amber-200 transition">
                            <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <h2 class="text-xl font-semibold text-gray-800 mb-2">Ma Filière / Coordination</h2>
                        <p class="text-gray-500">Gérez votre filière, vos apprenants et le suivi pédagogique.</p>
                    </a>
                @endif

                @if ($user->hasSpecialiteModule())
                    <a href="/specialite" class="group bg-white rounded-xl shadow-lg p-8 transition transform hover:-translate-y-1 hover:shadow-xl">
                        <div class="w-14 h-14 bg-sky-100 rounded-2xl flex items-center justify-center mb-5 group-hover:bg-sky-200 transition">
                            <svg class="w-7 h-7 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.747 0-3.332.477-4.5 1.253"/></svg>
                        </div>
                        <h2 class="text-xl font-semibold text-gray-800 mb-2">Ma Spécialité / Mes Cours</h2>
                        <p class="text-gray-500">Accédez à vos spécialités, cours et notes des apprenants.</p>
                    </a>
                @endif
            </div>
        </div>
    @endvolt
</x-layouts.app>
