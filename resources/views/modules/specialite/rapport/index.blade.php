
<?php
use function Laravel\Folio\{name,middleware};
use Livewire\Volt\Component;
name('specialite.rapports');
middleware(['auth','verified'])
?>
<x-layouts.app>
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <header class="mb-10">
                <h1 class="text-4xl font-extrabold text-gray-800 tracking-tight">Rapports Semestriels</h1>
                <p class="mt-2 text-lg text-gray-500">Consultez vos rapports soumis et rédigez de nouveaux rapports pour la spécialité : Développement Logiciel</p>
            </header>

            <!-- Statistiques -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-indigo-50 rounded-xl shadow-lg p-6 text-center">
                    <h3 class="text-lg font-semibold text-gray-800">Rapports Soumis</h3>
                    <p class="text-3xl font-bold text-indigo-600">2</p>
                    <p class="text-sm text-gray-500">Pour l'année 2025</p>
                </div>
                <div class="bg-indigo-50 rounded-xl shadow-lg p-6 text-center">
                    <h3 class="text-lg font-semibold text-gray-800">Rapports en Attente</h3>
                    <p class="text-3xl font-bold text-indigo-600">1</p>
                    <p class="text-sm text-gray-500">Pour le semestre actuel</p>
                </div>
                <div class="bg-indigo-50 rounded-xl shadow-lg p-6 text-center">
                    <h3 class="text-lg font-semibold text-gray-800">Rapports en Retard</h3>
                    <p class="text-3xl font-bold text-indigo-600">0</p>
                    <p class="text-sm text-gray-500">Pour l'année 2025</p>
                </div>
            </div>

            <!-- Liste des Rapports Soumis -->
            <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Vos Rapports Soumis</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-2">
                        <thead>
                            <tr class="bg-gray-100 rounded-lg">
                                <th class="p-4 text-sm font-medium text-gray-600">Semestre</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Responsable</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Date de Soumission</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Statut</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">Semestre 1, 2025</td>
                                <td class="p-4">Dr. Sophie Mballa</td>
                                <td class="p-4">15/06/2025</td>
                                <td class="p-4 text-green-600">Soumis</td>
                                <td class="p-4">
                                    <a href="#" class="text-indigo-600 hover:underline">Voir</a>
                                    <a href="#" class="text-indigo-600 hover:underline ml-2">Télécharger</a>
                                </td>
                            </tr>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">Semestre 2, 2024</td>
                                <td class="p-4">Dr. Sophie Mballa</td>
                                <td class="p-4">10/12/2024</td>
                                <td class="p-4 text-green-600">Soumis</td>
                                <td class="p-4">
                                    <a href="#" class="text-indigo-600 hover:underline">Voir</a>
                                    <a href="#" class="text-indigo-600 hover:underline ml-2">Télécharger</a>
                                </td>
                            </tr>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">Semestre 2, 2025</td>
                                <td class="p-4">Dr. Sophie Mballa</td>
                                <td class="p-4">-</td>
                                <td class="p-4 text-yellow-600">En attente</td>
                                <td class="p-4">
                                    <a href="#" class="text-indigo-600 hover:underline">Voir brouillon</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Rédiger un Nouveau Rapport -->
            <div class="bg-white rounded-xl shadow-lg p-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Rédiger un Nouveau Rapport Semestriel</h2>
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Semestre</label>
                        <select class="mt-1 w-full p-3 border border-gray-300 rounded-lg">
                            <option value="s2-2025">Semestre 2, 2025</option>
                            <option value="s1-2026">Semestre 1, 2026</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Titre du Rapport</label>
                        <input type="text" value="Rapport Semestriel - Développement Logiciel" class="mt-1 w-full p-3 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Contenu du Rapport</label>
                        <textarea class="mt-1 w-full p-3 border border-gray-300 rounded-lg" rows="6">Résumé des activités académiques, performances des étudiants, et recommandations pour la spécialité.</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Fichier (PDF)</label>
                        <input type="file" accept=".pdf" class="mt-1 w-full p-3 border border-gray-300 rounded-lg">
                    </div>
                </div>
                <div class="mt-8 flex justify-end">
                    <button class="bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-200">Soumettre le rapport</button>
                </div>
            </div>
        </div>
    @endvolt
</x-layouts.app>

