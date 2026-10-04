
<?php
use function Laravel\Folio\{name,middleware};
use Livewire\Volt\Component;
name('admin.communication');
middleware(['auth','verified'])
?>
<x-layouts.app>
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <header class="mb-12 text-center">
                <h1 class="text-4xl font-bold text-gray-900 bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent tracking-tight">Gestion de la Communication</h1>
                <p class="mt-3 text-lg text-gray-600">Envoyez des emails aux étudiants et au personnel.</p>
            </header>

            <!-- Formulaire Envoyer Email -->
            <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Envoyer un Email</h2>
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Destinataires</label>
                        <select class="mt-1 w-full p-3 border border-gray-300 rounded-lg">
                            <option value="all">Tous (Étudiants et Personnel)</option>
                            <option value="etudiants">Étudiants uniquement</option>
                            <option value="personnel">Personnel uniquement</option>
                            <option value="dev">Étudiants : Développement Logiciel</option>
                            <option value="sophie-mballa">Dr. Sophie Mballa</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Sujet</label>
                        <input type="text" value="Mise à jour du calendrier académique" class="mt-1 w-full p-3 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Contenu</label>
                        <textarea class="mt-1 w-full p-3 border border-gray-300 rounded-lg" rows="6">Veuillez consulter le nouveau calendrier académique pour le semestre 1, 2025, disponible sur la plateforme.</textarea>
                    </div>
                </div>
                <div class="mt-8 flex justify-end space-x-4">
                    <button class="bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-300">Envoyer</button>
                    <button class="bg-gray-600 text-white py-2 px-6 rounded-lg hover:bg-gray-700 transition duration-300">Annuler</button>
                </div>
            </div>

            <!-- Historique des Emails -->
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Historique des Emails</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-2">
                        <thead>
                            <tr class="bg-gray-100 rounded-lg">
                                <th class="p-4 text-sm font-medium text-gray-600">Sujet</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Destinataires</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Date</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">Mise à jour du calendrier académique</td>
                                <td class="p-4">Tous</td>
                                <td class="p-4">09/07/2025</td>
                                <td class="p-4">
                                    <a href="#" class="text-indigo-600 hover:underline">Voir détails</a>
                                </td>
                            </tr>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">Rappel : Frais de scolarité</td>
                                <td class="p-4">Étudiants</td>
                                <td class="p-4">08/07/2025</td>
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

