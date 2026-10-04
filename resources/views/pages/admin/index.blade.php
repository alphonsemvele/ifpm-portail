<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\Filiere;
use App\Models\PaiementSalaire;
use App\Models\Preinscription;
use App\Models\Specialite;
use App\Models\Support;
use App\Models\User;

middleware(['auth', 'verified', 'role']);

new class extends Component {
    public function with(): array
    {
        $etudiantsParFiliere = User::where('role', 'student')->where('status', 'Success')
            ->selectRaw('filiere_id, count(*) as n')->groupBy('filiere_id')->pluck('n', 'filiere_id');

        return [
            'etudiants' => User::where('role', 'student')->where('status', 'Success')->count(),
            'etudiantsEnAttente' => User::where('role', 'student')->where('status', 'pending')->count(),
            'personnel' => User::whereNotIn('role', ['student', 'etudiant'])->where('status', 'Success')->count(),
            'filieres' => Filiere::where('status', 'Success')->count(),
            'specialites' => Specialite::where('status', 'Success')->count(),
            'preinscriptions' => Preinscription::where('status', 'pending')->count(),
            'supports' => Support::where('status', 'pending')->count(),
            'paieDuMois' => PaiementSalaire::where('mois', now()->month)->where('annee', now()->year)->count(),
            'effectifs' => Filiere::where('status', 'Success')->orderBy('name')->get(['id', 'name'])
                ->map(fn ($f) => ['nom' => $f->name, 'effectif' => (int) ($etudiantsParFiliere[$f->id] ?? 0)]),
            'dernieresPreinscriptions' => Preinscription::latest()->limit(6)->get(),
        ];
    }
};
?>

<x-layouts.app title="Tableau de bord">
    @volt
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Bonjour {{ auth()->user()->name }}</h1>
                <p class="text-gray-500">{{ config('etablissement.nom') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="/admin/etudiant" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-800 text-white rounded-lg text-sm font-medium hover:bg-blue-900">Apprenants</a>
                <a href="/admin/preinscription" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">Candidatures</a>
                {{-- La paie est passée au portail : module « Personnel & paie ». --}}
                <a href="{{ rtrim(config('portail.url'), '/') }}/personnel" target="_blank" rel="noopener" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">
                    Personnel &amp; paie
                    <svg class="h-3.5 w-3.5 opacity-60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M21 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5"/></svg>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <a href="/admin/etudiant" class="bg-white rounded-xl shadow p-4 hover:ring-1 hover:ring-blue-300">
                <p class="text-xs uppercase text-gray-500">Apprenants inscrits</p>
                <p class="text-3xl font-bold text-blue-800">{{ $etudiants }}</p>
                <p class="text-xs text-gray-500">{{ $etudiantsEnAttente }} en attente de validation</p>
            </a>
            <a href="/admin/filiere" class="bg-white rounded-xl shadow p-4 hover:ring-1 hover:ring-blue-300">
                <p class="text-xs uppercase text-gray-500">Filières actives</p>
                <p class="text-3xl font-bold text-gray-800">{{ $filieres }}</p>
                <p class="text-xs text-gray-500">{{ $specialites }} spécialité(s)</p>
            </a>
            <a href="/admin/personnel" class="bg-white rounded-xl shadow p-4 hover:ring-1 hover:ring-blue-300">
                <p class="text-xs uppercase text-gray-500">Personnel actif</p>
                <p class="text-3xl font-bold text-gray-800">{{ $personnel }}</p>
                <p class="text-xs text-gray-500">{{ $paieDuMois }} paie(s) ce mois-ci</p>
            </a>
            <a href="/admin/preinscription" class="bg-white rounded-xl shadow p-4 hover:ring-1 hover:ring-blue-300">
                <p class="text-xs uppercase text-gray-500">À traiter</p>
                <p class="text-3xl font-bold {{ $preinscriptions + $supports ? 'text-amber-600' : 'text-gray-800' }}">{{ $preinscriptions + $supports }}</p>
                <p class="text-xs text-gray-500">{{ $preinscriptions }} candidature(s) · {{ $supports }} support</p>
            </a>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <section class="xl:col-span-2 bg-white rounded-xl shadow p-4 sm:p-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-base font-semibold text-gray-800">Apprenants par filière</h2>
                    <a href="/admin/etudiant" class="text-sm text-blue-800 hover:underline">Voir les apprenants</a>
                </div>
                <div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm" data-style-compact data-export-titre="Apprenants par filière">
                            <thead><tr><th>Filière</th><th class="text-right">Apprenants</th></tr></thead>
                            <tbody>
                                @forelse ($effectifs as $ligne)
                                    <tr>
                                        <td>{{ $ligne['nom'] }}</td>
                                        <td class="text-right font-medium {{ $ligne['effectif'] ? 'text-gray-900' : 'text-gray-300' }}">{{ $ligne['effectif'] }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="text-center text-gray-500">Aucune filière active.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section class="bg-white rounded-xl shadow p-4 sm:p-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-base font-semibold text-gray-800">Dernières candidatures</h2>
                    <a href="/admin/preinscription" class="text-sm text-blue-800 hover:underline">Tout voir</a>
                </div>
                <div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm" data-style-compact data-export-titre="Dernières candidatures">
                            <thead><tr><th>Candidat</th><th>Statut</th></tr></thead>
                            <tbody>
                                @forelse ($dernieresPreinscriptions as $p)
                                    <tr>
                                        <td><span class="block font-medium">{{ trim($p->name.' '.$p->last_name) }}</span><span class="text-xs text-gray-500">{{ $p->ref }}</span></td>
                                        <td>
                                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $p->status === 'Success' ? 'bg-emerald-100 text-blue-800' : ($p->status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                                                {{ ['Success' => 'Validée', 'failed' => 'Rejetée'][$p->status] ?? 'En attente' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="text-center text-gray-500">Aucune préinscription.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>

        <h2 class="mt-8 mb-3 text-base font-semibold text-gray-800">Accès rapides</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach (config('navigation.admin') as $entree)
                @continue(! isset($entree['liens']))
                <div class="bg-white rounded-xl shadow p-4">
                    <p class="text-xs uppercase text-gray-500 mb-2">{{ $entree['groupe'] }}</p>
                    <ul class="grid grid-cols-2 gap-x-3 gap-y-1 text-sm">
                        @foreach ($entree['liens'] as $lien)
                            <li><a href="{{ $lien['lien'] }}" class="text-blue-900 hover:underline">{{ $lien['titre'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
    @endvolt
</x-layouts.app>
