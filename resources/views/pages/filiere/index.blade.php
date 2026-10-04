<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Specialite;

name('filiere.index');
middleware(['auth', 'verified','role']);

new class extends Component {
    public $filiere;
    public $user;
    public $totalEtudiants = 0;
    public $totalSpecialites = 0;
    public $totalPersonnel = 0;
    public $hasFiliere = false;

    public function mount()
    {
        $this->user = auth()->user();

        if ($this->user->filiere_id && $this->user->filiere) {
            $this->hasFiliere = true;
            $this->filiere = $this->user->filiere;

            if ($this->filiere) {
                // Apprenants
                $this->totalEtudiants = User::where('filiere_id', $this->filiere->id)
                    ->where('role', 'student')
                    ->where('status', 'Success')
                    ->count();

                // Spécialités actives
                $this->totalSpecialites = Specialite::where('filiere_id', $this->filiere->id)
                    ->where('status', 'Success')
                    ->count();

                // Personnel (enseignants + coordinateurs)
                $this->totalPersonnel = User::where('filiere_id', $this->filiere->id)
                    ->whereIn('role', ['enseignant', 'coordinateur', 'teacher'])
                    ->where('status', 'Success')
                    ->count();
            }
        }
    }
};
?>

<x-layouts.app header="true">
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            @if(!$hasFiliere)
                <!-- Message si aucune filière n'est attribuée -->
                <div class="min-h-[60vh] flex items-center justify-center">
                    <div class="bg-white rounded-2xl shadow-xl p-10 max-w-lg text-center">
                        <div class="mx-auto w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mb-6">
                            <svg class="w-10 h-10 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800 mb-4">Aucune filière attribuée</h2>
                        <p class="text-gray-600 mb-6">Aucune filière ne vous a encore été attribuée. Veuillez patienter pendant que l'administration configure votre compte.</p>
                        <div class="bg-gray-50 rounded-xl p-4 mb-6">
                            <p class="text-sm text-gray-500">En attendant, vous pouvez consulter votre profil ou contacter l'administration.</p>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-3 justify-center">
                            <a href="/filiere/profil" class="inline-flex items-center justify-center px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Voir mon profil
                            </a>
                            @if(config('etablissement.email'))
                            <a href="mailto:{{ config('etablissement.email') }}" class="inline-flex items-center justify-center px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                                Contacter le support
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <!-- Header avec info filière -->
                <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">{{ $filiere->name ?? 'Gestion de Filière' }}</h1>
                        <p class="text-gray-500">Administrez les spécialités de votre filière et supervisez le personnel avec efficacité.</p>
                    </div>
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div>
                            
                            
                        </div>
                        <a href="/filiere/profil" class="mt-4 md:mt-0 inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 transition duration-200">
                            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Mon Profil
                        </a>
                    </div>
                </div>

                <!-- Carte Profil Rapide -->
                <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl shadow-xl p-6 mb-8 text-black">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center">
                                @if($user->photo)
                                    <img src="{{ asset('storage/' . $user->photo) }}" alt="Photo" class="w-14 h-14 rounded-full object-cover">
                                @else
                                    <span class="text-2xl font-bold">{{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr($user->lastname ?? '', 0, 1)) }}</span>
                                @endif
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold">{{ $user->name }} {{ $user->lastname ?? '' }}</h3>
                                <p class="text-indigo-100">{{ ucfirst($user->role) }} - {{ $filiere->name }}</p>
                                <p class="text-indigo-200 text-sm">{{ $user->email }}</p>
                            </div>
                        </div>
                        <div class="mt-4 md:mt-0 flex space-x-3">
                            <a href="/filiere/profil" class="px-4 py-2 bg-white/20 rounded-lg hover:bg-white/30 transition duration-200 text-sm">
                                Modifier le profil
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Statistiques -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-indigo-50 rounded-xl shadow-lg p-6 text-center">
                        <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">Apprenants Inscrits</h3>
                        <p class="text-3xl font-bold text-indigo-600">{{ $totalEtudiants }}</p>
                        <p class="text-sm text-gray-500">Total dans la filière</p>
                    </div>
                    <div class="bg-indigo-50 rounded-xl shadow-lg p-6 text-center">
                        <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">Spécialités Actives</h3>
                        <p class="text-3xl font-bold text-indigo-600">{{ $totalSpecialites }}</p>
                        <p class="text-sm text-gray-500">Dans la filière</p>
                    </div>
                    <div class="bg-indigo-50 rounded-xl shadow-lg p-6 text-center">
                        <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">Membres du Personnel</h3>
                        <p class="text-3xl font-bold text-indigo-600">{{ $totalPersonnel }}</p>
                        <p class="text-sm text-gray-500">Enseignants et coordinateurs</p>
                    </div>
                </div>

                <!-- Sous-modules (menu) -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <a href="/filiere/bulletins" class="bg-white rounded-xl shadow-lg p-6 text-center transition transform hover:scale-105 hover:shadow-xl block">
    <div class="w-14 h-14 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
    </div>
    <h2 class="text-xl font-semibold text-gray-800 mb-2">Bulletins de Paie</h2>
    <p class="text-gray-600 text-sm">Consultez vos bulletins de salaire mensuels.</p>
