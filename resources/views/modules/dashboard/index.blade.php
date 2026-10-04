
<?php
use function Laravel\Folio\{name,middleware};
use Livewire\Volt\Component;
use App\Http\Middleware\RoleBasedRedirect;

name('dashboard.index');
middleware(['auth','verified', RoleBasedRedirect::class])
?>
<x-layouts.app>
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <header class="mb-10">
                <h1 class="text-4xl font-extrabold text-gray-800 tracking-tight">Bienvenue à l'ISM NDAZOA</h1>
                <p class="mt-2 text-lg text-gray-500">Gérez votre parcours académique avec élégance et efficacité.</p>
            </header>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Compte Étudiant -->
                <div class="bg-white rounded-xl shadow-lg p-6 text-center transition transform hover:scale-105">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Mon Compte Étudiant</h2>
                    <p class="text-gray-600 mb-4">Accédez à vos informations personnelles et académiques.</p>
                    <a href="dashboard/compte" class="inline-block bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-200">Voir mon compte</a>
                </div>

                <!-- Barbillard Numérique -->
                <div class="bg-white rounded-xl shadow-lg p-6 text-center transition transform hover:scale-105">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Barbillard Numérique</h2>
                    <p class="text-gray-600 mb-4">Consultez les annonces et communications officielles.</p>
                    <a href="dashboard/barbillard" class="inline-block bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-200">Voir les annonces</a>
                </div>

                <!-- Cours & Calendrier -->
                <div class="bg-white rounded-xl shadow-lg p-6 text-center transition transform hover:scale-105">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Mes Cours & Calendrier</h2>
                    <p class="text-gray-600 mb-4">Planifiez et suivez vos cours et emplois du temps.</p>
                    <a href="dashboard/cours" class="inline-block bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-200">Gérer mes cours</a>
                </div>

                <!-- Gestion Financière -->
                <div class="bg-white rounded-xl shadow-lg p-6 text-center transition transform hover:scale-105">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Gestion Financière</h2>
                    <p class="text-gray-600 mb-4">Suivez et gérez vos frais de scolarité.</p>
                    <a href="dashboard/finance" class="inline-block bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-200">Gérer les finances</a>
                </div>

                <!-- Gestion des Filières -->
                <div class="bg-white rounded-xl shadow-lg p-6 text-center transition transform hover:scale-105">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Gestion des Filières</h2>
                    <p class="text-gray-600 mb-4">Explorez les détails de votre filière académique.</p>
                    <a href="dashboard/filiere" class="inline-block bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-200">Gérer ma filière</a>
                </div>

                <!-- Bibliothèque -->
                <div class="bg-white rounded-xl shadow-lg p-6 text-center transition transform hover:scale-105">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Bibliothèque</h2>
                    <p class="text-gray-600 mb-4">Consultez le catalogue et gérez vos emprunts.</p>
                    <a href="dashboard/bibliotheque" class="inline-block bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-200">Accéder à la bibliothèque</a>
                </div>

                <!-- Examens -->
                <div class="bg-white rounded-xl shadow-lg p-6 text-center transition transform hover:scale-105">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Examens</h2>
                    <p class="text-gray-600 mb-4">Consultez les plannings et résultats des examens.</p>
                    <a href="dashboard/examen" class="inline-block bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-200">Gérer les examens</a>
                </div>

                <!-- Vie Étudiante -->
                <div class="bg-white rounded-xl shadow-lg p-6 text-center transition transform hover:scale-105">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Vie Étudiante</h2>
                    <p class="text-gray-600 mb-4">Participez aux événements et clubs universitaires.</p>
                    <a href="dashboard/vie_scolaire" class="inline-block bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-200">Découvrir les activités</a>
                </div>

                  <div class="bg-white rounded-xl shadow-lg p-6 text-center transition transform hover:scale-105">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Support & Assistance</h2>
                    <p class="text-gray-600 mb-4">Obtenez de l'aide pour vos questions administratives.</p>
                    <a href="dashboard/support" class="inline-block bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-200">Contacter le support</a>
                </div>
            </div>
        </div>
    @endvolt
</x-layouts.app>
