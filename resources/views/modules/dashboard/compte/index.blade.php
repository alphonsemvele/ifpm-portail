
<?php
use function Laravel\Folio\{name,middleware};
use Livewire\Volt\Component;
name('compte.index');
middleware(['auth','verified'])
?>
<x-layouts.app>
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <header class="mb-10">
                <h1 class="text-4xl font-extrabold text-gray-800 tracking-tight">Mon Compte Étudiant</h1>
                <p class="mt-2 text-lg text-gray-500">Gérez vos informations personnelles et votre parcours académique avec une interface intuitive.</p>
            </header>

            <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Profil Personnel</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Nom</label>
                        <input type="text" value="Dupont" class="mt-1 w-full p-3 border border-gray-300 rounded-lg bg-gray-50" disabled>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Prénom</label>
                        <input type="text" value="Jean" class="mt-1 w-full p-3 border border-gray-300 rounded-lg bg-gray-50" disabled>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Email</label>
                        <input type="email" value="jean.dupont@example.com" class="mt-1 w-full p-3 border border-gray-300 rounded-lg bg-gray-50" disabled>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Numéro de téléphone</label>
                        <input type="text" value="+237 6XX XXX XXX" class="mt-1 w-full p-3 border border-gray-300 rounded-lg">
                    </div>
                </div>
                <div class="mt-8 flex justify-end">
                    <button class="bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-200">Mettre à jour le profil</button>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Dossier Académique</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Matricule</label>
                        <input type="text" value="ISM2025001" class="mt-1 w-full p-3 border border-gray-300 rounded-lg bg-gray-50" disabled>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Filière</label>
                        <input type="text" value="Informatique" class="mt-1 w-full p-3 border border-gray-300 rounded-lg bg-gray-50" disabled>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Niveau</label>
                        <input type="text" value="Licence 2" class="mt-1 w-full p-3 border border-gray-300 rounded-lg bg-gray-50" disabled>
                    </div>
                </div>
            </div>
        </div>
    @endvolt
</x-layouts.app>

