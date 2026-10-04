
<?php
use function Laravel\Folio\{name,middleware};
use Livewire\Volt\Component;
name('admin.cours-calendrier');
middleware(['auth','verified'])
?>
<x-layouts.app>
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <header class="mb-12 text-center">
                <h1 class="text-4xl font-bold text-gray-900 bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent tracking-tight">Gestion des Cours et Calendrier</h1>
                <p class="mt-3 text-lg text-gray-600">Configurez les cours, le calendrier et affectez un personnel aux cours.</p>
            </header>

            <!-- Formulaire Créer/Modifier Cours -->
            <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Ajouter ou Modifier un Cours</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Nom du Cours</label>
                        <input type="text" value="Algorithmique Avancée" class="mt-1 w-full p-3 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Filière</label>
                        <select class="mt-1 w-full p-3 border border-gray-300 rounded-lg">
                            <option value="informatique">Informatique</option>
                            <option value="gestion">Gestion</option>
                            <option value="sante">Santé</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Spécialité</label>
                        <select class="mt-1 w-full p-3 border border-gray-300 rounded-lg">
                            <option value="dev">Développement Logiciel</option>
                            <option value="cyber">Cybersécurité</option>
                            <option value="ia">Intelligence Artificielle</option>
                            <option value="reseaux">Réseaux et Télécommunications</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Responsable</label>
                        <select class="mt-1 w-full p-3 border border-gray-300 rounded-lg">
                            <option value="sophie-mballa">Dr. Sophie Mballa</option>
                            <option value="paul-ekomo">Prof. Paul Ekomo</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Date de Début</label>
                        <input type="date" value="2025-09-01" class="mt-1 w-full p-3 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Date de Fin</label>
                        <input type="date" value="2025-12-15" class="mt-1 w-full p-3 border border-gray-300 rounded-lg">
                    </div>
                </div>
                <div class="mt-8 flex justify-end space-x-4">
                    <button class="bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-300">Enregistrer</button>
                    <button class="bg-gray-600 text-white py-2 px-6 rounded-lg hover:bg-gray-700 transition duration-300">Annuler</button>
                </div>
            </div>

            <!-- Liste des Cours -->
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Liste des Cours</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-2">
                        <thead>
                            <tr class="bg-gray-100 rounded-lg">
                                <th class="p-4 text-sm font-medium text-gray-600">Cours</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Filière</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Spécialité</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Responsable</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Période</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">Algorithmique Avancée</td>
                                <td class="p-4">Informatique</td>
                                <td class="p-4">Développement Logiciel</td>
                                <td class="p-4">Dr. Sophie Mballa</td>
                                <td class="p-4">01/09/2025 - 15/12/2025</td>
                                <td class="p-4">
                                    <a href="#" class="text-indigo-600 hover:underline">Modifier</a>
                                    <a href="#" class="text-red-600 hover:underline ml-2">Supprimer</a>
                                </td>
                            </tr>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">Sécurité des Systèmes</td>
                                <td class="p-4">Informatique</td>
                                <td class="p-4">Cybersécurité</td>
                                <td class="p-4">Prof. Paul Ekomo</td>
                                <td class="p-4">01/09/2025 - 15/12/2025</td>
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

