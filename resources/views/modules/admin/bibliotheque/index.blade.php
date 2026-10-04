
<?php
use function Laravel\Folio\{name,middleware};
use Livewire\Volt\Component;
name('admin.bibliotheque');
middleware(['auth','verified'])
?>
<x-layouts.app>
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <header class="mb-12 text-center">
                <h1 class="text-4xl font-bold text-gray-900 bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent tracking-tight">Gestion de la Bibliothèque</h1>
                <p class="mt-3 text-lg text-gray-600">Gérez les livres, suivez les emprunts et mettez à jour les statuts.</p>
            </header>

            <!-- Formulaire Ajouter/Modifier Livre -->
            <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Ajouter ou Modifier un Livre</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Titre</label>
                        <input type="text" value="Introduction à l'Algorithmique" class="mt-1 w-full p-3 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Auteur</label>
                        <input type="text" value="Thomas Cormen" class="mt-1 w-full p-3 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">ISBN</label>
                        <input type="text" value="978-0262033848" class="mt-1 w-full p-3 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Statut</label>
                        <select class="mt-1 w-full p-3 border border-gray-300 rounded-lg">
                            <option value="disponible">Disponible</option>
                            <option value="emprunte">Emprunté</option>
                        </select>
                    </div>
                </div>
                <div class="mt-8 flex justify-end space-x-4">
                    <button class="bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-300">Enregistrer</button>
                    <button class="bg-gray-600 text-white py-2 px-6 rounded-lg hover:bg-gray-700 transition duration-300">Annuler</button>
                </div>
            </div>

            <!-- Liste des Livres -->
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Liste des Livres</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-2">
                        <thead>
                            <tr class="bg-gray-100 rounded-lg">
                                <th class="p-4 text-sm font-medium text-gray-600">Titre</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Auteur</th>
                                <th class="p-4 text-sm font-medium text-gray-600">ISBN</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Statut</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Emprunteur</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">Introduction à l'Algorithmique</td>
                                <td class="p-4">Thomas Cormen</td>
                                <td class="p-4">978-0262033848</td>
                                <td class="p-4 text-yellow-600">Emprunté</td>
                                <td class="p-4">Marie Ngo</td>
                                <td class="p-4">
                                    <a href="#" class="text-indigo-600 hover:underline">Modifier</a>
                                    <a href="#" class="text-green-600 hover:underline ml-2">Marquer comme rendu</a>
                                </td>
                            </tr>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">Cybersécurité Fondamentale</td>
                                <td class="p-4">William Stallings</td>
                                <td class="p-4">978-0135647073</td>
                                <td class="p-4 text-green-600">Disponible</td>
                                <td class="p-4">-</td>
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

