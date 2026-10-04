
<?php
use function Laravel\Folio\{name,middleware};
use Livewire\Volt\Component;
name('admin.personnel');
middleware(['auth','verified'])
?>
<x-layouts.app>
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <header class="mb-12 text-center">
                <h1 class="text-4xl font-bold text-gray-900 bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent tracking-tight">Gestion du Personnel</h1>
                <p class="mt-3 text-lg text-gray-600">Créez, modifiez, supprimez des responsables et affectez-les à une filière ou spécialité.</p>
            </header>

            <!-- Formulaire Créer/Modifier Personnel -->
            <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Ajouter ou Modifier un Responsable</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Nom Complet</label>
                        <input type="text" value="Dr. Sophie Mballa" class="mt-1 w-full p-3 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Rôle</label>
                        <select class="mt-1 w-full p-3 border border-gray-300 rounded-lg">
                            <option value="resp-filiere">Responsable de Filière</option>
                            <option value="resp-specialite">Responsable de Spécialité</option>
                            <option value="enseignant">Enseignant</option>
                        </select>
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
                        <label class="block text-sm font-medium text-gray-600">Spécialité (optionnel)</label>
                        <select class="mt-1 w-full p-3 border border-gray-300 rounded-lg">
                            <option value="">Aucune</option>
                            <option value="dev">Développement Logiciel</option>
                            <option value="cyber">Cybersécurité</option>
                            <option value="ia">Intelligence Artificielle</option>
                            <option value="reseaux">Réseaux et Télécommunications</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Email</label>
                        <input type="email" value="sophie.mballa@ismndazoa.edu" class="mt-1 w-full p-3 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Téléphone</label>
                        <input type="text" value="+237 600 987 654" class="mt-1 w-full p-3 border border-gray-300 rounded-lg">
                    </div>
                </div>
                <div class="mt-8 flex justify-end space-x-4">
                    <button class="bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-300">Enregistrer</button>
                    <button class="bg-gray-600 text-white py-2 px-6 rounded-lg hover:bg-gray-700 transition duration-300">Annuler</button>
                </div>
            </div>

            <!-- Liste du Personnel -->
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Liste du Personnel</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-2">
                        <thead>
                            <tr class="bg-gray-100 rounded-lg">
                                <th class="p-4 text-sm font-medium text-gray-600">Nom</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Rôle</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Filière</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Spécialité</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">Dr. Sophie Mballa</td>
                                <td class="p-4">Responsable de Spécialité</td>
                                <td class="p-4">Informatique</td>
                                <td class="p-4">Développement Logiciel</td>
                                <td class="p-4">
                                    <a href="#" class="text-indigo-600 hover:underline">Modifier</a>
                                    <a href="#" class="text-red-600 hover:underline ml-2">Supprimer</a>
                                </td>
                            </tr>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">Prof. Paul Ekomo</td>
                                <td class="p-4">Responsable de Filière</td>
                                <td class="p-4">Informatique</td>
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

