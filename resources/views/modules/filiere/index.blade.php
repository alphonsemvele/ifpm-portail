
<?php
use function Laravel\Folio\{name,middleware};
use Livewire\Volt\Component;
name('filiere.index');
middleware(['auth','verified'])
?>
<x-layouts.app>
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <header class="mb-10">
                <h1 class="text-4xl font-extrabold text-gray-800 tracking-tight">Gestion des Spécialités</h1>
                <p class="mt-2 text-lg text-gray-500">Administrez les spécialités de votre filière et supervisez le personnel avec efficacité.</p>
            </header>

            <!-- Statistiques -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-indigo-50 rounded-xl shadow-lg p-6 text-center">
                    <h3 class="text-lg font-semibold text-gray-800">Étudiants Inscrits</h3>
                    <p class="text-3xl font-bold text-indigo-600">350</p>
                    <p class="text-sm text-gray-500">Total dans la filière</p>
                </div>
                <div class="bg-indigo-50 rounded-xl shadow-lg p-6 text-center">
                    <h3 class="text-lg font-semibold text-gray-800">Spécialités Actives</h3>
                    <p class="text-3xl font-bold text-indigo-600">4</p>
                    <p class="text-sm text-gray-500">Dans la filière</p>
                </div>
                <div class="bg-indigo-50 rounded-xl shadow-lg p-6 text-center">
                    <h3 class="text-lg font-semibold text-gray-800">Membres du Personnel</h3>
                    <p class="text-3xl font-bold text-indigo-600">25</p>
                    <p class="text-sm text-gray-500">Enseignants et coordinateurs</p>
                </div>
            </div>

            <!-- Sous-modules -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white rounded-xl shadow-lg p-6 text-center transition transform hover:scale-105">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Spécialités</h2>
                    <p class="text-gray-600 mb-4">Gérez les spécialités de la filière, ajoutez ou modifiez des programmes.</p>
                    <a href="filiere/specialite/index" class="inline-block bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-200">Accéder</a>
                </div>
                <div class="bg-white rounded-xl shadow-lg p-6 text-center transition transform hover:scale-105">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Personnel</h2>
                    <p class="text-gray-600 mb-4">Supervisez les enseignants et coordinateurs affectés aux spécialités.</p>
                    <a href="filiere/personnel/index" class="inline-block bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-200">Accéder</a>
                </div>
                <div class="bg-white rounded-xl shadow-lg p-6 text-center transition transform hover:scale-105">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Statistiques Détaillées</h2>
                    <p class="text-gray-600 mb-4">Analysez les données par spécialité (étudiants, cours, performance).</p>
                    <a href="filiere/statistique/index" class="inline-block bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-200">Accéder</a>
                </div>
                <div class="bg-white rounded-xl shadow-lg p-6 text-center transition transform hover:scale-105">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Cours par Spécialité</h2>
                    <p class="text-gray-600 mb-4">Gérez les cours associés à chaque spécialité.</p>
                    <a href="filiere/cours/index" class="inline-block bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-200">Accéder</a>
                </div>
                <div class="bg-white rounded-xl shadow-lg p-6 text-center transition transform hover:scale-105">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Liste des Étudiants</h2>
                    <p class="text-gray-600 mb-4">Consultez les informations des étudiants par spécialité.</p>
                    <a href="filiere/etudiant/index" class="inline-block bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-200">Accéder</a>
                </div>
                 <div class="bg-white rounded-xl shadow-lg p-6 text-center transition transform hover:scale-105">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Présences</h2>
                    <p class="text-gray-600 mb-4">Consultez les statistiques de présence et d'absence par spécialité et par étudiant.</p>
                    <a href="/filiere/presence" class="inline-block bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-200">Accéder</a>
                </div>

                 <div class="bg-white rounded-xl shadow-lg p-6 text-center transition transform hover:scale-105">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Rapports</h2>
                    <p class="text-gray-600 mb-4">Soumettez et consultez vos rapports semestriels.</p>
                    <a href="filiere/rapport" class="inline-block bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-200">Gérer les rapports</a>
                </div>




            </div>
        </div>
    @endvolt
</x-layouts.app>

