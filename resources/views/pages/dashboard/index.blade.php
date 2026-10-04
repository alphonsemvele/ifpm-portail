<?php
use function Laravel\Folio\{name, middleware};

middleware(['auth', 'verified', 'role']);
?>
<x-layouts.app :header='true'>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Bienvenue à l'IUM NDAZOA</h1>
                <p class="text-gray-500">Consultez votre profil et vos notes en toute simplicité.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

            <!-- Mon Profil -->
            <a href="dashboard/compte"
               class="group bg-white rounded-2xl shadow-lg p-8 transition transform hover:-translate-y-1 hover:shadow-xl">
                <div class="w-14 h-14 bg-emerald-100 rounded-2xl flex items-center justify-center mb-5 group-hover:bg-emerald-200 transition">
                    <svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <h2 class="text-2xl font-semibold text-gray-800 mb-2">Mon Profil</h2>
                <p class="text-gray-500">Accédez à vos informations personnelles et à votre dossier académique.</p>
            </a>

            <!-- Barbillard : notes + annonces -->
            <a href="dashboard/barbillard"
               class="group bg-white rounded-2xl shadow-lg p-8 transition transform hover:-translate-y-1 hover:shadow-xl">
                <div class="w-14 h-14 bg-indigo-100 rounded-2xl flex items-center justify-center mb-5 group-hover:bg-indigo-200 transition">
                    <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                </div>
                <h2 class="text-2xl font-semibold text-gray-800 mb-2">Barbillard</h2>
                <p class="text-gray-500">Consultez vos notes et résultats par filière ainsi que les annonces officielles.</p>
            </a>

        </div>
    </div>

</x-layouts.app>
