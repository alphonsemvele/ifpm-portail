<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\PaiementSalaire;
use App\Models\User;

name('finance.index');
middleware(['auth', 'verified', 'role']);

new class extends Component {
    public $user;
    public int $effectif = 0;
    public float $masseSalariale = 0;
    public int $enAttente = 0;

    public function mount()
    {
        $this->user = auth()->user();

        $this->effectif = User::whereNotNull('profil_salaire_id')->count();

        $dernier = PaiementSalaire::orderByDesc('annee')->orderByDesc('mois')->first();
        if ($dernier) {
            $this->masseSalariale = PaiementSalaire::where('mois', $dernier->mois)
                ->where('annee', $dernier->annee)->sum('salaire_net');
        }
        $this->enAttente = PaiementSalaire::where('statut', 'en_attente')->count();
    }
};
?>

<x-layouts.app header="true">
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Espace Finance &amp; RH</h1>
                    <p class="text-gray-500">Bienvenue {{ $user->name }} — gestion des salaires, profils et bulletins.</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center shadow-lg" style="background:#059669;">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        
                        
                    </div>
                </div>
            </div>

            {{-- KPIs --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-10">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Effectif configuré</p>
                    <p class="mt-1 text-3xl font-bold text-gray-800">{{ $effectif }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Masse salariale (dernier mois)</p>
                    <p class="mt-1 text-3xl font-bold text-emerald-600">{{ number_format($masseSalariale, 0, ',', ' ') }} <span class="text-base text-gray-400">FCFA</span></p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Bulletins en attente</p>
                    <p class="mt-1 text-3xl font-bold text-amber-500">{{ $enAttente }}</p>
                </div>
            </div>

            {{-- Modules RH & Paie --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-5">
                @php
                    $modules = [
                        ['/admin/rh/categories',    'Catégories',  'Échelons & grades',   'emerald', 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z'],
                        ['/admin/rh/indemnites',    'Indemnités',  'Éléments positifs',   'green',   'M12 6v6m0 0v6m0-6h6m-6 0H6'],
                        ['/admin/rh/retenues',      'Retenues',    'Éléments négatifs',   'red',     'M20 12H4'],
                        ['/admin/rh/profils',       'Profils',     'Fiches de paie',      'sky',     'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z'],
                        ['/admin/bulletins-paie',   'Bulletins',   'Édition & export PDF','indigo',  'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                        ['/admin/paie',             'Payer',       'Virements du mois',   'amber',   'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ];
                @endphp
                @foreach ($modules as [$href, $title, $sub, $color, $path])
                    <a href="{{ $href }}" class="group bg-white rounded-xl shadow-md p-5 text-center transition transform hover:-translate-y-1 hover:shadow-lg border-t-4 border-{{ $color }}-500">
                        <div class="flex justify-center mb-3">
                            <div class="w-12 h-12 bg-{{ $color }}-100 rounded-full flex items-center justify-center group-hover:bg-{{ $color }}-200 transition">
                                <svg class="w-6 h-6 text-{{ $color }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $path }}"/></svg>
                            </div>
                        </div>
                        <h3 class="font-semibold text-gray-800 text-sm">{{ $title }}</h3>
                        <p class="text-xs text-gray-500 mt-1">{{ $sub }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    @endvolt
</x-layouts.app>
