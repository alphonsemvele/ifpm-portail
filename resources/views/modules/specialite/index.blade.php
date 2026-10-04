
<?php
use function Laravel\Folio\{name,middleware};
use Livewire\Volt\Component;
name('specialite.index');
middleware(['auth','verified'])
?>
<x-layouts.app>
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <header class="mb-10">
                <h1 class="text-4xl font-extrabold text-gray-800 tracking-tight">Administration de la Spécialité</h1>
                <p class="mt-2 text-lg text-gray-500">Gérez votre spécialité : Développement Logiciel</p>
            </header>

            <!-- Statistiques -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-indigo-50 rounded-xl shadow-lg p-6 text-center">
                    <h3 class="text-lg font-semibold text-gray-800">Étudiants Inscrits</h3>
                    <p class="text-3xl font-bold text-indigo-600">120</p>
                    <p class="text-sm text-gray-500">Dans la spécialité</p>
                </div>
                <div class="bg-indigo-50 rounded-xl shadow-lg p-6 text-center">
                    <h3 class="text-lg font-semibold text-gray-800">Taux de Réussite</h3>
                    <p class="text-3xl font-bold text-indigo-600">88%</p>
                    <p class="text-sm text-gray-500">Semestre 1, 2025</p>
                </div>
                <div class="bg-indigo-50 rounded-xl shadow-lg p-6 text-center">
                    <h3 class="text-lg font-semibold text-gray-800">Cours Actifs</h3>
                    <p class="text-3xl font-bold text-indigo-600">8</p>
                    <p class="text-sm text-gray-500">Dans la spécialité</p>
                </div>
            </div>

            <!-- Modules -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white rounded-xl shadow-lg p-6 text-center transition transform hover:scale-105">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Liste des Étudiants</h2>
                    <p class="text-gray-600 mb-4">Consultez les informations des étudiants de la spécialité.</p>
                    <a href="/specialite/etudiant" class="inline-block bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-200">Accéder</a>
                </div>
                <div class="bg-white rounded-xl shadow-lg p-6 text-center transition transform hover:scale-105">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Cours</h2>
                    <p class="text-gray-600 mb-4">Gérez les cours associés à la spécialité.</p>
                    <a href="/specialite/cours" class="inline-block bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-200">Accéder</a>
                </div>
                <div class="bg-white rounded-xl shadow-lg p-6 text-center transition transform hover:scale-105">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Rapports</h2>
                    <p class="text-gray-600 mb-4">Consultez vos rapports soumis et rédigez de nouveaux rapports semestriels.</p>
                    <a href="/specialite/rapport" class="inline-block bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-200">Accéder</a>
                </div>
                <div class="bg-white rounded-xl shadow-lg p-6 text-center transition transform hover:scale-105">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Présences</h2>
                    <p class="text-gray-600 mb-4">Suivez les présences des étudiants aux cours.</p>
                    <a href="/specialite/presence" class="inline-block bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-200">Accéder</a>
                </div>
            </div>
        </div>
    @endvolt
</x-layouts.app>

