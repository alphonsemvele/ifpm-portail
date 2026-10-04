
<?php
use function Laravel\Folio\{name,middleware};
use Livewire\Volt\Component;
name('admin.annonces');
middleware(['auth','verified'])
?>
<x-layouts.app>
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <header class="mb-12 text-center">
                <h1 class="text-4xl font-bold text-gray-900 bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent tracking-tight">Gestion des Annonces</h1>
                <p class="mt-3 text-lg text-gray-600">Créez, visualisez et validez des annonces pour le tableau d'affichage.</p>
            </header>

            <!-- Formulaire Créer/Modifier Annonce -->
            <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Créer ou Modifier une Annonce</h2>
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Titre</label>
                        <input type="text" value="Réunion Générale du Semestre" class="mt-1 w-full p-3 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Contenu</label>
                        <textarea class="mt-1 w-full p-3 border border-gray-300 rounded-lg" rows="6">Réunion générale pour tous les étudiants et le personnel le 15/07/2025 à 10h dans l'amphithéâtre A.</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Date de Publication</label>
                        <input type="date" value="2025-07-10" class="mt-1 w-full p-3 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Public Cible</label>
                        <select class="mt-1 w-full p-3 border border-gray-300 rounded-lg">
                            <option value="all">Tous (Étudiants et Personnel)</option>
                            <option value="etudiants">Étudiants uniquement</option>
                            <option value="personnel">Personnel uniquement</option>
                        </select>
                    </div>
                </div>
                <div class="mt-8 flex justify-end space-x-4">
                    <button class="bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-300">Prévisualiser</button>
                    <button class="bg-green-600 text-white py-2 px-6 rounded-lg hover:bg-green-700 transition duration-300">Valider</button>
                    <button class="bg-gray-600 text-white py-2 px-6 rounded-lg hover:bg-gray-700 transition duration-300">Annuler</button>
                </div>
            </div>

            <!-- Liste des Annonces -->
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Liste des Annonces</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-2">
                        <thead>
                            <tr class="bg-gray-100 rounded-lg">
                                <th class="p-4 text-sm font-medium text-gray-600">Titre</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Date</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Public</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Statut</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">Réunion Générale du Semestre</td>
                                <td class="p-4">10/07/2025</td>
                                <td class="p-4">Tous</td>
                                <td class="p-4 text-green-600">Validée</td>
                                <td class="p-4">
                                    <a href="#" class="text-indigo-600 hover:underline">Modifier</a>
                                    <a href="#" class="text-red-600 hover:underline ml-2">Supprimer</a>
                                </td>
                            </tr>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">Atelier sur la Cybersécurité</td>
                                <td class="p-4">12/07/2025</td>
                                <td class="p-4">Étudiants</td>
                                <td class="p-4 text-yellow-600">En attente</td>
                                <td class="p-4">
                                    <a href="#" class="text-indigo-600 hover:underline">Modifier</a>
                                    <a href="#" class="text-red-600 hover:underline ml-2">Supprimer</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endvolt
</x-layouts.app>

