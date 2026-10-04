
<?php
use function Laravel\Folio\{name,middleware};
use Livewire\Volt\Component;
name('admin.support');
middleware(['auth','verified'])
?>
<x-layouts.app>
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <header class="mb-12 text-center">
                <h1 class="text-4xl font-bold text-gray-900 bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent tracking-tight">Gestion des Requêtes de Support</h1>
                <p class="mt-3 text-lg text-gray-600">Consultez les requêtes de support et les informations des émetteurs.</p>
            </header>

            <!-- Liste des Requêtes -->
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Requêtes de Support</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-2">
                        <thead>
                            <tr class="bg-gray-100 rounded-lg">
                                <th class="p-4 text-sm font-medium text-gray-600">Émetteur</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Rôle</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Problème</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Date</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Statut</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">Marie Ngo</td>
                                <td class="p-4">Étudiant</td>
                                <td class="p-4">Problème d'accès à la plateforme</td>
                                <td class="p-4">09/07/2025</td>
                                <td class="p-4 text-yellow-600">En attente</td>
                                <td class="p-4">
                                    <a href="#" class="text-indigo-600 hover:underline">Voir détails</a>
                                    <a href="#" class="text-green-600 hover:underline ml-2">Résoudre</a>
                                </td>
                            </tr>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">Dr. Sophie Mballa</td>
                                <td class="p-4">Responsable de Spécialité</td>
                                <td class="p-4">Erreur dans le rapport semestriel</td>
                                <td class="p-4">08/07/2025</td>
                                <td class="p-4 text-green-600">Résolu</td>
                                <td class="p-4">
                                    <a href="#" class="text-indigo-600 hover:underline">Voir détails</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endvolt
</x-layouts.app>