</a>
                    <a href="/filiere/specialite/index" class="bg-white rounded-xl shadow-lg p-6 text-center transition transform hover:scale-105 hover:shadow-xl block">
                        <div class="w-14 h-14 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <h2 class="text-xl font-semibold text-gray-800 mb-2">Spécialités</h2>
                        <p class="text-gray-600 text-sm">Gérez les spécialités de la filière, ajoutez ou modifiez des programmes.</p>
                    </a>

                    <a href="/filiere/personnel/index" class="bg-white rounded-xl shadow-lg p-6 text-center transition transform hover:scale-105 hover:shadow-xl block">
                        <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <h2 class="text-xl font-semibold text-gray-800 mb-2">Personnel</h2>
                        <p class="text-gray-600 text-sm">Supervisez les enseignants et coordinateurs affectés aux spécialités.</p>
                    </a>

                    <a href="/filiere/ue/index" class="bg-white rounded-xl shadow-lg p-6 text-center transition transform hover:scale-105 hover:shadow-xl block">
                        <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <h2 class="text-xl font-semibold text-gray-800 mb-2">UE</h2>
                        <p class="text-gray-600 text-sm">Gérez les unités d’enseignement à chaque spécialité.</p>
                    </a>

                    <a href="/filiere/cours/index" class="bg-white rounded-xl shadow-lg p-6 text-center transition transform hover:scale-105 hover:shadow-xl block">
                        <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <h2 class="text-xl font-semibold text-gray-800 mb-2">Cours</h2>
                        <p class="text-gray-600 text-sm">Gérez les cours associés à chaque UE.</p>
                    </a>

                    <a href="/filiere/examens" class="bg-white rounded-xl shadow-lg p-6 text-center transition transform hover:scale-105 hover:shadow-xl block">
                        <div class="w-14 h-14 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                        </div>
                        <h2 class="text-xl font-semibold text-gray-800 mb-2">Examens</h2>
                        <p class="text-gray-600 text-sm">Attribuez et gérez les notes CC et examen.</p>
                    </a>

                    <a href="/filiere/etudiant/index" class="bg-white rounded-xl shadow-lg p-6 text-center transition transform hover:scale-105 hover:shadow-xl block">
                        <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <h2 class="text-xl font-semibold text-gray-800 mb-2">Liste des Apprenants</h2>
                        <p class="text-gray-600 text-sm">Consultez les informations des apprenants par spécialité.</p>
                    </a>

                    <a href="/filiere/rapport" class="bg-white rounded-xl shadow-lg p-6 text-center transition transform hover:scale-105 hover:shadow-xl block">
                        <div class="w-14 h-14 bg-teal-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-7 h-7 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h2 class="text-xl font-semibold text-gray-800 mb-2">Rapports</h2>
                        <p class="text-gray-600 text-sm">Soumettez et consultez vos rapports semestriels.</p>
                    </a>

                    <a href="/filiere/profil" class="bg-white rounded-xl shadow-lg p-6 text-center transition transform hover:scale-105 hover:shadow-xl block">
                        <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-7 h-7 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <h2 class="text-xl font-semibold text-gray-800 mb-2">Mon Profil</h2>
                        <p class="text-gray-600 text-sm">Consultez et modifiez vos informations personnelles.</p>
                    </a>
                </div>
            @endif
        </div>
    @endvolt
</x-layouts.app>